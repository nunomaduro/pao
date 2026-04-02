<?php

declare(strict_types=1);

namespace Pao\Support;

/**
 * @link https://github.com/toon-format/spec
 *
 * @internal
 *
 * @phpstan-import-type Result from \Pao\Execution
 */
final class ToonEncoder
{
    /**
     * @param  Result  $data
     */
    public static function encode(array $data): string
    {
        $lines = [];

        $lines[] = 'result: '.$data['result'];
        $lines[] = 'tests: '.$data['tests'];
        $lines[] = 'passed: '.$data['passed'];
        $lines[] = 'duration_ms: '.$data['duration_ms'];

        if (isset($data['failed'])) {
            $lines[] = 'failed: '.$data['failed'];
        }

        if (isset($data['failures']) && $data['failures'] !== []) {
            self::encodeTestDetails($lines, 'failures', $data['failures']);
        }

        if (isset($data['errors'])) {
            $lines[] = 'errors: '.$data['errors'];
        }

        if (isset($data['error_details']) && $data['error_details'] !== []) {
            self::encodeTestDetails($lines, 'error_details', $data['error_details']);
        }

        if (isset($data['skipped'])) {
            $lines[] = 'skipped: '.$data['skipped'];
        }

        if (isset($data['output']) && $data['output'] !== []) {
            $count = count($data['output']);
            $lines[] = "output[{$count}]:";

            foreach ($data['output'] as $line) {
                $lines[] = ' - '.self::escapeValue($line);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<string>  $lines
     * @param  list<array{test: string, file: string, line: int, message: string}>  $details
     */
    private static function encodeTestDetails(array &$lines, string $key, array $details): void
    {
        $count = count($details);
        $lines[] = "{$key}[{$count}]{test,file,line,message}:";

        foreach ($details as $detail) {
            $lines[] = ' '.implode(',', [
                self::escapeValue($detail['test']),
                self::escapeValue($detail['file']),
                $detail['line'],
                self::escapeValue($detail['message']),
            ]);
        }
    }

    private static function escapeValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        $needsQuoting = str_contains($value, ',')
            || str_contains($value, "\n")
            || str_contains($value, "\r")
            || str_contains($value, '"')
            || $value !== trim($value);

        if (! $needsQuoting) {
            return $value;
        }

        $escaped = str_replace('\\', '\\\\', $value);
        $escaped = str_replace('"', '\\"', $escaped);
        $escaped = str_replace("\n", '\\n', $escaped);
        $escaped = str_replace("\r", '\\r', $escaped);

        return '"'.$escaped.'"';
    }
}
