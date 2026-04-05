<?php

declare(strict_types=1);

namespace Fixtures\Phpstan\Clean;

final class Clean
{
    public function greet(string $name): string
    {
        return 'Hello, '.$name;
    }
}
