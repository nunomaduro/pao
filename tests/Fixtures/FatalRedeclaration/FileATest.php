<?php

declare(strict_types=1);

function myDuplicateHelper(): string
{
    return 'a';
}

it('uses helper from file A', function (): void {
    expect(myDuplicateHelper())->toBe('a');
});
