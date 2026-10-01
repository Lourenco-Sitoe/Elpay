<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KycMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && in_array($user->type, ['admin', 'client'], true)) {
            return $next($request);
        }

        if ($user) {
            $kyc = $user->kyc;
            if (! $kyc || $kyc->status !== 'approved') {
                return redirect()->route('kyc.index')
                    ->with('error', 'Complete e aprove o seu KYC para continuar.');
            }
        }

        return $next($request);
    }
}
