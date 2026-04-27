<?php

declare(strict_types=1);

namespace Laravel\Pao\Drivers\Phpstan;

use Laravel\Pao\Drivers\Starter as BaseStarter;
use Laravel\Pao\UserFilters\CaptureFilter;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class Starter extends BaseStarter
{
    public function name(): string
    {
        return 'phpstan';
    }

    public function start(): void
    {
        $this->registerNullFilter();
        $this->silenceStderr();

        /** @var array<int, string> $argv */
        $argv = $_SERVER['argv'];
        $argv = $this->ensureErrorFormatJson($argv);
        $argv = $this->ensureNoProgress($argv);
        $_SERVER['argv'] = $argv;

        $this->silenceStdout();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parse(): ?array
    {
        $captured = trim(CaptureFilter::output());

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

        if (! is_array($data) || ! isset($data['totals'])) {
            return null;
        }

        /** @var array<string, list<array{line: int, message: string, identifier: string, ignorable?: bool, tip?: string}>> $errorDetails */
        $errorDetails = [];
        $totalFileErrors = 0;

        /** @var array<string, array{errors: int, messages: list<array{message: string, line: int, identifier?: string, ignorable?: bool, tip?: string}>}> $files */
        $files = is_array($data['files'] ?? null) ? $data['files'] : [];

        foreach ($files as $file => $fileData) {
            foreach ($fileData['messages'] as $message) {
                $totalFileErrors++;

                $detail = [
                    'line' => $message['line'],
                    'message' => $message['message'],
                    'identifier' => $message['identifier'] ?? 'unknown',
                ];

                if (isset($message['ignorable']) && $message['ignorable'] === false) {
                    $detail['ignorable'] = false;
                }

                if (isset($message['tip']) && $message['tip'] !== '') {
                    $detail['tip'] = $message['tip'];
                }

                $errorDetails[$file][] = $detail;
            }
        }

        /** @var list<string> $errors */
        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];

        /** @var list<string> $generalErrors */
        $generalErrors = array_values(array_filter($errors, static fn (string $error): bool => $error !== ''));

        $totalErrors = $totalFileErrors + count($generalErrors);

        /** @var array<string, mixed> $result */
        $result = [
            'result' => $totalErrors > 0 ? 'failed' : 'passed',
            'errors' => $totalErrors,
        ];

        if ($errorDetails !== []) {
            $verbose = $this->isVerbose();
            $limit = 30;

            if (! $verbose && $totalFileErrors > $limit) {
                $result['error_details'] = $this->truncateGrouped($errorDetails, $limit);
                $result['truncated'] = true;
                $result['hint'] = 'Pass -v to see all errors.';
            } else {
                $result['error_details'] = $errorDetails;
            }
        }

        if ($generalErrors !== []) {
            $result['general_errors'] = $generalErrors;
        }

        return $result;
    }

    /**
     * @param  array<string, list<array{line: int, message: string, identifier: string, ignorable?: bool, tip?: string}>>  $grouped
     * @return array<string, list<array{line: int, message: string, identifier: string, ignorable?: bool, tip?: string}>>
     */
    private function truncateGrouped(array $grouped, int $limit): array
    {
        $result = [];
        $count = 0;

        foreach ($grouped as $file => $errors) {
            foreach ($errors as $error) {
                if ($count >= $limit) {
                    break 2;
                }

                $result[$file][] = $error;
                $count++;
            }
        }

        return $result;
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
