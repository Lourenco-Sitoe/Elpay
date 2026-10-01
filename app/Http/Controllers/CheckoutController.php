<?php

namespace App\Http\Controllers;

use App\Mail\SaleConfirmationMail;
use App\Models\Affiliate;
use App\Models\Bump;
use App\Models\Commission;
use App\Models\Coupon;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Sale;
use App\Models\Upsell;
use App\Models\User;
use App\Services\Payments\PaymentGatewayManager;
use App\Traits\LogsActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    use LogsActivity;

    public function show(string $slug, Request $request): View
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        abort_if(($product->status ?? 'approved') !== 'approved', 404);

        // Tracking de afiliado: ?ref=CODE -> cookie aff_{productId} 30 dias.
        if ($request->filled('ref')) {
            cookie()->queue(cookie('aff_' . $product->id, $request->get('ref'), 60 * 24 * 30));
        }
        // UTM: guarda para atribuir à venda no process().
        if ($request->query('utm_source') || $request->query('utm_medium') || $request->query('utm_campaign')) {
            session(['utm' => $request->only(['utm_source', 'utm_medium', 'utm_campaign'])]);
        }

        $bumps = Bump::where('main_product_id', $product->id)->where('active', true)->with('bumpProduct')->get();

        return view('checkout.show', ['product' => $product, 'bumps' => $bumps, 'ref' => $request->get('ref')]);
    }

    public function process(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'payment_method' => 'required|in:mpesa,emola',
            'bumps' => 'nullable|array',
            'bumps.*' => 'exists:bumps,id',
            'coupon' => 'nullable|string|max:50',
        ]);

        $product = Product::findOrFail($data['product_id']);
        abort_if(($product->status ?? 'approved') !== 'approved', 404);

        // Afiliado: param ref > session > cookie.
        $ref = $request->get('ref') ?? session('aff_' . $product->id) ?? $request->cookie('aff_' . $product->id);
        $affiliate = $ref ? Affiliate::where('affiliate_code', $ref)->where('product_id', $product->id)->first() : null;

        // Cupom.
        $coupon = null;
        $discount = 0;
        if (! empty($data['coupon'])) {
            $coupon = Coupon::where('code', $data['coupon'])->first();
            $check = $this->couponIsValid($coupon, $product);
            if (! $check['valid']) {
                return back()->withErrors(['coupon' => $check['message']])->withInput();
            }
            $discount = round($product->price * ($coupon->percent / 100), 2);
        }

        // Bumps (vendas separadas por bump, conforme §6.1).
        $bumps = collect();
        $bumpIds = $data['bumps'] ?? [];
        if ($bumpIds) {
            $bumps = Bump::whereIn('id', $bumpIds)
                ->where('main_product_id', $product->id)
                ->where('active', true)
                ->with('bumpProduct')
                ->get();
        }

        $total = max(0, round($product->price - $discount, 2));

        // Cliente: localiza ou cria (guest checkout).
        $isGuest = false;
        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            $isGuest = true;
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make(Str::random(16)),
                'type' => 'client',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        $sale = Sale::create([
            'product_id' => $product->id,
            'buyer_id' => $user->id,
            'producer_id' => $product->producer_id,
            'affiliate_id' => $affiliate?->user_id,
            'price' => $product->price,
            'discount' => $discount,
            'final_price' => $total,
            'status' => 'pending',
            'payment_method' => $data['payment_method'],
            'buyer_name' => $data['name'],
            'buyer_email' => $data['email'],
            'buyer_phone' => $data['phone'],
            'coupon_id' => $coupon?->id,
            'affiliate_code' => $affiliate?->affiliate_code,
            'meta' => ['bumps' => $bumps->pluck('id')->values()->all(), 'utm' => session('utm', [])],
            'is_upsell' => false,
        ]);

        // Pagamento via gateway (mock aprova de imediato; mpesa/emola ficam pendentes).
        try {
            $result = PaymentGatewayManager::driver()->initiate($sale, $data['phone']);
        } catch (\Throwable $e) {
            $sale->delete();

            return back()->withErrors(['payment_method' => 'Pagamento indisponível: ' . $e->getMessage()])->withInput();
        }

        if (! $result->approved()) {
            Payment::create([
                'sale_id' => $sale->id,
                'amount' => $total,
                'method' => $data['payment_method'],
                'status' => 'pending',
                'transaction_ref' => $result->reference,
                'phone' => $data['phone'],
            ]);
            session(['last_sale_id' => $sale->id, 'is_guest_' . $sale->id => $isGuest]);

            return redirect()->route('checkout.success', $sale->id)
                ->with('info', $result->message ?? 'Aguarda a confirmação do pagamento no teu telemóvel.');
        }

        $this->finalizeSale($sale, $result->reference);

        session(['last_sale_id' => $sale->id, 'is_guest_' . $sale->id => $isGuest]);

        return redirect()->route('checkout.success', $sale->id);
    }

    /** Marca venda como paga e executa todos os efeitos (pagamento, acesso, saldos, bumps, comissão). */
    public function finalizeSale(Sale $sale, ?string $reference = null): void
    {
        if ($sale->status === 'paid') {
            return;
        }

        $sale->loadMissing(['product', 'coupon']);
        $product = $sale->product;
        $total = (float) $sale->final_price;
        $affiliate = $sale->affiliate_code
            ? Affiliate::where('affiliate_code', $sale->affiliate_code)->where('product_id', $sale->product_id)->first()
            : null;

        $sale->status = 'paid';
        $sale->save();

        // Coprodução: divide com parceiros aprovados antes de creditar o produtor.
        $partnerShareTotal = 0;
        if ($product) {
            $partners = \App\Models\ProductPartner::where('product_id', $sale->product_id)
                ->where('status', 'approved')->get();
            foreach ($partners as $pp) {
                $pAmount = round($total * ((float) $pp->share_percent / 100), 2);
                if ($pAmount <= 0) {
                    continue;
                }
                \App\Models\PartnerEarning::create([
                    'sale_id' => $sale->id,
                    'product_id' => $sale->product_id,
                    'partner_id' => $pp->user_id,
                    'share_percent' => $pp->share_percent,
                    'amount' => $pAmount,
                    'status' => 'pending',
                ]);
                $partnerShareTotal += $pAmount;
                if ($pUser = User::find($pp->user_id)) {
                    $pUser->increment('balance', $pAmount);
                }
            }
        }

        Payment::create([
            'sale_id' => $sale->id,
            'amount' => $total,
            'method' => $sale->payment_method,
            'status' => 'approved',
            'transaction_ref' => $reference,
            'phone' => $sale->buyer_phone,
            'approved_at' => now(),
        ]);

        ProductAccess::firstOrCreate([
            'user_id' => $sale->buyer_id,
            'product_id' => $sale->product_id,
            'sale_id' => $sale->id,
        ]);

        // Credita produtor (líquido da parte dos parceiros).
        $producer = User::find($sale->producer_id);
        if ($producer) {
            $producer->increment('balance', max(0, $total - $partnerShareTotal));
        }

        // Comissão do afiliado.
        if ($affiliate && $product) {
            $rate = $affiliate->custom_rate ?? $product->commission_rate ?? 0;
            $amount = round($total * ($rate / 100), 2);
            if ($amount > 0) {
                Commission::create([
                    'sale_id' => $sale->id,
                    'product_id' => $sale->product_id,
                    'affiliate_id' => $affiliate->user_id,
                    'rate' => $rate,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);
                $affiliate->increment('balance', $amount);
                $affiliate->increment('total_earned', $amount);
                $affiliate->increment('total_sales', 1);
                $affUser = User::find($affiliate->user_id);
                $affUser?->increment('balance', $amount);
            }
        }

        if ($sale->coupon_id && ($coupon = Coupon::find($sale->coupon_id))) {
            $coupon->increment('used_count');
        }
        try {
            $product?->increment('sales_count');
        } catch (\Throwable) {
        }

        // Vendas separadas dos bumps (§6.1): Sale + Payment + ProductAccess cada.
        $bumpIds = $sale->meta['bumps'] ?? [];
        if ($bumpIds && $product) {
            $bumpItems = Bump::whereIn('id', $bumpIds)
                ->where('main_product_id', $product->id)
                ->where('active', true)
                ->with('bumpProduct')
                ->get();
            foreach ($bumpItems as $bump) {
                $bumpProduct = $bump->bumpProduct;
                if (! $bumpProduct) {
                    continue;
                }
                $bumpPrice = (float) $bump->price;
                $bumpSale = Sale::create([
                    'product_id' => $bumpProduct->id,
                    'buyer_id' => $sale->buyer_id,
                    'producer_id' => $bumpProduct->producer_id,
                    'affiliate_id' => $affiliate?->user_id,
                    'price' => $bumpPrice,
                    'discount' => 0,
                    'final_price' => $bumpPrice,
                    'status' => 'paid',
                    'payment_method' => $sale->payment_method,
                    'buyer_name' => $sale->buyer_name,
                    'buyer_email' => $sale->buyer_email,
                    'buyer_phone' => $sale->buyer_phone,
                    'affiliate_code' => $sale->affiliate_code,
                    'parent_sale_id' => $sale->id,
                    'is_upsell' => false,
                ]);
                Payment::create([
                    'sale_id' => $bumpSale->id,
                    'amount' => $bumpPrice,
                    'method' => $sale->payment_method,
                    'status' => 'approved',
                    'transaction_ref' => $reference,
                    'phone' => $sale->buyer_phone,
                    'approved_at' => now(),
                ]);
                ProductAccess::firstOrCreate([
                    'user_id' => $sale->buyer_id,
                    'product_id' => $bumpProduct->id,
                    'sale_id' => $bumpSale->id,
                ]);
                $bumpProducer = User::find($bumpProduct->producer_id);
                $bumpProducer?->increment('balance', $bumpPrice);
                try {
                    $bumpProduct->increment('sales_count');
                } catch (\Throwable) {
                }
            }
        }

        try {
            if ($buyer = User::find($sale->buyer_id)) {
                Mail::to($buyer->email)->send(new SaleConfirmationMail($sale));
            }
        } catch (\Throwable) {
        }

        $this->logActivity('sale.created', 'Venda #' . $sale->id . ' finalizada.', $sale->buyer_id);

        // Webhooks do produtor.
        \App\Services\WebhookDispatcher::dispatch('sale.paid', [
            'sale_id' => $sale->id,
            'product_id' => $sale->product_id,
            'amount' => $total,
            'payment_method' => $sale->payment_method,
            'buyer_email' => $sale->buyer_email,
        ], $sale->producer_id);
        if ($affiliate) {
            \App\Services\WebhookDispatcher::dispatch('commission.created', [
                'sale_id' => $sale->id,
                'affiliate_id' => $affiliate->user_id,
                'amount' => round($total * ((float) ($affiliate->custom_rate ?? $product?->commission_rate ?? 0) / 100), 2),
            ], $sale->producer_id);
        }
    }

    public function success(int $sale, Request $request): View
    {
        $saleModel = Sale::with(['product', 'payments'])->findOrFail($sale);
        $upsell = Upsell::where('main_product_id', $saleModel->product_id)->where('active', true)->with(['upsellProduct', 'downsellProduct'])->first();
        $needsPassword = (bool) session('is_guest_' . $saleModel->id, false);
        $showDownsell = (bool) session('show_downsell_' . $saleModel->id, false) || $request->boolean('downsell');

        return view('checkout.success', [
            'sale' => $saleModel,
            'upsell' => $upsell,
            'needsPassword' => $needsPassword,
            'showDownsell' => $showDownsell,
        ]);
    }

    protected function ownSale(Sale $sale): void
    {
        $mine = auth()->check() && (int) $sale->buyer_id === (int) auth()->id();
        $guestFlow = (int) session('last_sale_id') === (int) $sale->id;
        abort_unless($mine || $guestFlow, 403);
    }

    public function applyUpsell(Request $request, int $sale): RedirectResponse
    {
        $parent = Sale::findOrFail($sale);
        abort_unless($parent->buyer_id, 404);
        $this->ownSale($parent);
        $upsell = Upsell::where('main_product_id', $parent->product_id)->where('active', true)->firstOrFail();
        $upsellProduct = Product::findOrFail($upsell->upsell_product_id);

        $price = $upsell->special_price ?? $upsellProduct->price;

        $child = Sale::create([
            'product_id' => $upsellProduct->id,
            'buyer_id' => $parent->buyer_id,
            'producer_id' => $upsellProduct->producer_id,
            'affiliate_id' => $parent->affiliate_id,
            'price' => $price,
            'discount' => 0,
            'final_price' => $price,
            'status' => 'paid',
            'payment_method' => $parent->payment_method,
            'buyer_name' => $parent->buyer_name,
            'buyer_email' => $parent->buyer_email,
            'buyer_phone' => $parent->buyer_phone,
            'parent_sale_id' => $parent->id,
            'is_upsell' => true,
        ]);

        Payment::create([
            'sale_id' => $child->id,
            'amount' => $price,
            'method' => $parent->payment_method,
            'status' => 'approved',
            'phone' => $parent->buyer_phone,
            'approved_at' => now(),
        ]);

        ProductAccess::firstOrCreate([
            'user_id' => $parent->buyer_id,
            'product_id' => $upsellProduct->id,
            'sale_id' => $child->id,
        ]);

        $producer = User::find($upsellProduct->producer_id);
        $producer?->increment('balance', $price);

        return redirect()->route('checkout.success', $child->id)->with('success', 'Upsell adicionado com sucesso!');
    }

    public function applyDownsell(Request $request, int $sale): RedirectResponse
    {
        $parent = Sale::findOrFail($sale);
        abort_unless($parent->buyer_id, 404);
        $this->ownSale($parent);
        $upsell = Upsell::where('main_product_id', $parent->product_id)->where('active', true)->firstOrFail();
        abort_unless($upsell->downsell_product_id, 404);
        $downsellProduct = Product::findOrFail($upsell->downsell_product_id);
        $price = $upsell->downsell_price ?? $downsellProduct->price;

        $child = Sale::create([
            'product_id' => $downsellProduct->id,
            'buyer_id' => $parent->buyer_id,
            'producer_id' => $downsellProduct->producer_id,
            'affiliate_id' => $parent->affiliate_id,
            'price' => $price,
            'discount' => 0,
            'final_price' => $price,
            'status' => 'paid',
            'payment_method' => $parent->payment_method,
            'buyer_name' => $parent->buyer_name,
            'buyer_email' => $parent->buyer_email,
            'buyer_phone' => $parent->buyer_phone,
            'parent_sale_id' => $parent->id,
            'is_upsell' => true,
        ]);

        Payment::create([
            'sale_id' => $child->id,
            'amount' => $price,
            'method' => $parent->payment_method,
            'status' => 'approved',
            'phone' => $parent->buyer_phone,
            'approved_at' => now(),
        ]);

        ProductAccess::firstOrCreate([
            'user_id' => $parent->buyer_id,
            'product_id' => $downsellProduct->id,
            'sale_id' => $child->id,
        ]);

        $producer = User::find($downsellProduct->producer_id);
        $producer?->increment('balance', $price);

        return redirect()->route('checkout.success', $child->id)->with('success', 'Downsell adicionado com sucesso!');
    }

    public function declineUpsell(Request $request, int $sale): RedirectResponse
    {        $parent = Sale::findOrFail($sale);
        $this->ownSale($parent);
        $upsell = Upsell::where('main_product_id', $parent->product_id)->where('active', true)->first();

        if ($upsell && $upsell->downsell_product_id) {
            session(['show_downsell_' . $parent->id => true]);

            return redirect()->route('checkout.success', $parent->id)->with('info', 'Que tal esta alternativa mais em conta?');
        }

        return redirect()->route('client.dashboard')->with('success', 'Compra concluída!');
    }

    public function setPassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'password' => 'required|confirmed|min:8',
        ]);
        $sale = Sale::findOrFail($data['sale_id']);
        // Só o próprio guest (mesma sessão da compra) pode definir a password.
        abort_unless((bool) session('is_guest_' . $sale->id, false), 403);
        $user = User::findOrFail($sale->buyer_id);
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('client.dashboard')->with('success', 'Palavra-passe definida. Bem-vindo!');
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string', 'product_id' => 'required|exists:products,id']);
        $product = Product::findOrFail($request->product_id);
        $coupon = Coupon::where('code', $request->code)->first();
        $check = $this->couponIsValid($coupon, $product);

        if (! $check['valid']) {
            return response()->json(['valid' => false, 'message' => $check['message']]);
        }

        $discount = round($product->price * ($coupon->percent / 100), 2);

        return response()->json(['valid' => true, 'discount' => $discount, 'total' => round($product->price - $discount, 2)]);
    }

    protected function couponIsValid($coupon, $product): array
    {
        if (! $coupon) {
            return ['valid' => false, 'message' => 'Cupom inválido.'];
        }
        if (isset($coupon->active) && ! $coupon->active) {
            return ['valid' => false, 'message' => 'Cupom inativo.'];
        }
        if (! empty($coupon->product_id) && (int) $coupon->product_id !== (int) $product->id) {
            return ['valid' => false, 'message' => 'Cupom não válido para este produto.'];
        }
        if (! empty($coupon->expires_at) && now()->gt($coupon->expires_at)) {
            return ['valid' => false, 'message' => 'Cupom expirado.'];
        }
        if (! empty($coupon->max_uses) && ($coupon->used_count ?? 0) >= $coupon->max_uses) {
            return ['valid' => false, 'message' => 'Cupom esgotado.'];
        }

        return ['valid' => true, 'message' => 'OK'];
    }
}
