<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compatibilidade com MySQL antigo (chaves utf8mb4 limitadas a 1000 bytes).
        Schema::defaultStringLength(191);
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $unreadNotifications = Notification::where(function ($q) {
                    $q->where('user_id', auth()->id())->orWhereNull('user_id');
                })->whereNull('read_at')->latest()->take(10)->get();
                $unreadCount = Notification::where(function ($q) {
                    $q->where('user_id', auth()->id())->orWhereNull('user_id');
                })->whereNull('read_at')->count();
            } else {
                $unreadNotifications = collect();
                $unreadCount = 0;
            }

            $view->with(compact('unreadNotifications', 'unreadCount'));
        });
    }
}
