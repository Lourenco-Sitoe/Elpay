<?php

namespace App\Http\Controllers;

use App\Models\Bump;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BumpController extends Controller
{
    public function index(): View
    {
        $bumps = Bump::with(['mainProduct', 'bumpProduct'])
            ->whereHas('mainProduct', fn ($q) => $q->where('producer_id', auth()->id()))
            ->latest()->paginate(15);

        return view('bumps.index', compact('bumps'));
    }

    public function create(): View
    {
        $products = \App\Models\Product::where('producer_id', auth()->id())->where('status', 'approved')->get();

        return view('bumps.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'main_product_id' => 'required|exists:products,id',
            'bump_product_id' => 'required|exists:products,id|different:main_product_id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:1',
            'active' => 'nullable|boolean',
        ]);
        $this->ownProduct((int) $data['main_product_id']);
        $this->ownProduct((int) $data['bump_product_id']);

        Bump::create([
            'main_product_id' => $data['main_product_id'],
            'bump_product_id' => $data['bump_product_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'active' => $data['active'] ?? true,
        ]);

        return redirect()->route('bumps.index')->with('success', 'Order bump criado.');
    }

    public function edit(Bump $bump): View
    {
        $this->own($bump);
        $products = \App\Models\Product::where('producer_id', auth()->id())->get();

        return view('bumps.edit', compact('bump', 'products'));
    }

    public function update(Request $request, Bump $bump): RedirectResponse
    {
        $this->own($bump);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'active' => 'nullable|boolean',
        ]);
        $bump->update([
            'title' => $data['title'],
            'price' => $data['price'],
            'active' => $data['active'] ?? $bump->active,
        ]);

        return redirect()->route('bumps.index')->with('success', 'Order bump atualizado.');
    }

    public function destroy(Bump $bump): RedirectResponse
    {
        $this->own($bump);
        $bump->delete();

        return redirect()->route('bumps.index')->with('success', 'Order bump eliminado.');
    }

    protected function own(Bump $bump): void
    {
        $main = $bump->mainProduct;
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
