<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && in_array($user->status, ['inactive', 'banned'], true)) {
            $route = $request->route()?->getName() ?? '';

            $allowed = ['logout', 'dashboard', 'notifications.', 'support.'];
            foreach ($allowed as $prefix) {
                if ($route === $prefix || str_starts_with($route, $prefix)) {
                    return $next($request);
                }
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'A sua conta está ' . $user->status . '. Contacte o suporte.']);
        }

        return $next($request);
    }
}
