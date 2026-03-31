<?php

declare(strict_types=1);

namespace Pao\UserFilters;

use php_user_filter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class CaptureFilter extends php_user_filter
{
    private static string $captured = '';

    /**
     * @param  resource  $in
     * @param  resource  $out
     * @param  int  $consumed
     */
    public function filter($in, $out, &$consumed, bool $closing): int // @pest-ignore-type
    {
        /** @var object{data: string, datalen: int}|null $bucket */
        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;
            self::$captured .= $bucket->data;
            $bucket->data = '';
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    public static function output(): string
    {
        return self::$captured;
    }

    public static function reset(): void
    {
        self::$captured = '';
    }
}
