<?php

declare(strict_types=1);
use Symfony\Component\Process\Process;

$pestStdoutConfig = 'tests/Fixtures/Pest/phpunit.xml';

function decodeFromMixedOutput(Process $process): mixed
{
    $raw = $process->getOutput();

    $jsonStart = strpos($raw, '{"result":');

    if ($jsonStart !== false && $jsonStart > 0) {
        $raw = substr($raw, $jsonStart);
    }

    return json_decode(trim($raw), associative: true, flags: JSON_THROW_ON_ERROR);
}

it('phpunit produces valid json despite fwrite stdout noise', function (): void {
    $output = decodeFromMixedOutput(runWith('phpunit', 'StdoutStressTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(7);
});

it('phpunit produces valid json for failing tests with stdout noise', function (): void {
    $output = decodeFromMixedOutput(runWith('phpunit', 'StdoutFailingStressTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(4)
        ->and($output['passed'])->toBe(1)
        ->and($output['failed'])->toBe(2)
        ->and($output['errors'])->toBe(1);
});

it('pest produces clean json despite fwrite stdout in passing test', function () use ($pestStdoutConfig): void {
    $output = decodeOutput(runWith('pest', 'PestStdoutTest::it writes to stdout and passes', config: $pestStdoutConfig));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('pest produces clean json despite fwrite stdout in failing test', function () use ($pestStdoutConfig): void {
    $output = decodeOutput(runWith('pest', 'PestStdoutTest::it writes to stdout and fails', config: $pestStdoutConfig));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(1)
        ->and($output['failed'])->toBe(1);
});

it('pest produces clean json despite large fwrite stdout', function () use ($pestStdoutConfig): void {
    $output = decodeOutput(runWith('pest', 'PestStdoutTest::it writes large output and passes', config: $pestStdoutConfig));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('paratest produces valid json despite fwrite stdout noise', function (): void {
    $output = decodeOutput(runWith('paratest', 'StdoutStressTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(7);
});

it('paratest produces valid json for failing tests with stdout noise', function (): void {
    $output = decodeOutput(runWith('paratest', 'StdoutFailingStressTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(4)
        ->and($output['passed'])->toBe(1);
});

it('pest parallel produces valid json for failing tests with stdout noise', function (): void {
    $output = decodeOutput(runWith('pest', 'StdoutFailingStressTest', extraArgs: ['--parallel']));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(4)
        ->and($output['passed'])->toBe(1);
});
