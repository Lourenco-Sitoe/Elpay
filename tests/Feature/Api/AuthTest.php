<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_devolve_token_em_envelope_padrao(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        // A factory usa password 'password' por defeito (Breeze).
        $res = $this->postJson('/api/v2/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['success', 'message', 'data' => ['token', 'user']]);
    }

    public function test_login_invalido_retorna_401_padrao(): void
    {
        $user = User::factory()->create();

        $res = $this->postJson('/api/v2/login', [
            'email' => $user->email,
            'password' => 'errada',
        ]);

        $res->assertUnauthorized()->assertJson(['success' => false]);
    }

    public function test_user_exige_token_e_logout_revoga(): void
    {
        $this->getJson('/api/v2/user')->assertUnauthorized();

        $user = User::factory()->create(['status' => 'active']);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v2/user')->assertOk();

        $this->withToken($token)->postJson('/api/v2/logout')->assertOk();

        // Token revogado: volta a ser 401. (forgetGuards limpa o cache de
        // guards entre requests — no HTTP real cada request arranca limpo.)
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v2/user')->assertUnauthorized();
    }
}
