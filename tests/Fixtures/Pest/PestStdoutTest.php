<?php

declare(strict_types=1);

it('writes to stdout and passes', function (): void {
    fwrite(STDOUT, 'pest fwrite output');

    expect(true)->toBeTrue();
});

it('writes to stdout and fails', function (): void {
    fwrite(STDOUT, 'pest fwrite before failure');

    expect(false)->toBeTrue();
});

it('writes large output and passes', function (): void {
    fwrite(STDOUT, str_repeat('noise', 10000));

    expect(true)->toBeTrue();
});
