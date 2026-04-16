<?php
declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    public static function connect(): PDO
    {
        $dsn = 'mysql:host=' . Config::get('DB_HOST')
             . ';dbname=' . Config::get('DB_NAME')
             . ';charset=utf8mb4';

        return new PDO($dsn, Config::get('DB_USER'), Config::get('DB_PASS'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}