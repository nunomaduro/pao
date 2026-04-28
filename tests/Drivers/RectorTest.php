<?php

declare(strict_types=1);

it('outputs json for clean code', function (): void {
    $process = runRector('tests/Fixtures/Rector/clean/rector.php', extraArgs: ['--dry-run']);

    $output = decodeOutput($process);

    expect($output['tool'])->toBe('rector')
        ->and($output['result'])->toBe('passed')
        ->and($output['changed_files'])->toBe(0)
        ->and($output['errors'])->toBe(0)
        ->and($output)->not->toHaveKey('change_details')
        ->and($output)->not->toHaveKey('error_details');
});

it('outputs json for code with changes', function (): void {
    $process = runRector('tests/Fixtures/Rector/changes/rector.php', extraArgs: ['--dry-run']);

    $output = decodeOutput($process);

    expect($output['tool'])->toBe('rector')
        ->and($output['result'])->toBe('failed')
        ->and($output['changed_files'])->toBe(1)
        ->and($output['errors'])->toBe(0)
        ->and($output['change_details'])->toHaveCount(1)
        ->and($output['change_details'][0]['file'])->toEndWith('NeedsChange.php')
        ->and($output['change_details'][0]['line'])->toBeGreaterThan(0)
        ->and($output['change_details'][0]['applied_rectors'])->toContain('DirNameFileConstantToDirConstantRector');
});

it('passes through normal output without agent', function (): void {
    $process = runRector('tests/Fixtures/Rector/changes/rector.php', withAgent: false, extraArgs: ['--dry-run']);

    $raw = $process->getOutput();

    expect($raw)->not->toContain('"tool"')
        ->and($raw)->toContain('would have been changed');
});
