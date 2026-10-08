<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

// Health-check leve (usado pela app mobile para validar ligação e cookie anti-bot).
Route::get('/ping', fn () => response()->json(['ok' => true, 'time' => now()->toIso8601String()]));

// ---- v2 padronizada (envelope {success, message, data}) — convive com a v1 ----
Route::prefix('v2')->name('api.v2.')->group(function () {
    Route::get('/produtos', [\App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('/produtos/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
});

Route::post('/register', [ApiController::class, 'register']);
Route::post('/login', [ApiController::class, 'login']);
Route::get('/products', [ApiController::class, 'products']);
Route::get('/products/{slug}', [ApiController::class, 'productDetail']);
Route::post('/checkout', [ApiController::class, 'checkout']);
Route::get('/me', [ApiController::class, 'me']);
Route::get('/me/purchases', [ApiController::class, 'purchases']);
Route::get('/me/stats', [ApiController::class, 'stats']);
Route::get('/me/sales', [ApiController::class, 'sales']);
Route::get('/me/products', [ApiController::class, 'myProducts']);
Route::get('/me/withdrawals', [ApiController::class, 'withdrawals']);
Route::post('/me/withdrawals', [ApiController::class, 'withdraw']);
Route::post('/payments/callback/{driver}', [\App\Http\Controllers\PaymentCallbackController::class, 'handle'])->name('api.payments.callback');
