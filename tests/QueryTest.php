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
        $this->connection = DB::connection('clickhouse');
    }

    public function testWhere(): void
    {
        self::assertTrue(
            $this->connection
                ->table('analytics')
                ->whereRaw('analytical_id > 1')
                ->where('ts', '>', strtotime('-1 day'))
                ->count() > 0
        );
    }
}
