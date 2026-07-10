<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$row = $row ?? [];
$history = $history ?? [];
$statusLabel = $statusLabel ?? '승인 대기';
$decisionUrl = $decisionUrl ?? '#';
$adminName = $adminName ?? 'admin';
?>

<style>
    .deliberation-page {
        color: #172033;
        font-size: 14px;
    }

    .deliberation-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .deliberation-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .deliberation-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .deliberation-card {
        margin-bottom: 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
    }

    .deliberation-card-head {
        padding: 12px 16px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .deliberation-card-body {
        padding: 16px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr);
        gap: 0;
        border-top: 1px solid #d8e0ea;
        border-left: 1px solid #d8e0ea;
    }

    .detail-label,
    .detail-value {
        padding: 12px 14px;
        border-right: 1px solid #d8e0ea;
        border-bottom: 1px solid #d8e0ea;
    }

    .detail-label {
        color: #4b586b;
        background: #f8fafc;
        font-size: 13px;
        font-weight: 800;
    }

    .detail-value {
        word-break: break-word;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: #e2e8f0;
    }

    .status-pill.approve {
        color: #166534;
        background: #dcfce7;
    }

    .status-pill.reject {
        color: #991b1b;
        background: #fee2e2;
    }

    .status-pill.wait {
        color: #92400e;
        background: #fef3c7;
    }

    .history-table td,
    .history-table th {
        vertical-align: middle;
    }

    .review-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1050;
        background: rgba(15, 23, 42, 0.5);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .review-modal.is-open {
        display: flex;
    }

    .review-modal-panel {
        width: min(560px, 100%);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.24);
        overflow: hidden;
    }

    .review-modal-head,
    .review-modal-foot {
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .review-modal-foot {
        border-bottom: 0;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .review-modal-body {
        padding: 16px;
    }

    .radio-row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .reject-box {
        display: none;
    }

    .reject-box.is-open {
        display: block;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header deliberation-header deliberation-page">
        <div>
            <div class="screen-id">AMFC003_01_L02</div>
            <h1>심의필 신청 상세</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; 컨텐츠 관리 &gt; 심의필 신청 관리 &gt; 상세</div>
    </section>

    <section class="content deliberation-page">
        <div class="container-fluid">
            <div class="mb-3 d-flex gap-2 flex-wrap">
                <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDecisionModal()">승인/거부 처리</button>
            </div>

            <div class="deliberation-card">
                <div class="deliberation-card-head">심의필 신청 정보</div>
                <div class="deliberation-card-body">
                    <div class="detail-grid">
                        <div class="detail-label">상담 FC 정보</div>
                        <div class="detail-value">
                            <?php if (!empty($row['member_id'])): ?>
                                <a href="<?= base_url('admin/fc-members/' . (int) $row['member_id']) ?>">
                                    <?= esc(($row['name'] ?? '-') . ' (' . ($row['email'] ?? '-') . ')') ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                        <div class="detail-label">심의필 번호</div>
                        <div class="detail-value"><?= esc($row['deliberation_no'] ?? '-') ?></div>
                        <div class="detail-label">심의필 승인 기간</div>
                        <div class="detail-value"><?= esc(($row['approval_start'] ?? '-') . ' ~ ' . ($row['approval_end'] ?? '-')) ?></div>
                        <div class="detail-label">심의 의견</div>
                        <div class="detail-value"><?= nl2br(esc((string) ($row['deliberation_opinion'] ?? '-'))) ?></div>
                        <div class="detail-label">심의결과 회신문 파일</div>
                        <div class="detail-value">
                            <?php if (!empty($row['deliberation_file'])): ?>
                                <a href="<?= base_url(ltrim($row['deliberation_file'], '/')) ?>" class="btn btn-outline-primary btn-sm" download>
                                    파일 다운로드
                                </a>
                                <span class="ms-2"><?= esc(basename($row['deliberation_file'])) ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                        <div class="detail-label">승인 처리 현황</div>
                        <div class="detail-value">
                            <span class="status-pill <?= strtolower((string) ($row['status'] ?? 'WAIT')) === 'approve' ? 'approve' : (strtolower((string) ($row['status'] ?? 'WAIT')) === 'reject' ? 'reject' : 'wait') ?>">
                                <?= esc($statusLabel) ?>
                            </span>
                            <span class="ms-2 text-muted"><?= esc($row['approve_at'] ?? '-') ?></span>
                        </div>
                        <div class="detail-label">거부 사유</div>
                        <div class="detail-value"><?= nl2br(esc((string) ($row['reject_reason'] ?? '-'))) ?></div>
                    </div>
                </div>
            </div>

            <div class="deliberation-card">
                <div class="deliberation-card-head d-flex justify-content-between align-items-center">
                    <span>승인 처리 히스토리</span>
                    <span class="text-muted"><?= number_format(count($history)) ?>건</span>
                </div>
                <div class="deliberation-card-body table-responsive">
                    <table class="table table-hover history-table align-middle">
                        <thead>
                            <tr>
                                <th>변경일시</th>
                                <th>이전 상태</th>
                                <th>변경 상태</th>
                                <th>거부 사유</th>
                                <th>처리자</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history)): ?>
                                <?php foreach ($history as $item): ?>
                                    <tr>
                                        <td><?= esc($item['changed_at'] ?? '-') ?></td>
                                        <td><?= esc($item['old_status'] ?? '-') ?></td>
                                        <td><?= esc($item['new_status'] ?? '-') ?></td>
                                        <td><?= esc($item['reject_reason'] ?? '-') ?></td>
                                        <td><?= esc($item['changed_by'] ?? $adminName) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">승인 이력이 없습니다.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="decisionModal" class="review-modal" role="dialog" aria-modal="true" aria-labelledby="decisionModalTitle" onclick="closeDecisionModal()">
    <div class="review-modal-panel" onclick="event.stopPropagation()">
        <form action="<?= esc($decisionUrl) ?>" method="post" id="decisionForm">
            <?= csrf_field() ?>
            <div class="review-modal-head">
                <strong id="decisionModalTitle">승인 / 거부 처리</strong>
            </div>
            <div class="review-modal-body">
                <div class="radio-row">
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="decision" value="APPROVE" checked>
                        <span class="form-check-label">승인</span>
                    </label>
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="decision" value="REJECT">
                        <span class="form-check-label">거부</span>
                    </label>
                </div>

                <div class="reject-box" id="rejectBox">
                    <label for="reject_reason" class="form-label">거부 사유</label>
                    <textarea id="reject_reason" name="reject_reason" class="form-control" rows="5" placeholder="거부 사유를 입력하세요."></textarea>
                </div>
            </div>
            <div class="review-modal-foot">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeDecisionModal()">취소</button>
                <button type="submit" class="btn btn-primary btn-sm">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
const decisionModal = document.getElementById('decisionModal');
const rejectBox = document.getElementById('rejectBox');
const decisionForm = document.getElementById('decisionForm');
const rejectReason = document.getElementById('reject_reason');

function openDecisionModal() {
    decisionModal.classList.add('is-open');
    updateDecisionMode();
}

function closeDecisionModal() {
    decisionModal.classList.remove('is-open');
}

function updateDecisionMode() {
    const selected = decisionForm.querySelector('input[name="decision"]:checked');
    const isReject = selected && selected.value === 'REJECT';
    rejectBox.classList.toggle('is-open', isReject);
    rejectReason.required = isReject;
}

decisionForm.querySelectorAll('input[name="decision"]').forEach(function (radio) {
    radio.addEventListener('change', updateDecisionMode);
});

decisionForm.addEventListener('submit', function (event) {
    const selected = decisionForm.querySelector('input[name="decision"]:checked');
    if (selected && selected.value === 'REJECT' && rejectReason.value.trim() === '') {
        event.preventDefault();
        rejectReason.focus();
        alert('거부 사유를 입력해주세요.');
    }
});
</script>

<?= $this->include('admin/layout/footer') ?>
