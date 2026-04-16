<?php
declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

final class Config
{
    public static function load(string $basePath): void
    {
        $dotenv = Dotenv::createImmutable($basePath);
        $dotenv->load();
    }

    public static function get(string $key, ?string $default = null): string
    {
        return $_ENV[$key] ?? $default ?? '';
    }
}

function escapeString(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}