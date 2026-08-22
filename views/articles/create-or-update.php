<?php
declare(strict_types=1);

use App\Helper\SecurityHelper;

/**
 * @var array $article
 * @var string $h1
 * @var string $desc
 * @var string $nameMethod
 */

['title' => $title, 'excerpt' => $excerpt, 'content_html' => $contentHtml] = $article;

$articleId = isset($article['id']) ? (int)$article['id'] : null;
$csrfToken = SecurityHelper::csrfToken($_SESSION);

$errorsTitle = [];
$errorsExcerpt = [];
$errorsContentHtml = [];
$titleIsInvalid = '';
$excerptIsInvalid = '';
$contentHtmlIsInvalid = '';
$validation = $_SESSION['validation'] ?? [];
if (is_array($validation)) {
    $validationTitle = $validation['title'] ?? [];
    $validationExcerpt = $validation['excerpt'] ?? [];
    $validationContentHtml = $validation['content_html'] ?? [];

    $errorsTitle = is_array($validationTitle) ? $validationTitle : [];
    $errorsExcerpt = is_array($validationExcerpt) ? $validationExcerpt : [];
    $errorsContentHtml = is_array($validationContentHtml) ? $validationContentHtml : [];

    $titleIsInvalid = count($errorsTitle) > 0 ? ' is-invalid' : '';
    $excerptIsInvalid = count($errorsExcerpt) > 0 ? ' is-invalid' : '';
    $contentHtmlIsInvalid = count($errorsContentHtml) > 0 ? ' is-invalid' : '';
}
deleteSessionKey('validation');

$oldFormValue = $_SESSION['old_form_value'] ?? [];
if (!is_array($oldFormValue)) {
    $oldFormValue = [];
}

$oldTitle = $oldFormValue['title'] ?? $title;
$oldExcerpt = $oldFormValue['excerpt'] ?? $excerpt;
$oldContentHtml = $oldFormValue['content_html'] ?? $contentHtml;
$title = is_string($oldTitle) ? $oldTitle : $title;
$excerpt = is_string($oldExcerpt) ? $oldExcerpt : $excerpt;
$contentHtml = is_string($oldContentHtml) ? $oldContentHtml : $contentHtml;
deleteSessionKey('old_form_value');
?>

<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= SecurityHelper::escapeHtml($h1) ?></h1>
    <span><?= SecurityHelper::escapeHtml($desc) ?></span>
</div>
<div class="card">
    <div class="card-header">
        <span>Используемый контроллер: <code><?= SecurityHelper::escapeHtml($nameMethod) ?></code></span>
    </div>
    <div class="card-body px-3 px-md-5">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <?php if ('edit' === $type): ?>
                    <form
                        id="delete-article-form"
                        action="/articles/<?= $articleId ?>/delete"
                        method="POST"
                    >
                        <input
                            type="hidden"
                            name="_csrf"
                            value="<?= SecurityHelper::escapeHtml($csrfToken) ?>"
                        >
                    </form>
                    <form action="/articles/<?= $articleId ?>/edit" method="POST">
                <?php else: ?>
                    <form action="/articles/create" method="POST">
                <?php endif; ?>
                    <input
                        type="hidden"
                        name="_csrf"
                        value="<?= SecurityHelper::escapeHtml($csrfToken) ?>"
                    >
                    <div class="mb-3">
                        <label for="title" class="form-label">Название:</label>
                        <input
                                type="text"
                                id="title"
                                class="form-control<?= SecurityHelper::escapeHtml($titleIsInvalid) ?>"
                                name="title"
                                value="<?= SecurityHelper::escapeHtml($title) ?>"
                                aria-describedby="titleFeedback"
                                required
                        >
                        <?php if (count($errorsTitle) > 0): ?>
                            <?php foreach ($errorsTitle as $errorTitle): ?>
                                <div id="titleFeedback" class="invalid-feedback">
                                    <?= SecurityHelper::escapeHtml($errorTitle) ?>
                                    <?php unset($errorTitle); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Краткое содержание:</label>
                        <textarea
                                id="excerpt"
                                class="form-control<?= SecurityHelper::escapeHtml($excerptIsInvalid) ?>"
                                name="excerpt"
                                rows="3"
                                aria-describedby="excerptFeedback"
                                required
                        ><?= SecurityHelper::escapeHtml($excerpt) ?></textarea>
                        <?php if (count($errorsExcerpt) > 0): ?>
                            <?php foreach ($errorsExcerpt as $errorExcerpt): ?>
                                <div id="excerptFeedback" class="invalid-feedback">
                                    <?= SecurityHelper::escapeHtml($errorExcerpt) ?>
                                </div>
                            <?php endforeach; ?>
                            <?php unset($errorsExcerpt); ?>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="contentHtmlFeedback" class="form-label">Содержание:</label>
                        <textarea
                                id="contentHtmlFeedback"
                                class="form-control<?= SecurityHelper::escapeHtml($contentHtmlIsInvalid) ?>"
                                name="content_html"
                                rows="6"
                                aria-describedby="contentHtmlFeedback"
                                required
                        ><?= SecurityHelper::escapeHtml($contentHtml) ?></textarea>
                        <?php if (count($errorsContentHtml) > 0): ?>
                            <?php foreach ($errorsContentHtml as $errorContentHtml): ?>
                                <div id="contentHtmlFeedback" class="invalid-feedback">
                                    <?= SecurityHelper::escapeHtml($errorContentHtml) ?>
                                </div>
                            <?php endforeach; ?>
                            <?php unset($errorsContentHtml); ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-auto d-flex flex-column flex-md-row justify-content-between gap-3">
                        <?php if ('edit' === $type): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-lg btn-outline-dark">
                                    Обновить
                                </button>
                                <button
                                    type="submit"
                                    form="delete-article-form"
                                    class="btn btn-lg btn-outline-danger"
                                >
                                    Удалить
                                </button>
                            </div>
                            <a href="/articles" class="btn btn-lg btn-outline-dark align-self-md-center">
                                К списку статей
                            </a>
                        <?php else: ?>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-lg btn-outline-dark">
                                    <?= 'Создать' ?>
                                </button>
                                <a href="/articles" class="btn btn-lg btn-outline-dark">
                                    К списку статей
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
