<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

final class DoesNotPerformAssertionsTest extends TestCase
{
    #[DoesNotPerformAssertions]
    public function test_no_assertions_but_not_risky(): void
    {
        $result = 1 + 1; // @phpstan-ignore variable.unused
    }
}
