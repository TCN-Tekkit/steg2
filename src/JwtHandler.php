<?php
declare(strict_types=1);

namespace App;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JwtHandler
{
    public static function create(int $userId, string $username, string $role): string
    {
        $now = time();
        $exp = $now + (int) Config::get('JWT_EXP_SECONDS', '3600');

        $payload = [
            'iss' => Config::get('JWT_ISSUER'),
            'aud' => Config::get('JWT_AUDIENCE'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $exp,
            'sub' => $userId,
            'username' => $username,
            'role' => $role,
        ];

        return JWT::encode($payload, Config::get('JWT_SECRET'), 'HS256');
    }

    public static function decode(string $token): array
    {
        return (array) JWT::decode($token, new Key(Config::get('JWT_SECRET'), 'HS256'));
    }

public static function bearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? '';

    if ($header === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (preg_match('/Bearer\s+(\S+)/', $header, $matches) === 1) {
        return $matches[1];
    }

    return null;
}
}