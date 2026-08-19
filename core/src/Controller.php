<?php
declare(strict_types=1);

namespace Yaa\Framework;

class Controller
{
    protected string $layout = LAYOUT;

    // Мета данные: title, description, keywords
    protected array $meta = [];

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
        $page = $_GET['page'] ?? null;

        if (!is_int($page) && !is_string($page)) {
            return 1;
        }

        return (int)$page;
    }

    protected static function getPerPage(int $perPage = 10): int
    {
        return $perPage;
    }

    /**
     * @param class-string<Model> $classModel
     */
    protected static function getTotalPages(string $classModel, string $tableName): int
    {
        $total = $classModel::getInstance()
            ->getColumn("SELECT COUNT(*) AS count FROM $tableName");

        return is_numeric($total) ? (int)$total : 0;
    }
}
