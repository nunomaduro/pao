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
            'yaml' => self::formatYaml($data),
            default => json_encode($data, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function formatYaml(array $data): string
    {
        if (! class_exists(Yaml::class)) {
            throw new \RuntimeException('PAO_FORMAT=yaml requires symfony/yaml. Install it with: composer require symfony/yaml --dev');
        }

        return rtrim(Yaml::dump($data, 4, 2));
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
