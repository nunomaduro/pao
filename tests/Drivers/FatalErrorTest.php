<?php

declare(strict_types=1);

$fatalConfig = 'tests/Fixtures/FatalRedeclaration/phpunit.xml';

it('outputs json with error details when a fatal error occurs during test loading', function () use ($fatalConfig): void {
    $process = runWith('pest', 'FileATest', config: $fatalConfig, extraEnv: ['PAO_DISABLE' => false]);

    $output = decodeFromMixedOutput($process);

    expect($output['tool'])->toBe('pest')
        ->and($output['result'])->toBe('error')
        ->and($output['message'])->toContain('Cannot redeclare')
        ->and($output['message'])->toContain('myDuplicateHelper');
});

it('outputs readable error when a fatal error occurs without agent detection', function () use ($fatalConfig): void {
    $process = runWith('pest', 'FileATest', withAgent: false, config: $fatalConfig, extraEnv: ['PAO_DISABLE' => false]);

    $output = $process->getOutput().$process->getErrorOutput();

    expect($output)->toContain('Cannot redeclare')
        ->and($output)->toContain('myDuplicateHelper');
});
