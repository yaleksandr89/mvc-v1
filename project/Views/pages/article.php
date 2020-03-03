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
            <div class="card-body">
                <small class="form-text text-muted">
                    Опубликовано: <?= $article['published_at'] ?>
                </small>
                <small class="form-text text-muted">
                    Обновленно: <?= $article['updated_at'] ?>
                </small>
                <h5 class="card-title"><?= $article['title'] ?></h5>
                <p class="card-text"><?= $article['content_html'] ?></p>
            </div>
        </div>
    </div>
</div>