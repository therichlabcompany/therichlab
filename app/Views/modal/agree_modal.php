<!-- MFC007_L01 · 이용약관 — 중앙 팝업만 노출  is-open -->
<div class="c-modal terms" role="dialog" aria-modal="true" id = "agree_popup">
    <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title"><?= esc($agreementTerms['title'] ?? '이용약관') ?></h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <?php $termsContent = trim((string) ($agreementTerms['content'] ?? '')); ?>
            <?php if ($termsContent !== ''): ?>
                <div class="modal-policy-text"><?= nl2br(esc($termsContent)) ?></div>
            <?php else: ?>
                <p class="modal-text">등록된 이용약관이 없습니다.</p>
            <?php endif; ?>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-close>확인</button>
        </div>
    </div>
</div>
