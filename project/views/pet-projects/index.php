<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= $h1 ?></h1>
    <span><?= $desc ?></span>
</div>
<div class="card">
    <div class="card-header">
        <span>Используемый контроллер: <code><?= $nameMethod ?></code></span>
    </div>
    <div class="card-body pl-5 pr-5">
        <ul class="list-group list-group-flush">
            <li class="list-group-item" data-id="property-container">
                <p>
                    Сервис по сокращению ссылок. Оригинальная и сокращённая ссылка хранится в БД, реализована
                    простая админка (вывод всех сокращённых ссылок).
                </p>
                <a class="btn link_dark w-100 pets" href="<?= $data['data']['url-shortener'] ?>"
                   target="_blank" rel="nofollow noopener">
                    <span>URL Shortener</span><span class="blink">_</span>
                </a>
            </li>
            <li class="list-group-item" data-id="delegation">
                <p>
                    Реализована возможность отсылать различные шаблоны писем. Обработчик использует библиотеку
                    PHPMailer (отправка происходит через Яндекс), также я сверстал 3 адаптивных шаблона
                    (Foundation for email 2).
                </p>
                <p>
                    <a class="btn link_dark w-100 pets" href="<?= $data['data']['phpmailer'] ?>"
                       target="_blank" rel="nofollow noopener">
                        <span>PHPMailer + адаптивные шаблоны</span><span class="blink">_</span>
                    </a>
                </p>


            </li>
            <li class="list-group-item" data-id="event-channel">
                <p>
                    Скрипт зеркально отражает изображение по вертикали. На сервере сохраняется оригинал и обработанное
                    ихображение.
                </p>
                <a class="btn link_dark w-100 pets" href="<?= $data['data']['img-mirroring'] ?>"
                   target="_blank" rel="nofollow noopener">
                    <span>Отзеркаливание изображения</span><span class="blink">_</span>
                </a>
            </li>
            <li class="list-group-item" data-id="interface-pattern">
                <p>
                    Плагин вывода календаря с возможностью устанавливаться события. Реализован для CMS WordPress.
                </p>
                <a class="btn link_dark w-100 mb-3 pets" href="<?= $data['data']['caltoeve-github'] ?>"
                   target="_blank" rel="nofollow noopener">
                    <span>Calendar-To-Events (Ссылка на github)</span><span class="blink">_</span>
                </a>
                <a class="btn link_dark w-100 pets" href="<?= $data['data']['caltoeve-plugin'] ?>"
                   target="_blank" rel="nofollow noopener">
                    <span>Calendar-To-Events (Ссылка на WordPress)</span><span class="blink">_</span>
                </a>
            </li>
        </ul>
    </div>
</div>