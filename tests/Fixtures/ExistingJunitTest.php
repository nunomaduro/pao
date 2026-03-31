<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class ExistingJunitTest extends TestCase
{
    public function test_it_passes(): void
    {
        $this->assertTrue(true);
    }
}
