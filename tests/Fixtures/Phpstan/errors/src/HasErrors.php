<?php

declare(strict_types=1);

namespace Fixtures\Phpstan\Errors;

final class HasErrors
{
    public function noReturnType()
    {
        return 'hello';
    }

    public function undefinedMethod(): void
    {
        $this->doesNotExist();
    }
}
