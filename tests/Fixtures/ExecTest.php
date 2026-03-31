<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class ExecTest extends TestCase
{
    public function test_shell_exec(): void
    {
        $result = shell_exec('echo hello');

        $this->assertStringContainsString('hello', (string) $result);
    }

    public function test_proc_open(): void
    {
        $process = proc_open(
            [PHP_BINARY, '-r', 'echo "from child";'],
            [1 => ['pipe', 'w']],
            $pipes
        );

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($process);

        $this->assertSame('from child', $output);
    }
}
