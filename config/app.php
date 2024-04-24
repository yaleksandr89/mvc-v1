<?php

function env(string $key, $default = null)
{
    $value = array_key_exists($key, $_ENV) ? $_ENV[$key] : false;

    return $value !== false ? $value : $default;
}

// HTTP or HTTPS
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
