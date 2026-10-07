<?php

use App\Http\Middleware\ActiveMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AffiliateMiddleware;
use App\Http\Middleware\KycMiddleware;
use App\Http\Middleware\ProducerMiddleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Atrás de proxy reverso/Cloudflare em produção (TRUST_PROXIES=* no .env).
        if ($proxies = env('TRUST_PROXIES')) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : explode(',', $proxies));
        }
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'producer' => ProducerMiddleware::class,
            'affiliate' => AffiliateMiddleware::class,
            'kyc' => KycMiddleware::class,
            'active' => ActiveMiddleware::class,
        ]);
        $middleware->appendToGroup('web', [SetLocale::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
