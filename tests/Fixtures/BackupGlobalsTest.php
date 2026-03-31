<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
final class BackupGlobalsTest extends TestCase
{
    public function test_modifies_global(): void
    {
        $GLOBALS['pao_test_global'] = 'modified';

        $this->assertSame('modified', $GLOBALS['pao_test_global']);
    }

    public function test_global_is_restored(): void
    {
        $this->assertArrayNotHasKey('pao_test_global', $GLOBALS);
    }
}
