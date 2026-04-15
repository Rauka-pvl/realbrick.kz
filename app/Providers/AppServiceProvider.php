<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        View::composer('*', function ($view) {
            $items = (array) session('cart.items', []);
            $cartCount = 0;
            foreach ($items as $item) {
                $cartCount += (int) ($item['qty'] ?? 0);
            }
            $view->with('cartCount', $cartCount);
        });
    }
}
