<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="apple-touch-icon" sizes="180x180" href="<?= PROJECT_IMG ?>/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= PROJECT_IMG ?>/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= PROJECT_IMG ?>/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?= PROJECT_IMG ?>/favicon/site.webmanifest">
    <link rel="mask-icon" href="<?= PROJECT_IMG ?>/favicon/safari-pinned-tab.svg" color="#ed6325">
    <link rel="shortcut icon" href="<?= PROJECT_IMG ?>/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="<?= PROJECT_IMG ?>/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" href="<?= PROJECT_CSS ?>/bootstrap.css">
    <link rel="stylesheet" href="<?= PROJECT_CSS ?>/user.css">

    <title><?= $title ?></title>
    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="<?= $keywords ?>">
</head>
<body>
<div class="wrapper">
    <header class="bg-dark mb-3">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <nav class="navbar navbar-expand-lg navbar-dark">
                        <a class="navbar-brand" href="/">
                            <img src="<?= PROJECT_IMG ?>/logotype-white" alt="Логотип">
                        </a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse"
                                data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                                aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item" data-id="articles">
                                    <a class="nav-link" href="/articles">Статьи</a>
                                </li>
                                <li class="nav-item" data-id="pet-projects">
                                    <a class="nav-link" href="/pet-projects">Выполненные пет-проекты</a>
                                </li>
                                <li class="nav-item" data-id="contacts">
                                    <a class="nav-link" href="/contacts">Контакты</a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <main>
                        <?= $content ?>
                    </main>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer mt-3">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <footer class="footer">
                        <p class="text-light m-0"><?= date('Y')?> | Тестовый сайт</p>
                    </footer>
                </div>
            </div>
        </div>
    </footer>
</div>
<script src="<?= PROJECT_JS ?>/jquery.js"></script>
<script src="<?= PROJECT_JS ?>/popper.js"></script>
<script src="<?= PROJECT_JS ?>/bootstrap.js"></script>
<script src="<?= PROJECT_JS ?>/user.js"></script>
</body>
</html>