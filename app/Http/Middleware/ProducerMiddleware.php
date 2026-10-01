<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProducerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! in_array(auth()->user()->type, ['producer', 'admin'], true)) {
            abort(403, 'Acesso restrito ao produtor.');
        }

        return $next($request);
    }
}
