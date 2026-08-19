<?php
declare(strict_types=1);

use App\Helper\SecurityHelper;

$csrfToken = SecurityHelper::csrfToken($_SESSION);
?>
<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= SecurityHelper::escapeHtml($h1) ?></h1>
    <span><?= SecurityHelper::escapeHtml($desc) ?></span>
</div>
<div class="card">
    <div class="card-header">
        <div class="mt-auto d-flex justify-content-between align-items-center h-100">
            <span>Используемый контроллер: <code><?= SecurityHelper::escapeHtml($nameMethod) ?></code></span>
            <a href="/articles/create" class="btn btn-lg btn-outline-dark btn_link_dark">
                Создать статью
            </a>
        </div>
    </div>
    <div class="card-body pl-5 pr-5">
        <?php if (count($articles) > 0): ?>
            <?php foreach ($articles as $article): ?>
                <div class="card mb-3">
                    <div class="card-body d-flex flex-column">
                        <h2 class="card-title"><?= SecurityHelper::escapeHtml($article['title']) ?></h2>
                        <p class="card-text"><?= SecurityHelper::escapeHtml($article['excerpt']) ?></p>
                        <div class="mt-auto d-flex justify-content-between">
                            <div>
                                <a href="/articles/<?= (int)$article['id'] ?>/show" class="btn btn-lg btn-outline-dark btn_link_dark">
                                    Открыть
                                </a>
                                <a href="/articles/<?= (int)$article['id'] ?>/edit" class="btn btn-lg btn-outline-dark btn_link_dark">
                                    Редактировать
                                </a>
                                <form action="/articles/<?= (int)$article['id'] ?>/delete" method="POST" class="d-inline">
                                    <input
                                            type="hidden"
                                            name="_csrf"
                                            value="<?= SecurityHelper::escapeHtml($csrfToken) ?>"
                                    >
                                    <button type="submit" class="btn btn-lg btn-outline-danger">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                            <div class="text-end d-flex flex-column">
                                <small class="form-text text-muted">
                                    Опубликовано: <?= SecurityHelper::escapeHtml($article['published_at']) ?>
                                </small>
                                <small class="form-text text-muted">
                                    Обновлено: <?= SecurityHelper::escapeHtml($article['updated_at']) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?= $paginator ?>

        <?php else: ?>
            <div class="alert alert-warning m-0" role="alert">
                <strong>Статьи не найдены!</strong><br/>
                Создайте статью через форму на сайте либо загрузить дамп, который находиться в директории: <code>docs/mysql-dump </code>
            </div>
        <?php endif; ?>
    </div>
</div>
