<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function runWith(string $binary, string $filter, bool $withAgent = true, array $extraArgs = [], string $config = 'tests/Fixtures/phpunit.xml'): Process
{
    $env = [
        'AI_AGENT' => $withAgent ? '1' : false,
        'CLAUDECODE' => false,
        'CLAUDE_CODE' => false,
    ];

    $command = [PHP_BINARY, 'vendor/bin/'.$binary, '--configuration', $config, '--filter', $filter, ...$extraArgs];

    $process = new Process(
        command: $command,
        cwd: dirname(__DIR__),
        env: $env,
    );

    $process->run();

    return $process;
}

function decodeOutput(Process $process): mixed
{
    $raw = $process->getOutput();

    $jsonStart = strpos($raw, '{');

    if ($jsonStart !== false && $jsonStart > 0) {
        $raw = substr($raw, $jsonStart);
    }

    $decoded = json_decode(trim($raw), associative: true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $stderr = $process->getErrorOutput();
        $exitCode = $process->getExitCode();
        $command = $process->getCommandLine();

        $pluginsFile = dirname(__DIR__).'/vendor/pest-plugins.json';
        $plugins = file_exists($pluginsFile) ? file_get_contents($pluginsFile) : 'FILE NOT FOUND';

        throw new RuntimeException(
            'Failed to decode JSON: '.json_last_error_msg()."\n".
            sprintf('Command: %s%s', $command, PHP_EOL).
            sprintf('Exit code: %s%s', $exitCode, PHP_EOL).
            sprintf('OS: %s%s', PHP_OS_FAMILY, PHP_EOL).
            sprintf('pest-plugins.json: %s%s', $plugins, PHP_EOL).
            'STDOUT: '.substr($process->getOutput(), 0, 1000)."\n".
            'STDERR: '.substr($stderr, 0, 500)
        );
    }

    return $decoded;
}
