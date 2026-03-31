<?php

declare(strict_types=1);

it('passes', function (): void {
    expect(true)->toBeTrue();
});

it('fails on this line', function (): void {
    expect(false)->toBeTrue();
});
