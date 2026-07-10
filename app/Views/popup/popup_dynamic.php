<?php
$popupList = $popupList ?? [];

if (empty($popupList)) {
    return;
}
?>

<style>
    .dynamic-popup-modal {
        display: none;
    }

    .dynamic-popup-modal.is-open {
        display: flex;
    }

    .dynamic-popup-panel {
        width: min(520px, calc(100vw - 32px));
        max-height: calc(100vh - 32px);
        overflow: hidden;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.24);
    }

    .dynamic-popup-media img {
        display: block;
        width: 100%;
        height: auto;
    }

    .dynamic-popup-foot {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 16px 16px;
    }
</style>

<?php foreach ($popupList as $index => $popup): ?>
    <?php
    $popupId = (int) ($popup['popup_id'] ?? 0);
    $popupTitle = trim((string) ($popup['title'] ?? ''));
    $popupKey = 'myfc_popup_hidden_' . $popupId;
    $imageUrl = !empty($popup['image_path']) ? base_url(ltrim((string) $popup['image_path'], '/')) : '';
    $linkUrl = trim((string) ($popup['link_url'] ?? ''));
    $linkTarget = (($popup['link_target'] ?? '') === '_blank') ? '_blank' : '_self';
    ?>
    <div
        id="dynamic-popup-<?= $popupId ?>"
        class="c-modal dynamic-popup-modal js-dynamic-popup"
        role="dialog"
        aria-modal="true"
        aria-labelledby="dynamic-popup-title-<?= $popupId ?>"
        data-popup-key="<?= esc($popupKey) ?>"
        data-popup-index="<?= (int) $index ?>"
    >
        <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>
        <div class="c-modal-panel dynamic-popup-panel">
            <div class="c-modal-head">
                <h2 class="c-modal-title" id="dynamic-popup-title-<?= $popupId ?>"><?= esc($popupTitle) ?></h2>
                <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
            </div>
            <div class="c-modal-body p-0">
                <div class="dynamic-popup-media">
                    <?php if ($imageUrl !== ''): ?>
                        <?php if ($linkUrl !== ''): ?>
                            <a href="<?= esc($linkUrl) ?>" target="<?= esc($linkTarget) ?>" rel="noopener">
                                <img src="<?= esc($imageUrl) ?>" alt="<?= esc($popupTitle) ?>">
                            </a>
                        <?php else: ?>
                            <img src="<?= esc($imageUrl) ?>" alt="<?= esc($popupTitle) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dynamic-popup-foot">
                <button type="button" class="btn btn-outline-secondary btn-sm js-popup-hide">오늘 그만보기</button>
                <button type="button" class="btn btn-line btn-sm" data-popup-close>닫기</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
(function () {
    const modals = Array.from(document.querySelectorAll('.js-dynamic-popup'));
    if (!modals.length) {
        return;
    }

    const body = document.body;
    const now = new Date();
    const today = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
    let currentIndex = -1;

    function isHidden(modal) {
        const key = modal.dataset.popupKey;
        try {
            return localStorage.getItem(key) === today;
        } catch (error) {
            return false;
        }
    }

    function openNext(fromIndex) {
        for (let i = fromIndex; i < modals.length; i++) {
            if (isHidden(modals[i])) {
                continue;
            }

            modals[i].classList.add('is-open');
            body.classList.add('popup-open');
            currentIndex = i;
            return;
        }

        currentIndex = -1;
        body.classList.remove('popup-open');
    }

    function closeCurrent(markHidden) {
        if (currentIndex < 0 || currentIndex >= modals.length) {
            return;
        }

        const modal = modals[currentIndex];
        if (markHidden) {
            try {
                localStorage.setItem(modal.dataset.popupKey, today);
            } catch (error) {
                // ignore
            }
        }

        modal.classList.remove('is-open');
        openNext(currentIndex + 1);
    }

    modals.forEach(function (modal) {
        modal.querySelectorAll('[data-popup-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                closeCurrent(false);
            });
        });

        const hideButton = modal.querySelector('.js-popup-hide');
        if (hideButton) {
            hideButton.addEventListener('click', function () {
                closeCurrent(true);
            });
        }
    });

    openNext(0);
})();
</script>
