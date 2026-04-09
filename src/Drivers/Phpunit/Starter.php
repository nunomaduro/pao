<?php

declare(strict_types=1);

namespace Pao\Drivers\Phpunit;

use Pao\Drivers\Concerns\JunitParsable;
use Pao\Drivers\Starter as BaseStarter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Starter extends BaseStarter
{
    use JunitParsable;

    public function start(): void
    {
        $this->registerNullFilter();

        /** @var list<string> $serverArgv */
        $serverArgv = $_SERVER['argv'];

        $argv = $this->ensureJunitLog($serverArgv);

        if (! in_array('--no-output', $argv, true)) {
            $argv[] = '--no-output';
        }

        $_SERVER['argv'] = $argv;
    }
}
