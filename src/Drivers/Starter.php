<?php

declare(strict_types=1);

namespace Pao\Drivers;

use Pao\Contracts\Driver;
use Pao\Execution;
use Pao\UserFilters\NullFilter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
abstract class Starter implements Driver
{
    protected function registerNullFilter(): void
    {
        if (! in_array('agent_output_null', stream_get_filters(), true)) {
            stream_filter_register('agent_output_null', NullFilter::class);
        }
    }

    protected function silenceStdout(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return;
        }

        Execution::current()->filter = stream_filter_append(STDOUT, 'agent_output_null', STREAM_FILTER_WRITE) ?: null;
    }

    protected function saveStdout(): void
    {
        Execution::current()->stdout = @fopen('php://stdout', 'w') ?: STDOUT;
    }

    /**
     * @param  array<int, string>  $argv
     * @return array<int, string>
     */
    protected function ensureJunitLog(array $argv): array
    {
        return Execution::current()->ensureJunitLog($argv);
    }
}
