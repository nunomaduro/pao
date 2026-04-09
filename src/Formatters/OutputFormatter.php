<?php

declare(strict_types=1);

namespace Pao\Formatters;

use Symfony\Component\Yaml\Yaml;

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
            'pretty' => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
            'yaml' => rtrim(Yaml::dump($data, 4, 2)),
            default => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR),
        };
    }

    /**
     * @return 'json'|'pretty'|'yaml'
     */
    public static function resolveFormat(): string
    {
        /** @var string $raw */
        $raw = $_SERVER['PAO_FORMAT'] ?? 'json';
        $format = strtolower(trim($raw));

        return match ($format) {
            'pretty', 'yaml' => $format,
            default => 'json',
        };
    }
}
