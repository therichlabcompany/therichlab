<!-- is-open -->
<div class="c-modal terms " role="dialog" aria-modal="true" id = "privacy_popup">
    <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title"><?= esc($agreementPrivacy['title'] ?? '개인정보 수집 및 이용') ?></h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <?php $privacyContent = trim((string) ($agreementPrivacy['content'] ?? '')); ?>
            <?php if ($privacyContent !== ''): ?>
                <div class="modal-policy-text"><?= nl2br(esc($privacyContent)) ?></div>
            <?php else: ?>
                <p class="modal-text">등록된 개인정보처리방침이 없습니다.</p>
            <?php endif; ?>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-close>확인</button>
        </div>
    </div>
</div>
