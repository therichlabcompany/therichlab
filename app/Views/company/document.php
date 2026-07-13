<?php
$title = (string) ($title ?? '');
$content = trim((string) ($content ?? ''));
$lines = $content !== '' ? preg_split('/\R/u', $content) : [];
?>
<main>
    <div class="page-inner">
        <h1 class="page-main-title"><?= esc($title) ?></h1>

        <article class="terms-body">
            <?php if (!$lines): ?>
                <p>등록된 내용이 없습니다.</p>
            <?php else: ?>
                <?php foreach ($lines as $line): ?>
                    <?php $line = trim($line); ?>
                    <?php if ($line === ''): ?>
                        <div class="terms-spacer" aria-hidden="true"></div>
                    <?php elseif (preg_match('/^(\[.+\]|제\d+조|\d+\.)/u', $line) === 1): ?>
                        <h3><?= esc($line) ?></h3>
                    <?php elseif (str_starts_with($line, '- ')): ?>
                        <p class="terms-list-item"><?= esc($line) ?></p>
                    <?php else: ?>
                        <p><?= esc($line) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </article>
    </div>
</main>
