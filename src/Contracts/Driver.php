<?php

declare(strict_types=1);

namespace Pao\Contracts;

/**
 * @internal
 */
interface Driver
{
    public function start(): void;

    public function name(): string;

    /**
     * @return array<string, mixed>|null
     */
    public function parse(): ?array;
}
