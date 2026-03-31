<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class NoticeTest extends TestCase
{
    public function test_it_triggers_notice(): void
    {
        @trigger_error('FYI something happened', E_USER_NOTICE);

        $this->assertTrue(true);
    }
}
