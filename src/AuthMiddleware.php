<?php
declare(strict_types=1);

namespace App;

use Throwable;

final class AuthMiddleware
{
    public static function requireAuth(?string $requiredRole = null): array
    {
        $token = JwtHandler::bearerToken();

        if (!$token) {
            Response::error('Missing bearer token', 401);
        }

        try {
            $payload = JwtHandler::decode($token);
        } catch (Throwable $e) {
            Response::error('Invalid or expired token', 401);
        }

        if ($requiredRole !== null && (($payload['role'] ?? '') !== $requiredRole)) {
            Response::error('Forbidden', 403);
        }

        return $payload;
    }
}