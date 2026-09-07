<?php
$rejectReason = $review['reject_reason'] ?? '';
$rejectDate   = $review['updated_at'] ?? $review['created_at'] ?? '';
$isReject     = !empty($review) && $review['status'] === 'REJECT';
?>
<main>
    <div class="page-inner-narrow consult-request">
        <h1 class="page-main-title">심의필 관련 정보를 제출해주세요.</h1>
        <p class="page-main-lead">
            심의필 번호와 보험계약 체결 전 주의사항을<br class="br-mo" />
            입력한 후 ‘승인요청하기’를 누르면 관리자가 검토하여<br class="br-mo" />
            회원가입이 최종 완료됩니다.
        </p>
        <!-- <div class="link-end">
            <a href="MFC005_L01_02_L02.html" class="btn btn-line btn-sm">승인 거부 사유 보기</a>
        </div> -->

        <?php if (($review['status'] ?? '') === 'REJECT'): ?>
    <div class="link-end">
        <button type="button"
                class="btn btn-line btn-sm js-open-reject-modal">
            승인 거부 사유 보기
        </button>
    </div>
<?php endif; ?>
        <form class="form-box" id="fc-reviewed-form" method="post" enctype="multipart/form-data">
            <div class="form-field">
                <span class="form-label">심의필 번호 입력 <b>*</b></span>
                <p class="form-text">심의필 증빙 회신문 번호를 입력해주세요.</p>
                <input
                    class="form-input"
                    name="deliberation_no"
                    type="text"
                    inputmode="numeric"
                    maxlength="20"
                    autocomplete="off"
                    placeholder="202503002"
                    title="숫자만 입력 가능합니다."
                    value="<?= esc($review['deliberation_no'] ?? '') ?>" />
            </div>

            <div class="form-field">
                <span class="form-label">심의필 승인 기간 <b>*</b></span>
                <p class="form-text">시작일·종료일을 선택하면 입력란에 반영됩니다.</p>
                <div class="form-inline">
                    <div class="consult-date">
                        <input
                            class="form-input"
                            type="text"
                            name="approval_start"
                            placeholder="시작일 선택"
                            readonly
                            autocomplete="off"
                            value="<?= esc($review['approval_start'] ?? '') ?>" />
                        <div class="consult-date-picker" hidden>
                            <div class="consult-date-picker-head">
                                <button type="button" class="consult-date-picker-nav prev" data-date-nav="prev" aria-label="이전 달"></button>
                                <div class="consult-date-picker-select">
                                    <select class="consult-year"></select>
                                    <select class="consult-month"></select>
                                </div>
                                <button type="button" class="consult-date-picker-nav next" data-date-nav="next" aria-label="다음 달"></button>
                            </div>
                            <ol class="consult-date-picker-week">
                                <li>일</li>
                                <li>월</li>
                                <li>화</li>
                                <li>수</li>
                                <li>목</li>
                                <li>금</li>
                                <li>토</li>
                            </ol>
                            <div class="consult-date-picker-days"></div>
                        </div>
                    </div>
                    <div class="consult-date">
                        <input
                            class="form-input"
                            type="text"
                            name="approval_end"
                            placeholder="종료일 선택"
                            readonly
                            autocomplete="off"
                            value="<?= esc($review['approval_end'] ?? '') ?>" />
                        <div class="consult-date-picker" hidden>
                            <div class="consult-date-picker-head">
                                <button type="button" class="consult-date-picker-nav prev" data-date-nav="prev" aria-label="이전 달"></button>
                                <div class="consult-date-picker-select">
                                    <select class="consult-year"></select>
                                    <select class="consult-month"></select>
                                </div>
                                <button type="button" class="consult-date-picker-nav next" data-date-nav="next" aria-label="다음 달"></button>
                            </div>
                            <ol class="consult-date-picker-week">
                                <li>일</li>
                                <li>월</li>
                                <li>화</li>
                                <li>수</li>
                                <li>목</li>
                                <li>금</li>
                                <li>토</li>
                            </ol>
                            <div class="consult-date-picker-days"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-field">
                <span class="form-label">적격(조건부 승인) 심의의견</span>
                <p class="form-text">심의 관련 안내문을 입력해 주세요.</p>
                <textarea
                    class="form-textarea"
                    name="deliberation_opinion"
                    maxlength="1000"
                    placeholder=""><?= esc($review['deliberation_opinion'] ?? '') ?></textarea>
            </div>

            <div class="file-upload" data-deliberation-upload>
                <span class="form-label">심의결과 회신문 파일 업로드 <b>*</b></span>
                <div class="file-upload-rows" data-upload-rows>
                    <div data-upload-row>
                        <input
                            class="form-input"
                            type="text"
                            readonly
                            placeholder="파일을 선택해 주세요"
                            value="<?= esc($review['deliberation_file'] ?? '') ?>" />
                        <input
                            id="deliberation-file"
                            class="visually-hidden"
                            name="deliberation_file"
                            type="file"
                            tabindex="-1"
                            accept="
                            image/*,
                            application/pdf,
                            application/msword,
                            application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                            application/vnd.ms-excel,
                            application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,
                            .hwp,
                            .jpg,.jpeg,.png,.webp,.gif
                            "/>
                        <label for="deliberation-file" class="file-upload-file-trigger">파일찾기</label>
                    </div>
                </div>
            </div>
            <div class="form-field">
                <span class="form-label">심의필 제출 안내</span>
                <ul class="dash-list">
                    <li>심의필 번호 제출 후 운영관리자가 검토하여 승인 여부를 안내드립니다.</li>
                    <li>승인 처리까지 업무일기준 평균 2~3일 소요될 수 있습니다.</li>
                    <li>승인 전까지는 프로필 정보 수정이 제한되며, 승인 후 프로필 관리에서 수정 가능합니다.</li>
                    <li>제출하신 심의필 번호에 오류가 있을 경우 승인 요청이 거절될 수 있습니다.</li>
                    <li>승인 완료 시, 프로필이 고객에게 노출됩니다.</li>
                </ul>
            </div>
            <div class="form-actions">
                <!-- <button
                type="button"
                class="btn btn-primary"
                >

                <?= empty($review) ? '승인요청하기' : '수정하기' ?> -->

                <button
                    type="submit"
                    class="btn btn-primary">

                    <?= empty($review) ? '승인요청하기' : '수정하기' ?>

                </button>
            </div>
        </form>
    </div>
</main>

<div class="c-modal sm" id="reviewed-confirm-modal" role="dialog" aria-modal="true" aria-label="심의필 정보 등록 확인" hidden>
    <button type="button" class="c-modal-backdrop" data-reviewed-confirm-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <button type="button" class="c-modal-close" data-reviewed-confirm-close aria-label="닫기"></button>
        <div class="c-modal-body">
            <p class="modal-text">
                등록된 프로필 및 소개 자료와 제출한 심의필 번호 및 보험계약 체결 전 주의사항이 사실과 다를 경우 등의 모든 법적 책임(민원, 분쟁, 준법 위반 등)은 전적으로 FC 본인에게 있습니다.
            </p>
        </div>
        <div class="c-modal-foot">
            <div class="c-modal-btns">
                <button type="button" class="btn btn-primary" data-reviewed-confirm>승인 요청 완료</button>
                <button type="button" class="btn btn-sub" data-reviewed-confirm-close>닫기</button>
            </div>
        </div>
    </div>
</div>

<?php if ($isReject): ?>
<div class="c-modal sm is-open" role="dialog" aria-modal="true" id="reject-modal">

    <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>

    <div class="c-modal-panel">

        <div class="c-modal-head">
            <h2 class="c-modal-title">승인 거부 사유</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>

        <div class="c-modal-body">

            <p class="c-modal-meta">
                <time><?= esc($rejectDate) ?></time>
            </p>

            <textarea class="form-textarea" name="reject_reason" readonly rows="6"><?= esc($rejectReason) ?></textarea>

        </div>

        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-close>닫기</button>
        </div>

    </div>
</div>
<?php endif; ?>


<script>
    
(function () {

    const status = "<?= $review['status'] ?? '' ?>";
    const rejectReason = <?= json_encode($review['reject_reason'] ?? '') ?>;
    const rejectAt = <?= json_encode($review['updated_at'] ?? '') ?>;

    const modal = document.getElementById('reject-modal');
    const body = document.body;

    if (!modal) return;

    function openModal() {

        const timeEl = modal.querySelector('time');
        const reasonEl = modal.querySelector('textarea');

        if (timeEl) timeEl.textContent = rejectAt || '';
        if (reasonEl) reasonEl.value = rejectReason || '거부 사유가 없습니다.';

        modal.classList.add('is-open');
        body.classList.add('popup-open');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        body.classList.remove('popup-open');
    }

    // =========================
    // 버튼 클릭
    // =========================
    document.addEventListener('click', function (e) {

        if (e.target.closest('.js-open-reject-modal')) {
            openModal();
        }

        if (e.target.closest('[data-popup-close]')) {
            closeModal();
        }
    });

    // =========================
    // REJECT 자동 오픈
    // =========================
    window.addEventListener('load', function () {
        if (status === 'REJECT') {
            openModal();
        }
    });

})();

    (function() {

        const form = document.getElementById('fc-reviewed-form');
        if (!form) return;

        let selectedFile = null;

        const fileInput = form.querySelector('input[name="deliberation_file"]');
        const fileDisplay = form.querySelector('[data-upload-row] input[readonly]');
        const confirmModal = document.getElementById('reviewed-confirm-modal');
        const confirmButton = confirmModal.querySelector('[data-reviewed-confirm]');
        let pendingFormData = null;

        function openConfirmModal() {
            confirmModal.hidden = false;
            confirmModal.classList.add('is-open');
            document.body.classList.add('popup-open');
        }

        function closeConfirmModal() {
            confirmModal.classList.remove('is-open');
            confirmModal.hidden = true;
            document.body.classList.remove('popup-open');
        }

        // =========================
        // 파일 선택
        // =========================

        fileInput.addEventListener('change', function() {

            selectedFile = this.files[0] || null;

            if (!selectedFile) {
                return;
            }

            const ext = selectedFile.name.split('.').pop().toLowerCase();

            const allow = [
                'doc',
                'docx',
                'xls',
                'xlsx',
                'hwp'
            ];

            if (!allow.includes(ext)) {

                alert('업로드 가능한 파일 형식이 아닙니다.');

                this.value = '';
                selectedFile = null;

                return;
            }

            fileDisplay.value = selectedFile.name;

        });

        // =========================
        // submit validation
        // =========================

        form.addEventListener('submit', (e) => {

            e.preventDefault();

            const deliberationNo = form.deliberation_no.value.trim();
            const approvalStart = form.approval_start.value.trim();
            const approvalEnd = form.approval_end.value.trim();
            const deliberationOpinion = form.deliberation_opinion.value.trim();

            // 수정 화면에서 기존 파일명
            const oldFile = fileDisplay.value.trim();

            if (!deliberationNo) {

                alert('심의필 번호를 입력해주세요.');
                form.deliberation_no.focus();
                return;

            }

            if (!/^[0-9]+$/.test(deliberationNo)) {

                alert('심의필 번호는 숫자만 입력 가능합니다.');
                form.deliberation_no.focus();
                return;

            }

            if (!approvalStart) {

                alert('심의 승인 시작일을 선택해주세요.');
                return;

            }

            if (!approvalEnd) {

                alert('심의 승인 종료일을 선택해주세요.');
                return;

            }

            if (new Date(approvalStart) > new Date(approvalEnd)) {

                alert('종료일은 시작일 이후여야 합니다.');
                return;

            }

            // 신규등록이면 파일 필수
            if (!oldFile && !selectedFile) {

                alert('심의결과 회신문 파일을 업로드해주세요.');
                return;

            }

            const formData = new FormData();

            formData.append('deliberation_no', deliberationNo);
            formData.append('approval_start', approvalStart);
            formData.append('approval_end', approvalEnd);
            formData.append('deliberation_opinion', deliberationOpinion);

            if (selectedFile) {
                formData.append('deliberation_file', selectedFile);
            }

            pendingFormData = formData;
            openConfirmModal();
        });

        confirmModal.querySelectorAll('[data-reviewed-confirm-close]').forEach((button) => {
            button.addEventListener('click', closeConfirmModal);
        });

        confirmButton.addEventListener('click', async () => {
            if (!pendingFormData) {
                return;
            }

            confirmButton.disabled = true;

            try {

                const res = await fetch('/mypage/ajax_save_reviewed', {

                    method: 'POST',
                    body: pendingFormData

                });

                const result = await res.json();

                if (result.result === 'success') {
                    closeConfirmModal();
                    alert('심의필 정보 <?= empty($review) ? '등록' : '수정' ?>이 완료되었습니다.');
                    location.reload();
                } else {
                    alert(result.msg || '저장 실패');
                }
            } catch (err) {
                console.error(err);
                alert('서버 오류');
            } finally {
                confirmButton.disabled = false;
            }
        });

    })();
</script>
