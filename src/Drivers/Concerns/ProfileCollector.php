<?php

declare(strict_types=1);

namespace Laravel\Pao\Drivers\Concerns;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\Finished;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class ProfileCollector
{
    private static bool $executionStarted = false;

    private static ?HRTime $startTime = null;

    private static float $preparedAt = 0.0;

    /** @var list<array{test: string, file: string, duration_ms: int}> */
    private static array $entries = [];

    public static function executionStarted(): void
    {
        self::$executionStarted = true;
    }

    public static function hasExecutionStarted(): bool
    {
        return self::$executionStarted;
    }

    public static function startTimer(HRTime $time): void
    {
        self::$startTime = $time;
    }

    public static function startTimerFromNanoseconds(float $nanoseconds): void
    {
        $seconds = (int) ($nanoseconds / 1_000_000_000);
        $nanos = (int) ($nanoseconds - ($seconds * 1_000_000_000));

        self::$startTime = HRTime::fromSecondsAndNanoseconds($seconds, $nanos);
    }

    public static function durationMs(): int
    {
        if (! self::$startTime instanceof HRTime) {
            return 0;
        }

        $startNs = (self::$startTime->seconds() * 1_000_000_000) + self::$startTime->nanoseconds();

        return (int) round((hrtime(true) - $startNs) / 1_000_000);
    }

    public static function prepared(): void
    {
        self::$preparedAt = hrtime(true);
    }

    public static function finished(Finished $event): void
    {
        $test = $event->test();

        $file = $test->file();
        $doubleColonPos = strpos($file, '::');
        if ($doubleColonPos !== false) {
            $file = substr($file, 0, $doubleColonPos);
        }

        self::$entries[] = [
            'test' => $test instanceof TestMethod ? $test->nameWithClass() : $test->id(),
            'file' => $file,
            'duration_ms' => self::$preparedAt > 0
                ? (int) round((hrtime(true) - self::$preparedAt) / 1_000_000)
                : (int) round($event->telemetryInfo()->durationSincePrevious()->asFloat() * 1000),
        ];

        self::$preparedAt = 0.0;
    }

    /**
     * @return list<array{test: string, file: string, duration_ms: int}>
     */
    public static function entries(): array
    {
        return self::$entries;
    }
}
