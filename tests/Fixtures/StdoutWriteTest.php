<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class StdoutWriteTest extends TestCase
{
    public function test_fwrite_stdout(): void
    {
        fwrite(STDOUT, 'direct stdout write');

        $this->assertTrue(true);
    }

    public function test_stdout_is_valid_resource(): void
    {
        $this->assertTrue(is_resource(STDOUT) || STDOUT instanceof \CurlHandle === false);
    }
}
