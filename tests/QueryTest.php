<?php

namespace Patoui\LaravelClickhouse\Tests;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class QueryTest extends TestCase
{
    /** @var ConnectionInterface */
    private $connection;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection = DB::connection('clickhouse');

        $this->connection->statement('
            CREATE TABLE IF NOT EXISTS analytics (
                dt              Date DEFAULT toDate(ts),
                ts              DateTime,
                analytic_id     UInt32,
                status          String
            ) ENGINE = MergeTree (dt, (analytic_id, dt), 8192);
        ');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->connection->statement('TRUNCATE TABLE IF EXISTS analytics');
    }

    public function testInsert(): void
    {
        $this->expectNotToPerformAssertions();
        $this->connection->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => 321, 'status' => 204]
        );
    }

    public function testTableInsert(): void
    {
        $this->expectNotToPerformAssertions();
        $this->connection->table('analytics')->insert([
            'ts'          => time(),
            'analytic_id' => 321,
            'status'      => 204,
        ]);
    }

    public function testWhere(): void
    {
        $this->connection->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );
        $this->connection->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );
        self::assertSame(
            2,
            $this->connection
                ->table('analytics')
                ->where('ts', '>', strtotime('-1 day'))
                ->count()
        );
    }
}
