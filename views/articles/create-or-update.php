<?php
/**
 * @var array $article
 * @var string $h1
 * @var string $desc
 * @var string $nameMethod
 */

['title' => $title, 'excerpt' => $excerpt, 'content_html' => $contentHtml] = $article;

$errorsTitle = [];
$errorsExcerpt = [];
$errorsContentHtml = [];
$titleIsInvalid = '';
$excerptIsInvalid = '';
$contentHtmlIsInvalid = '';
if (array_key_exists('validation', $_SESSION)) {
    $errorsTitle = $_SESSION['validation']['title'] ?? [];
    $errorsExcerpt = $_SESSION['validation']['excerpt'] ?? [];
    $errorsContentHtml = $_SESSION['validation']['content_html'] ?? [];

    $titleIsInvalid = count($errorsTitle) > 0 ? ' is-invalid' : '';
    $excerptIsInvalid = count($errorsExcerpt) > 0 ? ' is-invalid' : '';
    $contentHtmlIsInvalid = count($errorsContentHtml) > 0 ? ' is-invalid' : '';
    deleteSessionKey('validation');
}

$title = $_SESSION['old_form_value']['title'] ?? $title;
$excerpt = $_SESSION['old_form_value']['excerpt'] ?? $excerpt;
$contentHtml = $_SESSION['old_form_value']['content_html'] ?? $contentHtml;
deleteSessionKey('old_form_value');
?>

<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= $h1 ?></h1>
    <span><?= $desc ?></span>
</div>
<div class="card">
    <div class="card-header">
        <span>Используемый контроллер: <code><?= $nameMethod ?></code></span>
    </div>
    <div class="card-body pl-5 pr-5">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <?php if ('edit' === $type): ?>
                    <form action="/articles/<?= $article['id'] ?>/edit" method="POST">
                <?php else: ?>
                    <form action="/articles/create" method="POST">
                <?php endif; ?>
                    <div class="mb-3">
                        <label for="title" class="form-label">Название:</label>
                        <input
                                type="text"
                                id="title"
                                class="form-control<?= $titleIsInvalid ?>"
                                name="title"
                                value="<?= $title ?>"
                                aria-describedby="titleFeedback"
                                required
                        >
                        <?php if (count($errorsTitle) > 0): ?>
                            <?php foreach ($errorsTitle as $errorTitle): ?>
                                <div id="titleFeedback" class="invalid-feedback">
                                    <?= $errorTitle ?>
                                    <?php unset($errorTitle); ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="excerpt" class="form-label">Краткое содержание:</label>
                        <textarea
                                id="excerpt"
                                class="form-control<?= $excerptIsInvalid ?>"
                                name="excerpt"
                                rows="3"
                                aria-describedby="excerptFeedback"
                                required
                        ><?= $excerpt ?></textarea>
                        <?php if (count($errorsExcerpt) > 0): ?>
                            <?php foreach ($errorsExcerpt as $errorExcerpt): ?>
                                <div id="excerptFeedback" class="invalid-feedback">
                                    <?= $errorExcerpt ?>
                                </div>
                            <?php endforeach; ?>
                            <?php unset($errorsExcerpt); ?>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="contentHtmlFeedback" class="form-label">Содержание:</label>
                        <textarea
                                id="contentHtmlFeedback"
                                class="form-control<?= $contentHtmlIsInvalid ?>"
                                name="content_html"
                                rows="6"
                                aria-describedby="contentHtmlFeedback"
                                required
                        ><?= $contentHtml ?></textarea>
                        <?php if (count($errorsContentHtml) > 0): ?>
                            <?php foreach ($errorsContentHtml as $errorContentHtml): ?>
                                <div id="contentHtmlFeedback" class="invalid-feedback">
                                    <?= $errorContentHtml ?>
                                </div>
                            <?php endforeach; ?>
                            <?php unset($errorsContentHtml); ?>
                        <?php endif; ?>
                    </div>
                    <div class="mt-auto d-flex justify-content-between">
                        <?php if ('edit' === $type): ?>
                            <div>
                                <button type="submit" class="btn btn-lg btn-outline-dark btn_link_dark">
                                    Обновить
                                </button>
                                <a href="/articles/<?= $article['id'] ?>/delete" class="btn btn-lg btn-outline-danger btn_link_dark ms-2">
                                    Удалить
                                </a>
                            </div>
                            <a href="/articles" class="btn btn-lg btn-outline-dark">
                                К списку статей
                            </a>
                        <?php else: ?>
                            <button type="submit" class="btn btn-lg btn-outline-dark btn_link_dark">
                                <?= 'Создать' ?>
                            </button>
                            <a href="/articles" class="btn btn-lg btn-outline-dark btn_link_dark">
                                К списку статей
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
