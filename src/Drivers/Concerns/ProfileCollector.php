<?php

declare(strict_types=1);

namespace Pao\Drivers\Concerns;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Finished;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class ProfileCollector
{
    private static float $preparedAt = 0.0;

    /** @var list<array{test: string, file: string, duration_ms: int}> */
    private static array $entries = [];

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
