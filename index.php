<?php

namespace Core;

require_once 'vendor/autoload.php';

$routes = require ROOT . '/config/routes.php';
$track = (new Router)->getTrack($routes, $_SERVER['REQUEST_URI']);
$page  = (new Dispatcher)->getPage($track);

echo (new View) -> render($page);