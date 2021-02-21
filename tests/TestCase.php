<?php

namespace Patoui\LaravelClickhouse\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Patoui\LaravelClickhouse\LaravelClickhouseServiceProvider;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        if (!extension_loaded('SeasClick')) {
            self::fail('Extension not loaded: SeasClick.' . PHP_SHLIB_SUFFIX);
        }
        // TODO: setup docker with test database

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [LaravelClickhouseServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', '603046c97f00a193540915');
        $app['config']->set('database.default', 'clickhouse');
        $app['config']->set('database.connections.clickhouse', [
            'host'     => '127.0.0.1',
            'port'     => '9000',
            'database' => 'profile',
            'username' => 'default',
            'password' => '',
        ]);
    }
}
