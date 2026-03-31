<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataProviderTest extends TestCase
{
    /** @return array<string, array{int, int, int}> */
    public static function additionProvider(): array
    {
        return [
            'one plus one' => [1, 1, 2],
            'two plus two' => [2, 2, 4],
            'wrong result' => [1, 1, 99],
        ];
    }

    #[DataProvider('additionProvider')]
    public function test_addition(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, $a + $b);
    }
}
