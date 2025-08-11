<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        Blade::directive('money', function ($amount) {
            return "Rp<?php echo number_format($amount, 0, ',', '.'); ?>";
        });

        Blade::directive('price', function ($amount) {
            return "<?php echo number_format($amount, 0, ',', '.'); ?>";
        });

        Blade::directive('stock', function ($quantity) {
            return "<?php echo $quantity?>,00";
        });
    }
}
