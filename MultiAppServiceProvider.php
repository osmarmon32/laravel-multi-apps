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
        //$this->app->make('wisdmLabs\todolist\TodolistController');
		/*
		
    $this->mergeConfigFrom(
        __DIR__.'/path/to/config/courier.php', 'courier'
    );
		*/
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
        /*		
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMigrationsFrom(__DIR__.'/migrations');
		$this->loadTranslationsFrom(__DIR__.'/path/to/translations', 'courier');
        $this->loadViewsFrom(__DIR__.'/views', 'todolist');
        $this->publishes([
            __DIR__.'/views' => base_path('resources/views/wisdmlabs/todolist'),
        ]);
		
		if ($this->app->runningInConsole()) {
			$this->commands([
				FooCommand::class,
				BarCommand::class,
			]);
		}
		*/
		//php artisan vendor:publish --tag=reddireccion\modelsscaffold\ModelsScaffoldServiceProvider  
    }
}
