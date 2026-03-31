<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class LargeOutputTest extends TestCase
{
    public function test_large_echo(): void
    {
        echo str_repeat('x', 10000);

        $this->assertTrue(true);
    }
}
