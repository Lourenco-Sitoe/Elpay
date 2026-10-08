<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_produtos_em_envelope_padrao(): void
    {
        $producer = User::factory()->create(['type' => 'producer']);
        Product::factory()->create(['producer_id' => $producer->id, 'status' => 'approved']);
        Product::factory()->create(['producer_id' => $producer->id, 'status' => 'pending']);

        $res = $this->getJson('/api/v2/produtos');

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data', 'meta' => ['current_page', 'total']]);
        $this->assertCount(1, $res->json('data'));
    }

    public function test_detalhe_inexistente_retorna_404_padrao(): void
    {
        $res = $this->getJson('/api/v2/produtos/999999');

        $res->assertNotFound()->assertJson(['success' => false]);
    }
}
