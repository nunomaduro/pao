<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ManyTestsTest extends TestCase
{
    /** @return array<string, array{int}> */
    public static function hundredDatasets(): array
    {
        $data = [];
        for ($i = 1; $i <= 100; $i++) {
            $data["dataset {$i}"] = [$i];
        }

        return $data;
    }

    #[DataProvider('hundredDatasets')]
    public function test_with_many_datasets(int $value): void
    {
        $this->assertGreaterThan(0, $value);
    }
}
