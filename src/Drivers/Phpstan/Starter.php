<?php

declare(strict_types=1);

namespace Pao\Drivers\Phpstan;

use Pao\Drivers\Starter as BaseStarter;
use Pao\Execution;
use Pao\Support\PhpstanParser;
use Pao\UserFilters\CaptureFilter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Starter extends BaseStarter
{
    public function start(): void
    {
        if (! $this->isAnalyseCommand()) {
            return;
        }

        $this->registerNullFilter();
        $this->silenceStderr();

        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'];
        $argv = $this->ensureErrorFormatJson($argv);
        $argv = $this->ensureNoProgress($argv);
        $_SERVER['argv'] = $argv;

        $execution = Execution::current();
        $execution->captureStdout();

        register_shutdown_function(static function (): void {
            if (! Execution::running()) {
                return;
            }

            $execution = Execution::current();
            $execution->restoreStdout();

            $captured = trim(CaptureFilter::output());

            if ($captured === '') {
                return;
            }

            $result = PhpstanParser::parse($captured);

            if ($result === null) {
                return;
            }

            fwrite(STDOUT, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
        });
    }

    private function isAnalyseCommand(): bool
    {
        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'] ?? [];

        return in_array('analyse', $argv, true) || in_array('analyze', $argv, true);
    }

    /**
     * @param  array<int, string>  $argv
     * @return array<int, string>
     */
    private function ensureErrorFormatJson(array $argv): array
    {
        $filtered = [];
        $skipNext = false;

        foreach ($argv as $arg) {
            if ($skipNext) {
                $skipNext = false;

                continue;
            }

            if (str_starts_with($arg, '--error-format=')) {
                continue;
            }

            if ($arg === '--error-format') {
                $skipNext = true;

                continue;
            }

            $filtered[] = $arg;
        }

        $filtered[] = '--error-format=json';

        return $filtered;
    }

    /**
     * @param  array<int, string>  $argv
     * @return array<int, string>
     */
    private function ensureNoProgress(array $argv): array
    {
        if (! in_array('--no-progress', $argv, true)) {
            $argv[] = '--no-progress';
        }

        return $argv;
    }
}
