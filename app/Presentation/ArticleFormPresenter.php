<?php
declare(strict_types=1);

namespace App\Presentation;

use InvalidArgumentException;

class ArticleFormPresenter
{
    /**
     * @param array{
     *     id?: int|string,
     *     title: string,
     *     excerpt: string,
     *     content_html: string
     * } $article
     * @param mixed $validation
     * @param mixed $oldFormValue
     * @param string $csrfToken
     * @param string $type
     *
     * @return array{
     *     type: 'create'|'edit',
     *     formAction: string,
     *     deleteAction: string|null,
     *     csrfToken: string,
     *     title: string,
     *     excerpt: string,
     *     contentHtml: string,
     *     errorsTitle: list<string>,
     *     errorsExcerpt: list<string>,
     *     errorsContentHtml: list<string>,
     *     titleIsInvalid: ''|' is-invalid',
     *     excerptIsInvalid: ''|' is-invalid',
     *     contentHtmlIsInvalid: ''|' is-invalid'
     * }
     */
    public static function prepare(
        array $article,
        mixed $validation,
        mixed $oldFormValue,
        string $csrfToken,
        string $type
    ): array {
        if ($type !== 'create' && $type !== 'edit') {
            throw new InvalidArgumentException("Unsupported article form type: $type");
        }

        $formAction = '/articles/create';
        $deleteAction = null;

        if ($type === 'edit') {
            $articleId = $article['id'] ?? null;
            if (
                (!is_int($articleId) && !is_string($articleId)) ||
                preg_match('/^[0-9]+$/D', (string)$articleId) !== 1 ||
                (int)$articleId < 1
            ) {
                throw new InvalidArgumentException('A valid numeric article ID is required for edit mode.');
            }

            $articleId = (int)$articleId;
            $formAction = "/articles/$articleId/edit";
            $deleteAction = "/articles/$articleId/delete";
        }

        $errorsTitle = self::errorsFor($validation, 'title');
        $errorsExcerpt = self::errorsFor($validation, 'excerpt');
        $errorsContentHtml = self::errorsFor($validation, 'content_html');

        $title = $article['title'];
        $excerpt = $article['excerpt'];
        $contentHtml = $article['content_html'];

        if (is_array($oldFormValue)) {
            $title = is_string($oldFormValue['title'] ?? null)
                ? $oldFormValue['title']
                : $title;
            $excerpt = is_string($oldFormValue['excerpt'] ?? null)
                ? $oldFormValue['excerpt']
                : $excerpt;
            $contentHtml = is_string($oldFormValue['content_html'] ?? null)
                ? $oldFormValue['content_html']
                : $contentHtml;
        }

        return [
            'type' => $type,
            'formAction' => $formAction,
            'deleteAction' => $deleteAction,
            'csrfToken' => $csrfToken,
            'title' => $title,
            'excerpt' => $excerpt,
            'contentHtml' => $contentHtml,
            'errorsTitle' => $errorsTitle,
            'errorsExcerpt' => $errorsExcerpt,
            'errorsContentHtml' => $errorsContentHtml,
            'titleIsInvalid' => $errorsTitle === [] ? '' : ' is-invalid',
            'excerptIsInvalid' => $errorsExcerpt === [] ? '' : ' is-invalid',
            'contentHtmlIsInvalid' => $errorsContentHtml === [] ? '' : ' is-invalid',
        ];
    }

    /**
     * @return list<string>
     */
    private static function errorsFor(mixed $validation, string $field): array
    {
        if (!is_array($validation)) {
            return [];
        }

        $messages = $validation[$field] ?? null;
        if (!is_array($messages)) {
            return [];
        }

        return array_values(array_filter($messages, is_string(...)));
    }
}
