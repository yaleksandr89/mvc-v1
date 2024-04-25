<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= $article['title'] ?></h1>
    <span><?= $desc ?></span>
</div>
<div class="card">
    <div class="card-header">
        <span>Используемый контроллер: <code><?= $nameMethod ?></code></span>
    </div>
    <div class="card-body pl-5 pr-5">
        <div class="card mb-3">
            <div class="card-body d-flex flex-column">
                <p class="card-text"><?= $article['content_html'] ?></p>
                <div class="mt-auto d-flex justify-content-between">
                    <a href="/articles" class="btn btn-lg btn-outline-dark">
                        Назад
                    </a>
                    <div class="text-end d-flex flex-column">
                        <small class="form-text text-muted">
                            Опубликовано: <?= $article['published_at'] ?>
                        </small>
                        <small class="form-text text-muted">
                            Обновлено: <?= $article['updated_at'] ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>