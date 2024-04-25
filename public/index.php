<?php

namespace Yaa\Framework;

define('BASE_PATH', dirname(__DIR__));

use Symfony\Component\Dotenv\Dotenv;

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/vendor/autoload.php';
$routes = include BASE_PATH . '/config/routes.php';

$dotenv = new Dotenv();
$dotenv->load(BASE_PATH . '/.env');
date_default_timezone_set(env('APP_TIMEZONE', 'Europe/Moscow'));

$track = (new Router())->getTrack($routes, $_SERVER['REQUEST_URI']);
$page = (new Dispatcher())->getPage($track);

echo (new View())->render($page);
