<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class ShutdownFunctionTest extends TestCase
{
    public function test_register_shutdown_function(): void
    {
        register_shutdown_function(static function (): void {
            //
        });

        $this->assertTrue(true);
    }
}
