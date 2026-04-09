<?php

declare(strict_types=1);

namespace Pao\Drivers\Pest;

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

        $arguments[] = '--no-output';

        return $arguments;
    }
}
