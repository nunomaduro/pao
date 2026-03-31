<?php

declare(strict_types=1);

namespace Pao\Drivers\Pest;

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
        $this->saveStdout();
        $this->silenceStdout();
    }
}
