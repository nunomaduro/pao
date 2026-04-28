<?php

declare(strict_types=1);

namespace Laravel\Pao\Drivers\Rector;

use Laravel\Pao\Drivers\Starter as BaseStarter;
use Laravel\Pao\UserFilters\CaptureFilter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Starter extends BaseStarter
{
    private ?int $outputBufferLevel = null;

    public function name(): string
    {
        return 'rector';
    }

    public function start(): void
    {
        $this->registerNullFilter();
        $this->silenceStderr();

        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'];
        $argv = $this->ensureOutputFormatJson($argv);
        $argv = $this->ensureNoProgressBar($argv);
        $_SERVER['argv'] = $argv;
        $GLOBALS['argv'] = $argv;

        $this->silenceStdout();

        $this->outputBufferLevel = ob_get_level();
        ob_start();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parse(): ?array
    {
        $captured = trim(CaptureFilter::output().$this->bufferedOutput());

        CaptureFilter::reset();

        if ($captured === '') {
            return null;
        }

        $start = strpos($captured, '{');

        if ($start !== false && $start > 0) {
            $captured = substr($captured, $start);
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($captured, associative: true);

        if (! is_array($data) || ! is_array($data['totals'] ?? null)) {
            return null;
        }

        /** @var array<string, mixed> $totals */
        $totals = $data['totals'];
        $changedFiles = $totals['changed_files'] ?? 0;
        $errors = $totals['errors'] ?? 0;

        if (! is_int($changedFiles) || ! is_int($errors)) {
            return null;
        }

        /** @var array<string, mixed> $result */
        $result = [
            'result' => $errors > 0 || ($changedFiles > 0 && $this->isDryRun()) ? 'failed' : 'passed',
            'changed_files' => $changedFiles,
            'errors' => $errors,
        ];

        $changeDetails = $this->changeDetails($data);

        if ($changeDetails !== []) {
            $verbose = $this->isVerbose();
            $limit = 30;

            if (! $verbose && count($changeDetails) > $limit) {
                $result['change_details'] = array_slice($changeDetails, 0, $limit);
                $result['truncated'] = true;
                $result['hint'] = 'Pass -v to see all changes.';
            } else {
                $result['change_details'] = $changeDetails;
            }
        }

        $errorDetails = $this->errorDetails($data);

        if ($errorDetails !== []) {
            $result['error_details'] = $errorDetails;
        }

        return $result;
    }

    private function bufferedOutput(): string
    {
        if ($this->outputBufferLevel === null) {
            return '';
        }

        $buffered = '';

        while (ob_get_level() > $this->outputBufferLevel) {
            $buffer = ob_get_clean();

            if ($buffer === false) {
                break;
            }

            $buffered = $buffer.$buffered;
        }

        return $buffered;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{file: string, line?: int, applied_rectors?: list<string>}>
     */
    private function changeDetails(array $data): array
    {
        $details = [];

        /** @var list<array{file?: mixed, diff?: mixed, applied_rectors?: mixed}> $fileDiffs */
        $fileDiffs = is_array($data['file_diffs'] ?? null) ? $data['file_diffs'] : [];

        foreach ($fileDiffs as $fileDiff) {
            if (! is_string($fileDiff['file'] ?? null)) {
                continue;
            }

            $detail = ['file' => $fileDiff['file']];

            if (is_string($fileDiff['diff'] ?? null)) {
                $line = $this->firstChangedLine($fileDiff['diff']);

                if ($line !== null) {
                    $detail['line'] = $line;
                }
            }

            if (is_array($fileDiff['applied_rectors'] ?? null)) {
                $appliedRectors = array_values(array_filter(
                    $fileDiff['applied_rectors'],
                    is_string(...),
                ));

                if ($appliedRectors !== []) {
                    $detail['applied_rectors'] = array_map(
                        static fn (string $rector): string => basename(str_replace('\\', '/', $rector)),
                        $appliedRectors,
                    );
                }
            }

            $details[] = $detail;
        }

        if ($details !== []) {
            return $details;
        }

        /** @var list<mixed> $changedFiles */
        $changedFiles = is_array($data['changed_files'] ?? null) ? $data['changed_files'] : [];

        foreach ($changedFiles as $changedFile) {
            if (is_string($changedFile)) {
                $details[] = ['file' => $changedFile];
            }
        }

        return $details;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{message: string, file?: string, line?: int, caused_by?: string}>
     */
    private function errorDetails(array $data): array
    {
        $details = [];

        /** @var list<array{message?: mixed, file?: mixed, line?: mixed, caused_by?: mixed}> $errors */
        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];

        foreach ($errors as $error) {
            if (! is_string($error['message'] ?? null)) {
                continue;
            }

            $detail = ['message' => $error['message']];

            if (is_string($error['file'] ?? null)) {
                $detail['file'] = $error['file'];
            }

            if (is_int($error['line'] ?? null)) {
                $detail['line'] = $error['line'];
            }

            if (is_string($error['caused_by'] ?? null)) {
                $detail['caused_by'] = $error['caused_by'];
            }

            $details[] = $detail;
        }

        return $details;
    }

    private function firstChangedLine(string $diff): ?int
    {
        $matches = [];

        if (preg_match('/@@\s+-\d+(?:,\d+)?\s+\+(?<line>\d+)/', $diff, $matches) !== 1) {
            return null;
        }

        return (int) $matches['line'];
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

    /**
     * @param  array<int, string>  $argv
     * @return array<int, string>
     */
    private function ensureNoProgressBar(array $argv): array
    {
        if (! in_array('--no-progress-bar', $argv, true)) {
            $argv[] = '--no-progress-bar';
        }

        return $argv;
    }

    private function isDryRun(): bool
    {
        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'] ?? [];

        foreach ($argv as $arg) {
            if (in_array($arg, ['--dry-run', '-n'], true)) {
                return true;
            }
        }

        return false;
    }

    private function isVerbose(): bool
    {
        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'] ?? [];

        foreach ($argv as $arg) {
            if (in_array($arg, ['-v', '-vv', '-vvv', '--verbose'], true)) {
                return true;
            }
        }

        return false;
    }
}
