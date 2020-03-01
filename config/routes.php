<?php

use Core\Route;

return [
    // Главная
    new Route('/', 'main', 'index'),

    // Тестовая страница 1
    new Route('/articles', 'page', 'all'),
    // Тестовая страница 2
    new Route('/article/:id', 'page', 'single'),

    // Вполненные пет-проекты
    new Route('/pet-projects', 'pet', 'index'),

    // Контакты
    new Route('/contacts', 'contact', 'index'),
];
