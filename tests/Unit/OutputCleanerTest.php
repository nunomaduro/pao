<?php

declare(strict_types=1);

use Pao\OutputCleaner;

it('strips ANSI escape codes', function (): void {
    expect(OutputCleaner::clean("\e[32mSuccess\e[0m"))->toBe('Success');
});

it('strips control characters', function (): void {
    expect(OutputCleaner::clean("Hello\x07World"))->toBe('HelloWorld');
});

it('strips unicode replacement character', function (): void {
    expect(OutputCleaner::clean("Hello\u{FFFD}World"))->toBe('HelloWorld');
});

it('strips box-drawing characters', function (): void {
    expect(OutputCleaner::clean('┌──────┐'))->toBeEmpty();
});

it('strips decorative symbols', function (): void {
    expect(OutputCleaner::clean('✔ passed ✖ failed ⚠ warning'))->toBe(' passed failed warning');
});

it('compresses dot separators', function (): void {
    expect(OutputCleaner::clean('Name ..................... Value'))->toBe('Name .. Value');
});

it('does not compress two dots', function (): void {
    expect(OutputCleaner::clean('file..php'))->toBe('file..php');
});

it('compresses horizontal whitespace', function (): void {
    expect(OutputCleaner::clean('Name     Value   Extra'))->toBe('Name Value Extra');
});

it('compresses empty lines', function (): void {
    expect(OutputCleaner::clean("Line 1\n\n\nLine 2"))->toBe("Line 1\nLine 2");
});

it('handles combined formatting', function (): void {
    $input = "\e[32m  Application Name \e[0m..................... \e[33mLaravel\e[0m";

    expect(OutputCleaner::clean($input))->toBe(' Application Name .. Laravel');
});

it('returns empty string for only decorative content', function (): void {
    expect(OutputCleaner::clean('├─────────────────────────────┤'))->toBeEmpty();
});
