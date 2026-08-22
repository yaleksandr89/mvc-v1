<?php
declare(strict_types=1);

use App\Helper\SecurityHelper;

/**
 * @var string $h1
 * @var string $desc
 * @var string $nameMethod
 * @var array{
 *     telegram: array{label: string, url: string},
 *     vk: array{label: string, url: string},
 *     email: array{label: string, url: string},
 *     linkedin: array{label: string, url: string},
 *     github: array{label: string, url: string}
 * } $contacts
 */
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
            <?php foreach ($contacts as $modifier => $contact): ?>
                <a
                    class="btn link_dark <?= SecurityHelper::escapeHtml($modifier) ?> mb-2"
                    href="<?= SecurityHelper::escapeHtml($contact['url']) ?>"
                    target="_blank"
                    rel="nofollow noopener"
                >
                    <?= SecurityHelper::escapeHtml($contact['label']) ?><span class="blink">_</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
