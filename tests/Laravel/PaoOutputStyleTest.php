<?php

declare(strict_types=1);

use Pao\Laravel\PaoOutputStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

it('sets decorated to false', function (): void {
    $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);

    expect($output->isDecorated())->toBeTrue();

    new PaoOutputStyle(new ArrayInput([]), $output);

    expect($output->isDecorated())->toBeFalse();
});

it('strips ANSI codes from write output', function (): void {
    $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
    $style = new PaoOutputStyle(new ArrayInput([]), $output);

    $style->write("\e[32mSuccess\e[0m");

    expect($output->fetch())->toBe('Success');
});

it('strips box-drawing characters from writeln output', function (): void {
    $output = new BufferedOutput;
    $style = new PaoOutputStyle(new ArrayInput([]), $output);

    $style->writeln('┌─ Name ─── Value ─┐');

    expect(trim($output->fetch()))->toBe('Name Value');
});

it('compresses dot separators', function (): void {
    $output = new BufferedOutput;
    $style = new PaoOutputStyle(new ArrayInput([]), $output);

    $style->writeln('Application Name ..................... Laravel');

    expect(trim($output->fetch()))->toBe('Application Name .. Laravel');
});

it('handles iterable messages', function (): void {
    $output = new BufferedOutput;
    $style = new PaoOutputStyle(new ArrayInput([]), $output);

    $style->writeln(['Line .... one', 'Line .... two']);

    expect($output->fetch())->toContain('Line .. one')
        ->toContain('Line .. two');
});
