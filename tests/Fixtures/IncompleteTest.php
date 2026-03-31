<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class IncompleteTest extends TestCase
{
    public function test_it_is_incomplete(): void
    {
        $this->markTestIncomplete('Work in progress');
    }
}
