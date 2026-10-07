<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliateMaterial;
use App\Models\Product;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    use LogsActivity;

    protected function myProductOrFail(int $id): Product
    {
        $product = Product::findOrFail($id);
        $user = auth()->user();
        if (($user->type ?? '') !== 'admin') {
            abort_unless((int) $product->producer_id === (int) $user->id, 403);
        }

        return $product;
    }

    /** Materiais de divulgação de um produto (produtor gere; afiliado lê). */
    public function index(Product $product): View
    {
        $this->myProductOrFail($product->id);
        $materials = AffiliateMaterial::where('product_id', $product->id)->latest()->get();

        return view('producer.materials', compact('product', 'materials'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $this->myProductOrFail($product->id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'kind' => 'required|in:text,image,video,link,banner',
            'content' => 'nullable|string',
            'file_url' => 'nullable|url|max:2048',
        ]);
        AffiliateMaterial::create([...$data, 'product_id' => $product->id]);

        return back()->with('success', 'Material criado.');
    }

    public function update(Request $request, AffiliateMaterial $material): RedirectResponse
    {
        $this->myProductOrFail($material->product_id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'kind' => 'required|in:text,image,video,link,banner',
            'content' => 'nullable|string',
            'file_url' => 'nullable|url|max:2048',
            'active' => 'nullable|boolean',
        ]);
        $material->fill($data);
        $material->active = $request->boolean('active', true);
        $material->save();

        return back()->with('success', 'Material atualizado.');
    }

    public function destroy(AffiliateMaterial $material): RedirectResponse
    {
        $this->myProductOrFail($material->product_id);
        $material->delete();

        return back()->with('success', 'Material removido.');
    }

    /** Vista do afiliado: materiais dos produtos onde é afiliado aprovado. */
    public function affiliateView(): View
    {
        $productIds = Affiliate::where('user_id', auth()->id())
            ->where('status', 'approved')->pluck('product_id');
        $materials = AffiliateMaterial::with('product')
            ->whereIn('product_id', $productIds)->where('active', true)->latest()->get();

        return view('affiliate.materials', compact('materials'));
    }
}
