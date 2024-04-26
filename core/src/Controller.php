<?php

namespace Yaa\Framework;

class Controller
{
    protected string $layout = LAYOUT;

    // Мета данные: title, description, keywords
    protected array $meta;

    // Текущая страница пагинация
    protected int $page = 1;

    // Количество записей на странице
    protected int $perPage = 10;

    // Общее количество записей
    protected int $total = 0;

    protected function render(string $view, array $data = []): Page
    {
        return new Page(
            $this->layout,
            $this->meta,
            $view,
            $data
        );
    }
}
