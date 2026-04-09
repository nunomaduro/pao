<?php

declare(strict_types=1);

use Pao\Formatters\OutputFormatter;
use Symfony\Component\Yaml\Yaml;

beforeEach(function (): void {
    $_SERVER['PAO_FORMAT'] = 'yaml';
});

afterEach(function (): void {
    unset($_SERVER['PAO_FORMAT']);
});

it('yaml round-trips a passing test result', function (): void {
    $data = [
        'result' => 'passed',
        'tests' => 1002,
        'passed' => 1002,
        'duration_ms' => 321,
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips a failed test result with failures', function (): void {
    $data = [
        'result' => 'failed',
        'tests' => 4,
        'passed' => 1,
        'duration_ms' => 1520,
        'failed' => 2,
        'failures' => [
            [
                'test' => 'ExampleTest::testItFails',
                'file' => '/app/tests/ExampleTest.php',
                'line' => 42,
                'message' => 'Failed asserting that false is true.',
            ],
        ],
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips error details with all fields', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 1,
        'error_details' => [
            [
                'file' => '/app/Controller.php',
                'line' => 9,
                'message' => 'Method Controller::index() should return int but returns string.',
                'identifier' => 'return.type',
                'ignorable' => false,
                'tip' => 'https://phpstan.org/blog/solving-phpstan-error-type',
            ],
        ],
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips raw output lines', function (): void {
    $data = [
        'result' => 'passed',
        'tests' => 10,
        'passed' => 10,
        'duration_ms' => 500,
        'raw' => [
            'Http/Controllers/Controller 100.0%',
            'Models/User 0.0%',
            'Total: 33.3%',
        ],
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips profile entries', function (): void {
    $data = [
        'result' => 'passed',
        'tests' => 3,
        'passed' => 3,
        'duration_ms' => 1200,
        'profile' => [
            [
                'test' => 'SlowTest::testSlow',
                'file' => '/app/tests/SlowTest.php',
                'duration_ms' => 800,
            ],
            [
                'test' => 'MediumTest::testMedium',
                'file' => '/app/tests/MediumTest.php',
                'duration_ms' => 300,
            ],
        ],
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips phpstan general errors', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 2,
        'error_details' => [
            [
                'file' => '/app/test.php',
                'line' => 1,
                'message' => 'Undefined variable $foo',
                'identifier' => 'variable.undefined',
            ],
        ],
        'general_errors' => ['Autoloading error in bootstrap'],
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips multiple failures and errors together', function (): void {
    $data = [
        'result' => 'failed',
        'tests' => 10,
        'passed' => 6,
        'duration_ms' => 2500,
        'failed' => 2,
        'failures' => [
            ['test' => 'TestA::testOne', 'file' => '/a.php', 'line' => 10, 'message' => 'Expected true, got false'],
            ['test' => 'TestA::testTwo', 'file' => '/a.php', 'line' => 20, 'message' => 'Arrays do not match'],
        ],
        'errors' => 1,
        'error_details' => [
            ['test' => 'TestB::testThree', 'file' => '/b.php', 'line' => 5, 'message' => 'Division by zero'],
        ],
        'skipped' => 1,
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips strings with special characters', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 1,
        'error_details' => [
            [
                'file' => '/app/test.php',
                'line' => 1,
                'message' => 'Expected "hello" but got "world": comparison failed',
                'identifier' => 'test.assertion',
            ],
        ],
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('yaml round-trips clean phpstan result', function (): void {
    $data = [
        'result' => 'passed',
        'errors' => 0,
    ];

    $decoded = Yaml::parse(OutputFormatter::format($data));

    expect($decoded)->toBe($data);
});

it('json round-trips a passing test result', function (): void {
    unset($_SERVER['PAO_FORMAT']);

    $data = [
        'result' => 'passed',
        'tests' => 1002,
        'passed' => 1002,
        'duration_ms' => 321,
    ];

    $decoded = json_decode(OutputFormatter::format($data), associative: true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBe($data);
});

it('json round-trips a complex failed result', function (): void {
    unset($_SERVER['PAO_FORMAT']);

    $data = [
        'result' => 'failed',
        'tests' => 10,
        'passed' => 6,
        'duration_ms' => 2500,
        'failed' => 2,
        'failures' => [
            ['test' => 'TestA::testOne', 'file' => '/app/tests/TestA.php', 'line' => 10, 'message' => 'Expected true'],
        ],
        'errors' => 1,
        'error_details' => [
            ['test' => 'TestB::testTwo', 'file' => '/app/tests/TestB.php', 'line' => 5, 'message' => 'Division by zero'],
        ],
        'skipped' => 1,
        'raw' => ['Coverage: 85.5%'],
    ];

    $decoded = json_decode(OutputFormatter::format($data), associative: true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBe($data);
});
