<?php

declare(strict_types=1);

namespace Pao\Support;

use SimpleXMLElement;

/**
 * @internal
 *
 * @phpstan-import-type Result from \Pao\Execution
 */
final class JunitParser
{
    /**
     * @return Result|null
     */
    public static function parse(string $junitFile): ?array
    {
        if (! file_exists($junitFile)) {
            return null;
        }

        $xml = simplexml_load_file($junitFile);

        if (! $xml instanceof SimpleXMLElement) {
            return null;
        }

        $tests = 0;
        $failures = 0;
        $errors = 0;
        $skipped = 0;
        $duration = 0.0;

        /** @var list<array{test: string, file: string, line: int, message: string}> $failureDetails */
        $failureDetails = [];

        /** @var list<array{test: string, file: string, line: int, message: string}> $errorDetails */
        $errorDetails = [];

        foreach ($xml->testsuite as $suite) {
            $tests += (int) $suite['tests'];
            $failures += (int) $suite['failures'];
            $errors += (int) $suite['errors'];
            $duration += (float) $suite['time'];
        }

        foreach ($xml->xpath('//testcase') ?? [] as $testcase) {
            if (property_exists($testcase, 'skipped') && $testcase->skipped !== null) {
                $skipped++;
            }

            if (property_exists($testcase, 'failure') && $testcase->failure !== null) {
                $message = trim((string) $testcase->failure);
                [$file, $line] = self::resolveLocation((string) $testcase['file'], (int) $testcase['line'], $message);

                $failureDetails[] = [
                    'test' => $testcase['class'].'::'.$testcase['name'],
                    'file' => $file,
                    'line' => $line,
                    'message' => $message,
                ];
            }

            if (property_exists($testcase, 'error') && $testcase->error !== null) {
                $message = trim((string) $testcase->error);
                [$file, $line] = self::resolveLocation((string) $testcase['file'], (int) $testcase['line'], $message);

                $errorDetails[] = [
                    'test' => $testcase['class'].'::'.$testcase['name'],
                    'file' => $file,
                    'line' => $line,
                    'message' => $message,
                ];
            }
        }

        /** @var Result $result */
        $result = [
            'result' => ($failures > 0 || $errors > 0) ? 'failed' : 'passed',
            'tests' => $tests,
            'passed' => $tests - $failures - $errors - $skipped,
            'duration_ms' => (int) round($duration * 1000),
        ];

        if ($failures > 0) {
            $result['failed'] = $failures;
            $result['failures'] = $failureDetails;
        }

        if ($errors > 0) {
            $result['errors'] = $errors;
            $result['error_details'] = $errorDetails;
        }

        if ($skipped > 0) {
            $result['skipped'] = $skipped;
        }

        return $result;
    }

    /**
     * @return array{string, int}
     */
    private static function resolveLocation(string $file, int $line, string $message): array
    {
        if ($line > 0) {
            return [$file, $line];
        }

        if (preg_match('/\bat\s+(.+\.php):(\d+)/', $message, $matches) === 1) {
            return [$matches[1], (int) $matches[2]];
        }

        return [$file, $line];
    }
}
