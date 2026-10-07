<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Sale;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProducerController extends Controller
{
    use LogsActivity;

    protected function myProductIds(): array
    {
        return Product::where('producer_id', auth()->id())->pluck('id')->all();
    }

    protected function myProductOrFail(int $id): Product
    {
        $product = Product::findOrFail($id);
        abort_unless((int) $product->producer_id === (int) auth()->id(), 403);

        return $product;
    }

    /** Vendas do produtor com filtros (estado, período, produto, afiliado, pesquisa). */
    public function sales(Request $request): View
    {
        $ids = $this->myProductIds();
        $query = Sale::with(['product', 'buyer'])->whereIn('product_id', $ids);

        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'cancelled', 'refunded'])) {
            $query->where('status', $request->status);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('affiliate')) {
            $query->where('affiliate_code', $request->affiliate);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn ($w) => $w->where('buyer_name', 'like', "%{$q}%")->orWhere('buyer_email', 'like', "%{$q}%"));
        }

        $sales = $query->latest()->paginate(15)->withQueryString();
        $products = Product::where('producer_id', auth()->id())->orderBy('title')->get();
        $summary = [
            'count' => (clone $query)->count(),
            'paid' => (float) (clone $query)->where('status', 'paid')->sum('final_price'),
        ];

        return view('producer.sales', compact('sales', 'products', 'summary'));
    }

    /** Clientes do produtor (só compradores dos seus produtos). */
    public function customers(Request $request): View
    {
        $ids = $this->myProductIds();
        $query = ProductAccess::with(['product', 'user', 'sale'])
            ->whereHas('product', fn ($q) => $q->where('producer_id', auth()->id()));

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('user', fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        $accesses = $query->latest()->paginate(15)->withQueryString();
        $products = Product::where('producer_id', auth()->id())->orderBy('title')->get();

        return view('producer.customers', compact('accesses', 'products'));
    }

    /** Concede acesso manual a um cliente (produto próprio). */
    public function grantAccess(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'email' => 'required|email',
        ]);
        $product = $this->myProductOrFail((int) $data['product_id']);
        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return back()->withErrors(['email' => 'Utilizador não encontrado.']);
        }
        ProductAccess::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
        $this->logActivity('producer.access.granted', "Acesso concedido: user #{$user->id} -> produto #{$product->id}");

        return back()->with('success', 'Acesso concedido.');
    }

    /** Remove acesso de um cliente (só produtos próprios). */
    public function revokeAccess(ProductAccess $access): RedirectResponse
    {
        abort_unless((int) $access->product->producer_id === (int) auth()->id(), 403);
        $access->delete();
        $this->logActivity('producer.access.revoked', "Acesso #{$access->id} removido.");

        return back()->with('success', 'Acesso removido.');
    }

    /** Histórico de compras de um cliente (só produtos do produtor). */
    public function customerHistory(int $userId): View
    {
        $ids = $this->myProductIds();
        $sales = Sale::with('product')->whereIn('product_id', $ids)->where('buyer_id', $userId)->latest()->get();
        $user = User::findOrFail($userId);

        return view('producer.customer-history', compact('sales', 'user'));
    }
}
