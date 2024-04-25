<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= $h1 ?></h1>
    <span><?= $desc ?></span>
</div>
<div class="card">
    <div class="card-header">
        <div class="mt-auto d-flex justify-content-between align-items-center h-100">
            <span>Используемый контроллер: <code><?= $nameMethod ?></code></span>
            <a href="/articles/create" class="btn btn-lg btn-outline-dark">
                Создать статью
            </a>
        </div>
    </div>
    <div class="card-body pl-5 pr-5">
        <?php if (count($articles) > 0): ?>
            <?php foreach ($articles as $article): ?>
                <div class="card mb-3">
                    <div class="card-body d-flex flex-column">
                        <h2 class="card-title"><?= $article['title'] ?></h2>
                        <p class="card-text"><?= $article['excerpt'] ?></p>
                        <div class="mt-auto d-flex justify-content-between">
                            <div>
                                <a href="/articles/<?= $article['id'] ?>/show" class="btn btn-lg btn-outline-dark">
                                    Открыть
                                </a>
                                <a href="/articles/<?= $article['id'] ?>/edit" class="btn btn-lg btn-outline-dark">
                                    Редактировать
                                </a>
                                <a href="/articles/<?= $article['id'] ?>/delete" class="btn btn-lg btn-outline-danger">
                                    Удалить
                                </a>
                            </div>
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
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning m-0" role="alert">
                <strong>Статьи не найдены!</strong><br/>
                Создайте статью через форму на сайте либо загрузить дамп, который находиться в директории: <code>docs/mysql-dump </code>
            </div>
        <?php endif; ?>
    </div>
</div>