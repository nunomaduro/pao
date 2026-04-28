<?php

declare(strict_types=1);

use Laravel\Pao\Drivers\Rector\Starter;
use Laravel\Pao\UserFilters\CaptureFilter;
use Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;

function rectorParse(string $input, array $argv = ['rector', 'process', '--dry-run']): ?array
{
    $originalArgv = $_SERVER['argv'] ?? [];
    $_SERVER['argv'] = $argv;

    CaptureFilter::reset();

    if (! in_array('agent_output_capture', stream_get_filters(), true)) {
        stream_filter_register('agent_output_capture', CaptureFilter::class);
    }

    $filter = stream_filter_append(STDOUT, 'agent_output_capture', STREAM_FILTER_WRITE);
    fwrite(STDOUT, $input);

    if (is_resource($filter)) {
        stream_filter_remove($filter);
    }

    $result = (new Starter)->parse();

    CaptureFilter::reset();
    $_SERVER['argv'] = $originalArgv;

    return $result;
}

it('returns null for empty string', function (): void {
    expect(rectorParse(''))->toBeNull();
});

it('returns null for invalid json', function (): void {
    expect(rectorParse('not json'))->toBeNull();
});

it('returns passed for no changes or errors', function (): void {
    $json = (string) json_encode([
        'totals' => ['changed_files' => 0, 'errors' => 0],
    ]);

    $result = rectorParse($json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('passed')
        ->and($result['changed_files'])->toBe(0)
        ->and($result['errors'])->toBe(0)
        ->and($result)->not->toHaveKey('change_details')
        ->and($result)->not->toHaveKey('error_details');
});

it('returns failed for dry-run changes', function (): void {
    $json = (string) json_encode([
        'totals' => ['changed_files' => 1, 'errors' => 0],
        'file_diffs' => [
            [
                'file' => 'src/Foo.php',
                'diff' => "@@ -7,7 +7,7 @@\n-        return array('foo');\n+        return ['foo'];",
                'applied_rectors' => [LongArrayToShortArrayRector::class],
            ],
        ],
        'changed_files' => ['src/Foo.php'],
    ]);

    $result = rectorParse($json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('failed')
        ->and($result['changed_files'])->toBe(1)
        ->and($result['errors'])->toBe(0)
        ->and($result['change_details'])->toBe([
            [
                'file' => 'src/Foo.php',
                'line' => 7,
                'applied_rectors' => ['LongArrayToShortArrayRector'],
            ],
        ]);
});

it('returns passed for non dry-run changes', function (): void {
    $json = (string) json_encode([
        'totals' => ['changed_files' => 1, 'errors' => 0],
        'changed_files' => ['src/Foo.php'],
    ]);

    $result = rectorParse($json, ['rector', 'process']);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('passed')
        ->and($result['changed_files'])->toBe(1)
        ->and($result['change_details'])->toBe([
            ['file' => 'src/Foo.php'],
        ]);
});

it('includes rector system errors', function (): void {
    $json = (string) json_encode([
        'totals' => ['changed_files' => 0, 'errors' => 1],
        'errors' => [
            [
                'message' => 'Could not parse file.',
                'file' => 'src/Broken.php',
                'line' => 12,
                'caused_by' => LongArrayToShortArrayRector::class,
            ],
        ],
    ]);

    $result = rectorParse($json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('failed')
        ->and($result['errors'])->toBe(1)
        ->and($result['error_details'])->toBe([
            [
                'message' => 'Could not parse file.',
                'file' => 'src/Broken.php',
                'line' => 12,
                'caused_by' => LongArrayToShortArrayRector::class,
            ],
        ]);
});

it('strips leading non-json content', function (): void {
    $json = (string) json_encode([
        'totals' => ['changed_files' => 0, 'errors' => 0],
    ]);

    $result = rectorParse("Loaded Rector config\n".$json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('passed');
});
