<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class OutputBufferingTest extends TestCase
{
    public function test_nested_ob_start(): void
    {
        ob_start();
        echo 'buffered output';
        $content = ob_get_clean();

        $this->assertSame('buffered output', $content);
    }

    public function test_multiple_ob_levels(): void
    {
        ob_start();
        ob_start();
        echo 'inner';
        $inner = ob_get_clean();
        echo 'outer';
        $outer = ob_get_clean();

        $this->assertSame('inner', $inner);
        $this->assertSame('outer', $outer);
    }

    public function test_ob_discard(): void
    {
        ob_start();
        echo 'discarded';
        ob_end_clean();

        $this->assertTrue(true);
    }
}
