<?php

declare(strict_types=1);

namespace Pao\Drivers\Phpstan;

use Pao\Drivers\Starter as BaseStarter;
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

        $this->silenceStdout();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parse(): ?array
    {
        $captured = trim(CaptureFilter::output());

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

        /** @var list<array{file: string, line: int, message: string, identifier: string}> $errorDetails */
        $errorDetails = [];

        /** @var array<string, array{errors: int, messages: list<array{message: string, line: int, identifier?: string}>}> $files */
        $files = is_array($data['files'] ?? null) ? $data['files'] : [];

        foreach ($files as $file => $fileData) {
            foreach ($fileData['messages'] as $message) {
                $errorDetails[] = [
                    'file' => $file,
                    'line' => $message['line'],
                    'message' => $message['message'],
                    'identifier' => $message['identifier'] ?? 'unknown',
                ];
            }
        }

        /** @var list<string> $errors */
        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];

        /** @var list<string> $generalErrors */
        $generalErrors = array_values(array_filter($errors, static fn (string $error): bool => $error !== ''));

        $totalErrors = count($errorDetails) + count($generalErrors);

        /** @var array<string, mixed> $result */
        $result = [
            'result' => $totalErrors > 0 ? 'failed' : 'passed',
            'errors' => $totalErrors,
        ];

        if ($errorDetails !== []) {
            $result['error_details'] = $errorDetails;
        }

        if ($generalErrors !== []) {
            $result['general_errors'] = $generalErrors;
        }

        return $result;
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
