<?php

/**
 * Copyright ©2026 cdme. All rights reserved.
 * Author: https://cdme.cn
 * Email:  hi@cdme.cn
 */

declare(strict_types=1);

namespace app\core;

use Exception;

class Env
{
    protected static array $vars = [];

    public static function load(string $envPath = ROOT . '/.env'): void
    {
        if (!file_exists($envPath)) {
            throw new Exception('.env 不存在');
        }

        if (!is_readable($envPath)) {
            throw new Exception('.env 无法读取');
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ($key === '') {
                continue;
            }

            if (!str_starts_with($value, '"') && !str_starts_with($value, "'")) {
                $pos = strpos($value, ' #');
                if ($pos !== false) {
                    $value = trim(substr($value, 0, $pos));
                }
            }

            if (strlen($value) >= 2) {
                if (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                    $value = substr($value, 1, -1);
                } elseif (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                    $value = stripcslashes(substr($value, 1, -1));
                }
            }

            $valueLower = strtolower($value);
            $value = match ($valueLower) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => $value,
            };

            self::$vars[$key] = $value;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$vars[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$vars);
    }

    public static function all(): array
    {
        return self::$vars;
    }
}
