<?php

declare(strict_types=1);

namespace Tests\Laravel;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        unset($_SERVER['AI_AGENT'], $_SERVER['PAO_DISABLE'], $_SERVER['CLAUDE_CODE'], $_SERVER['CLAUDECODE']);
        putenv('CLAUDE_CODE');
        putenv('CLAUDECODE');
        putenv('PAO_DISABLE');

        parent::setUp();
    }

    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [];
    }

    /**
     * @return list<string>
     */
    public function ignorePackageDiscoveriesFrom(): array
    {
        return ['laravel/pao'];
    }
}
