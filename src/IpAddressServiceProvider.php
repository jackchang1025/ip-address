<?php

namespace Weijiajia\IpAddress;

use Illuminate\Support\ServiceProvider;

class IpAddressServiceProvider extends ServiceProvider
{

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerConfig();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
       
    }


    /**
     * Register config.
     */
    protected function registerConfig(): void
    {

        $configPath = __DIR__ . '/config.php';
    
        $this->publishes([
            $configPath => config_path('ip-address.php'),
        ], 'config');
    
        $this->mergeConfigFrom($configPath, 'ip-address');
    }
  
}
