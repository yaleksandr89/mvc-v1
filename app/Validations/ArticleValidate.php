<?php

namespace App\Validations;

use App\Models\ArticleModal;

class ArticleValidate
{
    public static function validate(array $data): array
    {
        $errors = [];

        $id = $data['id'] ?? null;
        $title = mb_strtolower(trim($data['title']));
        $excerpt = mb_strtolower(trim($data['excerpt']));
        $contentHtml = mb_strtolower(trim($data['content_html']));

        // Название
        if (empty($title)) {
            $errors['title']['empty'] = 'Не может быть пустым';
        }
        if (mb_strlen($title) < 15) {
            $errors['title']['minLength'] = 'Минимум 15 символов';
        }
        if (is_null($id) && ArticleModal::getInstance()->getByColumn('title', $title)) {
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
