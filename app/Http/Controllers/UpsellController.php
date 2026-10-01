<?php

namespace App\Http\Controllers;

use App\Models\Upsell;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UpsellController extends Controller
{
    public function index(): View
    {
        $upsells = Upsell::with(['mainProduct', 'upsellProduct'])
            ->whereHas('mainProduct', fn ($q) => $q->where('producer_id', auth()->id()))
            ->latest()->paginate(15);

        return view('upsells.index', compact('upsells'));
    }

    public function create(): View
    {
        $products = \App\Models\Product::where('producer_id', auth()->id())->where('status', 'approved')->get();

        return view('upsells.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'main_product_id' => 'required|exists:products,id',
            'upsell_product_id' => 'required|exists:products,id|different:main_product_id',
            'special_price' => 'required|numeric|min:1',
            'downsell_product_id' => 'nullable|exists:products,id',
            'downsell_price' => 'nullable|numeric|min:1',
            'active' => 'nullable|boolean',
        ]);
        $this->ownProduct((int) $data['main_product_id']);
        $this->ownProduct((int) $data['upsell_product_id']);
        if (! empty($data['downsell_product_id'])) {
            $this->ownProduct((int) $data['downsell_product_id']);
        }

        Upsell::create([
            'producer_id' => auth()->id(),
            'main_product_id' => $data['main_product_id'],
            'upsell_product_id' => $data['upsell_product_id'],
            'special_price' => $data['special_price'],
            'downsell_product_id' => $data['downsell_product_id'] ?? null,
            'downsell_price' => $data['downsell_price'] ?? null,
            'active' => $data['active'] ?? true,
        ]);

        return redirect()->route('upsells.index')->with('success', 'Upsell criado.');
    }

    public function edit(Upsell $upsell): View
    {
        $this->own($upsell);
        $products = \App\Models\Product::where('producer_id', auth()->id())->get();

        return view('upsells.edit', compact('upsell', 'products'));
    }

    public function update(Request $request, Upsell $upsell): RedirectResponse
    {
        $this->own($upsell);
        $data = $request->validate([
            'special_price' => 'required|numeric|min:1',
            'downsell_product_id' => 'nullable|exists:products,id',
            'downsell_price' => 'nullable|numeric|min:1',
            'active' => 'nullable|boolean',
        ]);
        $upsell->update([
            'special_price' => $data['special_price'],
            'downsell_product_id' => $data['downsell_product_id'] ?? null,
            'downsell_price' => $data['downsell_price'] ?? null,
            'active' => $data['active'] ?? $upsell->active,
        ]);

        return redirect()->route('upsells.index')->with('success', 'Upsell atualizado.');
    }

    public function destroy(Upsell $upsell): RedirectResponse
    {
        $this->own($upsell);
        $upsell->delete();

        return redirect()->route('upsells.index')->with('success', 'Upsell eliminado.');
    }

    protected function own(Upsell $upsell): void
    {
        $main = $upsell->mainProduct;
        abort_unless(
            $main && ((auth()->user()->type ?? '') === 'admin' || (int) $main->producer_id === (int) auth()->id()),
            403
        );
    }

    protected function ownProduct(int $productId): void
    {
        $product = \App\Models\Product::findOrFail($productId);
        abort_unless(
            (auth()->user()->type ?? '') === 'admin' || (int) $product->producer_id === (int) auth()->id(),
            403
        );
    }
}
