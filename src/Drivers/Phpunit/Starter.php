<?php

declare(strict_types=1);

namespace Pao\Drivers\Phpunit;

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

        /** @var list<string> $serverArgv */
        $serverArgv = $_SERVER['argv'];

        $argv = $this->ensureJunitLog($serverArgv);

        $argv[] = '--extension';
        $argv[] = Extension::class;
        $argv[] = '--no-output';

        $_SERVER['argv'] = $argv;
    }
}
