<?php

declare(strict_types=1);

namespace Patoui\LaravelClickhouse\Tests\Feature;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Patoui\LaravelClickhouse\Tests\Models\Analytic;
use Patoui\LaravelClickhouse\Tests\TestCase;

class ModelTest extends TestCase
{
    public function test_create(): void
    {
        // Arrange & Act & Assert
        $this->expectNotToPerformAssertions();
        Analytic::create([
            'ts' => time(),
            'analytic_id' => 321,
            'status' => 204,
            'is_enabled' => true,
        ]);
    }

    public function test_create_count(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => 321, 'status' => 204]);
        Analytic::create(['ts' => time() + 1, 'analytic_id' => 123, 'status' => 204]);

        // Act & Assert
        self::assertEquals(2, Analytic::count());
    }

    public function test_where(): void
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

    public function test_where_string(): void
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

    public function test_or_where(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => $analyticId = mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => $analyticId++, 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            2,
            Analytic::where('analytic_id', $analyticId--)
                ->orWhere('analytic_id', $analyticId)
                ->count()
        );
    }

    public function test_where_date(): void
    {
        // Arrange
        Analytic::create(['ts' => strtotime('-1 day'), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            1,
            Analytic::whereDate('ts', date('Y-m-d'))->count()
        );
        self::assertSame(
            1,
            Analytic::whereDate('ts', new DateTimeImmutable)->count()
        );
    }

    public function test_where_day(): void
    {
        // Arrange
        Analytic::create(['ts' => strtotime('-1 day'), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            1,
            Analytic::whereDay('ts', date('d'))->count()
        );
        self::assertSame(
            1,
            Analytic::whereDay('ts', new DateTimeImmutable)->count()
        );
    }

    public function test_where_month(): void
    {
        // Arrange
        Analytic::create(['ts' => strtotime('-2 months'), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            1,
            Analytic::whereMonth('ts', date('m'))->count()
        );
        self::assertSame(
            1,
            Analytic::whereMonth('ts', new DateTimeImmutable)->count()
        );
    }

    public function test_where_year(): void
    {
        // Arrange
        Analytic::create(['ts' => strtotime('-2 years'), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            1,
            Analytic::whereYear('ts', date('Y'))->count()
        );
        self::assertSame(
            1,
            Analytic::whereYear('ts', new DateTimeImmutable)->count()
        );
    }

    public function test_where_time(): void
    {
        // Arrange
        $time = strtotime('-11 minutes');
        Analytic::create(['ts' => $time, 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599)]);

        // Act & Assert
        self::assertSame(
            1,
            Analytic::whereTime('ts', date('H:i:s', $time))->count()
        );
        self::assertSame(
            1,
            Analytic::whereTime(
                'ts',
                (new DateTimeImmutable)->setTimestamp($time)
            )->count()
        );
    }

    public function test_where_json(): void
    {
        // Arrange
        $time = strtotime('-11 minutes');
        Analytic::create([
            'ts' => time(),
            'analytic_id' => mt_rand(1000, 9999),
            'status' => mt_rand(200, 599),
            'metadata' => json_encode(['other_id' => $otherId = 'vid_known_identifier_321']),
        ]);
        Analytic::create([
            'ts' => time(),
            'analytic_id' => mt_rand(1000, 9999),
            'status' => mt_rand(200, 599),
            'metadata' => json_encode(['other_id' => uniqid('vid_', true)]),
        ]);

        // Act & Assert
        self::assertSame(
            1,
            Analytic::where('metadata->other_id', $otherId)->count()
        );
    }

    public function test_where_null(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599), 'name' => 'not_null', 'label' => 'Page View']);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599), 'name' => 'null']);

        // Act & Assert
        $analyticsQuery = Analytic::whereNull('label');
        self::assertSame(1, $analyticsQuery->count());
        self::assertSame(
            'null',
            $analyticsQuery->value('name'),
        );
    }

    public function test_where_not_null(): void
    {
        // Arrange
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599), 'name' => 'not_null', 'label' => 'Page View']);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => mt_rand(200, 599), 'name' => 'null']);

        // Act & Assert
        $analyticsQuery = Analytic::whereNotNull('label');
        self::assertSame(1, $analyticsQuery->count());
        self::assertSame(
            'not_null',
            $analyticsQuery->value('name'),
        );
    }

    public function test_where_in(): void
    {
        // Arrange
        $ok = 200;
        $created = 201;
        $accepted = 202;
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => $ok, 'name' => 'not_null', 'label' => 'Page View']);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => $created, 'name' => 'not_null', 'label' => 'Page View']);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => $accepted, 'name' => 'not_null', 'label' => 'Page View']);

        // Act & Assert
        $analyticsQuery = Analytic::whereIn('status', [$ok, $accepted]);
        self::assertSame(
            2,
            $analyticsQuery->count(),
        );
        $analytics = $analyticsQuery->get()->sortBy('status')->values();
        self::assertSame($ok, $analytics[0]->status);
        self::assertSame($accepted, $analytics[1]->status);
    }

    public function test_where_not_in(): void
    {
        // Arrange
        $ok = 200;
        $created = 201;
        $accepted = 202;
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => $ok, 'name' => 'not_null', 'label' => 'Page View']);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => $created, 'name' => 'not_null', 'label' => 'Page View']);
        Analytic::create(['ts' => time(), 'analytic_id' => mt_rand(1000, 9999), 'status' => $accepted, 'name' => 'not_null', 'label' => 'Page View']);

        // Act & Assert
        $analyticsQuery = Analytic::whereNotIn('status', [$ok, $accepted]);
        self::assertSame(
            1,
            $analyticsQuery->count(),
        );
        self::assertSame($created, $analyticsQuery->first()->status);
    }

    public function test_multiple_where(): void
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

    public function test_update(): void
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
                'name' => 'page_visit',
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

    public function test_json_extract(): void
    {
        // Arrange
        Analytic::create([
            'ts' => time(),
            'analytic_id' => 123,
            'status' => 200,
            'name' => json_encode(['action' => 'page_visit', 'referer' => ['https://google.com/']]),
        ]);
        Analytic::create([
            'ts' => time(),
            'analytic_id' => 124,
            'status' => 200,
            'name' => json_encode(['action' => 'page_view', 'referer' => ['https://duckduckgo.com/']]),
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
