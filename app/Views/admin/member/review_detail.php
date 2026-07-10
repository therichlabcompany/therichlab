<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<style>
    .review-detail-page {
        color: #172033;
        font-size: 14px;
    }

    .review-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .review-detail-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .review-detail-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .review-detail-card {
        max-width: 920px;
        margin: 0 auto;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .review-detail-head {
        padding: 14px 18px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .review-detail-body {
        padding: 18px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 150px minmax(0, 1fr);
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
        word-break: break-all;
    }

    .detail-value.content {
        min-height: 180px;
        white-space: pre-wrap;
    }

    .rating {
        color: #f59e0b;
        font-weight: 800;
    }

    @media (max-width: 576px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header review-detail-header review-detail-page">
        <div>
            <div class="screen-id">AMFC003_01_L03_01</div>
            <h1>후기 상세</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; 개인회원 &gt; 상세 &gt; 후기리스트 &gt; 상세</div>
    </section>

    <section class="content review-detail-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/reviews') ?>" class="btn btn-outline-secondary btn-sm">
                    후기리스트로 돌아가기
                </a>
            </div>

            <div class="review-detail-card">
                <div class="review-detail-head">후기 상세정보</div>
                <div class="review-detail-body">
                    <div class="detail-grid">
                        <div class="detail-label">별점</div>
                        <div class="detail-value rating">★ <?= number_format((float) ($review['rating'] ?? 0), 1) ?></div>
                        <div class="detail-label">작성일자</div>
                        <div class="detail-value"><?= esc($review['created_at'] ?? '-') ?></div>
                        <div class="detail-label">FC</div>
                        <div class="detail-value"><?= esc($review['fc_name'] ?? '-') ?></div>
                        <div class="detail-label">소속/지역</div>
                        <div class="detail-value"><?= esc($review['company'] ?? '-') ?> / <?= esc($review['region'] ?? '-') ?></div>
                        <div class="detail-label">후기 제목</div>
                        <div class="detail-value"><?= esc($review['title'] ?? '-') ?></div>
                        <div class="detail-label">후기 내용</div>
                        <div class="detail-value content"><?= esc($review['body'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
