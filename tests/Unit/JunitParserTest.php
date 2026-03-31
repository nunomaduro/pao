<?php

declare(strict_types=1);

use Pao\Support\JunitParser;

function writeXml(string $content): string
{
    $file = tempnam(sys_get_temp_dir(), 'pao-test-').'.xml';
    file_put_contents($file, $content);

    return $file;
}

it('returns null for non-existent file', function (): void {
    expect(JunitParser::parse('/tmp/does-not-exist-'.uniqid().'.xml'))->toBeNull();
});

it('returns null for invalid xml', function (): void {
    $file = writeXml('not xml at all');

    expect(JunitParser::parse($file))->toBeNull();

    @unlink($file);
});

it('parses passing tests', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="3" assertions="3" errors="0" failures="0" skipped="0" time="0.050">
        <testsuite name="Tests\ExampleTest" file="tests/ExampleTest.php" tests="3" assertions="3" errors="0" failures="0" skipped="0" time="0.050">
          <testcase name="test_one" file="tests/ExampleTest.php" line="10" class="Tests\ExampleTest" assertions="1" time="0.010"/>
          <testcase name="test_two" file="tests/ExampleTest.php" line="15" class="Tests\ExampleTest" assertions="1" time="0.020"/>
          <testcase name="test_three" file="tests/ExampleTest.php" line="20" class="Tests\ExampleTest" assertions="1" time="0.020"/>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['result'])->toBe('passed')
        ->and($result['tests'])->toBe(3)
        ->and($result['passed'])->toBe(3)
        ->and($result['duration_ms'])->toBe(50)
        ->and($result)->not->toHaveKey('failed')
        ->and($result)->not->toHaveKey('errors')
        ->and($result)->not->toHaveKey('skipped');

    @unlink($file);
});

it('parses failing tests with file and line', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="2" assertions="2" errors="0" failures="1" skipped="0" time="0.030">
        <testsuite name="Tests\FailTest" file="tests/FailTest.php" tests="2" assertions="2" errors="0" failures="1" skipped="0" time="0.030">
          <testcase name="test_ok" file="tests/FailTest.php" line="10" class="Tests\FailTest" assertions="1" time="0.010"/>
          <testcase name="test_bad" file="tests/FailTest.php" line="15" class="Tests\FailTest" assertions="1" time="0.020">
            <failure type="PHPUnit\Framework\ExpectationFailedException">Failed asserting that false is true.

tests/FailTest.php:17</failure>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['result'])->toBe('failed')
        ->and($result['tests'])->toBe(2)
        ->and($result['passed'])->toBe(1)
        ->and($result['failed'])->toBe(1)
        ->and($result['failures'])->toHaveCount(1)
        ->and($result['failures'][0]['test'])->toBe('Tests\FailTest::test_bad')
        ->and($result['failures'][0]['file'])->toBe('tests/FailTest.php')
        ->and($result['failures'][0]['line'])->toBe(15)
        ->and($result['failures'][0]['message'])->toContain('Failed asserting');

    @unlink($file);
});

it('parses errored tests', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="1" assertions="0" errors="1" failures="0" skipped="0" time="0.005">
        <testsuite name="Tests\ErrorTest" file="tests/ErrorTest.php" tests="1" assertions="0" errors="1" failures="0" skipped="0" time="0.005">
          <testcase name="test_boom" file="tests/ErrorTest.php" line="10" class="Tests\ErrorTest" assertions="0" time="0.005">
            <error type="RuntimeException">RuntimeException: Boom

tests/ErrorTest.php:12</error>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['result'])->toBe('failed')
        ->and($result['errors'])->toBe(1)
        ->and($result['error_details'])->toHaveCount(1)
        ->and($result['error_details'][0]['test'])->toBe('Tests\ErrorTest::test_boom')
        ->and($result['error_details'][0]['line'])->toBe(10)
        ->and($result['error_details'][0]['message'])->toContain('Boom');

    @unlink($file);
});

it('parses skipped tests', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="2" assertions="1" errors="0" failures="0" skipped="1" time="0.010">
        <testsuite name="Tests\SkipTest" file="tests/SkipTest.php" tests="2" assertions="1" errors="0" failures="0" skipped="1" time="0.010">
          <testcase name="test_ok" file="tests/SkipTest.php" line="10" class="Tests\SkipTest" assertions="1" time="0.005"/>
          <testcase name="test_skip" file="tests/SkipTest.php" line="15" class="Tests\SkipTest" assertions="0" time="0.005">
            <skipped/>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['result'])->toBe('passed')
        ->and($result['tests'])->toBe(2)
        ->and($result['passed'])->toBe(1)
        ->and($result['skipped'])->toBe(1);

    @unlink($file);
});

it('parses mixed failures and errors', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="4" assertions="2" errors="1" failures="1" skipped="0" time="0.100">
        <testsuite name="Tests\MixedTest" file="tests/MixedTest.php" tests="4" assertions="2" errors="1" failures="1" skipped="0" time="0.100">
          <testcase name="test_pass_one" file="tests/MixedTest.php" line="5" class="Tests\MixedTest" assertions="1" time="0.010"/>
          <testcase name="test_pass_two" file="tests/MixedTest.php" line="10" class="Tests\MixedTest" assertions="1" time="0.010"/>
          <testcase name="test_fail" file="tests/MixedTest.php" line="15" class="Tests\MixedTest" assertions="1" time="0.040">
            <failure type="PHPUnit\Framework\ExpectationFailedException">Expected true got false</failure>
          </testcase>
          <testcase name="test_error" file="tests/MixedTest.php" line="20" class="Tests\MixedTest" assertions="0" time="0.040">
            <error type="RuntimeException">Kaboom</error>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['result'])->toBe('failed')
        ->and($result['tests'])->toBe(4)
        ->and($result['passed'])->toBe(2)
        ->and($result['failed'])->toBe(1)
        ->and($result['errors'])->toBe(1)
        ->and($result['failures'])->toHaveCount(1)
        ->and($result['error_details'])->toHaveCount(1);

    @unlink($file);
});

it('parses data provider tests with named datasets', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="3" assertions="3" errors="0" failures="1" skipped="0" time="0.030">
        <testsuite name="Tests\DataTest" file="tests/DataTest.php" tests="3" assertions="3" errors="0" failures="1" skipped="0" time="0.030">
          <testsuite name="Tests\DataTest::test_add" tests="3" assertions="3" errors="0" failures="1" skipped="0" time="0.030">
            <testcase name="test_add with data set &quot;one plus one&quot;" file="tests/DataTest.php" line="20" class="Tests\DataTest" assertions="1" time="0.010"/>
            <testcase name="test_add with data set &quot;two plus two&quot;" file="tests/DataTest.php" line="20" class="Tests\DataTest" assertions="1" time="0.010"/>
            <testcase name="test_add with data set &quot;wrong&quot;" file="tests/DataTest.php" line="20" class="Tests\DataTest" assertions="1" time="0.010">
              <failure type="PHPUnit\Framework\ExpectationFailedException">Failed asserting that 2 is identical to 99.</failure>
            </testcase>
          </testsuite>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['tests'])->toBe(3)
        ->and($result['passed'])->toBe(2)
        ->and($result['failed'])->toBe(1)
        ->and($result['failures'][0]['test'])->toContain('wrong');

    @unlink($file);
});

it('resolves line from message when line is 0 (Pest closures)', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="0.010">
        <testsuite name="Tests\PestTest" file="tests/PestTest.php::it fails" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="0.010">
          <testcase name="it fails" file="tests/PestTest.php::it fails" line="0" class="Tests\PestTest" assertions="1" time="0.010">
            <failure type="PHPUnit\Framework\ExpectationFailedException">it fails
Failed asserting that true is false.
at tests/PestTest.php:8</failure>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['failures'][0]['file'])->toBe('tests/PestTest.php')
        ->and($result['failures'][0]['line'])->toBe(8);

    @unlink($file);
});

it('keeps original line when line is non-zero', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="0.010">
        <testsuite name="Tests\ExampleTest" file="tests/ExampleTest.php" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="0.010">
          <testcase name="test_it" file="tests/ExampleTest.php" line="42" class="Tests\ExampleTest" assertions="1" time="0.010">
            <failure type="PHPUnit\Framework\ExpectationFailedException">Nope
at tests/ExampleTest.php:44</failure>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['failures'][0]['file'])->toBe('tests/ExampleTest.php')
        ->and($result['failures'][0]['line'])->toBe(42);

    @unlink($file);
});

it('resolves line from error message when line is 0', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="1" assertions="0" errors="1" failures="0" skipped="0" time="0.005">
        <testsuite name="Tests\PestTest" file="tests/PestTest.php::it errors" tests="1" assertions="0" errors="1" failures="0" skipped="0" time="0.005">
          <testcase name="it errors" file="tests/PestTest.php::it errors" line="0" class="Tests\PestTest" assertions="0" time="0.005">
            <error type="RuntimeException">it errors
RuntimeException: Boom
at tests/PestTest.php:15</error>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['error_details'][0]['file'])->toBe('tests/PestTest.php')
        ->and($result['error_details'][0]['line'])->toBe(15);

    @unlink($file);
});

it('handles empty test suite', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="0" assertions="0" errors="0" failures="0" skipped="0" time="0.000"/>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['result'])->toBe('passed')
        ->and($result['tests'])->toBe(0)
        ->and($result['passed'])->toBe(0)
        ->and($result['duration_ms'])->toBe(0);

    @unlink($file);
});

it('handles deeply nested testsuites', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="root" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="0.010">
        <testsuite name="level1" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="0.010">
          <testsuite name="level2" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="0.010">
            <testsuite name="level3" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="0.010">
              <testcase name="test_deep" file="tests/DeepTest.php" line="5" class="Tests\DeepTest" assertions="1" time="0.010"/>
            </testsuite>
          </testsuite>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['result'])->toBe('passed')
        ->and($result['tests'])->toBe(1)
        ->and($result['passed'])->toBe(1);

    @unlink($file);
});

it('converts duration to milliseconds correctly', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="1.234">
        <testsuite name="Tests\SlowTest" file="tests/SlowTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="1.234">
          <testcase name="test_slow" file="tests/SlowTest.php" line="5" class="Tests\SlowTest" assertions="1" time="1.234"/>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['duration_ms'])->toBe(1234);

    @unlink($file);
});

it('omits failed key when no failures', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="0.001">
        <testsuite name="Tests\OkTest" file="tests/OkTest.php" tests="1" assertions="1" errors="0" failures="0" skipped="0" time="0.001">
          <testcase name="test_ok" file="tests/OkTest.php" line="5" class="Tests\OkTest" assertions="1" time="0.001"/>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result)->not->toHaveKey('failed')
        ->and($result)->not->toHaveKey('failures')
        ->and($result)->not->toHaveKey('errors')
        ->and($result)->not->toHaveKey('error_details')
        ->and($result)->not->toHaveKey('skipped');

    @unlink($file);
});

it('falls back to line 0 when message has no file reference', function (): void {
    $file = writeXml('<?xml version="1.0"?>
    <testsuites>
      <testsuite name="default" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="0.010">
        <testsuite name="Tests\PestTest" file="tests/PestTest.php::it fails" tests="1" assertions="1" errors="0" failures="1" skipped="0" time="0.010">
          <testcase name="it fails" file="tests/PestTest.php::it fails" line="0" class="Tests\PestTest" assertions="1" time="0.010">
            <failure type="PHPUnit\Framework\ExpectationFailedException">Some failure without file reference</failure>
          </testcase>
        </testsuite>
      </testsuite>
    </testsuites>');

    $result = JunitParser::parse($file);

    expect($result['failures'][0]['file'])->toBe('tests/PestTest.php::it fails')
        ->and($result['failures'][0]['line'])->toBe(0);

    @unlink($file);
});
