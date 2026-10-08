<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends BaseController
{
    /**
     * GET /api/v2/produtos — catálogo público, só produtos aprovados.
     */
    public function index(): JsonResponse
    {
        $products = Product::with('producer:id,name')
            ->where('status', 'approved')
            ->latest()
            ->paginate(15);

        return $this->paginated($products);
    }

    /**
     * GET /api/v2/produtos/{id} — detalhe público de um produto aprovado.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('producer:id,name')
            ->where('status', 'approved')
            ->find($id);

        if (! $product) {
            return $this->error('Produto não encontrado.', 404);
        }

        return $this->success($product);
    }

    /**
     * POST /api/v2/produtos — cria rascunho do utilizador autenticado.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validateProduct($request);

        $product = Product::create([
            'producer_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'price' => $data['price'],
            'currency' => $data['currency'] ?? 'MZN',
            'type' => $data['type'],
            'category' => $data['category'] ?? null,
            'commission_rate' => $data['commission_rate'] ?? 10,
            'affiliation_mode' => 'automatic',
            'payment_methods' => ['mpesa', 'emola'],
            'slug' => 'tmp-'.Str::random(8),
            'status' => 'draft',
        ]);
        $product->slug = Str::slug($data['title']).'-'.$product->id;
        $product->save();

        return $this->created($product, 'Produto criado como rascunho.');
    }

    /**
     * PUT /api/v2/produtos/{id} — atualiza (dono ou admin).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = $this->ownedOrFail($request, $id);
        if ($product instanceof JsonResponse) {
            return $product;
        }

        $data = $this->validateProduct($request, true);
        $product->fill($data);
        if (isset($data['title'])) {
            $product->slug = Str::slug($data['title']).'-'.$product->id;
        }
        $product->save();

        return $this->success($product->fresh(), 'Produto atualizado.');
    }

    /**
     * DELETE /api/v2/produtos/{id} — remove (dono ou admin).
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = $this->ownedOrFail($request, $id);
        if ($product instanceof JsonResponse) {
            return $product;
        }

        $product->delete();

        return $this->success(null, 'Produto eliminado.');
    }

    protected function validateProduct(Request $request, bool $partial = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'in:MZN,USD'],
            'type' => ['required', 'in:course,ebook,file,link,app'],
            'category' => ['nullable', 'string', 'max:100'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:90'],
        ];

        if ($partial) {
            foreach (['title', 'price', 'type'] as $field) {
                array_unshift($rules[$field], 'sometimes');
            }
        }

        $data = $request->validate($rules);

        $currency = $data['currency'] ?? 'MZN';
        $min = $currency === 'MZN' ? 100 : 5;
        if (isset($data['price']) && (float) $data['price'] < $min) {
            abort(response()->json([
                'success' => false,
                'message' => 'Preço abaixo do mínimo.',
                'errors' => ['price' => ["Mínimo de {$currency} ".number_format($min, 2).'.']],
            ], 422));
        }

        return $data;
    }

    protected function ownedOrFail(Request $request, int $id): Product|JsonResponse
    {
        $product = Product::find($id);
        if (! $product) {
            return $this->error('Produto não encontrado.', 404);
        }

        $user = $request->user();
        if (($user->type ?? '') !== 'admin' && (int) $product->producer_id !== (int) $user->id) {
            return $this->error('Sem permissão para este produto.', 403);
        }

        return $product;
    }
}
