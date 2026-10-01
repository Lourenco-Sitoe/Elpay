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

        $existing = Affiliate::where('user_id', auth()->id())->where('product_id', $product->id)->first();
        if ($existing) {
            return redirect()->route('affiliate.links')->with('info', 'Já é afiliado deste produto.');
        }

        $affiliate = Affiliate::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'affiliate_code' => strtoupper(Str::random(8)) . $product->id,
            'status' => 'approved',
        ]);

        $this->logActivity('affiliate.joined', 'Aderiu ao produto: ' . $product->title);

        return redirect()->route('affiliate.links')->with('success', 'Link de afiliado criado: ' . $affiliate->affiliate_code);
    }

    public function myLinks(): View
    {
        $links = Affiliate::with('product')->where('user_id', auth()->id())->latest()->get();

        return view('affiliate.my-links', compact('links'));
    }

    public function commissions(): View
    {
        $commissions = Commission::with(['sale', 'product'])
            ->where('affiliate_id', auth()->id())
            ->latest()->paginate(15);

        return view('affiliate.commissions', compact('commissions'));
    }
}
