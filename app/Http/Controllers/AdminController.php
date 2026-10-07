<?php

namespace App\Http\Controllers;

use App\Mail\AffiliateStatusMail;
use App\Mail\ProductApprovedMail;
use App\Mail\UserStatusMail;
use App\Models\ActivityLog;
use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\KycVerification;
use App\Models\Notification;
use App\Models\PartnerEarning;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\WebhookDispatcher;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    use LogsActivity;

    /** Chaves de configuração permitidas — impede injeção de settings arbitrários. */
    public const SETTINGS_ALLOWLIST = [
        'platform_fee_percent',
        'default_commission_rate',
        'min_withdrawal',
        'withdrawal_fee_percent',
        'payment_driver',
        'mpesa_enabled',
        'emola_enabled',
        'site_name',
        'support_email',
        'maintenance',
    ];

    public function dashboard()
    {
        return app(DashboardController::class)->adminDashboard();
    }

    // ---- Users ----
    public function users(Request $request): View
    {
        $users = User::when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%")))
            ->latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function userUpdateStatus(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:active,inactive,banned']);
        if ((int) $user->id === (int) auth()->id() && $data['status'] !== 'active') {
            return back()->withErrors(['status' => 'Não podes desativar a tua própria conta de admin.']);
        }
        $user->status = $data['status'];
        $user->save();

        try {
            Notification::create(['user_id' => $user->id, 'title' => 'Estado da conta', 'message' => 'A sua conta passou a: '.$data['status']]);
            Mail::to($user->email)->send(new UserStatusMail($user, $data['status']));
        } catch (\Throwable) {
        }
        $this->logActivity('admin.user.status', "Utilizador #{$user->id} -> {$data['status']}");

        return back()->with('success', 'Estado atualizado.');
    }

    public function userDestroy(User $user): RedirectResponse
    {
        if ((int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['user' => 'Não podes eliminar a tua própria conta.']);
        }
        $user->delete();
        $this->logActivity('admin.user.deleted', "Utilizador #{$user->id} eliminado.");

        return back()->with('success', 'Utilizador eliminado.');
    }

    public function userCreate(): View
    {
        return view('admin.users-form');
    }

    public function userStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8',
            'type' => 'required|in:admin,producer,affiliate,client',
            'status' => 'required|in:active,inactive,banned',
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'type' => $data['type'],
            'status' => $data['status'],
            'email_verified_at' => now(),
        ]);
        $this->logActivity('admin.user.created', "Utilizador #{$user->id} ({$user->type}) criado.");

        return redirect()->route('admin.users')->with('success', 'Utilizador criado.');
    }

    public function userEdit(User $user): View
    {
        return view('admin.users-form', compact('user'));
    }

    public function userUpdate(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8',
            'type' => 'required|in:admin,producer,affiliate,client',
            'status' => 'required|in:active,inactive,banned',
        ]);
        if ((int) $user->id === (int) auth()->id() && ($data['type'] !== 'admin' || $data['status'] !== 'active')) {
            return back()->withErrors(['user' => 'Não podes remover o teu próprio acesso de admin.']);
        }
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'type' => $data['type'],
            'status' => $data['status'],
        ]);
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        $this->logActivity('admin.user.updated', "Utilizador #{$user->id} atualizado.");

        return redirect()->route('admin.users')->with('success', 'Utilizador atualizado.');
    }

    // ---- Products ----
    public function products(Request $request): View
    {
        $products = Product::with('producer')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15);

        return view('admin.products', [
            'products' => $products,
            'statusLabels' => ProductWizardController::STATUS_LABELS,
        ]);
    }

    /** Pré-visualização do produto para moderação — sem filtro de estado. */
    public function productShow(Product $product): View
    {
        $product->load(['producer', 'modules.lessons', 'coupons', 'affiliates.user']);
        $sales = Sale::where('product_id', $product->id)->latest()->limit(10)->get();

        return view('admin.products-show', compact('product', 'sales'));
    }

    public function productApprove(Product $product): RedirectResponse
    {
        $product->status = 'approved';
        $product->rejection_reason = null;
        $product->save();
        try {
            Mail::to($product->producer->email)->send(new ProductApprovedMail($product, 'approved'));
        } catch (\Throwable) {
        }
        $this->logActivity('admin.product.approved', "Produto #{$product->id} aprovado.");

        return back()->with('success', 'Produto aprovado.');
    }

    public function productReject(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        $product->status = 'rejected';
        $product->rejection_reason = $request->reason;
        $product->save();
        try {
            Mail::to($product->producer->email)->send(new ProductApprovedMail($product, 'rejected', $request->reason));
        } catch (\Throwable) {
        }
        $this->logActivity('admin.product.rejected', "Produto #{$product->id} rejeitado.");

        return back()->with('success', 'Produto rejeitado.');
    }

    public function productSuspend(Product $product): RedirectResponse
    {
        $product->status = 'suspended';
        $product->save();
        $this->logActivity('admin.product.suspended', "Produto #{$product->id} suspenso.");

        return back()->with('success', 'Produto suspenso.');
    }

    /** Admin pode editar qualquer produto (nome, preço, comissão, afiliação, URLs). */
    public function productEdit(Product $product): View
    {
        $product->load('producer');

        return view('admin.products-form', compact('product'));
    }

    public function productUpdate(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'type' => 'required|string|max:50',
            'category' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:90',
            'affiliation_mode' => 'required|in:automatic,manual',
            'affiliation_rules' => 'nullable|string|max:5000',
            'external_url' => 'nullable|url|max:2048',
            'sales_page_url' => 'nullable|url|max:2048',
            'status' => 'required|in:draft,pending,approved,rejected,suspended,banned',
        ]);
        $product->fill($data);
        if (($product->title ?? '') !== ($data['title'] ?? '')) {
            $product->slug = Str::slug($data['title']).'-'.$product->id;
        }
        $product->save();
        $this->logActivity('admin.product.updated', "Produto #{$product->id} editado pelo admin.");

        return redirect()->route('admin.products')->with('success', 'Produto atualizado.');
    }

    public function productDestroy(Product $product): RedirectResponse
    {
        $title = $product->title;
        $product->delete();
        $this->logActivity('admin.product.deleted', "Produto '{$title}' removido pelo admin.");

        return back()->with('success', 'Produto removido.');
    }

    /** Admin gere qualquer curso/área de membros (lista + atalho para conteúdo). */
    public function courses(Request $request): View
    {
        $products = Product::with(['producer', 'modules'])
            ->where('type', 'course')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->q.'%'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.courses', compact('products'));
    }

    // ---- Sales ----
    public function sales(Request $request): View
    {
        $query = Sale::with(['product', 'buyer']);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($w) => $w->where('buyer_name', 'like', "%{$q}%")->orWhere('buyer_email', 'like', "%{$q}%")->orWhere('id', $q));
        }
        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'cancelled', 'refunded'])) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        $sales = (clone $query)->latest()->paginate(15)->withQueryString();

        $base = Sale::query();
        $totalCount = (clone $base)->count();
        $paidCount = (clone $base)->where('status', 'paid')->count();
        $failCount = (clone $base)->whereIn('status', ['cancelled', 'refunded'])->count();
        $moved = (float) (clone $base)->sum('final_price');
        $received = (float) (clone $base)->where('status', 'paid')->sum('final_price');
        $lost = (float) (clone $base)->whereIn('status', ['cancelled', 'refunded'])->sum('final_price');

        return view('admin.sales', compact('sales', 'totalCount', 'paidCount', 'failCount', 'moved', 'received', 'lost'));
    }

    public function saleCancel(Sale $sale): RedirectResponse
    {
        if (in_array($sale->status, ['cancelled', 'refunded'], true)) {
            return back()->with('info', 'Venda já estava '.$sale->status.'.');
        }
        $sale->status = 'cancelled';
        $sale->save();
        $this->logActivity('admin.sale.cancelled', "Venda #{$sale->id} cancelada.");

        return back()->with('success', 'Venda cancelada.');
    }

    public function saleRefund(Sale $sale): RedirectResponse
    {
        if ($sale->status === 'refunded') {
            return back()->with('info', 'Venda já estava reembolsada.');
        }
        if ($sale->status !== 'paid') {
            return back()->withErrors(['sale' => 'Só vendas pagas podem ser reembolsadas.']);
        }
        $sale->status = 'refunded';
        $sale->save();
        $this->logActivity('admin.sale.refunded', "Venda #{$sale->id} reembolsada.");

        return back()->with('success', 'Venda reembolsada.');
    }

    // ---- Payments ----
    public function payments(): View
    {
        $payments = Payment::with('sale')->latest()->paginate(15);

        return view('admin.payments', compact('payments'));
    }

    public function paymentApprove(Payment $payment): RedirectResponse
    {
        if ($payment->status === 'approved') {
            return back()->with('info', 'Pagamento já aprovado.');
        }
        $payment->status = 'approved';
        $payment->approved_at = now();
        $payment->save();
        $sale = $payment->sale;
        if ($sale && $sale->status === 'pending') {
            try {
                app(CheckoutController::class)->finalizeSale($sale, $payment->transaction_ref);
            } catch (\Throwable) {
                $sale->status = 'paid';
                $sale->save();
            }
        }
        $this->logActivity('admin.payment.approved', "Pagamento #{$payment->id} aprovado.");

        return back()->with('success', 'Pagamento aprovado.');
    }

    public function paymentReject(Payment $payment): RedirectResponse
    {
        if (in_array($payment->status, ['rejected', 'approved'], true)) {
            return back()->with('info', 'Pagamento já estava '.$payment->status.'.');
        }
        $payment->status = 'rejected';
        $payment->save();
        if ($payment->sale && $payment->sale->status === 'pending') {
            $payment->sale->status = 'cancelled';
            $payment->sale->save();
        }
        $this->logActivity('admin.payment.rejected', "Pagamento #{$payment->id} rejeitado.");

        return back()->with('success', 'Pagamento rejeitado.');
    }

    // ---- Commissions ----
    public function commissions(): View
    {
        $commissions = Commission::with(['sale', 'product'])->latest()->paginate(15);
        $partnerEarnings = PartnerEarning::with(['product', 'partner'])->latest()->paginate(15, ['*'], 'ppage');

        return view('admin.commissions', compact('commissions', 'partnerEarnings'));
    }

    public function commissionMarkPaid(Commission $commission): RedirectResponse
    {
        if ($commission->status === 'paid') {
            return back()->with('info', 'Comissão já paga.');
        }
        $commission->status = 'paid';
        $commission->save();
        $this->logActivity('admin.commission.paid', "Comissão #{$commission->id} paga.");

        return back()->with('success', 'Comissão marcada como paga.');
    }

    // ---- Affiliates ----
    public function affiliatesAdmin(): View
    {
        $affiliates = Affiliate::with(['user', 'product'])->latest()->paginate(15);

        return view('admin.affiliates', compact('affiliates'));
    }

    public function affiliateApprove(Affiliate $affiliate): RedirectResponse
    {
        $affiliate->status = 'approved';
        $affiliate->save();
        try {
            Mail::to($affiliate->user->email)->send(new AffiliateStatusMail($affiliate, 'approved'));
        } catch (\Throwable) {
        }

        return back()->with('success', 'Afiliado aprovado.');
    }

    public function affiliateBlock(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $request->validate(['custom_rate' => 'nullable|numeric|min:0|max:90']);
        $affiliate->status = 'blocked';
        if ($request->filled('custom_rate')) {
            $affiliate->custom_rate = $request->custom_rate;
        }
        $affiliate->save();

        return back()->with('success', 'Afiliado bloqueado.');
    }

    // ---- Withdrawals ----
    public function withdrawalsAdmin(): View
    {
        $withdrawals = Withdrawal::with('user')->latest()->paginate(15);

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function withdrawalApprove(Withdrawal $withdrawal): RedirectResponse
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('info', 'Levantamento já estava '.$withdrawal->status.'.');
        }
        $withdrawal->status = 'approved';
        $withdrawal->processed_at = now();
        $withdrawal->save();
        $this->logActivity('admin.withdrawal.approved', "Levantamento #{$withdrawal->id} aprovado.");
        WebhookDispatcher::dispatch('withdrawal.approved', [
            'withdrawal_id' => $withdrawal->id,
            'user_id' => $withdrawal->user_id,
            'amount' => $withdrawal->amount,
        ], $withdrawal->user_id);

        return back()->with('success', 'Levantamento aprovado.');
    }

    public function withdrawalReject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:1000']);
        if ($withdrawal->status !== 'pending') {
            return back()->with('info', 'Levantamento já estava '.$withdrawal->status.'.');
        }
        $withdrawal->status = 'rejected';
        $withdrawal->rejection_reason = $request->reason;
        $withdrawal->save();

        $user = User::find($withdrawal->user_id);
        $user?->increment('balance', $withdrawal->amount);

        $this->logActivity('admin.withdrawal.rejected', "Levantamento #{$withdrawal->id} rejeitado + estorno.");

        return back()->with('success', 'Levantamento rejeitado e valor estornado.');
    }

    // ---- KYC ----
    public function kycAdmin(): View
    {
        $kycs = KycVerification::with('user')->latest()->paginate(15);

        return view('admin.kyc', compact('kycs'));
    }

    // ---- Reports ----
    public function reports(): View
    {
        $revenue = Sale::where('status', 'paid')->sum('final_price');
        $salesCount = Sale::where('status', 'paid')->count();
        $usersCount = User::count();
        $avg = $salesCount ? round($revenue / $salesCount, 2) : 0;
        $byMethod = Sale::where('status', 'paid')->groupBy('payment_method')->selectRaw('payment_method, SUM(final_price) as total')->pluck('total', 'payment_method');
        $byType = Product::groupBy('type')->selectRaw('type, COUNT(*) as c')->pluck('c', 'type');
        $monthly = collect();
        try {
            $rows = Sale::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, SUM(CASE WHEN status="paid" THEN final_price ELSE 0 END) as total, COUNT(CASE WHEN status="paid" THEN 1 END) as cnt')
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy('ym')->orderBy('ym')->get();
            $cursor = now()->subMonths(11)->startOfMonth();
            $map = $rows->keyBy('ym');
            $out = [];
            for ($i = 0; $i < 12; $i++) {
                $key = $cursor->format('Y-m');
                $out[] = [
                    'month' => $cursor->format('M Y'),
                    'total' => (float) ($map[$key]->total ?? 0),
                    'count' => (int) ($map[$key]->cnt ?? 0),
                ];
                $cursor->addMonth();
            }
            $monthly = collect($out);
        } catch (\Throwable) {
        }

        return view('admin.reports', [
            'revenue' => $revenue,
            'byMethod' => $byMethod,
            'byType' => $byType,
            'stats' => ['revenue' => round($revenue, 2), 'sales' => $salesCount, 'users' => $usersCount, 'avg' => $avg],
            'monthly' => $monthly,
            'mpesa' => (float) ($byMethod['mpesa'] ?? 0),
            'emola' => (float) ($byMethod['emola'] ?? 0),
        ]);
    }

    // ---- Communication ----
    public function communication(): View
    {
        return view('admin.communication');
    }

    public function sendMass(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'target' => 'required|in:all,admin,producer,affiliate,client',
        ]);

        $query = User::query();
        if ($data['target'] !== 'all') {
            $query->where('type', $data['target']);
        }
        foreach ($query->pluck('id') as $uid) {
            try {
                Notification::create(['user_id' => $uid, 'title' => $data['title'], 'message' => $data['message']]);
            } catch (\Throwable) {
                break;
            }
        }
        $this->logActivity('admin.broadcast', 'Mensagem em massa: '.$data['title']);

        return back()->with('success', 'Mensagem enviada.');
    }

    // ---- Messages ----
    public function messages(): View
    {
        $messages = SupportMessage::with('user')->latest()->paginate(15);

        return view('admin.messages', compact('messages'));
    }

    public function reply(Request $request, SupportMessage $message): RedirectResponse
    {
        $request->validate(['reply' => 'required|string']);
        $message->admin_reply = $request->reply;
        $message->status = 'answered';
        $message->save();

        return back()->with('success', 'Resposta enviada.');
    }

    // ---- Logs ----
    public function logs(): View
    {
        $logs = ActivityLog::with('user')->latest()->paginate(20);

        return view('admin.logs', compact('logs'));
    }

    // ---- Settings ----
    public function settings(): View
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return view('admin.settings', compact('settings'));
    }

    public function settingsUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.platform_fee_percent' => 'nullable|numeric|min:0|max:50',
            'settings.default_commission_rate' => 'nullable|numeric|min:0|max:90',
            'settings.min_withdrawal' => 'nullable|numeric|min:0',
            'settings.withdrawal_fee_percent' => 'nullable|numeric|min:0|max:30',
            'settings.payment_driver' => 'nullable|in:mock,mpesa,emola',
            'settings.mpesa_enabled' => 'nullable|in:0,1',
            'settings.emola_enabled' => 'nullable|in:0,1',
            'settings.site_name' => 'nullable|string|max:255',
            'settings.support_email' => 'nullable|email|max:255',
            'settings.maintenance' => 'nullable|in:0,1',
        ]);
        foreach ($data['settings'] as $key => $value) {
            if (! in_array($key, self::SETTINGS_ALLOWLIST, true)) {
                continue;
            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        $this->logActivity('admin.settings.updated', 'Configurações da plataforma atualizadas.');

        return back()->with('success', 'Definições guardadas.');
    }
}
