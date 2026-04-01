<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class StdoutStressTest extends TestCase
{
    public function test_fwrite_stdout_before_assertion(): void
    {
        fwrite(STDOUT, 'direct write before assertion');

        $this->assertTrue(true);
    }

    public function test_fwrite_stdout_after_assertion(): void
    {
        $this->assertTrue(true);

        fwrite(STDOUT, 'direct write after assertion');
    }

    public function test_multiple_fwrite_stdout(): void
    {
        for ($i = 0; $i < 100; $i++) {
            fwrite(STDOUT, "line {$i}\n");
        }

        $this->assertTrue(true);
    }

    public function test_echo_and_fwrite_mixed(): void
    {
        echo 'echo output';
        fwrite(STDOUT, 'fwrite output');
        echo 'more echo';

        $this->assertTrue(true);
    }

    public function test_fwrite_large_payload(): void
    {
        fwrite(STDOUT, str_repeat('x', 100000));

        $this->assertTrue(true);
    }

    public function test_fwrite_binary_data(): void
    {
        fwrite(STDOUT, "\x00\x01\x02\xFF");

        $this->assertTrue(true);
    }

    public function test_fwrite_unicode(): void
    {
        fwrite(STDOUT, '日本語テスト 🚀 émojis café');

        $this->assertTrue(true);
    }
}
