<div class="c-modal sm" id="fc-profile-update-confirm-modal" role="dialog" aria-modal="true" aria-label="프로필 수정 확인" hidden>
    <button type="button" class="c-modal-backdrop" data-fc-update-cancel aria-label="닫기"></button>
    <div class="c-modal-panel">
        <button type="button" class="c-modal-close" data-fc-update-cancel aria-label="닫기"></button>
        <div class="c-modal-body">
            <p class="modal-text"><strong>입력하신 내용은 관리자 승인 없이<br />즉시 프로필에 반영됩니다.</strong></p>
            <br />
            <p class="form-text">수정된 프로필 및 소개 자료와 제출한 심의필 번호 및 보험계약 체결 전 주의 사항이 사실과 다를 경우 등의 모든 법적 책임(민원, 분쟁, 준법 위반 등)은 전적으로 FC 본인에게 있습니다.</p>
        </div>
        <div class="c-modal-foot"><div class="c-modal-btns"><button type="button" class="btn btn-primary" data-fc-update-confirm>수정 확인</button><button type="button" class="btn btn-sub" data-fc-update-cancel>닫기</button></div></div>
    </div>
</div>

<div class="c-modal sm fc-profile-update-complete" id="fc-profile-update-complete-modal" role="dialog" aria-modal="true" aria-label="프로필 정보 수정 완료" hidden>
    <button type="button" class="c-modal-backdrop" data-fc-update-complete-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <button type="button" class="c-modal-close" data-fc-update-complete-close aria-label="닫기"></button>
        <div class="c-modal-body">
            <p class="modal-text"><strong>프로필 정보 수정이 완료 되었습니다.<br /><br />프로필 정보 수정 시<br />재심의 받으셔야 됩니다.</strong></p>
        </div>
        <div class="c-modal-foot"><div class="c-modal-btns"><button type="button" class="btn btn-primary" data-fc-deliberation-preview>심의필 요청용 화면 미리보기</button><a href="<?= base_url('mypage/fcreviewed') ?>" class="btn btn-sub">심의필 정보 관리 바로가기</a></div></div>
    </div>
</div>

<script>
window.MyFCProfileUpdateModal = (function () {
    const confirmModal = document.getElementById('fc-profile-update-confirm-modal');
    const completeModal = document.getElementById('fc-profile-update-complete-modal');

    function open(modal) {
        modal.hidden = false;
        modal.classList.add('is-open');
        document.body.classList.add('popup-open');
    }

    function close(modal) {
        modal.classList.remove('is-open');
        modal.hidden = true;
        document.body.classList.remove('popup-open');
    }

    completeModal.querySelectorAll('[data-fc-update-complete-close]').forEach(function (button) {
        button.addEventListener('click', function () { close(completeModal); });
    });

    return {
        confirm: function () {
            return new Promise(function (resolve) {
                open(confirmModal);
                const finish = function (approved) {
                    close(confirmModal);
                    resolve(approved);
                };
                confirmModal.querySelector('[data-fc-update-confirm]').addEventListener('click', function () { finish(true); }, { once: true });
                confirmModal.querySelectorAll('[data-fc-update-cancel]').forEach(function (button) {
                    button.addEventListener('click', function () { finish(false); }, { once: true });
                });
            });
        },
        showComplete: function () { open(completeModal); }
    };
})();
</script>
<?php include APPPATH . 'Views/modal/fc_deliberation_preview_modal.php'; ?>
