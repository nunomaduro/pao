<?php

declare(strict_types=1);

use Pao\Support\ToonDecoder;
use Pao\Support\ToonEncoder;

it('encodes passing result', function (): void {
    $result = [
        'result' => 'passed',
        'tests' => 3,
        'passed' => 3,
        'duration_ms' => 50,
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toBe(implode("\n", [
        'result: passed',
        'tests: 3',
        'passed: 3',
        'duration_ms: 50',
    ]));
});

it('encodes failing result with failures', function (): void {
    $result = [
        'result' => 'failed',
        'tests' => 2,
        'passed' => 1,
        'duration_ms' => 100,
        'failed' => 1,
        'failures' => [
            [
                'test' => 'Tests\ExampleTest::test_bad',
                'file' => 'tests/ExampleTest.php',
                'line' => 15,
                'message' => 'Failed asserting that false is true.',
            ],
        ],
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('result: failed')
        ->and($toon)->toContain('failed: 1')
        ->and($toon)->toContain('failures[1]{test,file,line,message}:')
        ->and($toon)->toContain('Tests\ExampleTest::test_bad,tests/ExampleTest.php,15,Failed asserting that false is true.');
});

it('encodes error details', function (): void {
    $result = [
        'result' => 'failed',
        'tests' => 1,
        'passed' => 0,
        'duration_ms' => 5,
        'errors' => 1,
        'error_details' => [
            [
                'test' => 'Tests\ErrorTest::test_boom',
                'file' => 'tests/ErrorTest.php',
                'line' => 10,
                'message' => 'RuntimeException: Boom',
            ],
        ],
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('errors: 1')
        ->and($toon)->toContain('error_details[1]{test,file,line,message}:');
});

it('encodes skipped count', function (): void {
    $result = [
        'result' => 'passed',
        'tests' => 2,
        'passed' => 1,
        'duration_ms' => 10,
        'skipped' => 1,
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('skipped: 1');
});

it('encodes output lines', function (): void {
    $result = [
        'result' => 'passed',
        'tests' => 1,
        'passed' => 1,
        'duration_ms' => 10,
        'output' => ['Hello from test', 'Another line'],
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('output[2]:')
        ->and($toon)->toContain(' - Hello from test')
        ->and($toon)->toContain(' - Another line');
});

it('escapes messages containing commas', function (): void {
    $result = [
        'result' => 'failed',
        'tests' => 1,
        'passed' => 0,
        'duration_ms' => 5,
        'failed' => 1,
        'failures' => [
            [
                'test' => 'Tests\FailTest::test_bad',
                'file' => 'tests/FailTest.php',
                'line' => 42,
                'message' => 'Expected 1, got 2',
            ],
        ],
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('"Expected 1, got 2"');
});

it('escapes messages containing newlines', function (): void {
    $result = [
        'result' => 'failed',
        'tests' => 1,
        'passed' => 0,
        'duration_ms' => 5,
        'failed' => 1,
        'failures' => [
            [
                'test' => 'Tests\FailTest::test_bad',
                'file' => 'tests/FailTest.php',
                'line' => 42,
                'message' => "Line 1\nLine 2",
            ],
        ],
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('"Line 1\nLine 2"');
});

it('escapes messages containing quotes', function (): void {
    $result = [
        'result' => 'failed',
        'tests' => 1,
        'passed' => 0,
        'duration_ms' => 5,
        'failed' => 1,
        'failures' => [
            [
                'test' => 'Tests\FailTest::test_bad',
                'file' => 'tests/FailTest.php',
                'line' => 42,
                'message' => 'Expected "hello"',
            ],
        ],
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('"Expected \\"hello\\""');
});

it('roundtrips through encoder and decoder', function (): void {
    $original = [
        'result' => 'failed',
        'tests' => 4,
        'passed' => 1,
        'duration_ms' => 5234,
        'failed' => 2,
        'failures' => [
            [
                'test' => 'Tests\MyTest::testA',
                'file' => '/path/a.php',
                'line' => 42,
                'message' => 'Expected true',
            ],
            [
                'test' => 'Tests\MyTest::testB',
                'file' => '/path/b.php',
                'line' => 10,
                'message' => 'Values do not match, expected 1',
            ],
        ],
        'errors' => 1,
        'error_details' => [
            [
                'test' => 'Tests\MyTest::testC',
                'file' => '/path/c.php',
                'line' => 7,
                'message' => 'RuntimeException: Null given',
            ],
        ],
        'skipped' => 1,
        'output' => ['Debug info here', 'More output'],
    ];

    $toon = ToonEncoder::encode($original);
    $decoded = ToonDecoder::decode($toon);

    expect($decoded['result'])->toBe('failed')
        ->and($decoded['tests'])->toBe(4)
        ->and($decoded['passed'])->toBe(1)
        ->and($decoded['duration_ms'])->toBe(5234)
        ->and($decoded['failed'])->toBe(2)
        ->and($decoded['failures'])->toHaveCount(2)
        ->and($decoded['failures'][0]['test'])->toBe('Tests\MyTest::testA')
        ->and($decoded['failures'][0]['line'])->toBe(42)
        ->and($decoded['failures'][1]['message'])->toBe('Values do not match, expected 1')
        ->and($decoded['errors'])->toBe(1)
        ->and($decoded['error_details'])->toHaveCount(1)
        ->and($decoded['skipped'])->toBe(1)
        ->and($decoded['output'])->toBe(['Debug info here', 'More output']);
});

it('handles empty string values', function (): void {
    $result = [
        'result' => 'failed',
        'tests' => 1,
        'passed' => 0,
        'duration_ms' => 5,
        'failed' => 1,
        'failures' => [
            [
                'test' => 'Tests\FailTest::test_bad',
                'file' => 'tests/FailTest.php',
                'line' => 42,
                'message' => '',
            ],
        ],
    ];

    $toon = ToonEncoder::encode($result);

    expect($toon)->toContain('""');
});

it('decodes toon with blank lines between entries', function (): void {
    $toon = "result: passed\n\ntests: 2\n   \npassed: 2\nduration_ms: 10";

    $decoded = ToonDecoder::decode($toon);

    expect($decoded['result'])->toBe('passed')
        ->and($decoded['tests'])->toBe(2)
        ->and($decoded['passed'])->toBe(2);
});

it('decodes tabular rows with blank lines between them', function (): void {
    $toon = "result: failed\ntests: 1\npassed: 0\nduration_ms: 5\nfailed: 1\nfailures[2]{test,file,line,message}:\n Tests\\A::a,a.php,1,Fail\n\n Tests\\B::b,b.php,2,Oops";

    $decoded = ToonDecoder::decode($toon);

    expect($decoded['failures'])->toHaveCount(2)
        ->and($decoded['failures'][0]['test'])->toBe('Tests\\A::a')
        ->and($decoded['failures'][1]['test'])->toBe('Tests\\B::b');
});

it('decodes csv rows with all escape sequences', function (): void {
    $toon = "result: failed\ntests: 1\npassed: 0\nduration_ms: 5\nfailed: 1\nfailures[1]{test,file,line,message}:\n Tests\\A::a,a.php,1,\"has\\n\\r\\t\\\"\\\\special\\x\"";

    $decoded = ToonDecoder::decode($toon);

    expect($decoded['failures'][0]['message'])->toBe("has\n\r\t\"\\special\\x");
});

it('decodes output list with quoted values containing escapes', function (): void {
    $toon = "result: passed\ntests: 1\npassed: 1\nduration_ms: 5\noutput[2]:\n - \"line with\\rnewline\\tand tab\"\n - plain line";

    $decoded = ToonDecoder::decode($toon);

    expect($decoded['output'][0])->toBe("line with\rnewline\tand tab")
        ->and($decoded['output'][1])->toBe('plain line');
});
