<?php

namespace Reddireccion\MultiApps\Foundation;

use Illuminate\Foundation\Bootstrap\RegisterFacades as LaravelRegisterFacades;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Facade;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Contracts\Foundation\Application;

class RegisterFacades extends LaravelRegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        AliasLoader::getInstance(array_merge(
            $app->make('config')->get(MULTI_APP_NAME.'.aliases', []),
            $app->make(PackageManifest::class)->aliases()
        ))->register();
    }
}