<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Patoui\LaravelClickhouse\Tests\TestCase;

class QueryTest extends TestCase
{
    public function test_insert(): void
    {
        // Arrange & Act & Assert
        $this->expectNotToPerformAssertions();
        DB::connection('clickhouse')->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => 321, 'status' => 204]
        );
    }

    public function test_table_insert(): void
    {
        // Arrange & Act & Assert
        $this->expectNotToPerformAssertions();
        DB::connection('clickhouse')->table('analytics')->insert([
            'ts' => time(),
            'analytic_id' => 321,
            'status' => 204,
        ]);
    }

    public function test_where(): void
    {
        // Arrange
        DB::connection('clickhouse')->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );
        DB::connection('clickhouse')->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );

        // Act & Assert
        self::assertSame(
            2,
            DB::connection('clickhouse')
                ->table('analytics')
                ->where('ts', '>', strtotime('-1 day'))
                ->count()
        );
    }

    public function test_multiple_wheres(): void
    {
        // Arrange
        DB::connection('clickhouse')->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );
        DB::connection('clickhouse')->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );

        // Act & Assert
        self::assertSame(
            2,
            DB::connection('clickhouse')
                ->table('analytics')
                ->where('ts', '>', strtotime('-1 day'))
                ->where('ts', '<', strtotime('+1 day'))
                ->count()
        );
    }

    public function test_select(): void
    {
        // Arrange
        DB::connection('clickhouse')->insert(
            'analytics',
            $row1 = ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );
        DB::connection('clickhouse')->insert(
            'analytics',
            $row2 = ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(600, 999)]
        );

        // Act
        $records = DB::connection('clickhouse')
            ->table('analytics')
            ->select('ts', 'status')
            ->get()
            ->toArray();
        // ensure order of records
        usort($records, static function ($a, $b) {
            if ($a['status'] === $b['status']) {
                return 0;
            }

            return ($a['status'] < $b['status']) ? -1 : 1;
        });

        // Assert
        self::assertEquals($row1['ts'], $records[0]['ts']);
        self::assertEquals($row1['status'], $records[0]['status']);
        self::assertEquals($row2['ts'], $records[1]['ts']);
        self::assertEquals($row2['status'], $records[1]['status']);
    }

    public function test_select_raw(): void
    {
        // Arrange
        DB::connection('clickhouse')->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]
        );

        // Act
        $record = DB::connection('clickhouse')
            ->table('analytics')
            ->selectRaw('toMonth(dt) as month_number')
            ->first();

        // Assert
        self::assertEquals($record['month_number'], idate('m'));
    }

    public function test_join(): void
    {
        // Arrange
        DB::connection('clickhouse')->statement('TRUNCATE TABLE IF EXISTS models');
        DB::connection('clickhouse')->statement('
            CREATE TABLE IF NOT EXISTS models (
                dt   Date DEFAULT toDate(ts),
                ts   DateTime,
                id   UInt32,
                name String
            )
            ENGINE = MergeTree
            PARTITION BY dt
            ORDER BY (id, dt);
        ');
        DB::connection('clickhouse')->insert(
            'models',
            ['ts' => time(), 'id' => 321, 'name' => 'Cool Name']
        );
        DB::connection('clickhouse')->insert(
            'analytics',
            ['ts' => time(), 'analytic_id' => 321, 'status' => $status = mt_rand(200, 599)]
        );

        // Act
        $record = DB::connection('clickhouse')
            ->table('analytics')
            ->join('models', 'models.id', '=', 'analytics.analytic_id')
            ->selectRaw('models.name as name, analytics.status as status')
            ->first();

        // Assert
        self::assertEquals($record['name'], 'Cool Name');
        self::assertEquals($record['status'], $status);
        DB::connection('clickhouse')->statement('TRUNCATE TABLE IF EXISTS models');
    }
}
