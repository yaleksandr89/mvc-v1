<?php declare(strict_types=1);

// HTTP or HHTPS
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

define('ROOT', dirname(__DIR__));
define('WORK_DIR', ROOT . '/project');

define('CONFIG', ROOT . '/config');
define('LOG', ROOT . '/log');
define('CORE', ROOT . '/vendor/core');

define('PROJECT_VIEW', WORK_DIR . '/Views');
define('PROJECT_IMG', '/project/assets/img');
define('PROJECT_CSS', '/project/assets/css');
define('PROJECT_JS', '/project/assets/js');

define('LAYOUT', 'default');
define('PATH', $appPath);