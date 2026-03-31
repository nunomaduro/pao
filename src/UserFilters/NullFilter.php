<?php

declare(strict_types=1);

namespace Pao\UserFilters;

use php_user_filter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class NullFilter extends php_user_filter
{
    public function filter($in, $out, &$consumed, bool $closing): int // @pest-ignore-type
    {
        /** @var object{data: string, datalen: int}|null $bucket */
        while ($bucket = stream_bucket_make_writeable($in)) {
            $consumed += $bucket->datalen;
            $bucket->data = '';
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
