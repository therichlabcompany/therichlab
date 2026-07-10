<?php
$startDate = $startDate ?? date('Y-m-d', strtotime('-1 month'));
$endDate = $endDate ?? date('Y-m-d');
$prevStartDate = $prevStartDate ?? date('Y-m-d', strtotime($startDate . ' -1 month'));
$prevEndDate = $prevEndDate ?? date('Y-m-d', strtotime($endDate . ' -1 month'));
$nextStartDate = $nextStartDate ?? date('Y-m-d', strtotime($startDate . ' +1 month'));
$nextEndDate = $nextEndDate ?? date('Y-m-d', strtotime($endDate . ' +1 month'));
$counts = $counts ?? [];
$members = $members ?? [];
$counsels = $counsels ?? [];
$reviews = $reviews ?? [];
$ads = $ads ?? [];
$exportUrl = $exportUrl ?? base_url('admin/dashboard/export?' . http_build_query(['start_date' => $startDate, 'end_date' => $endDate]));

$number = static fn ($value): string => number_format((int) ($value ?? 0));
$imageUrl = static function (?string $path): string {
    $path = trim((string) $path);
    return $path !== '' ? base_url(ltrim($path, '/')) : '';
};
?>

<style>
    .dashboard-page { color: #172033; font-size: 14px; }
    .dashboard-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
    .dashboard-head h1 { margin: 0; font-size: 24px; font-weight: 800; }
    .dashboard-breadcrumb { margin-top: 6px; color: #64748b; font-size: 13px; }
    .dashboard-toolbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .dashboard-search { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .dashboard-search input[type="date"] { width: 150px; height: 32px; font-size: 13px; }
    .dashboard-section { margin-top: 16px; border: 1px solid #d8e0ea; border-radius: 8px; background: #fff; }
    .dashboard-section-head { display: flex; justify-content: space-between; gap: 12px; padding: 13px 16px; border-bottom: 1px solid #d8e0ea; background: #f8fafc; font-weight: 800; }
    .metric-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 1px; background: #d8e0ea; }
    .metric-card { min-height: 112px; padding: 16px; background: #fff; }
    .metric-card strong { display: block; min-height: 38px; color: #516070; font-size: 13px; }
    .metric-card a { display: inline-block; margin-top: 8px; color: #172033; font-size: 28px; font-weight: 900; text-decoration: none; }
    .metric-card a:hover { color: #0d6efd; }
    .dashboard-lists { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 16px; }
    .dash-list { border: 1px solid #d8e0ea; border-radius: 8px; background: #fff; min-width: 0; }
    .dash-list-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 12px 14px; border-bottom: 1px solid #d8e0ea; background: #f8fafc; font-weight: 800; }
    .dash-list-body { padding: 6px 0; }
    .dash-row { display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: #172033; text-decoration: none; border-bottom: 1px solid #edf1f5; }
    .dash-row:last-child { border-bottom: 0; }
    .dash-row:hover { background: #f8fafc; color: #0d6efd; }
    .dash-avatar { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 50%; background: #e8eef5; object-fit: cover; display: inline-flex; align-items: center; justify-content: center; color: #64748b; font-weight: 800; }
    .dash-main { min-width: 0; flex: 1; }
    .dash-title { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 800; }
    .dash-sub { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #64748b; font-size: 12px; }
    .empty-row { padding: 26px 14px; color: #64748b; text-align: center; }
    .mini-split { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1px; margin-top: 1px; background: #d8e0ea; }
    .mini-metric { padding: 13px 16px; background: #fff; }
    .mini-metric strong { color: #64748b; font-size: 13px; }
    .mini-metric span { display: block; margin-top: 4px; font-size: 20px; font-weight: 900; }
    @media (max-width: 1200px) {
        .metric-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .dashboard-lists { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .dashboard-head { display: block; }
        .dashboard-toolbar { margin-top: 12px; }
        .dashboard-search input[type="date"] { width: 100%; }
        .metric-grid { grid-template-columns: 1fr; }
        .mini-split { grid-template-columns: 1fr; }
    }
</style>

<div class="dashboard-page">
    <div class="dashboard-head">
        <div>
            <h1>대시보드</h1>
            <div class="dashboard-breadcrumb">Main &gt; 대시보드</div>
        </div>
        <div class="dashboard-toolbar">
            <a href="<?= base_url('admin?' . http_build_query(['start_date' => $prevStartDate, 'end_date' => $prevEndDate])) ?>" class="btn btn-outline-secondary btn-sm">&lt;</a>
            <form action="<?= base_url('admin') ?>" method="get" class="dashboard-search admin-search-form admin-search-form--dashboard">
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($startDate) ?>">
                <span>~</span>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($endDate) ?>">
                <button type="submit" class="btn btn-primary btn-sm">검색</button>
            </form>
            <a href="<?= base_url('admin?' . http_build_query(['start_date' => $nextStartDate, 'end_date' => $nextEndDate])) ?>" class="btn btn-outline-secondary btn-sm">&gt;</a>
            <a href="<?= esc($exportUrl) ?>" class="btn btn-outline-primary btn-sm">EXCEL</a>
        </div>
    </div>

    <section class="dashboard-section">
        <div class="dashboard-section-head">
            <span>기간 데이터</span>
            <span><?= esc($startDate) ?> ~ <?= esc($endDate) ?></span>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <strong>신규 개인 회원</strong>
                <a href="<?= base_url('admin/members') ?>"><?= $number($counts['newUsers'] ?? 0) ?></a>
            </div>
            <div class="metric-card">
                <strong>신규 FC 회원</strong>
                <a href="<?= base_url('admin/fc-members') ?>"><?= $number($counts['newFcMembers'] ?? 0) ?></a>
            </div>
            <div class="metric-card">
                <strong>상담 요청</strong>
                <a href="<?= base_url('admin/contents/counsels?status=REQUEST') ?>"><?= $number($counts['counselRequests'] ?? 0) ?></a>
            </div>
            <div class="metric-card">
                <strong>신규 등록 후기</strong>
                <a href="<?= base_url('admin/contents/reviews') ?>"><?= $number($counts['newReviews'] ?? 0) ?></a>
            </div>
            <div class="metric-card">
                <strong>신규 광고 요청</strong>
                <a href="<?= base_url('admin/ads/normal') ?>"><?= $number($counts['newAds'] ?? 0) ?></a>
            </div>
            <div class="metric-card">
                <strong>심의필 승인 요청</strong>
                <a href="<?= base_url('admin/contents/deliberations') ?>"><?= $number($counts['reviewedRequests'] ?? 0) ?></a>
            </div>
        </div>
        <div class="mini-split">
            <div class="mini-metric">
                <strong>일반광고</strong>
                <span><?= $number($counts['normalAds'] ?? 0) ?></span>
            </div>
            <div class="mini-metric">
                <strong>배너광고</strong>
                <span><?= $number($counts['bannerAds'] ?? 0) ?></span>
            </div>
        </div>
    </section>

    <div class="dashboard-lists">
        <section class="dash-list">
            <div class="dash-list-head">
                <span>가입 회원</span>
                <a href="<?= base_url('admin/members') ?>" class="btn btn-outline-secondary btn-sm">더보기</a>
            </div>
            <div class="dash-list-body">
                <?php foreach ($members as $row): ?>
                    <a class="dash-row" href="<?= esc($row['detail_url']) ?>">
                        <?php $img = $imageUrl($row['image_path'] ?? ''); ?>
                        <?php if ($img !== ''): ?>
                            <img class="dash-avatar" src="<?= esc($img) ?>" alt="">
                        <?php else: ?>
                            <span class="dash-avatar"><?= esc(mb_substr((string) ($row['name'] ?? '회'), 0, 1)) ?></span>
                        <?php endif; ?>
                        <span class="dash-main">
                            <span class="dash-title"><?= esc(($row['member_type_label'] ?? '-') . ' · ' . ($row['name'] ?? '-')) ?></span>
                            <span class="dash-sub"><?= esc($row['email'] ?? '-') ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($members)): ?>
                    <div class="empty-row">데이터가 없습니다.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="dash-list">
            <div class="dash-list-head">
                <span>상담 요청</span>
                <a href="<?= base_url('admin/contents/counsels?status=REQUEST') ?>" class="btn btn-outline-secondary btn-sm">더보기</a>
            </div>
            <div class="dash-list-body">
                <?php foreach ($counsels as $row): ?>
                    <a class="dash-row" href="<?= base_url('admin/contents/counsels/' . (int) ($row['counsel_id'] ?? 0)) ?>">
                        <?php $img = $imageUrl($row['fc_profile_image'] ?? ''); ?>
                        <?php if ($img !== ''): ?>
                            <img class="dash-avatar" src="<?= esc($img) ?>" alt="">
                        <?php else: ?>
                            <span class="dash-avatar">FC</span>
                        <?php endif; ?>
                        <span class="dash-main">
                            <span class="dash-title">상담요청회원 : <?= esc($row['user_name'] ?? '-') ?></span>
                            <span class="dash-sub">FC회원 : <?= esc($row['fc_name'] ?? '-') ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($counsels)): ?>
                    <div class="empty-row">데이터가 없습니다.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="dash-list">
            <div class="dash-list-head">
                <span>광고 요청</span>
                <a href="<?= base_url('admin/ads/normal') ?>" class="btn btn-outline-secondary btn-sm">더보기</a>
            </div>
            <div class="dash-list-body">
                <?php foreach ($ads as $row): ?>
                    <?php $kind = ($row['ad_type'] ?? '') === 'banner' ? (($row['banner_position'] ?? '') === 'bottom' ? 'bottom' : 'top') : 'normal'; ?>
                    <a class="dash-row" href="<?= base_url('admin/ads/' . $kind . '/status?ad_id=' . (int) ($row['id'] ?? 0)) ?>">
                        <span class="dash-avatar">AD</span>
                        <span class="dash-main">
                            <span class="dash-title"><?= esc(($row['ad_type'] ?? '') === 'banner' ? '배너광고' : '일반광고') ?> · <?= esc($row['name'] ?? '-') ?></span>
                            <span class="dash-sub"><?= esc(($row['start_date'] ?? '-') . ' ~ ' . ($row['end_date'] ?? '-')) ?> / <?= number_format((int) ($row['amount'] ?? 0)) ?>원</span>
                        </span>
                    </a>
                <?php endforeach; ?>
                <?php if (empty($ads)): ?>
                    <div class="empty-row">데이터가 없습니다.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <section class="dash-list mt-3">
        <div class="dash-list-head">
            <span>신규 등록 후기</span>
            <a href="<?= base_url('admin/contents/reviews') ?>" class="btn btn-outline-secondary btn-sm">더보기</a>
        </div>
        <div class="dash-list-body">
            <?php foreach ($reviews as $row): ?>
                <a class="dash-row" href="<?= base_url('admin/contents/reviews/' . (int) ($row['review_id'] ?? 0)) ?>">
                    <span class="dash-avatar">후</span>
                    <span class="dash-main">
                        <span class="dash-title"><?= esc($row['title'] ?? '-') ?></span>
                        <span class="dash-sub"><?= esc(($row['user_name'] ?? '-') . ' (FC회원: ' . ($row['fc_name'] ?? '-') . ')') ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
            <?php if (empty($reviews)): ?>
                <div class="empty-row">데이터가 없습니다.</div>
            <?php endif; ?>
        </div>
    </section>
</div>
