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
            $query->where('title', 'like', '%'.request('q').'%');
        }
        if (in_array(request('status'), ['draft', 'pending', 'approved', 'rejected', 'suspended', 'banned'])) {
            $query->where('status', request('status'));
        }
        if (request()->filled('category')) {
            $query->where('category', request('category'));
        }
        $products = $query->paginate(12)->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => ProductWizardController::CATEGORIES,
            'statusLabels' => ProductWizardController::STATUS_LABELS,
            'formats' => ProductWizardController::FORMATS,
        ]);
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
            'currency' => 'nullable|string|max:10',
            'type' => 'required|in:course,ebook,file,link,app',
            'category' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:90',
            'affiliation_mode' => 'nullable|in:automatic,manual',
            'affiliation_rules' => 'nullable|string|max:5000',
            'file' => ['nullable', 'file', 'max:'.config('uploads.max_file_kb')],
            'cover' => ['nullable', 'image', 'max:'.config('uploads.max_image_kb')],
            'external_url' => 'nullable|url|max:2048',
            'sales_page_url' => 'nullable|url|max:2048',
        ]);
        $currency = $data['currency'] ?? 'MZN';
        $min = ProductWizardController::PRICE_MIN[$currency] ?? 1;
        if ((float) $data['price'] < $min) {
            return back()->withErrors(['price' => "Mínimo de {$currency} ".number_format($min, 2).'.'])->withInput();
        }

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
            'currency' => $data['currency'] ?? 'MZN',
            'type' => $data['type'],
            'category' => $data['category'] ?? null,
            'commission_rate' => $data['commission_rate'] ?? (float) setting('default_commission_rate', 10),
            'affiliation_mode' => $data['affiliation_mode'] ?? 'automatic',
            'affiliation_rules' => $data['affiliation_rules'] ?? null,
            'file_path' => $filePath,
            'cover' => $coverPath,
            'external_url' => $data['external_url'] ?? null,
            'sales_page_url' => $data['sales_page_url'] ?? null,
            'slug' => 'tmp-'.Str::random(8),
            'status' => 'draft',
        ]);
        $product->slug = Str::slug($data['title']).'-'.$product->id;
        $product->save();

        $this->logActivity('product.created', 'Produto criado: '.$product->title);

        return redirect()->route('products.wizard', [$product, 'overview'])->with('success', 'Produto cadastrado com sucesso');
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
            'currency' => 'nullable|string|max:10',
            'type' => 'required|in:course,ebook,file,link,app',
            'category' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:90',
            'affiliation_mode' => 'nullable|in:automatic,manual',
            'affiliation_rules' => 'nullable|string|max:5000',
            'file' => ['nullable', 'file', 'max:'.config('uploads.max_file_kb')],
            'cover' => ['nullable', 'image', 'max:'.config('uploads.max_image_kb')],
            'external_url' => 'nullable|url|max:2048',
            'sales_page_url' => 'nullable|url|max:2048',
        ]);
        $currency = $data['currency'] ?? $product->currency ?? 'MZN';
        $min = ProductWizardController::PRICE_MIN[$currency] ?? 1;
        if ((float) $data['price'] < $min) {
            return back()->withErrors(['price' => "Mínimo de {$currency} ".number_format($min, 2).'.'])->withInput();
        }

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
            'currency' => $data['currency'] ?? $product->currency ?? 'MZN',
            'type' => $data['type'],
            'category' => $data['category'] ?? $product->category,
            'commission_rate' => $data['commission_rate'] ?? $product->commission_rate,
            'affiliation_mode' => $data['affiliation_mode'] ?? $product->affiliation_mode ?? 'automatic',
            'affiliation_rules' => $data['affiliation_rules'] ?? $product->affiliation_rules,
            'external_url' => $data['external_url'] ?? null,
            'sales_page_url' => $data['sales_page_url'] ?? $product->sales_page_url,
        ]);
        $product->save();

        $this->logActivity('product.updated', 'Produto atualizado: '.$product->title);

        return redirect()->route('products.index')->with('success', 'Produto atualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        $product->delete();
        $this->logActivity('product.deleted', 'Produto eliminado: '.$product->title);

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
