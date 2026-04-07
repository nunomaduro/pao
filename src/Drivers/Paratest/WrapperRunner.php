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
 */
final readonly class WrapperRunner implements RunnerInterface
{
    private ParatestWrapperRunner $runner;

    public function __construct(
        Options $options,
        private OutputInterface $output,
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

        $result = $execution->result();

        if ($result !== null) {
            $execution->restoreStdout();

            $this->output->writeln(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $execution->flushStdout();
        }

        return $exitCode;
    }
}
