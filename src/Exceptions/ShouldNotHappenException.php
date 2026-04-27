<?php

declare(strict_types=1);

namespace Laravel\Pao\Exceptions;

use RuntimeException;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final class ShouldNotHappenException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('This should not have happened. Please report this issue at [https://github.com/laravel/pao/issues/new].');
    }
}
