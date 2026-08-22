<?php
declare(strict_types=1);

use App\Helper\SecurityHelper;
?>
<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= SecurityHelper::escapeHtml($article['title']) ?></h1>
    <span><?= SecurityHelper::escapeHtml($desc) ?></span>
</div>
<div class="card">
    <div class="card-header">
        <span>Используемый контроллер: <code><?= SecurityHelper::escapeHtml($nameMethod) ?></code></span>
    </div>
    <div class="card-body px-3 px-md-5">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <p class="card-text"><?= nl2br(SecurityHelper::escapeHtml($article['content_html'])) ?></p>
                <div class="mt-auto d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="/articles/<?= (int)$article['id'] ?>/edit" class="btn btn-lg btn-outline-dark">
                            Редактировать
                        </a>
                        <a href="/articles" class="btn btn-lg btn-outline-dark">
                            К списку статей
                        </a>
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
    </div>
</div>
