<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class DeprecationTest extends TestCase
{
    public function test_it_triggers_deprecation(): void
    {
        trigger_error('This function is deprecated', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }
}
