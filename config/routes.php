<?php

declare(strict_types=1);

use Yaa\Framework\Route;

// RU: Маршрутизатор выбирает первое совпадение, поэтому порядок маршрутов важен.
// EN: The router selects the first match, so route order matters.
/** @var list<Route> */
return [
    new Route('/', 'main', 'index'),

    new Route('/articles', 'article', 'all'),
    new Route('/articles/create', 'article', 'create'),
    new Route('/articles/:id/edit', 'article', 'edit'),
    new Route('/articles/:id/delete', 'article', 'delete'),
    new Route('/articles/:id/show', 'article', 'show'),

    new Route('/contacts', 'contact', 'index'),
];
