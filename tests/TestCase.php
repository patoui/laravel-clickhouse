<?php

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
                name            String DEFAULT \'\'
            ) ENGINE = MergeTree (dt, (analytic_id, dt), 8192);
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
            'host'     => 'lc_clickhouse',
            'port'     => '9000',
            'username' => 'default',
            'password' => '',
        ]);
    }
}
