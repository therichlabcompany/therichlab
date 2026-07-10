<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$reviewCount = count($reviews ?? []);
?>

<style>
    .review-page {
        color: #172033;
        font-size: 14px;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .review-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .review-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .review-panel {
        max-width: 920px;
        margin: 0 auto;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .review-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .review-list {
        max-height: 650px;
        overflow-y: auto;
        padding: 16px;
    }

    .review-item {
        display: block;
        padding: 16px;
        color: inherit;
        text-decoration: none;
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        background: #fff;
    }

    .review-item + .review-item {
        margin-top: 12px;
    }

    .review-item:hover {
        border-color: #1f7aff;
        background: #f8fbff;
    }

    .review-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 12px;
        margin-bottom: 8px;
        color: #64748b;
        font-size: 13px;
    }

    .review-rating {
        color: #f59e0b;
        font-weight: 800;
    }

    .review-title {
        display: -webkit-box;
        overflow: hidden;
        margin-bottom: 8px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.45;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .review-body {
        display: -webkit-box;
        overflow: hidden;
        color: #334155;
        line-height: 1.6;
        white-space: pre-wrap;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 5;
    }

    .review-fc {
        margin-top: 10px;
        color: #64748b;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .review-header {
            flex-direction: column;
        }

        .review-panel {
            max-width: none;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header review-header review-page">
        <div>
            <div class="screen-id">AMFC003_01_L03</div>
            <h1>후기리스트</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; 개인회원 &gt; 상세 &gt; 후기리스트</div>
    </section>

    <section class="content review-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= base_url('admin/members/' . (int) $m['member_id']) ?>" class="btn btn-outline-secondary btn-sm">
                    상세로 돌아가기
                </a>
            </div>

            <div class="review-panel">
                <div class="review-panel-head">
                    <span><?= esc($m['name'] ?? '-') ?> 회원이 작성한 후기 리스트</span>
                    <span class="text-muted"><?= number_format($reviewCount) ?>건</span>
                </div>

                <div class="review-list">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <a href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/reviews/' . (int) $review['review_id']) ?>" class="review-item">
                                <div class="review-meta">
                                    <span class="review-rating">★ <?= number_format((float) ($review['rating'] ?? 0), 1) ?></span>
                                    <span>작성일자 <?= esc($review['created_at'] ?? '-') ?></span>
                                    <span>FC <?= esc($review['fc_name'] ?? '-') ?></span>
                                    <span><?= esc($review['company'] ?? '-') ?></span>
                                    <span><?= esc($review['region'] ?? '-') ?></span>
                                </div>
                                <div class="review-title"><?= esc($review['title'] ?? '-') ?></div>
                                <div class="review-body"><?= esc($review['body'] ?? '') ?></div>
                                <div class="review-fc">
                                    상담 UID: <?= esc($review['counsel_uid'] ?? '-') ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">작성된 후기가 없습니다.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
