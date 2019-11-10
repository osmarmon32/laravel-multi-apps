<?php

namespace Reddireccion\MultiApps\Foundation;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Log\LogServiceProvider;
use Reddireccion\MultiApps\Routing\RoutingServiceProvider;

class Application extends LaravelApplication
{
    /**
     * Get the path to the application "app" directory.
     *
     * @param  string  $path
     * @return string
     */
    /*
    public function path($path = '')
    {
        $appPath = $this->appPath ?: $this->basePath.DIRECTORY_SEPARATOR.'app';

        return $appPath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
    */

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));
        $this->register(new LogServiceProvider($this));
        $this->register(new RoutingServiceProvider($this));
    }
    
    /**
     * Register all of the configured providers.
     *
     * @return void
     */
    public function registerConfiguredProviders()
    {
        $providers = Collection::make($this->config[MULTI_APP_NAME.'.providers'])
                        ->partition(function ($provider) {
                            return Str::startsWith($provider, 'Illuminate\\');
                        });

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
                    ->load($providers->collapse()->toArray());
    }
    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get(MULTI_APP_NAME.'.locale');
    }

    /**
     * Set the current application locale.
     *
     * @param  string  $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set(MULTI_APP_NAME.'.locale', $locale);

        $this['translator']->setLocale($locale);

        $this['events']->dispatch(new Events\LocaleUpdated($locale));
    }

}