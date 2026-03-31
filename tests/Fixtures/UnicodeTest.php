<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class UnicodeTest extends TestCase
{
    public function test_unicode_assertion_message(): void
    {
        $this->assertSame('héllo wörld', 'héllo wörld');
    }

    public function test_emoji_in_assertion(): void
    {
        $this->assertSame('test passed ✅', 'test passed ✅');
    }

    public function test_unicode_failure(): void
    {
        $this->assertSame('café', 'cafe');
    }
}
