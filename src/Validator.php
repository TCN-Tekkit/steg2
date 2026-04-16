<?php
declare(strict_types=1);

namespace App;

final class Validator
{
    public static function username(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $value) === 1;
    }

    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function password(string $value): bool
    {
        return strlen($value) >= 10
            && preg_match('/[A-Z]/', $value) === 1
            && preg_match('/[a-z]/', $value) === 1
            && preg_match('/\d/', $value) === 1;
    }

    public static function text(string $value, int $min = 1, int $max = 1000): bool
    {
        $len = mb_strlen(trim($value));
        return $len >= $min && $len <= $max;
    }

    public static function name(string $value): bool
    {
        $len = mb_strlen(trim($value));
        return $len >= 2 && $len <= 100;
    }

    public static function pin(string $value): bool
    {
        return preg_match('/^\d{4}$/', $value) === 1;
    }
}