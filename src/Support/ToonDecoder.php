<?php

declare(strict_types=1);

namespace Pao\Support;

/**
 * @link https://github.com/toon-format/spec
 *
 * @internal
 *
 * @phpstan-import-type Result from \Pao\Execution
 */
final class ToonDecoder
{
    /**
     * @return Result
     */
    public static function decode(string $toon): array
    {
        $lines = explode("\n", $toon);
        /** @var Result $result */
        $result = [];
        $i = 0;
        $count = count($lines);

        while ($i < $count) {
            $line = $lines[$i];

            if ($line === '' || ctype_space($line)) {
                $i++;

                continue;
            }

            if (preg_match('/^(\w+)\[(\d+)]\{(.+)}:\s*$/', $line, $matches) === 1) {
                $key = $matches[1];
                $expectedCount = (int) $matches[2];
                $fields = explode(',', $matches[3]);
                $rows = [];
                $i++;

                for ($r = 0; $r < $expectedCount && $i < $count; $r++, $i++) {
                    $rowLine = ltrim($lines[$i]);

                    if ($rowLine === '') {
                        $r--;

                        continue;
                    }

                    $values = self::parseCsvRow($rowLine, count($fields));
                    /** @var array<string, string|int> $row */
                    $row = [];

                    foreach ($fields as $fi => $field) {
                        $value = $values[$fi] ?? '';
                        $row[$field] = is_numeric($value) && ! str_contains($value, ' ') ? (int) $value : $value;
                    }

                    $rows[] = $row;
                }

                $result[$key] = $rows;

                continue;
            }

            if (preg_match('/^(\w+)\[(\d+)]:\s*$/', $line, $matches) === 1) {
                $key = $matches[1];
                $expectedCount = (int) $matches[2];
                $items = [];
                $i++;

                for ($r = 0; $r < $expectedCount && $i < $count; $r++, $i++) {
                    $itemLine = $lines[$i];
                    $itemLine = preg_replace('/^\s*-\s*/', '', $itemLine) ?? $itemLine;
                    $items[] = self::unescapeValue(trim($itemLine));
                }

                $result[$key] = $items;

                continue;
            }

            if (preg_match('/^(\w+):\s*(.*)$/', $line, $matches) === 1) {
                $key = $matches[1];
                $value = $matches[2];

                $result[$key] = match (true) {
                    $value === 'true' => true,
                    $value === 'false' => false,
                    $value === 'null' => null,
                    is_numeric($value) && ! str_contains($value, ' ') => str_contains($value, '.') ? (float) $value : (int) $value,
                    default => self::unescapeValue($value),
                };
            }

            $i++;
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    private static function parseCsvRow(string $row, int $fieldCount): array
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $len = strlen($row);

        for ($i = 0; $i < $len; $i++) {
            $char = $row[$i];

            if ($inQuotes) {
                if ($char === '\\' && $i + 1 < $len) {
                    $next = $row[$i + 1];
                    $current .= match ($next) {
                        'n' => "\n",
                        'r' => "\r",
                        't' => "\t",
                        '"' => '"',
                        '\\' => '\\',
                        default => '\\'.$next,
                    };
                    $i++;
                } elseif ($char === '"') {
                    $inQuotes = false;
                } else {
                    $current .= $char;
                }
            } elseif ($char === '"') {
                $inQuotes = true;
            } elseif ($char === ',' && count($values) < $fieldCount - 1) {
                $values[] = $current;
                $current = '';
            } else {
                $current .= $char;
            }
        }

        $values[] = $current;

        return $values;
    }

    private static function unescapeValue(string $value): string
    {
        if (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            $value = substr($value, 1, -1);
            $value = str_replace('\\n', "\n", $value);
            $value = str_replace('\\r', "\r", $value);
            $value = str_replace('\\t', "\t", $value);
            $value = str_replace('\\"', '"', $value);
            $value = str_replace('\\\\', '\\', $value);
        }

        return $value;
    }
}
