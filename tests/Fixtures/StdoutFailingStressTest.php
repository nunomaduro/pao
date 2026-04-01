<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class StdoutFailingStressTest extends TestCase
{
    public function test_fwrite_then_fail(): void
    {
        fwrite(STDOUT, 'output before failure');

        $this->assertTrue(false);
    }

    public function test_fail_then_fwrite(): void
    {
        $this->assertSame('expected', 'actual');
    }

    public function test_error_with_stdout(): void
    {
        fwrite(STDOUT, 'output before error');

        throw new \RuntimeException('Boom after stdout write');
    }

    public function test_passes_between_failures(): void
    {
        fwrite(STDOUT, 'this test passes');

        $this->assertTrue(true);
    }
}
