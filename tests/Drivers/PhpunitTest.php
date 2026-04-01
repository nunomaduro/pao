<?php

declare(strict_types=1);

it('outputs json for passing tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'PassingTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(2)
        ->and($output['passed'])->toBe(2)
        ->and($output)->not->toHaveKey('failed')
        ->and($output)->not->toHaveKey('errors');
});

it('outputs json for failing tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'FailingTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(2)
        ->and($output['failed'])->toBe(1)
        ->and($output['failures'])->toHaveCount(1)
        ->and($output['failures'][0]['file'])->toEndWith('FailingTest.php')
        ->and($output['failures'][0]['line'])->toBeGreaterThan(0);
});

it('outputs json for errored tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'ErrorTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['errors'])->toBe(1)
        ->and($output['error_details'])->toHaveCount(1)
        ->and($output['error_details'][0]['message'])->toContain('Something went wrong');
});

it('outputs json for skipped tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'SkippedTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['skipped'])->toBe(1);
});

it('outputs json for incomplete tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'IncompleteTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for deprecation tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'DeprecationTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for warning tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'WarningTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for notice tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'NoticeTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for risky tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'RiskyTest'));

    expect($output['tests'])->toBe(1);
});

it('outputs json for data provider tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'DataProviderTest'));

    expect($output['tests'])->toBe(3)
        ->and($output['passed'])->toBe(2)
        ->and($output['failed'])->toBe(1)
        ->and($output['failures'])->toHaveCount(1);
});

it('outputs json for dependent tests', function (): void {
    $output = decodeOutput(runWith('phpunit', 'DependsTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(2)
        ->and($output['passed'])->toBe(2);
});

it('outputs toon for tests with unexpected output', function (): void {
    $output = decodeFromMixedOutput(runWith('phpunit', 'UnexpectedOutputTest'));

    expect($output['result'])->toBe('passed')
        ->and($output['tests'])->toBe(1);
});

it('outputs json for multiple failures and errors', function (): void {
    $output = decodeOutput(runWith('phpunit', 'MultipleFailuresTest'));

    expect($output['result'])->toBe('failed')
        ->and($output['tests'])->toBe(4)
        ->and($output['passed'])->toBe(1)
        ->and($output['failed'])->toBe(2)
        ->and($output['errors'])->toBe(1)
        ->and($output['failures'])->toHaveCount(2)
        ->and($output['error_details'])->toHaveCount(1);
});

it('outputs normal phpunit output when no agent is detected', function (): void {
    $process = runWith('phpunit', 'PassingTest', withAgent: false);

    expect($process->getOutput())->not->toContain('result: passed')
        ->and($process->getOutput())->toContain('OK');
});
