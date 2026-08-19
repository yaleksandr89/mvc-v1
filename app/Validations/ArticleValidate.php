<?php
declare(strict_types=1);

namespace App\Validations;

use App\Models\ArticleModal;

class ArticleValidate
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function validate(
        string $title,
        string $excerpt,
        string $contentHtml,
        ?int $id = null
    ): array
    {
        $errors = [];

        $title = mb_strtolower(trim($title));
        $excerpt = mb_strtolower(trim($excerpt));
        $contentHtml = mb_strtolower(trim($contentHtml));

        // Название
        if (empty($title)) {
            $errors['title']['empty'] = 'Не может быть пустым';
        }
        if (mb_strlen($title) < 15) {
            $errors['title']['minLength'] = 'Минимум 15 символов';
        }
        if (
            ($article = ArticleModal::getInstance()->getByTitle($title)) &&
            ($id === null || (int)$article['id'] !== $id)
        ) {
            $errors['title']['unique'] = 'Название уже существует';
        }

        // Краткое содержание
        if (empty($excerpt)) {
            $errors['excerpt']['empty'] = 'Не может быть пустым';
        }
        if (mb_strlen($excerpt) < 50) {
            $errors['excerpt']['minLength'] = 'Минимум 50 символов';
        }

        // Содержание
        if (empty($contentHtml)) {
            $errors['content_html']['empty'] = 'Не может быть пустым';
        }
        if (mb_strlen($contentHtml) < 100) {
            $errors['content_html']['minLength'] = 'Минимум 100 символов';
        }

        return $errors;
    }
}
