<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class ErrorHandlerTest extends TestCase
{
    public function test_custom_error_handler(): void
    {
        $triggered = false;

        set_error_handler(static function () use (&$triggered): bool {
            $triggered = true;

            return true;
        });

        @trigger_error('test', E_USER_NOTICE);

        restore_error_handler();

        $this->assertTrue($triggered);
    }

    public function test_custom_exception_handler(): void
    {
        $previous = set_exception_handler(static function (): void {});

        restore_exception_handler();

        $this->assertTrue(true);
    }
}
