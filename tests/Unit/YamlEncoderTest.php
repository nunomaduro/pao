<?php

declare(strict_types=1);

use Pao\Formatters\YamlEncoder;

it('encodes a passing test result', function (): void {
    $data = [
        'result' => 'passed',
        'tests' => 1002,
        'passed' => 1002,
        'duration_ms' => 321,
    ];

    expect(YamlEncoder::encode($data))->toBe(
        "result: passed\ntests: 1002\npassed: 1002\nduration_ms: 321"
    );
});

it('encodes a failed test result with failures', function (): void {
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

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('result: failed')
        ->and($yaml)->toContain('tests: 4')
        ->and($yaml)->toContain('failed: 2')
        ->and($yaml)->toContain('failures:')
        ->and($yaml)->toContain('  - test: "ExampleTest::testItFails"')
        ->and($yaml)->toContain('    line: 42');
});

it('encodes error details', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 1,
        'error_details' => [
            [
                'test' => 'ErrorTest::testItErrors',
                'file' => '/app/tests/ErrorTest.php',
                'line' => 10,
                'message' => 'Something went wrong',
            ],
        ],
    ];

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('error_details:')
        ->and($yaml)->toContain('  - test: "ErrorTest::testItErrors"')
        ->and($yaml)->toContain('    message: Something went wrong');
});

it('encodes raw output lines', function (): void {
    $data = [
        'result' => 'passed',
        'tests' => 10,
        'passed' => 10,
        'duration_ms' => 500,
        'raw' => ['Hello world'],
    ];

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('raw:')
        ->and($yaml)->toContain('  - Hello world');
});

it('encodes phpstan output', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 2,
        'error_details' => [
            [
                'file' => '/app/Controller.php',
                'line' => 9,
                'message' => 'Method should return int.',
                'identifier' => 'return.type',
            ],
        ],
    ];

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('    identifier: return.type');
});

it('encodes profile entries', function (): void {
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
        ],
    ];

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('profile:')
        ->and($yaml)->toContain('  - test: "SlowTest::testSlow"')
        ->and($yaml)->toContain('    duration_ms: 800');
});

it('handles empty result', function (): void {
    $data = [
        'result' => 'passed',
        'errors' => 0,
    ];

    expect(YamlEncoder::encode($data))->toBe("result: passed\nerrors: 0");
});

it('handles boolean values', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 1,
        'error_details' => [
            [
                'file' => '/test.php',
                'line' => 1,
                'message' => 'Error',
                'identifier' => 'test',
                'ignorable' => false,
            ],
        ],
    ];

    expect(YamlEncoder::encode($data))->toContain('ignorable: false');
});

it('quotes strings that look like yaml values', function (): void {
    expect(YamlEncoder::encode(['key' => 'true']))->toBe('key: "true"');
    expect(YamlEncoder::encode(['key' => 'false']))->toBe('key: "false"');
    expect(YamlEncoder::encode(['key' => 'null']))->toBe('key: "null"');
    expect(YamlEncoder::encode(['key' => 'yes']))->toBe('key: "yes"');
    expect(YamlEncoder::encode(['key' => 'no']))->toBe('key: "no"');
});

it('quotes strings with colons', function (): void {
    expect(YamlEncoder::encode(['key' => 'foo: bar']))->toBe('key: "foo: bar"');
});

it('handles skipped count', function (): void {
    $data = [
        'result' => 'passed',
        'tests' => 5,
        'passed' => 4,
        'duration_ms' => 100,
        'skipped' => 1,
    ];

    expect(YamlEncoder::encode($data))->toContain('skipped: 1');
});

it('handles general errors from phpstan', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 1,
        'general_errors' => ['Autoloading error'],
    ];

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('general_errors:')
        ->and($yaml)->toContain('  - Autoloading error');
});

it('quotes empty strings', function (): void {
    expect(YamlEncoder::encode(['key' => '']))->toBe('key: ""');
});

it('handles strings starting with dash', function (): void {
    expect(YamlEncoder::encode(['key' => '-value']))->toBe('key: "-value"');
});

it('quotes strings with percent signs', function (): void {
    expect(YamlEncoder::encode(['key' => '100.0%']))->toContain('"100.0%"');
});

it('handles phpstan tip as url', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 1,
        'error_details' => [
            [
                'file' => '/test.php',
                'line' => 1,
                'message' => 'Error',
                'identifier' => 'test',
                'tip' => 'https://phpstan.org/tip',
            ],
        ],
    ];

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('tip: "https://phpstan.org/tip"');
});

it('handles multiple failures', function (): void {
    $data = [
        'result' => 'failed',
        'tests' => 4,
        'passed' => 1,
        'duration_ms' => 200,
        'failed' => 2,
        'failures' => [
            ['test' => 'TestA::a', 'file' => '/a.php', 'line' => 1, 'message' => 'fail a'],
            ['test' => 'TestB::b', 'file' => '/b.php', 'line' => 2, 'message' => 'fail b'],
        ],
        'errors' => 1,
        'error_details' => [
            ['test' => 'TestC::c', 'file' => '/c.php', 'line' => 3, 'message' => 'error c'],
        ],
    ];

    $yaml = YamlEncoder::encode($data);

    expect($yaml)->toContain('  - test: "TestA::a"')
        ->and($yaml)->toContain('  - test: "TestB::b"')
        ->and($yaml)->toContain('  - test: "TestC::c"')
        ->and($yaml)->toContain('    message: fail a')
        ->and($yaml)->toContain('    message: fail b')
        ->and($yaml)->toContain('    message: error c');
});
