<?php

declare(strict_types=1);

use Pao\Support\PhpstanParser;

it('returns null for empty string', function (): void {
    expect(PhpstanParser::parse(''))->toBeNull();
});

it('returns null for invalid json', function (): void {
    expect(PhpstanParser::parse('not json'))->toBeNull();
});

it('returns null for json without totals', function (): void {
    expect(PhpstanParser::parse('{"foo":"bar"}'))->toBeNull();
});

it('returns passed for zero errors', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 0, 'file_errors' => 0],
        'files' => [],
        'errors' => [],
    ]);

    $result = PhpstanParser::parse($json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('passed')
        ->and($result['errors'])->toBe(0)
        ->and($result)->not->toHaveKey('error_details')
        ->and($result)->not->toHaveKey('general_errors');
});

it('returns failed with error details', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 0, 'file_errors' => 2],
        'files' => [
            '/src/Foo.php' => [
                'errors' => 2,
                'messages' => [
                    ['message' => 'No type specified', 'line' => 17, 'ignorable' => true, 'identifier' => 'missingType.parameter'],
                    ['message' => 'Undefined property', 'line' => 42, 'ignorable' => true, 'identifier' => 'property.notFound'],
                ],
            ],
        ],
        'errors' => [],
    ]);

    $result = PhpstanParser::parse($json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('failed')
        ->and($result['errors'])->toBe(2)
        ->and($result['error_details'])->toHaveCount(2)
        ->and($result['error_details'][0]['file'])->toBe('/src/Foo.php')
        ->and($result['error_details'][0]['line'])->toBe(17)
        ->and($result['error_details'][0]['message'])->toBe('No type specified')
        ->and($result['error_details'][0]['identifier'])->toBe('missingType.parameter')
        ->and($result['error_details'][1]['identifier'])->toBe('property.notFound');
});

it('defaults identifier to unknown when missing', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 0, 'file_errors' => 1],
        'files' => [
            '/src/Foo.php' => [
                'errors' => 1,
                'messages' => [
                    ['message' => 'Some error', 'line' => 5],
                ],
            ],
        ],
        'errors' => [],
    ]);

    $result = PhpstanParser::parse($json);

    expect($result)->not->toBeNull()
        ->and($result['error_details'][0]['identifier'])->toBe('unknown');
});

it('captures general errors', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 1, 'file_errors' => 0],
        'files' => [],
        'errors' => ['Autoload file not found'],
    ]);

    $result = PhpstanParser::parse($json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('failed')
        ->and($result['errors'])->toBe(1)
        ->and($result['general_errors'])->toBe(['Autoload file not found'])
        ->and($result)->not->toHaveKey('error_details');
});

it('combines file errors and general errors', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 1, 'file_errors' => 1],
        'files' => [
            '/src/Foo.php' => [
                'errors' => 1,
                'messages' => [
                    ['message' => 'Error', 'line' => 10, 'identifier' => 'return.type'],
                ],
            ],
        ],
        'errors' => ['General error'],
    ]);

    $result = PhpstanParser::parse($json);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('failed')
        ->and($result['errors'])->toBe(2)
        ->and($result['error_details'])->toHaveCount(1)
        ->and($result['general_errors'])->toHaveCount(1);
});

it('discards tip and ignorable fields', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 0, 'file_errors' => 1],
        'files' => [
            '/src/Foo.php' => [
                'errors' => 1,
                'messages' => [
                    [
                        'message' => 'Access to undefined property',
                        'line' => 35,
                        'ignorable' => true,
                        'identifier' => 'property.notFound',
                        'tip' => 'Learn more: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property',
                    ],
                ],
            ],
        ],
        'errors' => [],
    ]);

    $result = PhpstanParser::parse($json);

    expect($result)->not->toBeNull()
        ->and($result['error_details'][0])->toBe([
            'file' => '/src/Foo.php',
            'line' => 35,
            'message' => 'Access to undefined property',
            'identifier' => 'property.notFound',
        ]);
});

it('strips leading non-json content like phpstan note lines', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 0, 'file_errors' => 0],
        'files' => [],
        'errors' => [],
    ]);

    $input = "Note: Using configuration file /home/user/project/phpstan.neon.\n".$json;

    $result = PhpstanParser::parse($input);

    expect($result)->not->toBeNull()
        ->and($result['result'])->toBe('passed')
        ->and($result['errors'])->toBe(0);
});

it('handles multiple files with multiple errors', function (): void {
    $json = (string) json_encode([
        'totals' => ['errors' => 0, 'file_errors' => 3],
        'files' => [
            '/src/Foo.php' => [
                'errors' => 2,
                'messages' => [
                    ['message' => 'Error 1', 'line' => 10, 'identifier' => 'return.type'],
                    ['message' => 'Error 2', 'line' => 20, 'identifier' => 'missingType.parameter'],
                ],
            ],
            '/src/Bar.php' => [
                'errors' => 1,
                'messages' => [
                    ['message' => 'Error 3', 'line' => 5, 'identifier' => 'method.notFound'],
                ],
            ],
        ],
        'errors' => [],
    ]);

    $result = PhpstanParser::parse($json);

    expect($result)->not->toBeNull()
        ->and($result['errors'])->toBe(3)
        ->and($result['error_details'])->toHaveCount(3)
        ->and($result['error_details'][0]['file'])->toBe('/src/Foo.php')
        ->and($result['error_details'][2]['file'])->toBe('/src/Bar.php');
});
