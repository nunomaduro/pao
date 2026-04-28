<?php

declare(strict_types=1);

namespace Laravel\Pao\Drivers\Rector;

use Laravel\Pao\Drivers\Starter as BaseStarter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Starter extends BaseStarter
{
    public function name(): string
    {
        return 'rector';
    }

    public function start(): void
    {
        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'];
        $argv = $this->ensureOutputFormatJson($argv);
        $_SERVER['argv'] = $argv;
        $GLOBALS['argv'] = $argv;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parse(): ?array
    {
        return null;
    }

    /**
     * @param  array<int, string>  $argv
     * @return array<int, string>
     */
    private function ensureOutputFormatJson(array $argv): array
    {
        $filtered = [];
        $skipNext = false;

        foreach ($argv as $arg) {
            if ($skipNext) {
                $skipNext = false;

                continue;
            }

            if (str_starts_with($arg, '--output-format=')) {
                continue;
            }

            if ($arg === '--output-format') {
                $skipNext = true;

                continue;
            }

            $filtered[] = $arg;
        }

        $filtered[] = '--output-format=json';

        return $filtered;
    }
}
