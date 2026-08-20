<?php
declare(strict_types=1);

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
    $value = array_key_exists($key, $_ENV) ? $_ENV[$key] : false;

    return $value !== false ? $value : $default;
}

function redirect(string $path, int $code = 302): never
{
    header(header: 'Location: ' . $path, response_code: $code);
    exit;
}

function deleteSessionKey(string $key): void
{
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
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
