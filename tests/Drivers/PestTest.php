<?php

declare(strict_types=1);

it('outputs json for passing tests', function (): void {
    $output = decodeOutput(runWith('pest', 'PassingTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(2)
        ->and($output['passed'])->toBe(2)
        ->and($output)->not->toHaveKey('failed')
        ->and($output)->not->toHaveKey('errors');
});

it('outputs json for failing tests', function (): void {
    $output = decodeOutput(runWith('pest', 'FailingTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(2)
        ->and($output['failed'])->toBe(1)
        ->and($output['failures'])->toHaveCount(1)
        ->and($output['failures'][0]['test'])->toContain('Failing');
});

it('outputs json for errored tests', function (): void {
    $output = decodeOutput(runWith('pest', 'ErrorTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['errors'])->toBe(1)
        ->and($output['error_details'])->toHaveCount(1)
        ->and($output['error_details'][0]['message'])->toContain('Something went wrong');
});

it('outputs json for skipped tests', function (): void {
    $output = decodeOutput(runWith('pest', 'SkippedTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['skipped'])->toBe(1);
});

it('outputs json for incomplete tests', function (): void {
    $output = decodeOutput(runWith('pest', 'IncompleteTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for deprecation tests', function (): void {
    $output = decodeOutput(runWith('pest', 'DeprecationTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for warning tests', function (): void {
    $output = decodeOutput(runWith('pest', 'WarningTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for notice tests', function (): void {
    $output = decodeOutput(runWith('pest', 'NoticeTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for risky tests', function (): void {
    $output = decodeOutput(runWith('pest', 'RiskyTest'));

    expect($output['tests'])->toBe(1);
});

it('outputs json for data provider tests', function (): void {
    $output = decodeOutput(runWith('pest', 'DataProviderTest'));

    expect($output['tests'])->toBe(3)
        ->and($output['passed'])->toBe(2)
        ->and($output['failed'])->toBe(1)
        ->and($output['failures'])->toHaveCount(1);
});

it('outputs json for dependent tests', function (): void {
    $output = decodeOutput(runWith('pest', 'DependsTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(2)
        ->and($output['passed'])->toBe(2);
});

it('outputs json for tests with unexpected output', function (): void {
    $output = decodeFromMixedOutput(runWith('pest', 'UnexpectedOutputTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for multiple failures and errors', function (): void {
    $output = decodeOutput(runWith('pest', 'MultipleFailuresTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(4)
        ->and($output['passed'])->toBe(1)
        ->and($output['failed'])->toBe(2)
        ->and($output['errors'])->toBe(1)
        ->and($output['failures'])->toHaveCount(2)
        ->and($output['error_details'])->toHaveCount(1);
});

it('outputs normal pest output when no agent is detected', function (): void {
    $process = runWith('pest', 'PassingTest', withAgent: false);

    expect($process->getOutput())->not->toContain('"result"')
        ->and($process->getOutput())->toContain('passed');
});
