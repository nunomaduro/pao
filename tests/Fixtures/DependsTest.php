<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class DependsTest extends TestCase
{
    public function test_first(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('test_first')]
    public function test_second(): void
    {
        $this->assertTrue(true);
    }
}
