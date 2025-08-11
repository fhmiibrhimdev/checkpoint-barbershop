<?php

namespace App\Providers;

use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AgentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $agent = new Agent();

        View::share('agent', $agent);
    }

    public function register()
    {
        //
    }
}
