<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    /**
     * POST /api/v2/login — autentica e devolve token Sanctum.
     * O sistema antigo (api_token) continua válido; este é o caminho novo.
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->error('Email ou senha incorretos.', 401);
        }

        if (($user->status ?? 'active') !== 'active') {
            return $this->error('Conta bloqueada. Fala connosco pelo suporte.', 403);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'phone', 'type', 'balance']),
        ], 'Login realizado com sucesso.');
    }

    /**
     * POST /api/v2/logout — revoga o token atual (guard auth:sanctum).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success(null, 'Sessão terminada.');
    }

    /**
     * GET /api/v2/user — perfil do token atual (guard auth:sanctum).
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'type' => $user->type,
            'balance' => (float) ($user->balance ?? 0),
        ]);
    }
}
