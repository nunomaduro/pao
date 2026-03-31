<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class MultipleFailuresTest extends TestCase
{
    public function test_it_passes(): void
    {
        $this->assertTrue(true);
    }

    public function test_it_fails_one(): void
    {
        $this->assertSame('expected', 'actual');
    }

    public function test_it_fails_two(): void
    {
        $this->assertTrue(false);
    }

    public function test_it_errors(): void
    {
        throw new \RuntimeException('Boom');
    }
}
