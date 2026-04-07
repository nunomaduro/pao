<?php

declare(strict_types=1);

namespace Pao\Drivers\Phpunit;

use PHPUnit\Runner\Extension\Extension as ExtensionInterface;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Extension implements ExtensionInterface
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->replaceOutput();
        $facade->replaceProgressOutput();
        $facade->replaceResultOutput();

        $facade->registerSubscribers(
            new Subscribers\TestRunnerFinishedSubscriber,
        );
    }
}
