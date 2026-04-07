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

    expect($output['result'])->toBe('failed')
        ->and($output['errors'])->toBe(2)
        ->and($output['error_details'])->toHaveCount(2)
        ->and($output['error_details'][0])->toHaveKeys(['file', 'line', 'message', 'identifier']);
});

it('includes correct identifiers in error details', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/errors/phpstan.neon');

    $output = decodeOutput($process);

    $identifiers = array_column($output['error_details'], 'identifier');

    expect($identifiers)->toContain('missingType.return')
        ->and($identifiers)->toContain('method.notFound');
});

it('reports correct file path in error details', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/errors/phpstan.neon');

    $output = decodeOutput($process);

    expect($output['error_details'][0]['file'])->toEndWith('HasErrors.php')
        ->and($output['error_details'][0]['line'])->toBeGreaterThan(0);
});

it('passes through normal output without agent', function (): void {
    $process = runPhpstan('tests/Fixtures/Phpstan/errors/phpstan.neon', withAgent: false);

    $raw = $process->getOutput();

    expect($raw)->not->toContain('"result"')
        ->and($raw)->toContain('Found 2 errors');
});
