<?php

namespace Bkfdev\Invoicable;

use Illuminate\Support\ServiceProvider;

class InvoicableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $sourceViewsPath = __DIR__.'/../resources/views';
        $this->loadViewsFrom($sourceViewsPath, 'invoicable');

        $this->publishes([
            $sourceViewsPath => resource_path('views/vendor/invoicable'),
        ], 'views');

        // Publish a config file
        $this->publishes([
            __DIR__.'/../config/invoicable.php' => config_path('invoicable.php'),
        ], 'config');

        // Publish migrations
         $this->publishes([
             __DIR__.'/../database/migrations/2021_02_09_163005_create_invoices_tables.php'
             => database_path('migrations/2021_02_09_163005_create_invoices_tables.php'),
         ], 'migrations');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/invoicable.php', 'invoicable');
    }
}
