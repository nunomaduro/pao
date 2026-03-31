<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class RiskyTest extends TestCase
{
    public function test_it_has_no_assertions(): void
    {
        $result = 1 + 1; // @phpstan-ignore variable.unused
    }
}
