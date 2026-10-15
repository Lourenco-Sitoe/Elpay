<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KycFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_produtor_ve_opcao_de_submeter_e_submete(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['type' => 'producer', 'status' => 'active']);

        // Página mostra estado + botão Nova submissão.
        $index = $this->actingAs($user)->get(route('kyc.index'));
        $index->assertOk();
        $index->assertSee('Nova submiss', false);

        // Formulário tem botão Submeter e aponta para kyc.store.
        $create = $this->actingAs($user)->get(route('kyc.create'));
        $create->assertOk();
        $create->assertSee('Submeter', false);
        $create->assertSee(route('kyc.store'), false);

        // Submissão válida cria registo pendente.
        $res = $this->actingAs($user)->post(route('kyc.store'), [
            'document_type' => 'bi',
            'document_front' => UploadedFile::fake()->image('frente.jpg'),
            'document_back' => UploadedFile::fake()->image('verso.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);
        $res->assertRedirect(route('kyc.index'));
        $this->assertDatabaseHas('kyc_verifications', [
            'user_id' => $user->id,
            'document_type' => 'bi',
            'status' => 'pending',
        ]);
    }

    public function test_falta_verso_devolve_422_com_erro_visivel(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['type' => 'producer', 'status' => 'active']);

        $res = $this->actingAs($user)->post(route('kyc.store'), [
            'document_type' => 'bi',
            'document_front' => UploadedFile::fake()->image('frente.jpg'),
            'selfie' => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $res->assertSessionHasErrors('document_back');
        $this->assertDatabaseMissing('kyc_verifications', ['user_id' => $user->id]);
    }
}
