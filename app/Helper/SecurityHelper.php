<?php
declare(strict_types=1);

namespace App\Helper;

use Random\RandomException;
use Stringable;

class SecurityHelper
{
    private const string CSRF_SESSION_KEY = '_csrf_token';

    public static function escapeHtml(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_scalar($value) && !$value instanceof Stringable) {
            return '';
        }

        return htmlspecialchars(
            (string)$value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }

    /**
     * @param array<string, mixed> $session
     *
     * @throws RandomException
     */
    public static function csrfToken(array &$session): string
    {
        $token = $session[self::CSRF_SESSION_KEY] ?? null;

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $session[self::CSRF_SESSION_KEY] = $token;
        }

        return $token;
    }

    /**
     * @param array<string, mixed> $session
     */
    public static function isValidCsrfToken(array $session, mixed $token): bool
    {
        $sessionToken = $session[self::CSRF_SESSION_KEY] ?? null;

        if (
            !is_string($sessionToken) ||
            $sessionToken === '' ||
            !is_string($token) ||
            $token === ''
        ) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function isPostRequest(string $method): bool
    {
        return strtoupper($method) === 'POST';
    }
}
