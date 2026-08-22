<?php
declare(strict_types=1);

use App\Helper\SecurityHelper;
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
        <div class="text-center mt-3 mb-3">
            <a class="btn link_dark telegram mb-2" href="<?= SecurityHelper::escapeHtml($contacts['telegram']) ?>" target="_blank"
               rel="nofollow noopener">
                <?= SecurityHelper::escapeHtml($contactLabels['telegram']) ?><span class="blink">_</span>
            </a>
            <a class="btn link_dark vk mb-2" href="<?= SecurityHelper::escapeHtml($contacts['vkontakte']) ?>"
               target="_blank" rel="nofollow noopener">
                <?= SecurityHelper::escapeHtml($contactLabels['vkontakte']) ?><span class="blink">_</span>
            </a>
            <a class="btn link_dark email mb-2"
               href="<?= SecurityHelper::escapeHtml($contacts['email']) ?>"
               target="_blank" rel="nofollow noopener">
                <?= SecurityHelper::escapeHtml($contactLabels['email']) ?><span class="blink">_</span>
            </a>
            <a class="btn link_dark linkedin mb-2" href="<?= SecurityHelper::escapeHtml($contacts['linkedin']) ?>"
               target="_blank" rel="nofollow noopener">
                <?= SecurityHelper::escapeHtml($contactLabels['linkedin']) ?><span class="blink">_</span>
            </a>
            <a class="btn link_dark github mb-2" href="<?= SecurityHelper::escapeHtml($contacts['github']) ?>"
               target="_blank" rel="nofollow noopener">
                <?= SecurityHelper::escapeHtml($contactLabels['github']) ?><span class="blink">_</span>
            </a>
        </div>
    </div>
</div>
