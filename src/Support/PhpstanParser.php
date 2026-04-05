<?php

declare(strict_types=1);

namespace Pao\Support;

/**
 * @internal
 *
 * @phpstan-type PhpstanErrorDetail array{file: string, line: int, message: string, identifier: string}
 * @phpstan-type PhpstanResult array{result: 'passed'|'failed', errors: int, error_details?: list<PhpstanErrorDetail>, general_errors?: list<string>}
 */
final class PhpstanParser
{
    /**
     * @return PhpstanResult|null
     */
    public static function parse(string $json): ?array
    {
        // PHPStan may prepend non-JSON lines to stdout (e.g. "Note: Using configuration file ...").
        // Strip everything before the first '{' so json_decode receives clean input.
        $start = strpos($json, '{');

        if ($start !== false && $start > 0) {
            $json = substr($json, $start);
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($json, associative: true);

        if (! is_array($data) || ! isset($data['totals'])) {
            return null;
        }

        /** @var list<PhpstanErrorDetail> $errorDetails */
        $errorDetails = [];

        /** @var array<string, array{errors: int, messages: list<array{message: string, line: int, ignorable?: bool, identifier?: string, tip?: string}>}> $files */
        $files = is_array($data['files'] ?? null) ? $data['files'] : [];

        foreach ($files as $file => $fileData) {
            foreach ($fileData['messages'] as $message) {
                $errorDetails[] = [
                    'file' => $file,
                    'line' => $message['line'],
                    'message' => $message['message'],
                    'identifier' => $message['identifier'] ?? 'unknown',
                ];
            }
        }

        /** @var list<string> $errors */
        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];

        /** @var list<string> $generalErrors */
        $generalErrors = array_values(array_filter($errors, static fn (string $error): bool => $error !== ''));

        $totalErrors = count($errorDetails) + count($generalErrors);

        /** @var PhpstanResult $result */
        $result = [
            'result' => $totalErrors > 0 ? 'failed' : 'passed',
            'errors' => $totalErrors,
        ];

        if ($errorDetails !== []) {
            $result['error_details'] = $errorDetails;
        }

        if ($generalErrors !== []) {
            $result['general_errors'] = $generalErrors;
        }

        return $result;
    }
}
