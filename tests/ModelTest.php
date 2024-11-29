<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse\Tests;

use Illuminate\Support\Facades\DB;

class ModelTest extends TestCase
{
    public function testCreate(): void
    {
        // Arrange & Act & Assert
        $this->expectNotToPerformAssertions();
        Analytic::create(['ts' => time(), 'analytic_id' => 321, 'status' => 204]);
    }

    public function testCreateCount(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => 321, 'status' => 204]);
        Analytic::create(['ts' => time() + 1, 'analytic_id' => 123, 'status' => 204]);

        // Act & Assert
        self::assertEquals(2, Analytic::count());
    }

    public function testWhere(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            2,
            Analytic::where('ts', '>', strtotime('-1 day'))->count()
        );
    }

    public function testWhereString(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599), 'name' => 'page_view']);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599), 'name' => 'page_bookmarked']);

        // Act & Assert
        self::assertSame(
            1,
            Analytic::where('name', 'page_bookmarked')->count()
        );
    }

    public function testMultipleWhere(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            2,
            Analytic::where('ts', '>', strtotime('-1 day'))
                    ->where('ts', '<', strtotime('+1 day'))
                    ->count()
        );
    }

    public function testUpdate(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => 123, 'status' => 204, 'name' => 'page_view']);

        // Pre-assert
        self::assertSame(
            1,
            Analytic::where('name', 'page_view')
                    ->where('status', 204)
                    ->count()
        );

        // Act
        Analytic::where('name', 'page_view')
                ->where('status', 204)
                ->update([
                    'name'   => 'page_visit',
                    'status' => 200,
                ]);

        // Needed to prevent race condition failure
        usleep(100000);

        // Assert
        self::assertSame(
            1,
            Analytic::where('name', 'page_visit')
                    ->where('status', 200)
                    ->count()
        );
    }

    public function testJsonExtract(): void
    {
        // Arrange
        Analytic::create([
            'ts'          => time(),
            'analytic_id' => 123,
            'status'      => 200,
            'name'        => json_encode(['action' => 'page_visit', 'referer' => ['https://google.com/']]),
        ]);
        Analytic::create([
            'ts'          => time(),
            'analytic_id' => 124,
            'status'      => 200,
            'name'        => json_encode(['action' => 'page_view', 'referer' => ['https://duckduckgo.com/']]),
        ]);

        // Act
        $results = Analytic::select([
            'dt',
            'ts',
            'analytic_id',
            'status',
            DB::raw("JSONExtractString(name, 'action') as action"),
            DB::raw("arrayElement(JSONExtract(name, 'referer', 'Array(String)'), 1) as main_referer"),
        ])->where('status', 200)->orderBy('analytic_id')->get();

        // Assert
        self::assertCount(2, $results);
        self::assertSame('page_visit', $results[0]->action);
        self::assertSame('https://google.com/', $results[0]->main_referer);
        self::assertSame('page_view', $results[1]->action);
        self::assertSame('https://duckduckgo.com/', $results[1]->main_referer);
    }
}
