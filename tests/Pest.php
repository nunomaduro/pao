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

function cleanOutput(string $raw): string
{
    $raw = str_replace("\r", '', $raw);

    return (string) preg_replace('/\e\[[0-9;]*m/', '', trim($raw));
}

function decodeFromMixedOutput(Process $process): mixed
{
    $raw = cleanOutput($process->getOutput());

    $jsonStart = strpos($raw, '{"result":');

    if ($jsonStart !== false && $jsonStart > 0) {
        $raw = substr($raw, $jsonStart);
    }

    return json_decode($raw, associative: true, flags: JSON_THROW_ON_ERROR);
}

function decodeOutput(Process $process): mixed
{
    $raw = cleanOutput($process->getOutput());

    $decoded = json_decode($raw, associative: true);

    if ($decoded === null) {
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
            sprintf('Raw output length: %s%s', strlen($process->getOutput()), PHP_EOL).
            'STDOUT: '.substr($process->getOutput(), 0, 2000)."\n".
            'STDERR: '.substr($stderr, 0, 500)
        );
    }

    return $decoded;
}
