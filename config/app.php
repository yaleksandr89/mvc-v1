<?php

session_start();

use JetBrains\PhpStorm\NoReturn;

const WORK_DIR = BASE_PATH . '/app';
const LOG = BASE_PATH . '/logs';
const PROJECT_VIEW = BASE_PATH . '/views';
const PROJECT_IMG = '/assets/img';
const PROJECT_CSS = '/assets/css';
const PROJECT_JS = '/assets/js';
const LAYOUT = 'default';

function env(string $key, $default = null)
{
    $value = array_key_exists($key, $_ENV) ? $_ENV[$key] : false;

    return $value !== false ? $value : $default;
}

#[NoReturn]
function redirect(string $path, int $code = 302): void
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

function validationFlashMessage(array $message): void
{
    deleteSessionKey('validation');
    $_SESSION['validation'] = $message;
}

function oldFormValue(array $values): void
{
    deleteSessionKey('old_form_value');
    $_SESSION['old_form_value'] = $values;
}
