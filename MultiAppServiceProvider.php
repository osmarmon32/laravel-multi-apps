<?php

namespace Reddireccion\MultiApps;

use Illuminate\Support\ServiceProvider;
use Reddireccion\MultiApps\Console\CreateNewApp;
use Illuminate\Support\Str;
use Illuminate\Encryption\Encrypter;
use RuntimeException;

class MultiAppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // overwrite default app config file for the new one
        app('config')->set('app',config(MULTI_APP_NAME));
        //set the public path to the new one
        $this->app->bind('path.public', function() {
            return base_path('public_'.MULTI_APP_NAME);
        });
        //Overwite Encrypter defined by Illuminate/Encryption/EncryptionServiceProvider defined in config file  
        $this->app->singleton('encrypter', function ($app) {
            $config = $app->make('config')->get(MULTI_APP_NAME);

            // If the key starts with "base64:", we will need to decode the key before handing
            // it off to the encrypter. Keys may be base-64 encoded for presentation and we
            // want to make sure to convert them back to the raw bytes before encrypting.
            if (Str::startsWith($key = $this->key($config), 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, $config['cipher']);
        });
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @param  array  $config
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function key(array $config)
    {
        return tap($config['key'], function ($key) {
            if (empty($key)) {
                throw new RuntimeException(
                    'No application encryption key has been specified.'
                );
            }
        });
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
        //load migrations from app folder inside migrations.
        $this->loadMigrationsFrom(database_path('/migrations/'.MULTI_APP_NAME));
    }
}
