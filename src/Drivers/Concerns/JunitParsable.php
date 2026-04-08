<?php

declare(strict_types=1);

namespace Pao\Drivers\Concerns;

use SimpleXMLElement;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
trait JunitParsable
{
    /**
     * @return array<string, mixed>|null
     */
    public ?string $junitFile = null;

    /**
     * @return array<string, mixed>|null
     */
    public function parse(): ?array
    {
        $junitFile = $this->junitFile;

        if ($junitFile === null) {
            return null;
        }

        if (! file_exists($junitFile)) {
            return null;
        }

        $xml = @simplexml_load_file($junitFile);

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

        $profileEnabled = in_array('--profile', $_SERVER['argv'] ?? [], true);

        /** @var list<array{test: string, file: string, duration_ms: int}> $profileEntries */
        $profileEntries = [];

        foreach ($xml->xpath('//testcase') ?? [] as $testcase) {
            if (property_exists($testcase, 'skipped') && $testcase->skipped !== null) {
                $skipped++;
            }

            if (property_exists($testcase, 'failure') && $testcase->failure !== null) {
                $message = trim((string) $testcase->failure);
                [$file, $line] = $this->resolveLocation((string) $testcase['file'], (int) $testcase['line'], $message);

                $failureDetails[] = [
                    'test' => $testcase['class'].'::'.$testcase['name'],
                    'file' => $file,
                    'line' => $line,
                    'message' => $message,
                ];
            }

            if (property_exists($testcase, 'error') && $testcase->error !== null) {
                $message = trim((string) $testcase->error);
                [$file, $line] = $this->resolveLocation((string) $testcase['file'], (int) $testcase['line'], $message);

                $errorDetails[] = [
                    'test' => $testcase['class'].'::'.$testcase['name'],
                    'file' => $file,
                    'line' => $line,
                    'message' => $message,
                ];
            }

            if ($profileEnabled) {
                $file = (string) $testcase['file'];
                $doubleColonPos = strpos($file, '::');
                if ($doubleColonPos !== false) {
                    $file = substr($file, 0, $doubleColonPos);
                }

                $profileEntries[] = [
                    'test' => (string) $testcase['class'].'::'.(string) $testcase['name'],
                    'file' => $file,
                    'duration_ms' => (int) round((float) $testcase['time'] * 1000),
                ];
            }
        }

        /** @var array<string, mixed> $result */
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

        if ($profileEntries !== []) {
            usort($profileEntries, fn (array $a, array $b): int => $b['duration_ms'] <=> $a['duration_ms']);
            $result['profile'] = array_slice($profileEntries, 0, 10);
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $argv
     * @return array<int, string>
     */
    public function ensureJunitLog(array $argv): array
    {
        if ($this->junitFile === null) {
            $this->junitFile = sys_get_temp_dir().'/agent-output-'.bin2hex(random_bytes(8)).'.xml';
        }

        if (! in_array('--log-junit', $argv, true)) {
            $argv[] = '--log-junit';
            $argv[] = $this->junitFile;

            return $argv;
        }

        $index = array_search('--log-junit', $argv, true);

        if ($index !== false && isset($argv[$index + 1])) {
            $this->junitFile = $argv[$index + 1];
        }

        return $argv;
    }

    /**
     * @return array{string, int}
     */
    private function resolveLocation(string $file, int $line, string $message): array
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
