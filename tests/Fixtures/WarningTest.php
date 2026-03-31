<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class WarningTest extends TestCase
{
    public function test_it_triggers_warning(): void
    {
        @trigger_error('Something looks wrong', E_USER_WARNING);

        $this->assertTrue(true);
    }
}
