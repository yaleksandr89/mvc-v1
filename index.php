<?php declare(strict_types=1);

namespace Core;

require_once 'vendor/autoload.php';
require ROOT . '/config/database.php';
$routes = require ROOT . '/config/routes.php';

$track = (new Router)->getTrack($routes, $_SERVER['REQUEST_URI']);
$page  = (new Dispatcher)->getPage($track);

echo (new View) -> render($page);