<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class SkippedTest extends TestCase
{
    public function test_it_is_skipped(): void
    {
        $this->markTestSkipped('Not ready yet');
    }
}
