<div id="popup-language" class="c-modal" data-popup-multiselect>
    <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title">상담 가능한 언어</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <ul class="c-modal-list">
                <?php foreach (fc_language_options() as $option): ?>
                    <li><button type="button" class="c-modal-option" data-value="<?= esc($option['value']) ?>"><span class="c-modal-option-label"><?= esc($option['label']) ?></span><span class="c-modal-option-ico"></span></button></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-confirm>확인</button>
        </div>
    </div>
</div>
