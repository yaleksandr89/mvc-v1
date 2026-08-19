<?php
declare(strict_types=1);

namespace Yaa\Framework;

define('BASE_PATH', dirname(__DIR__));

use Symfony\Component\Dotenv\Dotenv;

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/vendor/autoload.php';
$routes = include BASE_PATH . '/config/routes.php';

$dotenv = new Dotenv();
$dotenv->load(BASE_PATH . '/.env');
$timezone = env('APP_TIMEZONE', 'Europe/Moscow');
date_default_timezone_set(is_string($timezone) ? $timezone : 'Europe/Moscow');

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (!is_string($requestUri)) {
    $requestUri = '/';
}

$track = (new Router())->getTrack($routes, $requestUri);
$page = (new Dispatcher())->getPage($track);

echo (new View())->render($page);
