<?php

declare(strict_types=1);

namespace Pao\Laravel;

use AgentDetector\AgentDetector;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class ServiceProvider extends LaravelServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (isset($_SERVER['PAO_DISABLE'])) {
            return;
        }

        if (! AgentDetector::detect()->isAgent) {
            return;
        }

        $this->app->bind(OutputStyle::class, PaoOutputStyle::class);

        /** @var Dispatcher $events */
        $events = $this->app->make(Dispatcher::class);
        $events->listen(CommandStarting::class, function (CommandStarting $event): void {
            $event->output->setDecorated(false);
        });
    }
}
