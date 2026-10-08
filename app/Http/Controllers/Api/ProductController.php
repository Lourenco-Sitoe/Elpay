<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;

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
}
