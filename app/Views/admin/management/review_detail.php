<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$row = $row ?? [];
$statusLabel = $statusLabel ?? '노출중';
$toggleUrl = $toggleUrl ?? '#';
$historyViewCount = (int) ($row['view_count'] ?? 0);
$isHidden = (($row['display_status'] ?? 'Y') === 'N');
?>

<style>
    .review-manage-page {
        color: #172033;
        font-size: 14px;
    }

    .review-manage-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .review-manage-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .review-manage-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .review-card {
        margin-bottom: 16px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
    }

    .review-card-head {
        padding: 12px 16px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .review-card-body {
        padding: 16px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr);
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
    }

    .status-pill.show {
        color: #166534;
        background: #dcfce7;
    }

    .status-pill.hide {
        color: #991b1b;
        background: #fee2e2;
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
        width: min(520px, 100%);
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
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header review-manage-header review-manage-page">
        <div>
            <div class="screen-id">AMFC003_01_L03_01</div>
            <h1>후기 상세</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; 컨텐츠 관리 &gt; 후기 관리 &gt; 상세</div>
    </section>

    <section class="content review-manage-page">
        <div class="container-fluid">
            <div class="mb-3 d-flex gap-2 flex-wrap">
                <a href="<?= base_url('admin/contents/reviews') ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
                <button type="button" class="btn btn-primary btn-sm" onclick="openToggleModal()">노출 / 비노출</button>
            </div>

            <div class="review-card">
                <div class="review-card-head">후기 정보</div>
                <div class="review-card-body">
                    <div class="detail-grid">
                        <div class="detail-label">상담 FC 정보</div>
                        <div class="detail-value">
                            <?php if (!empty($row['fc_name'])): ?>
                                <?= esc(($row['fc_name'] ?? '-') . ' (' . ($row['fc_email'] ?? '-') . ')') ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                        <div class="detail-label">작성자</div>
                        <div class="detail-value"><?= esc(($row['user_name'] ?? '-') . ' (' . ($row['user_email'] ?? '-') . ')') ?></div>
                        <div class="detail-label">별점</div>
                        <div class="detail-value">★ <?= number_format((float) ($row['rating'] ?? 0), 1) ?></div>
                        <div class="detail-label">작성일시</div>
                        <div class="detail-value"><?= esc($row['created_at'] ?? '-') ?></div>
                        <div class="detail-label">노출상태</div>
                        <div class="detail-value">
                            <span class="status-pill <?= $isHidden ? 'hide' : 'show' ?>"><?= esc($statusLabel) ?></span>
                        </div>
                        <div class="detail-label">조회수</div>
                        <div class="detail-value"><?= number_format($historyViewCount) ?></div>
                        <div class="detail-label">제목</div>
                        <div class="detail-value"><?= esc($row['title'] ?? '-') ?></div>
                        <div class="detail-label">내용</div>
                        <div class="detail-value"><?= nl2br(esc((string) ($row['body'] ?? '-'))) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="toggleModal" class="review-modal" role="dialog" aria-modal="true" aria-labelledby="toggleModalTitle" onclick="closeToggleModal()">
    <div class="review-modal-panel" onclick="event.stopPropagation()">
        <form action="<?= esc($toggleUrl) ?>" method="post" id="toggleForm">
            <?= csrf_field() ?>
            <div class="review-modal-head">
                <strong id="toggleModalTitle">노출 / 비노출 처리</strong>
            </div>
            <div class="review-modal-body">
                <div class="radio-row">
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="display_status" value="Y" <?= $isHidden ? '' : 'checked' ?>>
                        <span class="form-check-label">노출</span>
                    </label>
                    <label class="form-check">
                        <input class="form-check-input" type="radio" name="display_status" value="N" <?= $isHidden ? 'checked' : '' ?>>
                        <span class="form-check-label">비노출</span>
                    </label>
                </div>
            </div>
            <div class="review-modal-foot">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeToggleModal()">취소</button>
                <button type="submit" class="btn btn-primary btn-sm">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
const toggleModal = document.getElementById('toggleModal');

function openToggleModal() {
    toggleModal.classList.add('is-open');
}

function closeToggleModal() {
    toggleModal.classList.remove('is-open');
}
</script>

<?= $this->include('admin/layout/footer') ?>
