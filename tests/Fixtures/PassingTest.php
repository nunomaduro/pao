<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class PassingTest extends TestCase
{
    public function test_it_passes(): void
    {
        $this->assertTrue(true);
    }

    public function test_it_also_passes(): void
    {
        $this->assertSame('foo', 'foo');
    }

    public function test_it_is_slow(): void
    {
        usleep(200000);
        $this->assertSame('foo', 'foo');
    }
}
