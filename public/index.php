<?php
declare(strict_types=1);

namespace Yaa\Framework;

define('BASE_PATH', dirname(__DIR__));

use App\Presentation\LayoutPresenter;

require_once BASE_PATH . '/config/app.php';
loadEnvFile(BASE_PATH . '/.env');
require_once BASE_PATH . '/vendor/autoload.php';
$routes = include BASE_PATH . '/config/routes.php';

$timezone = env('APP_TIMEZONE', 'Europe/Moscow');
date_default_timezone_set(is_string($timezone) ? $timezone : 'Europe/Moscow');

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (!is_string($requestUri)) {
    $requestUri = '/';
}

$track = new Router()->getTrack($routes, $requestUri);
$result = new Dispatcher()->dispatch($track);

if ($result instanceof Page) {
    $layoutData = LayoutPresenter::prepare($requestUri, pullSessionValue('flash'));
    $response = new View()->render($result, $layoutData);
} else {
    $response = $result;
}

http_response_code($response->getStatus());

foreach ($response->getHeaders() as $name => $value) {
    header("$name: $value", replace: true);
}

echo $response->getBody();
