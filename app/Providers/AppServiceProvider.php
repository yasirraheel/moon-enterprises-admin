<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Orders;
use App\Models\GamePrompts;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::withoutDoubleEncoding();
        Paginator::useBootstrap();

        // Share active orders grouped by category with the admin layout
        View::composer('admin.layout', function ($view) {
            $ordersByCategory = Orders::select('game_name', 'rttp')
                ->distinct()
                ->get()
                ->groupBy('game_name')
                ->map(function ($items) {
                    return $items->pluck('rttp')->toArray();
                });

            $view->with('ordersByCategory', $ordersByCategory);
        });
    }
}
