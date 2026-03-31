<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    public function test_putenv_and_getenv(): void
    {
        putenv('PAO_TEST_VAR=hello');

        $this->assertSame('hello', getenv('PAO_TEST_VAR'));

        putenv('PAO_TEST_VAR');
    }

    public function test_modify_server_superglobal(): void
    {
        $_SERVER['PAO_TEST'] = 'value';

        $this->assertSame('value', $_SERVER['PAO_TEST']);

        unset($_SERVER['PAO_TEST']);
    }

    public function test_modify_env_superglobal(): void
    {
        $_ENV['PAO_TEST'] = 'env_value';

        $this->assertSame('env_value', $_ENV['PAO_TEST']);

        unset($_ENV['PAO_TEST']);
    }
}
