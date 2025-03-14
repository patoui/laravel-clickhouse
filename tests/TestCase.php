<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Patoui\LaravelClickhouse\LaravelClickhouseServiceProvider;

class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        if (!extension_loaded('SeasClick')) {
            self::fail('Extension not loaded: SeasClick.' . PHP_SHLIB_SUFFIX);
        }

        parent::setUp();
        $this->truncateAnalyticsTable();
        $this->createAnalyticsTable();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->truncateAnalyticsTable();
    }

    public function createAnalyticsTable(): void
    {
        DB::connection('clickhouse')->statement('
            CREATE TABLE IF NOT EXISTS analytics (
                dt              Date DEFAULT toDate(ts),
                ts              DateTime,
                analytic_id     UInt32,
                status          UInt16,
                is_enabled      UInt8,
                label           Nullable(String),
                name            String DEFAULT \'\',
                metadata        Nullable(String),
            )
            ENGINE = MergeTree
            PARTITION BY dt
            ORDER BY (analytic_id, dt);
        ');
    }

    public function truncateAnalyticsTable(): void
    {
        DB::connection('clickhouse')->statement('TRUNCATE TABLE IF EXISTS analytics');
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
            'driver'   => 'clickhouse',
            'host'     => env('APP_ENV') === 'ci' ? 'localhost' : 'lc_clickhouse',
            'port'     => '9000',
            'username' => 'default',
            'password' => '',
        ]);
    }
}
