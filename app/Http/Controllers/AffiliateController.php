<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Product;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    use LogsActivity;

    public function marketplace(Request $request): View
    {
        $q = $request->get('q');
        $products = Product::where('status', 'approved')
            ->when($q, fn ($query) => $query->where('title', 'like', "%{$q}%"))
            ->latest()->paginate(12);

        return view('affiliate.marketplace', compact('products'));
    }

    public function join(int $productId): RedirectResponse
    {
        $product = Product::where('status', 'approved')->findOrFail($productId);

        // Produtor não pode afiliar-se ao próprio produto (evita auto-comissão).
        if ((int) $product->producer_id === (int) auth()->id()) {
            return back()->withErrors(['product' => 'Não podes ser afiliado do teu próprio produto.']);
        }

        $existing = Affiliate::where('user_id', auth()->id())->where('product_id', $product->id)->first();
        if ($existing) {
            return redirect()->route('affiliate.links')->with('info', 'Já é afiliado deste produto.');
        }

        // Respeita o modo definido pelo produtor: automatic aprova de imediato, manual fica pendente.
        $status = (($product->affiliation_mode ?? 'automatic') === 'manual') ? 'pending' : 'approved';

        $affiliate = Affiliate::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'affiliate_code' => strtoupper(Str::random(8)).$product->id,
            'status' => $status,
        ]);

        $this->logActivity('affiliate.joined', 'Aderiu ao produto: '.$product->title." ({$status})");

        return redirect()->route('affiliate.links')->with(
            'success',
            $status === 'pending'
                ? 'Pedido enviado. Aguarda aprovação do produtor. Código: '.$affiliate->affiliate_code
                : 'Link de afiliado criado: '.$affiliate->affiliate_code
        );
    }

    /** Regista um clique no link de afiliado (chamado nas páginas públicas com ?ref=). */
    public static function trackClick(Product $product, string $code): void
    {
        try {
            Affiliate::where('product_id', $product->id)
                ->where('affiliate_code', $code)
                ->where('status', 'approved')
                ->increment('clicks');
        } catch (\Throwable) {
        }
    }

    public function myLinks(): View
    {
        $links = Affiliate::with('product')->where('user_id', auth()->id())->latest()->get();
        // Produtos próprios (o produtor não pode ser afiliado de si mesmo,
        // por isso aparecem aqui com links diretos, sem ?ref=).
        $ownProducts = Product::where('producer_id', auth()->id())
            ->where('status', 'approved')
            ->latest()->get();

        return view('affiliate.my-links', compact('links', 'ownProducts'));
    }

    public function commissions(): View
    {
        $commissions = Commission::with(['sale', 'product'])
            ->where('affiliate_id', auth()->id())
            ->latest()->paginate(15);

        return view('affiliate.commissions', compact('commissions'));
    }
}
