<?php

declare(strict_types=1);

namespace Pao\Drivers\Phpunit\Subscribers;

use JsonException;
use Pao\Execution;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final readonly class TestRunnerFinishedSubscriber implements FinishedSubscriber
{
    /**
     * @throws JsonException
     */
    public function notify(Finished $event): void
    {
        $execution = Execution::current();

        if ($execution->stdout !== null) {
            return;
        }

        $telemetryInfo = $event->telemetryInfo();
        $memoryUsage = $telemetryInfo->memoryUsage();
        $memory = (float) ($memoryUsage->bytes() / 1024 / 1024);

        $data = $execution->result($memory);

        if ($data === null) {
            return;
        }

        fwrite(STDOUT, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
    }
}
