<?php

declare(strict_types=1);

namespace Pao\Formatters;

/**
 * @internal
 */
final class YamlEncoder
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function encode(array $data): string
    {
        return self::encodeValue($data, 0);
    }

    private static function encodeValue(mixed $value, int $indent): string
    {
        if (is_array($value) && $value !== []) {
            if (array_is_list($value)) {
                return self::encodeList($value, $indent);
            }

            /** @var array<string, mixed> $value */
            return self::encodeMap($value, $indent);
        }

        return self::encodeScalar($value);
    }

    /**
     * @param  array<string, mixed>  $map
     */
    private static function encodeMap(array $map, int $indent): string
    {
        $prefix = str_repeat('  ', $indent);
        $lines = [];

        foreach ($map as $key => $value) {
            if (is_array($value) && $value !== []) {
                $lines[] = $prefix.$key.':';
                $lines[] = self::encodeValue($value, $indent + 1);
            } else {
                $lines[] = $prefix.$key.': '.self::encodeScalar($value);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<mixed>  $list
     */
    private static function encodeList(array $list, int $indent): string
    {
        $prefix = str_repeat('  ', $indent);
        $lines = [];

        foreach ($list as $item) {
            if (is_array($item) && $item !== [] && ! array_is_list($item)) {
                $first = true;

                foreach ($item as $key => $value) {
                    if ($first) {
                        if (is_array($value) && $value !== []) {
                            $lines[] = $prefix.'- '.$key.':';
                            $lines[] = self::encodeValue($value, $indent + 2);
                        } else {
                            $lines[] = $prefix.'- '.$key.': '.self::encodeScalar($value);
                        }

                        $first = false;
                    } elseif (is_array($value) && $value !== []) {
                        $lines[] = $prefix.'  '.$key.':';
                        $lines[] = self::encodeValue($value, $indent + 2);
                    } else {
                        $lines[] = $prefix.'  '.$key.': '.self::encodeScalar($value);
                    }
                }
            } else {
                $lines[] = $prefix.'- '.self::encodeScalar($item);
            }
        }

        return implode("\n", $lines);
    }

    private static function encodeScalar(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value === '') {
            return '""';
        }

        if ($value === []) {
            return '[]';
        }

        /** @var string $value */
        $string = $value;

        if (self::needsQuoting($string)) {
            return '"'.self::escapeString($string).'"';
        }

        return $string;
    }

    private static function needsQuoting(string $value): bool
    {
        if (preg_match('/^[\d.]+$/', $value) === 1) {
            return true;
        }

        if (in_array(strtolower($value), ['true', 'false', 'yes', 'no', 'on', 'off', 'null', '~'], true)) {
            return true;
        }

        if (preg_match('/[:{}\[\],&*!|>\'"%@`#\n\r\t\\\\]/', $value) === 1) {
            return true;
        }

        if (in_array($value[0], ['-', '?', ' '], true)) {
            return true;
        }

        return str_ends_with($value, ' ');
    }

    private static function escapeString(string $value): string
    {
        return str_replace(
            ['\\', '"', "\n", "\r", "\t"],
            ['\\\\', '\\"', '\\n', '\\r', '\\t'],
            $value,
        );
    }
}
