<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use php_user_filter;
use PHPUnit\Framework\TestCase;

final class StreamFilterTest extends TestCase
{
    public function test_can_register_custom_stream_filter(): void
    {
        $name = 'test_custom_filter_'.uniqid();

        $result = stream_filter_register($name, CustomTestFilter::class);

        $this->assertTrue($result);
    }

    public function test_stdout_stream_filters_are_listable(): void
    {
        $filters = stream_get_filters();

        $this->assertIsArray($filters);
    }
}

class CustomTestFilter extends php_user_filter
{
    public function filter($in, $out, &$consumed, bool $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
