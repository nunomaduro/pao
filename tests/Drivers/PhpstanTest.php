<?php

declare(strict_types=1);

it('outputs json for clean code', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/clean/phpstan.neon');

    $output = decodeOutput($process);

    expect($output['result'])->toBe('passed')
        ->and($output['errors'])->toBe(0)
        ->and($output)->not->toHaveKey('error_details')
        ->and($output)->not->toHaveKey('general_errors');
});

it('outputs json for code with errors', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/errors/phpstan.neon');

    $output = decodeOutput($process);

    $filePath = array_key_first($output['error_details']);

    expect($output['result'])->toBe('failed')
        ->and($output['errors'])->toBe(2)
        ->and($output['error_details'][$filePath])->toHaveCount(2)
        ->and($output['error_details'][$filePath][0])->toHaveKeys(['line', 'message', 'identifier']);
});

it('includes correct identifiers in error details', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/errors/phpstan.neon');

    $output = decodeOutput($process);

    $identifiers = [];
    foreach ($output['error_details'] as $fileErrors) {
        foreach ($fileErrors as $error) {
            $identifiers[] = $error['identifier'];
        }
    }

    expect($identifiers)->toContain('missingType.return')
        ->and($identifiers)->toContain('method.notFound');
});

it('reports correct file path in error details', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/errors/phpstan.neon');

    $output = decodeOutput($process);

    $filePath = array_key_first($output['error_details']);

    expect($filePath)->toEndWith('HasErrors.php')
        ->and($output['error_details'][$filePath][0]['line'])->toBeGreaterThan(0);
});

it('passes through normal output without agent', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/errors/phpstan.neon', withAgent: false);

    $raw = $process->getOutput();

    expect($raw)->not->toContain('"result"')
        ->and($raw)->toContain('Found 2 errors');
});

it('truncates error details when more than 30 errors', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/many-errors/phpstan.neon');

    $output = decodeOutput($process);

    $totalErrors = array_sum(array_map(count(...), $output['error_details']));

    expect($output['result'])->toBe('failed')
        ->and($output['errors'])->toBe(50)
        ->and($totalErrors)->toBe(30)
        ->and($output['truncated'])->toBeTrue()
        ->and($output['hint'])->toBe('Pass -v to see all errors.');
});

it('shows all error details with verbose flag', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/many-errors/phpstan.neon', extraArgs: ['-v']);

    $output = decodeOutput($process);

    $totalErrors = array_sum(array_map(count(...), $output['error_details']));

    expect($output['result'])->toBe('failed')
        ->and($output['errors'])->toBe(50)
        ->and($totalErrors)->toBe(50)
        ->and($output)->not->toHaveKey('truncated')
        ->and($output)->not->toHaveKey('hint');
});
