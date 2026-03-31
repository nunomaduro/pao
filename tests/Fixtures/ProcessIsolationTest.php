<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class ProcessIsolationTest extends TestCase
{
    #[RunInSeparateProcess]
    public function test_in_separate_process(): void
    {
        $this->assertTrue(true);
    }

    public function test_in_main_process(): void
    {
        $this->assertTrue(true);
    }
}
