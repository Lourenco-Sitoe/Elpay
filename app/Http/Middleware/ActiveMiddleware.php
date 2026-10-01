<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($user && isset($user->status) && in_array($user->status, ['inactive', 'banned', 'suspended', 'blocked'])) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Conta inativa ou bloqueada. Contacte o suporte.']);
        }

        return $next($request);
    }
}
