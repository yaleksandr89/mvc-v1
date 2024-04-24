<?php

use Yaa\Framework\Route;

return [
    // Главная
    new Route('/', 'main', 'index'),

    // все статьи
    new Route('/articles', 'page', 'all'),
    // Выбранная страница
    new Route('/article/:id', 'page', 'single'),
    // Нужный диапазон статей
    new Route('/articles/:start/:end', 'page', 'custom'),

    // Контакты
    new Route('/contacts', 'contact', 'index'),
];
