<?php

declare(strict_types=1);

function myDuplicateHelper(): string
{
    return 'b';
}

it('uses helper from file B', function (): void {
    expect(myDuplicateHelper())->toBe('b');
});
