<?php

declare(strict_types=1);

namespace Pao\Formatters;

/**
 * @internal
 */
final class OutputFormatter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function format(array $data): string
    {
        return match (self::resolveFormat()) {
            'yaml' => YamlEncoder::encode($data),
            default => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR),
        };
    }

    /**
     * @return 'json'|'yaml'
     */
    public static function resolveFormat(): string
    {
        /** @var string $raw */
        $raw = $_SERVER['PAO_FORMAT'] ?? 'json';
        $format = strtolower(trim($raw));

        if ($format === 'yaml') {
            return 'yaml';
        }

        return 'json';
    }
}
