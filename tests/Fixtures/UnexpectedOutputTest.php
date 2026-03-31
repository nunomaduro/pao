<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class UnexpectedOutputTest extends TestCase
{
    public function test_it_echoes(): void
    {
        echo 'Hello from test!';

        $this->assertTrue(true);
    }
}
