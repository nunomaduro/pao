<?php

declare(strict_types=1);

namespace Pao\Drivers\Paratest;

use Pao\Execution;
use Pao\Support\ToonEncoder;
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

    public function run(): int
    {
        $exitCode = $this->runner->run();

        $execution = Execution::current();
        $execution->restoreStdout();

        $result = $execution->result();

        if ($result !== null) {
            $this->output->writeln(ToonEncoder::encode($result));
        }

        return $exitCode;
    }
}
