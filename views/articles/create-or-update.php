<?php
declare(strict_types=1);

use App\Helper\SecurityHelper;

/**
 * @var string $h1
 * @var string $desc
 * @var string $nameMethod
 * @var 'create'|'edit' $type
 * @var string $formAction
 * @var string|null $deleteAction
 * @var string $csrfToken
 * @var string $title
 * @var string $excerpt
 * @var string $contentHtml
 * @var list<string> $errorsTitle
 * @var list<string> $errorsExcerpt
 * @var list<string> $errorsContentHtml
 * @var ''|' is-invalid' $titleIsInvalid
 * @var ''|' is-invalid' $excerptIsInvalid
 * @var ''|' is-invalid' $contentHtmlIsInvalid
 */
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
                        action="<?= SecurityHelper::escapeHtml($deleteAction) ?>"
                        method="POST"
                    >
                        <input
                            type="hidden"
                            name="_csrf"
                            value="<?= SecurityHelper::escapeHtml($csrfToken) ?>"
                        >
                    </form>
                    <form action="<?= SecurityHelper::escapeHtml($formAction) ?>" method="POST">
                <?php else: ?>
                    <form action="<?= SecurityHelper::escapeHtml($formAction) ?>" method="POST">
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
                                    Создать
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
