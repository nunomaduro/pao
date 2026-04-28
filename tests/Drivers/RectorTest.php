<?php

declare(strict_types=1);

use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;

it('outputs json for clean code', function (): void {
    $process = runRector('tests/Fixtures/Rector/clean/rector.php', extraArgs: ['--dry-run']);

    $output = decodeOutput($process);

    expect($output)->not->toHaveKey('tool')
        ->and($output['totals']['changed_files'])->toBe(0)
        ->and($output['totals']['errors'])->toBe(0)
        ->and($output)->not->toHaveKey('file_diffs')
        ->and($output)->not->toHaveKey('errors');
});

it('outputs json for code with changes', function (): void {
    $process = runRector('tests/Fixtures/Rector/changes/rector.php', extraArgs: ['--dry-run']);

    $output = decodeOutput($process);

    expect($output)->not->toHaveKey('tool')
        ->and($output['totals']['changed_files'])->toBe(1)
        ->and($output['totals']['errors'])->toBe(0)
        ->and($output['file_diffs'])->toHaveCount(1)
        ->and($output['file_diffs'][0]['file'])->toEndWith('NeedsChange.php')
        ->and($output['file_diffs'][0]['diff'])->toContain("-        return [dirname(__FILE__), 'change'];")
        ->and($output['file_diffs'][0]['applied_rectors'])->toContain(DirNameFileConstantToDirConstantRector::class)
        ->and($output['changed_files'])->toContain('tests/Fixtures/Rector/changes/src/NeedsChange.php');
});

it('passes through normal output without agent', function (): void {
    $process = runRector('tests/Fixtures/Rector/changes/rector.php', withAgent: false, extraArgs: ['--dry-run']);

    $raw = $process->getOutput();

    expect($raw)->not->toContain('"tool"')
        ->and($raw)->toContain('would have been changed');
});
