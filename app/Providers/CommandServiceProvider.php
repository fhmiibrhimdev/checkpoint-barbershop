<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\MakeService;

class CommandServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Daftarkan command di sini
        $this->commands([
            MakeService::class,
        ]);
    }

    public function boot()
    {
        // Kode lainnya jika diperlukan
    }
}
