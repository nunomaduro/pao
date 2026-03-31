<?php

declare(strict_types=1);

namespace Pao\Drivers\Paratest;

use JsonException;
use Pao\Execution;
use ParaTest\Options;
use ParaTest\RunnerInterface;
use ParaTest\WrapperRunner\WrapperRunner as ParatestWrapperRunner;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 *
 * @codeCoverageIgnore
 *
 * @rector-ignore
 */
final class WrapperRunner implements RunnerInterface
{
    private readonly ParatestWrapperRunner $runner;

    public function __construct(// @phpstan-ignore constructor.unusedParameter
        Options $options,
        private readonly OutputInterface $output,
    ) {
        $this->runner = new ParatestWrapperRunner($options, new NullOutput);
    }

    /**
     * @throws JsonException
     */
    public function run(): int
    {
        $exitCode = $this->runner->run();

        $execution = Execution::current();
        $execution->restoreStdout();

        $result = $execution->result();

        if ($result !== null) {
            $this->output->writeln(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }

        return $exitCode;
    }
}
