<?php
declare(strict_types=1);

use Yaa\Framework\RedirectResponse;

session_start();

const WORK_DIR = __DIR__ . '/../app';
const LOG = __DIR__ . '/../logs';
const PROJECT_VIEW = __DIR__ . '/../views';
const PROJECT_IMG = '/assets/img';
const PROJECT_CSS = '/assets/css';
const PROJECT_JS = '/assets/js';
const LAYOUT = 'default';

function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    return array_key_exists($key, $_ENV) ? $_ENV[$key] : $default;
}

function redirect(string $path, int $code = 302): RedirectResponse
{
    return new RedirectResponse($path, $code);
}

function deleteSessionKey(string $key): void
{
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

function pullSessionValue(string $key, mixed $default = null): mixed
{
    if (!array_key_exists($key, $_SESSION)) {
        return $default;
    }

    $value = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $value;
}

function addFlashMessage(string $message, string $type = 'success'): void
{
    deleteSessionKey('flash');
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type,
    ];
}

/**
 * @param array<string, array<string, string>> $message
 */
function validationFlashMessage(array $message): void
{
    deleteSessionKey('validation');
    $_SESSION['validation'] = $message;
}

/**
 * @param array<array-key, mixed> $values
 */
function oldFormValue(array $values): void
{
    deleteSessionKey('old_form_value');
    $_SESSION['old_form_value'] = $values;
}
