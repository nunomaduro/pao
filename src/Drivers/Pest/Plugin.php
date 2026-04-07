<?php

declare(strict_types=1);

namespace Pao\Drivers\Pest;

use Pao\Drivers\Phpunit\Extension;
use Pao\Execution;
use Pao\UserFilters\CaptureFilter;
use Pest\Contracts\Plugins\AddsOutput;
use Pest\Contracts\Plugins\HandlesArguments;
use Pest\Contracts\Plugins\Terminable;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 *
 * @codeCoverageIgnore
 *
 * @phpstan-import-type Result from Execution
 */
final class Plugin implements AddsOutput, HandlesArguments, Terminable
{
    /** @var Result|null */
    private ?array $result = null;

    public function __construct(private readonly OutputInterface $output)
    {
        //
    }

    /**
     * @param  array<int, string>  $arguments
     * @return array<int, string>
     */
    public function handleArguments(array $arguments): array
    {
        if (! Execution::running()) {
            return $arguments;
        }

        $execution = Execution::current();

        $arguments = $execution->ensureJunitLog($arguments);

        if (! in_array('--parallel', $arguments, true)) {
            $arguments[] = '--extension';
            $arguments[] = Extension::class;
        }

        return $arguments;
    }

    public function addOutput(int $exitCode): int
    {
        if (! Execution::running()) {
            return $exitCode;
        }

        $execution = Execution::current();

        $memoryMb = (float) (memory_get_peak_usage(true) / 1024 / 1024);

        $this->result = $execution->result($memoryMb);

        $execution->captureStdout();

        return $exitCode;
    }

    public function terminate(): void
    {
        if ($this->result === null || ! Execution::running()) {
            return;
        }

        $execution = Execution::current();

        $captured = trim(CaptureFilter::output());

        $execution->restoreStdout();

        if ($captured !== '') {
            $captured = (string) preg_replace('/\e\[[0-9;]*m/', '', $captured);
            $captured = (string) preg_replace('/[─━│┌┐└┘├┤┬┴┼▓░▒═║╔╗╚╝╠╣╦╩╬]+/', '', $captured);
            $captured = (string) preg_replace('/\.{3,}/', ' ', $captured);
            $captured = (string) preg_replace('/[ \t]+/', ' ', $captured);
            $captured = (string) preg_replace('/\n\s*\n/', "\n", $captured);

            $lines = array_values(array_filter(array_map(trim(...), explode("\n", $captured))));

            if ($lines !== []) {
                $this->result['output'] = $lines;
            }
        }

        $this->output->writeln(json_encode($this->result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        $this->result = null;
    }
}
