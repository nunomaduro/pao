<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class ArgvTest extends TestCase
{
    public function test_argv_is_accessible(): void
    {
        $argv = $_SERVER['argv'] ?? [];

        $this->assertIsArray($argv);
        $this->assertNotEmpty($argv);
    }

    public function test_argv_contains_phpunit_binary(): void
    {
        $argv = $_SERVER['argv'] ?? [];

        $this->assertStringContainsString('phpunit', (string) $argv[0]);
    }
}
