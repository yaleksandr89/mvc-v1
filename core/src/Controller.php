<?php

namespace Yaa\Framework;

class Controller
{
    protected string $layout = LAYOUT;

    // Мета данные: title, description, keywords
    protected array $meta;

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

    protected static function getCurrentPage(): int
    {
        return array_key_exists('page', $_GET)
            ? (int)$_GET['page']
            : 1;
    }

    protected static function getPerPage(int $perPage = 10): int
    {
        return $perPage;
    }

    protected static function getTotalPages(string $classModel, string $tableName): int
    {
        /** @var Model $classModel - класс, который является дочернего класса Model */
        return $classModel::getInstance()
            ->getColumn("SELECT COUNT(*) AS count FROM $tableName");
    }
}
