<?php
declare(strict_types=1);

namespace App\Presentation;

class LayoutPresenter
{
    /**
     * @return array{
     *     activeNavigation: 'home'|'articles'|'create'|'contacts'|null,
     *     flash: array{
     *         message: string,
     *         type: 'primary'|'secondary'|'success'|'danger'|'warning'|'info'|'light'|'dark'
     *     }|null
     * }
     */
    public static function prepare(string $requestUri, mixed $flash): array
    {
        $requestPath = parse_url($requestUri, PHP_URL_PATH);
        if (!is_string($requestPath) || $requestPath === '') {
            $requestPath = '/';
        }

        if ($requestPath !== '/' && str_ends_with($requestPath, '/')) {
            $requestPath = substr($requestPath, 0, -1);
        }

        $activeNavigation = match (true) {
            $requestPath === '/' => 'home',
            $requestPath === '/articles',
            preg_match('#^/articles/[0-9]+/(show|edit)$#', $requestPath) === 1 => 'articles',
            $requestPath === '/articles/create' => 'create',
            $requestPath === '/contacts' => 'contacts',
            default => null,
        };

        if (!is_array($flash)) {
            return [
                'activeNavigation' => $activeNavigation,
                'flash' => null,
            ];
        }

        $message = $flash['message'] ?? '';
        if (!is_string($message)) {
            $message = '';
        }

        $type = $flash['type'] ?? 'success';
        $allowedTypes = [
            'primary',
            'secondary',
            'success',
            'danger',
            'warning',
            'info',
            'light',
            'dark',
        ];
        if (!is_string($type) || !in_array($type, $allowedTypes, true)) {
            $type = 'success';
        }

        return [
            'activeNavigation' => $activeNavigation,
            'flash' => [
                'message' => $message,
                'type' => $type,
            ],
        ];
    }
}
