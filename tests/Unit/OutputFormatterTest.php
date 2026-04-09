<?php

declare(strict_types=1);

use Pao\Formatters\OutputFormatter;

it('defaults to json format', function (): void {
    unset($_SERVER['PAO_FORMAT']);

    $data = ['result' => 'passed', 'tests' => 1, 'passed' => 1, 'duration_ms' => 10];

    expect(OutputFormatter::format($data))->toBe(
        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)
    );
});

it('outputs json when PAO_FORMAT is json', function (): void {
    $_SERVER['PAO_FORMAT'] = 'json';

    $data = ['result' => 'passed', 'tests' => 1, 'passed' => 1, 'duration_ms' => 10];

    expect(OutputFormatter::format($data))->toBe(
        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)
    );

    unset($_SERVER['PAO_FORMAT']);
});

it('outputs yaml when PAO_FORMAT is yaml', function (): void {
    $_SERVER['PAO_FORMAT'] = 'yaml';

    $data = ['result' => 'passed', 'tests' => 1, 'passed' => 1, 'duration_ms' => 10];

    expect(OutputFormatter::format($data))->toBe(
        "result: passed\ntests: 1\npassed: 1\nduration_ms: 10"
    );

    unset($_SERVER['PAO_FORMAT']);
});

it('handles PAO_FORMAT case insensitively', function (): void {
    $_SERVER['PAO_FORMAT'] = 'YAML';

    expect(OutputFormatter::resolveFormat())->toBe('yaml');

    $_SERVER['PAO_FORMAT'] = 'Yaml';

    expect(OutputFormatter::resolveFormat())->toBe('yaml');

    unset($_SERVER['PAO_FORMAT']);
});

it('falls back to json for unknown formats', function (): void {
    $_SERVER['PAO_FORMAT'] = 'xml';

    expect(OutputFormatter::resolveFormat())->toBe('json');

    $_SERVER['PAO_FORMAT'] = 'toml';

    expect(OutputFormatter::resolveFormat())->toBe('json');

    unset($_SERVER['PAO_FORMAT']);
});

it('resolves format as json when PAO_FORMAT is empty', function (): void {
    $_SERVER['PAO_FORMAT'] = '';

    expect(OutputFormatter::resolveFormat())->toBe('json');

    unset($_SERVER['PAO_FORMAT']);
});

it('resolves format as json when PAO_FORMAT has whitespace', function (): void {
    $_SERVER['PAO_FORMAT'] = '  yaml  ';

    expect(OutputFormatter::resolveFormat())->toBe('yaml');

    unset($_SERVER['PAO_FORMAT']);
});

it('produces valid json with slashes unescaped', function (): void {
    $data = [
        'result' => 'failed',
        'errors' => 1,
        'error_details' => [
            ['file' => '/app/tests/ExampleTest.php', 'line' => 1, 'message' => 'Error', 'identifier' => 'test'],
        ],
    ];

    unset($_SERVER['PAO_FORMAT']);

    $output = OutputFormatter::format($data);

    expect($output)->toContain('/app/tests/ExampleTest.php')
        ->and($output)->not->toContain('\\/');
});

it('produces yaml for complex test results', function (): void {
    $_SERVER['PAO_FORMAT'] = 'yaml';

    $data = [
        'result' => 'failed',
        'tests' => 10,
        'passed' => 8,
        'duration_ms' => 500,
        'failed' => 1,
        'failures' => [
            ['test' => 'FailTest::it', 'file' => '/test.php', 'line' => 5, 'message' => 'nope'],
        ],
        'errors' => 1,
        'error_details' => [
            ['test' => 'ErrTest::it', 'file' => '/err.php', 'line' => 10, 'message' => 'boom'],
        ],
    ];

    $yaml = OutputFormatter::format($data);

    expect($yaml)->toContain('result: failed')
        ->and($yaml)->toContain('failures:')
        ->and($yaml)->toContain('error_details:')
        ->and($yaml)->toContain("test: 'FailTest::it'")
        ->and($yaml)->toContain("test: 'ErrTest::it'");

    unset($_SERVER['PAO_FORMAT']);
});
