<?php

session_start();

// HTTP or HTTPS
use JetBrains\PhpStorm\NoReturn;

if ($_SERVER['SERVER_PORT'] !== '443') {
    // http://example.com/project/index.php
    $appPath = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
} else {
    // https://example.com/project/index.php
    $appPath = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
}

// http://example.com/project/index.php → http://example.com/project/
$appPath = preg_replace('~[^/]+$~', '', $appPath);

// http://example.com/project/ → http://example.com
$appPath = str_replace('/project/', '', $appPath);

define('PATH', $appPath);

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
