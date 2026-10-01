<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

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
