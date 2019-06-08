<?php

namespace Reddireccion\MultiApps;

use Illuminate\Support\ServiceProvider;
use Reddireccion\MultiApps\Console\CreateNewApp;

class MultiAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateNewApp::class
            ]);
        }
    }
}
