<?php

namespace Patoui\LaravelClickhouse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class LaravelClickhouseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        Model::setConnectionResolver($this->app['db']);
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Add database driver.
        $this->app->resolving('db', function ($db) {
            $db->extend('clickhouse', function ($config) {
                return new ClickhouseConnection($config);
            });
        });
    }
}
