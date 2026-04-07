<?php

declare(strict_types=1);

namespace Pao;

use AgentDetector\AgentResult;
use Pao\Contracts\Driver;
use Pao\Exceptions\ShouldNotHappenException;
use Pao\Support\JunitParser;
use Pao\UserFilters\CaptureFilter;
use Random\RandomException;

/**
 * @internal
 *
 * @codeCoverageIgnore
 *
 * @phpstan-type TestDetail array{test: string, file: string, line: int, message: string}
 * @phpstan-type Result array{result: 'passed'|'failed', tests: int, passed: int, duration_ms: int, failed?: int, failures?: list<TestDetail>, errors?: int, error_details?: list<TestDetail>, skipped?: int, output?: list<string>}
 */
final class Execution
{
    private static ?self $instance = null;

    /**
     * @param  resource|null  $stdout
     * @param  resource|null  $filter
     */
    private function __construct(
        public readonly AgentResult $agent,
        public string $junitFile,
        public mixed $stdout = null,
        public mixed $filter = null,
    ) {
        //
    }

    /**
     * @param  array<int, string>  $argv
     *
     * @throws RandomException
     */
    public static function start(AgentResult $agent, array $argv): void
    {
        if (self::running()) {
            throw new ShouldNotHappenException;
        }

        $binary = basename($argv[0] ?? '');

        $starter = match ($binary) {
            'paratest' => new Drivers\Paratest\Starter,
            'pest' => new Drivers\Pest\Starter,
            'phpunit' => new Drivers\Phpunit\Starter,
            default => null,
        };

        if ($starter instanceof Driver) {
            self::$instance = new self(
                $agent,
                sys_get_temp_dir().'/agent-output-'.bin2hex(random_bytes(8)).'.xml',
            );

            $starter->start();
        }
    }

    public static function running(): bool
    {
        return self::$instance instanceof Execution;
    }

    public static function current(): self
    {
        return self::$instance ?? throw new ShouldNotHappenException;
    }

    public function restoreStdout(): void
    {
        if (is_resource($this->filter)) {
            stream_filter_remove($this->filter);

            $this->filter = null;
        }
    }

    public function flushStdout(): void
    {
        if (! is_resource($this->filter)) {
            return;
        }

        $captured = CaptureFilter::output();

        $this->restoreStdout();

        if ($captured !== '') {
            fwrite(STDOUT, $captured);
        }
    }

    /**
     * @param  array<int, string>  $argv
     * @return array<int, string>
     */
    public function ensureJunitLog(array $argv): array
    {
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
     * @return Result|null
     */
    public function result(): ?array
    {
        return JunitParser::parse($this->junitFile);
    }
}
