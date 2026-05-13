<?php

declare(strict_types=1);

/** @codeCoverageIgnoreStart */

namespace Laravel\Pao;

use Laravel\AgentDetector\AgentDetector;

/** @var array<int, string>|null $argv */
$argv = $_SERVER['argv'] ?? null;

if (! is_array($argv) || $argv === []) {
    return;
}

if (isset($_SERVER['PAO_DISABLE'])) {
    return;
}

$agent = AgentDetector::detect();

if (! $agent->isAgent && ! isset($_SERVER['PAO_FORCE'])) {
    return;
}

if (array_intersect($argv, ['--version', '--help', '-h', 'worker'])) {
    return;
}

unset($_SERVER['COLLISION_PRINTER']);
$_SERVER['PEST_PARALLEL_NO_OUTPUT'] = '1';

register_shutdown_function(function (): void {
    $lastError = error_get_last();
    $hasFatalError = $lastError !== null
        && in_array($lastError['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR], true);

    $fatalErrorResult = $hasFatalError ? [
        'result' => 'error',
        'message' => sprintf('%s in %s on line %d', $lastError['message'], $lastError['file'], $lastError['line']),
    ] : [];

    if (! Execution::running()) {
        if ($fatalErrorResult !== []) {
            $binary = basename(($_SERVER['argv'] ?? [])[0] ?? 'unknown');

            fwrite(STDOUT, json_encode(['tool' => $binary] + $fatalErrorResult, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR).PHP_EOL);
        }

        return;
    }

    $execution = Execution::current();

    $result = $execution->driver->parse() ?: [];

    $captured = trim(UserFilters\CaptureFilter::output());

    $execution->restoreStdout();

    if ($result === []) {
        $result = $fatalErrorResult;
    }

    if ($captured !== '') {
        $captured = OutputCleaner::clean($captured);

        $lines = array_values(array_filter(
            array_map(trim(...), explode("\n", $captured)),
            fn (string $line): bool => $line !== ''
                && ! preg_match('/^[.st!]+$/', $line)
                && ! preg_match('/^(Tests:|Duration:|Parallel:|Time:|Generating code coverage)\s/', $line)
                && ! str_ends_with($line, 'by Sebastian Bergmann and contributors.'),
        ));

        if ($lines !== []) {
            $result['raw'] = $lines;
        }
    }

    if ($result !== []) {
        $result = ['tool' => $execution->driver->name()] + $result;

        $json = json_encode($result, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR).PHP_EOL;

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $stdout = is_resource($execution->stdout) ? $execution->stdout : STDOUT;
        fwrite($stdout, $json);
    }
});

Execution::start($agent, $argv);
