<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AffiliateMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! in_array(auth()->user()->type, ['affiliate', 'producer', 'admin'], true)) {
            abort(403, 'Acesso restrito ao afiliado.');
        }

        return $next($request);
    }
}
