<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class ErrorTest extends TestCase
{
    public function test_it_errors(): void
    {
        throw new \RuntimeException('Something went wrong');
    }
}
