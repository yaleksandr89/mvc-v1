<div class="alert alert-secondary pt-3 pb-3" role="alert">
    <h1><?= $h1 ?></h1>
    <span><?= $desc ?></span>
</div>
<div class="card">
    <div class="card-header">
        <span>Используемый контроллер: <code><?= $nameMethod ?></code></span>
    </div>
    <div class="card-body pl-5 pr-5">
        <div class="text-center mt-5 mb-5">
            <a class="btn link_dark telegram mb-2" href="<?= $contacts['telegram'] ?>" target="_blank"
               rel="nofollow noopener">
                Telegram<span class="blink">_</span>
            </a>
            <a class="btn link_dark skype mb-2" href="<?= $contacts['skype'] ?>">
                Skype<span class="blink">_</span>
            </a>
            <a class="btn link_dark vk mb-2" href="<?= $contacts['vkontakte'] ?>"
               target="_blank" rel="nofollow noopener">
                Vkontakte<span class="blink">_</span>
            </a>
            <a class="btn link_dark email mb-2"
               href="<?= $contacts['email'] ?>!"
               target="_blank" rel="nofollow noopener">
                Email<span class="blink">_</span>
            </a>
            <a class="btn link_dark linkedin mb-2" href="<?= $contacts['linkedin'] ?>"
               target="_blank" rel="nofollow noopener">
                Linkedin<span class="blink">_</span>
            </a>
            <a class="btn link_dark github mb-2" href="<?= $contacts['github'] ?>"
               target="_blank" rel="nofollow noopener">
                Github<span class="blink">_</span>
            </a>
        </div>
    </div>
</div>