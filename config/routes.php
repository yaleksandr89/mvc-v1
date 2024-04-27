<?php

use Yaa\Framework\Route;

// Порядок маршрутов для определенной группы имеет значение.
return [
    // Главная
    new Route('/', 'main', 'index'),

    // Список статьи
    new Route('/articles', 'article', 'all'),
    // Создать статью
    new Route('/articles/create', 'article', 'create'),
    //  редактировать статью
    new Route('/articles/:id/edit', 'article', 'edit'),
    // Удалить статью
    new Route('/articles/:id/delete', 'article', 'delete'),
    // Выбранная страница
    new Route('/articles/:id/show', 'article', 'show'),

    // Контакты
    new Route('/contacts', 'contact', 'index'),
];
