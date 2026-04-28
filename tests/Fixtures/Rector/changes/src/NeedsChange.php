<?php

declare(strict_types=1);

final class NeedsChange
{
    public function values(): array
    {
        return [dirname(__FILE__), 'change'];
    }
}
