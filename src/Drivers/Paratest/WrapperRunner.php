<?php

declare(strict_types=1);

namespace Pao\Drivers\Paratest;

use ParaTest\Options;
use ParaTest\RunnerInterface;
use ParaTest\WrapperRunner\WrapperRunner as ParatestWrapperRunner;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
final readonly class WrapperRunner implements RunnerInterface
{
    private ParatestWrapperRunner $runner;

    public function __construct(
        Options $options,
    ) {
        $this->runner = new ParatestWrapperRunner($options, new NullOutput);
    }

    public function run(): int
    {
        return $this->runner->run();
    }
}
