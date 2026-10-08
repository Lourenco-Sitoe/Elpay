<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWriteTest extends TestCase
{
    use RefreshDatabase;

    protected function tokenFor(User $user): string
    {
        return $user->createToken('mobile')->plainTextToken;
    }

    public function test_criar_produto_retorna_201_como_rascunho(): void
    {
        $user = User::factory()->create(['type' => 'producer', 'status' => 'active']);

        $res = $this->withToken($this->tokenFor($user))->postJson('/api/v2/produtos', [
            'title' => 'Curso Teste',
            'price' => 500,
            'currency' => 'MZN',
            'type' => 'course',
        ]);

        $res->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('products', ['title' => 'Curso Teste', 'status' => 'draft']);
    }

    public function test_preco_abaixo_do_minimo_retorna_422(): void
    {
        $user = User::factory()->create(['type' => 'producer', 'status' => 'active']);

        $res = $this->withToken($this->tokenFor($user))->postJson('/api/v2/produtos', [
            'title' => 'Barato',
            'price' => 50,
            'currency' => 'MZN',
            'type' => 'ebook',
        ]);

        $res->assertUnprocessable()->assertJson(['success' => false]);
    }

    public function test_produtor_nao_edita_produto_de_outro_403(): void
    {
        $owner = User::factory()->create(['type' => 'producer']);
        $other = User::factory()->create(['type' => 'producer']);
        $product = Product::factory()->create(['producer_id' => $owner->id]);

        $res = $this->withToken($this->tokenFor($other))->putJson("/api/v2/produtos/{$product->id}", [
            'title' => 'Roubado',
        ]);

        $res->assertForbidden()->assertJson(['success' => false]);
    }

    public function test_admin_edita_produto_de_outro_e_dono_apaga(): void
    {
        $owner = User::factory()->create(['type' => 'producer']);
        $admin = User::factory()->create(['type' => 'admin']);
        $product = Product::factory()->create(['producer_id' => $owner->id]);

        $this->withToken($this->tokenFor($admin))
            ->putJson("/api/v2/produtos/{$product->id}", ['title' => 'Editado pelo admin'])
            ->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'title' => 'Editado pelo admin']);

        $this->withToken($this->tokenFor($owner))
            ->deleteJson("/api/v2/produtos/{$product->id}")
            ->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_escrita_exige_token_401(): void
    {
        $this->postJson('/api/v2/produtos', ['title' => 'X'])->assertUnauthorized();
    }
}
