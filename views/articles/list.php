<?php
declare(strict_types=1);

use App\Helper\SecurityHelper;

/**
 * @var string $h1
 * @var string $desc
 * @var string $nameMethod
 * @var string $csrfToken
 * @var list<array{
 *     id: int|string,
 *     title: string,
 *     excerpt: string,
 *     content_html: string,
 *     published_at: string,
 *     updated_at: string
 * }> $articles
 * @var \Yaa\Framework\Pagination $paginator
 */
?>
<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= SecurityHelper::escapeHtml($h1) ?></h1>
    <span><?= SecurityHelper::escapeHtml($desc) ?></span>
</div>
<div class="card">
    <div class="card-header">
        <div class="mt-auto d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 h-100">
            <span>Используемый контроллер: <code><?= SecurityHelper::escapeHtml($nameMethod) ?></code></span>
            <a href="/articles/create" class="btn btn-lg btn-outline-dark align-self-md-center">
                Создать статью
            </a>
        </div>
    </div>
    <div class="card-body px-3 px-md-5">
        <?php if (count($articles) > 0): ?>
            <?php foreach ($articles as $article): ?>
                <div class="card mb-3">
                    <div class="card-body d-flex flex-column">
                        <h2 class="card-title"><?= SecurityHelper::escapeHtml($article['title']) ?></h2>
                        <p class="card-text"><?= SecurityHelper::escapeHtml($article['excerpt']) ?></p>
                        <div class="mt-auto d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="/articles/<?= (int)$article['id'] ?>/show" class="btn btn-lg btn-outline-dark">
                                    Открыть
                                </a>
                                <a href="/articles/<?= (int)$article['id'] ?>/edit" class="btn btn-lg btn-outline-dark">
                                    Редактировать
                                </a>
                                <form action="/articles/<?= (int)$article['id'] ?>/delete" method="POST">
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
                            <div class="text-md-end d-flex flex-column">
                                <small class="form-text text-body-secondary">
                                    Опубликовано: <?= SecurityHelper::escapeHtml($article['published_at']) ?>
                                </small>
                                <small class="form-text text-body-secondary">
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
                База данных пуста. Создайте статью через форму на сайте или загрузите демо-статьи из корня проекта командой <code>make demo-data</code>.
            </div>
        <?php endif; ?>
    </div>
</div>
