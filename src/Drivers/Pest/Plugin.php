<?php

declare(strict_types=1);

namespace Pao\Drivers\Pest;

use Pao\Drivers\Phpunit\Extension;
use Pao\Execution;
use Pest\Contracts\Plugins\HandlesArguments;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Plugin implements HandlesArguments
{
    public function __construct()
    {
        //
    }

    /**
     * @param  array<int, string>  $arguments
     * @return array<int, string>
     */
    public function handleArguments(array $arguments): array
    {
        if (! Execution::running()) {
            return $arguments;
        }

        $execution = Execution::current();

        $arguments = $execution->ensureJunitLog($arguments);

        $arguments[] = '--no-output';

        if (! in_array('--parallel', $arguments, true)) {
            $arguments[] = '--extension';
            $arguments[] = Extension::class;
        }

        return $arguments;
    }
}
