<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\LogsActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    use LogsActivity;

    public function index(): View
    {
        $user = auth()->user();
        $query = Product::with('producer')->latest();
        if (($user->type ?? '') !== 'admin') {
            $query->where('producer_id', $user->id);
        }
        if (request()->filled('q')) {
            $query->where('title', 'like', '%' . request('q') . '%');
        }
        if (in_array(request('status'), ['pending', 'approved', 'rejected'])) {
            $query->where('status', request('status'));
        }
        $products = $query->paginate(12)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'type' => 'required|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:90',
            'file' => 'nullable|file|max:51200',
            'cover' => 'nullable|image|max:4096',
            'external_url' => 'nullable|url|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('products', 'public');
        }
        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('products', 'public');
        }

        $product = Product::create([
            'producer_id' => auth()->id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'type' => $data['type'],
            'commission_rate' => $data['commission_rate'] ?? 0,
            'file_path' => $filePath,
            'cover' => $coverPath,
            'external_url' => $data['external_url'] ?? null,
            'slug' => 'tmp-' . Str::random(8),
            'status' => 'pending',
        ]);
        $product->slug = Str::slug($data['title']) . '-' . $product->id;
        $product->save();

        $this->logActivity('product.created', 'Produto criado: ' . $product->title);

        return redirect()->route('products.index')->with('success', 'Produto criado e enviado para aprovação.');
    }

    public function show(Product $product): View
    {
        $this->authorizeProduct($product);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->authorizeProduct($product);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:1',
            'type' => 'required|string|max:50',
            'commission_rate' => 'nullable|numeric|min:0|max:90',
            'file' => 'nullable|file|max:51200',
            'cover' => 'nullable|image|max:4096',
            'external_url' => 'nullable|url|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $product->file_path = $request->file('file')->store('products', 'public');
        }
        if ($request->hasFile('cover')) {
            $product->cover = $request->file('cover')->store('products', 'public');
        }
        $product->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'type' => $data['type'],
            'commission_rate' => $data['commission_rate'] ?? $product->commission_rate,
            'external_url' => $data['external_url'] ?? null,
        ]);
        $product->save();

        $this->logActivity('product.updated', 'Produto atualizado: ' . $product->title);

        return redirect()->route('products.index')->with('success', 'Produto atualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        $product->delete();
        $this->logActivity('product.deleted', 'Produto eliminado: ' . $product->title);

        return redirect()->route('products.index')->with('success', 'Produto eliminado.');
    }

    protected function authorizeProduct(Product $product): void
    {
        $user = auth()->user();
        if (($user->type ?? '') === 'admin') {
            return;
        }
        if ((int) $product->producer_id !== (int) $user->id) {
            abort(403);
        }
    }
}
