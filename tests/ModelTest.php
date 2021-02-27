<?php

namespace Patoui\LaravelClickhouse\Tests;

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
}
