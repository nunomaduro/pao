<?php

declare(strict_types=1);

namespace Laravel\Pao\Drivers\Paratest;

use Laravel\Pao\Drivers\Concerns\ProfileCollector;
use Laravel\Pao\Execution;
use ParaTest\Options;
use ParaTest\RunnerInterface;
use ParaTest\WrapperRunner\ResultPrinter;
use ParaTest\WrapperRunner\SuiteLoader;
use ParaTest\WrapperRunner\WrapperRunner as ParatestWrapperRunner;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\Util\ExcludeList;
use ReflectionObject;
use SplFileInfo;
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
    ) {
        $this->runner = new ParatestWrapperRunner($options, new NullOutput);
    }

    public function run(): int
    {
        $runner = $this->runner;
        $r = new ReflectionObject($runner);

        /** @var non-empty-string $directory */
        $directory = dirname((string) $r->getFileName(), 2);
        ExcludeList::addDirectory($directory);

        /** @var Options $options */
        $options = $r->getProperty('options')->getValue($runner);

        /** @var OutputInterface $output */
        $output = $r->getProperty('output')->getValue($runner);

        /** @var CodeCoverageFilterRegistry $filterRegistry */
        $filterRegistry = $r->getProperty('codeCoverageFilterRegistry')->getValue($runner);

        $suiteLoader = new SuiteLoader($options, $output, $filterRegistry);

        $result = TestResultFacade::result();

        $r->getProperty('pending')->setValue($runner, $suiteLoader->tests);

        /** @var ResultPrinter $printer */
        $printer = $r->getProperty('printer')->getValue($runner);
        $printer->setTestCount($suiteLoader->testCount);
        $printer->start();

        $startTime = hrtime(true);

        $r->getMethod('startWorkers')->invoke($runner);
        $r->getMethod('assignAllPendingTests')->invoke($runner);
        $r->getMethod('waitForAllToFinish')->invoke($runner);

        ProfileCollector::startTimerFromNanoseconds($startTime);

        /** @var list<SplFileInfo> $testResultFiles */
        $testResultFiles = $r->getProperty('testResultFiles')->getValue($runner);

        $mergedResult = $this->mergeTestResults($result, $testResultFiles);

        if (Execution::running()) {
            $driver = Execution::current()->driver;

            if ($driver instanceof Starter) {
                $driver->testResult = $mergedResult;
            }
        }

        /** @var int $exitCode */
        $exitCode = $r->getMethod('complete')->invoke($runner, $result);

        return $exitCode;
    }

    /**
     * @param  list<SplFileInfo>  $testResultFiles
     */
    private function mergeTestResults(TestResult $sum, array $testResultFiles): TestResult
    {
        foreach ($testResultFiles as $testResultFile) {
            if (! $testResultFile->isFile()) {
                continue;
            }

            $contents = file_get_contents($testResultFile->getPathname());

            if ($contents === false) {
                continue;
            }

            $testResult = unserialize($contents);

            if (! $testResult instanceof TestResult) {
                continue;
            }

            $sum = new TestResult(
                (int) $sum->hasTests() + (int) $testResult->hasTests(),
                $sum->numberOfTestsRun() + $testResult->numberOfTestsRun(),
                $sum->numberOfAssertions() + $testResult->numberOfAssertions(),
                [...$sum->testErroredEvents(), ...$testResult->testErroredEvents()],
                [...$sum->testFailedEvents(), ...$testResult->testFailedEvents()],
                array_merge_recursive($sum->testConsideredRiskyEvents(), $testResult->testConsideredRiskyEvents()), // @phpstan-ignore argument.type
                [...$sum->testSuiteSkippedEvents(), ...$testResult->testSuiteSkippedEvents()],
                [...$sum->testSkippedEvents(), ...$testResult->testSkippedEvents()],
                [...$sum->testMarkedIncompleteEvents(), ...$testResult->testMarkedIncompleteEvents()],
                array_merge_recursive($sum->testTriggeredPhpunitDeprecationEvents(), $testResult->testTriggeredPhpunitDeprecationEvents()), // @phpstan-ignore argument.type
                array_merge_recursive($sum->testTriggeredPhpunitErrorEvents(), $testResult->testTriggeredPhpunitErrorEvents()), // @phpstan-ignore argument.type
                array_merge_recursive($sum->testTriggeredPhpunitNoticeEvents(), $testResult->testTriggeredPhpunitNoticeEvents()), // @phpstan-ignore argument.type
                array_merge_recursive($sum->testTriggeredPhpunitWarningEvents(), $testResult->testTriggeredPhpunitWarningEvents()), // @phpstan-ignore argument.type
                [...$sum->testRunnerTriggeredDeprecationEvents(), ...$testResult->testRunnerTriggeredDeprecationEvents()],
                [...$sum->testRunnerTriggeredNoticeEvents(), ...$testResult->testRunnerTriggeredNoticeEvents()],
                [...$sum->testRunnerTriggeredWarningEvents(), ...$testResult->testRunnerTriggeredWarningEvents()],
                [...$sum->errors(), ...$testResult->errors()],
                [...$sum->deprecations(), ...$testResult->deprecations()],
                [...$sum->notices(), ...$testResult->notices()],
                [...$sum->warnings(), ...$testResult->warnings()],
                [...$sum->phpDeprecations(), ...$testResult->phpDeprecations()],
                [...$sum->phpNotices(), ...$testResult->phpNotices()],
                [...$sum->phpWarnings(), ...$testResult->phpWarnings()],
                $sum->numberOfIssuesIgnoredByBaseline() + $testResult->numberOfIssuesIgnoredByBaseline(),
            );
        }

        return $sum;
    }
}
