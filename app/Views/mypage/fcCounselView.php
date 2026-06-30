<main>
    <div class="page-inner consult-request">
        <h1 class="page-main-title">고객 상담 요청 상세</h1>

        <p class="page-main-lead">
            해당 정보는 상담 진행을 위한 용도로만 사용해야 하며<br />
            외부 공유는 금지됩니다.<br />
            상담이 완료되면 반드시 <strong>‘상담 완료’</strong>를 선택해 주세요.
        </p>

        <form class="form-box">

            <div class="consult-request-read">
                <p><span>이름</span><strong><?= esc($counsel['member_name']) ?></strong></p>
                <p><span>이메일</span><strong><?= esc($counsel['member_email']) ?></strong></p>
                <p><span>휴대폰 번호</span><strong><?= esc($counsel['member_phone']) ?></strong></p>
                <p><span>생년월일</span><strong><?= esc($counsel['birth']) ?></strong></p>

                <p><span>성별</span><strong>
                        <?= $counsel['gender'] === 'M' ? '남성' : ($counsel['gender'] === 'F' ? '여성' : '-') ?>
                    </strong></p>

                <p><span>지역</span><strong><?= esc($counsel['region'] ?? '-') ?></strong></p>

                <p>
                    <span>상담 요청 일시</span>
                    <strong><time><?= esc($counsel['created_at']) ?></time></strong>
                </p>
            </div>

            <div class="form-field">
                <label class="form-label">상담 요청 내용</label>
                <textarea class="form-textarea" readonly rows="8"><?= esc($counsel['content']) ?></textarea>
            </div>

            <div class="form-field">
                <span class="form-label">보험 자료 및 증권 첨부</span>

                <ul class="consult-detail-file-list">
                    <?php if (!empty($files)): ?>
                        <?php foreach ($files as $file): ?>
                            <li>
                                <a href="/<?= esc($file['file_path']) ?>" download class="consult-detail-file-link">
                                    <img src="/assets/images/ic-detail-download.svg" alt="" />
                                    <span><?= esc($file['original_name']) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="empty">첨부파일이 없습니다.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-primary" onclick="history.back();">확인</button>

                <div class="form-actions-sub">
                    <button
                        type="button"
                        class="btn btn-sub js-counsel-complete"
                        data-uid="<?= esc($counsel['counsel_uid']) ?>"
                        <?= $counsel['status'] !== 'REQUEST' ? 'disabled' : '' ?>>
                        상담완료하기
                    </button>

                    <button
                        type="button"
                        class="btn btn-sub js-counsel-cancel"
                        data-uid="<?= esc($counsel['counsel_uid']) ?>"
                        <?= $counsel['status'] !== 'REQUEST' ? 'disabled' : '' ?>>
                        상담거부처리
                    </button>
                </div>
            </div>

        </form>
    </div>
</main>

<script>
(function () {
    function updateStatus(uid, status) {
        fetch('/mypage/fccounsel/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                counsel_uid: uid,
                status: status
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.result) {
                alert(res.msg);
                return;
            }

            alert(res.msg);
            location.reload();
        })
        .catch(() => {
            alert('처리 중 오류가 발생했습니다.');
        });
    }

    document.querySelectorAll('.js-counsel-complete').forEach(btn => {
        btn.addEventListener('click', function () {
            updateStatus(this.dataset.uid, 'COMPLETE');
        });
    });

    document.querySelectorAll('.js-counsel-cancel').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('상담을 거부 처리하시겠습니까?')) return;
            updateStatus(this.dataset.uid, 'CANCEL');
        });
    });
})();
</script>