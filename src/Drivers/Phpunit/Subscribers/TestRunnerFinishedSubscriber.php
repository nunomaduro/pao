<?php

declare(strict_types=1);

namespace Pao\Drivers\Phpunit\Subscribers;

use Pao\Execution;
use Pao\Support\ToonEncoder;
use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final readonly class TestRunnerFinishedSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        $execution = Execution::current();

        if ($execution->stdout !== null) {
            return;
        }

        $data = $execution->result();

        if ($data === null) {
            return;
        }

        fwrite(STDOUT, ToonEncoder::encode($data).PHP_EOL);
    }
}
