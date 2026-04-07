<?php

declare(strict_types=1);

/** @codeCoverageIgnoreStart */

namespace Pao;

use AgentDetector\AgentDetector;

/** @var array<int, string>|null $argv */
$argv = $_SERVER['argv'] ?? null;

if (! is_array($argv) || $argv === []) {
    return;
}

$agent = AgentDetector::detect();

if (! $agent->isAgent) {
    return;
}

if (array_intersect($argv, ['--version', '--help', '-h'])) {
    return;
}

unset($_SERVER['COLLISION_PRINTER']);

register_shutdown_function(function (): void {
    if (! Execution::running()) {
        return;
    }

    $execution = Execution::current();

    $result = $execution->driver->parse() ?: [];

    $captured = trim(UserFilters\CaptureFilter::output());

    $execution->restoreStdout();

    if ($captured !== '') {
        $captured = (string) preg_replace('/\e\[[0-9;]*[A-Za-z]/', '', $captured);
        $captured = (string) preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $captured);
        $captured = (string) preg_replace('/\x{FFFD}/u', '', $captured);
        $captured = (string) preg_replace('/[─━│┌┐└┘├┤┬┴┼▓░▒═║╔╗╚╝╠╣╦╩╬➜▶►⚠✖✔●◆■▪→←↑↓▕⨯✕]+/u', '', $captured);
        $captured = (string) preg_replace('/\.{3,}/', ' ', $captured);
        $captured = (string) preg_replace('/[ \t]+/', ' ', $captured);
        $captured = (string) preg_replace('/\n\s*\n/', "\n", $captured);

        $lines = array_values(array_filter(
            array_map(trim(...), explode("\n", $captured)),
            fn (string $line): bool => $line !== ''
                && ! preg_match('/^(Tests:|Duration:|Parallel:|Time:|Generating code coverage)\s/', $line)
                && ! str_ends_with($line, 'by Sebastian Bergmann and contributors.'),
        ));

        if ($lines !== []) {
            $result['raw'] = $lines;
        }
    }

    if ($result !== []) {
        fwrite(STDOUT, json_encode($result, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR).PHP_EOL);
    }
});

Execution::start($agent, $argv);
