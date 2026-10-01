<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'type' => 'producer',
            'status' => 'active',
            'api_token' => Str::random(60),
            'email_verified_at' => now(),
        ]);

        return response()->json(['token' => $user->api_token, 'user' => $user], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $user->api_token = Str::random(60);
        $user->save();

        return response()->json(['token' => $user->api_token, 'user' => $user]);
    }

    public function products(): JsonResponse
    {
        $products = Product::where('status', 'approved')->latest()->paginate(15);

        return response()->json($products);
    }

    public function productDetail(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('status', 'approved')->firstOrFail();

        return response()->json($product);
    }

    protected function userFromToken(Request $request): ?User
    {
        $token = $request->bearerToken();

        return $token ? User::where('api_token', $token)->first() : null;
    }

    /** Perfil + saldo do utilizador autenticado. */
    public function me(Request $request): JsonResponse
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'type' => $user->type,
            'balance' => (float) ($user->balance ?? 0),
        ]);
    }

    /** Compras (acessos) do utilizador autenticado — alimenta a app mobile. */
    public function purchases(Request $request): JsonResponse
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $items = \App\Models\ProductAccess::with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json($items);
    }

    /** Estatísticas para o dashboard mobile (produtor e cliente). */
    public function stats(Request $request): JsonResponse
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        if (in_array($user->type, ['producer', 'admin'], true)) {
            $ids = \App\Models\Product::when($user->type !== 'admin', fn ($q) => $q->where('producer_id', $user->id))->pluck('id');
            $base = \App\Models\Sale::whereIn('product_id', $ids)->where('status', 'paid');
            $month = (clone $base)->where('created_at', '>=', now()->subDays(30));
            $refunded = \App\Models\Sale::whereIn('product_id', $ids)->whereIn('status', ['cancelled', 'refunded'])->where('created_at', '>=', now()->subDays(30));

            return response()->json([
                'sales_30d' => (clone $month)->count(),
                'revenue_30d' => round((clone $month)->sum('final_price'), 2),
                'lost_30d' => round($refunded->sum('final_price'), 2),
                'balance' => round((float) ($user->balance ?? 0), 2),
            ]);
        }

        return response()->json([
            'purchases' => \App\Models\Sale::where('buyer_id', $user->id)->where('status', 'paid')->count(),
            'spent' => round(\App\Models\Sale::where('buyer_id', $user->id)->where('status', 'paid')->sum('final_price'), 2),
            'balance' => round((float) ($user->balance ?? 0), 2),
        ]);
    }

    /** Histórico de vendas (produtor) ou compras (cliente). */
    public function sales(Request $request): JsonResponse
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $q = \App\Models\Sale::with(['product', 'buyer'])->latest();
        if (in_array($user->type, ['producer', 'admin'], true)) {
            $ids = \App\Models\Product::when($user->type !== 'admin', fn ($qq) => $qq->where('producer_id', $user->id))->pluck('id');
            $q->whereIn('product_id', $ids);
        } else {
            $q->where('buyer_id', $user->id);
        }

        return response()->json($q->paginate(15));
    }

    /** Produtos do produtor (app mobile). */
    public function myProducts(Request $request): JsonResponse
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        return response()->json(
            \App\Models\Product::where('producer_id', $user->id)->latest()->paginate(20)
        );
    }

    /** Saques: saldos + histórico. */
    public function withdrawals(Request $request): JsonResponse
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $approved = \App\Models\Withdrawal::where('user_id', $user->id)->where('status', 'approved')->sum('amount');

        return response()->json([
            'available' => round((float) ($user->balance ?? 0), 2),
            'withdrawn' => round((float) $approved, 2),
            'total' => round((float) ($user->balance ?? 0) + (float) $approved, 2),
            'history' => \App\Models\Withdrawal::where('user_id', $user->id)->latest()->paginate(15),
        ]);
    }

    /** Solicitar saque via app. */
    public function withdraw(Request $request): JsonResponse
    {
        $user = $this->userFromToken($request);
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }
        if (! in_array($user->type, ['producer', 'affiliate', 'admin'], true)) {
            return response()->json(['message' => 'Sem carteira.'], 403);
        }

        $min = (float) setting('min_withdrawal', 100);
        $feePercent = (float) setting('withdrawal_fee_percent', 2);
        $data = $request->validate([
            'amount' => 'required|numeric|min:' . $min,
            'method' => 'required|in:mpesa,emola',
            'phone' => 'required|string|max:30',
        ]);

        if ($data['amount'] > (float) ($user->balance ?? 0)) {
            return response()->json(['message' => 'Saldo insuficiente.'], 422);
        }

        $fee = round($data['amount'] * ($feePercent / 100), 2);
        $user->decrement('balance', $data['amount']);
        $w = \App\Models\Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'fee' => $fee,
            'net' => round($data['amount'] - $fee, 2),
            'method' => $data['method'],
            'phone' => $data['phone'],
            'status' => 'pending',
        ]);

        return response()->json(['withdrawal' => $w], 201);
    }

    public function checkout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'Token em falta.'], 401);
        }
        $buyer = User::where('api_token', $token)->first();
        if (! $buyer) {
            return response()->json(['message' => 'Token inválido.'], 401);
        }

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'payment_method' => 'required|in:mpesa,emola',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $total = (float) $product->price;

        $sale = Sale::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'producer_id' => $product->producer_id,
            'price' => $total,
            'discount' => 0,
            'final_price' => $total,
            'status' => 'pending',
            'payment_method' => $data['payment_method'],
            'buyer_name' => $buyer->name,
            'buyer_email' => $buyer->email,
            'is_upsell' => false,
        ]);

        try {
            $result = \App\Services\Payments\PaymentGatewayManager::driver()->initiate($sale, $buyer->phone ?? '');
        } catch (\Throwable $e) {
            $sale->delete();

            return response()->json(['message' => 'Pagamento indisponível: ' . $e->getMessage()], 422);
        }

        if (! $result->approved()) {
            \App\Models\Payment::create([
                'sale_id' => $sale->id,
                'amount' => $total,
                'method' => $data['payment_method'],
                'status' => 'pending',
                'transaction_ref' => $result->reference,
            ]);

            return response()->json(['sale' => $sale, 'status' => 'pending'], 202);
        }

        app(CheckoutController::class)->finalizeSale($sale, $result->reference);

        return response()->json(['sale' => $sale->fresh()], 201);
    }
}
