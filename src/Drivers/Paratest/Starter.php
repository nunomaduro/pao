<?php

declare(strict_types=1);

namespace Pao\Drivers\Paratest;

use Pao\Drivers\Starter as BaseStarter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Starter extends BaseStarter
{
    public function start(): void
    {
        $this->registerNullFilter();
        $this->silenceStdout();

        /** @var list<string> $serverArgv */
        $serverArgv = $_SERVER['argv'];

        $argv = $this->ensureJunitLog($serverArgv);

        $argv[] = '--runner';
        $argv[] = WrapperRunner::class;

        $_SERVER['argv'] = $argv;
    }
}
