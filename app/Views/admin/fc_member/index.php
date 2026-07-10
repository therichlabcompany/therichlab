<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$sort = $sort ?? 'recent_join';
$startDate = $startDate ?? '';
$endDate = $endDate ?? '';
$keyword = $keyword ?? '';
$error = $error ?? '';

$fcStatusLabel = static function ($member) {
    $reviewedStatus = $member['reviewed_status'] ?? '';
    $reviewStatus = $member['fc_review_status'] ?? '';

    if ($reviewedStatus === '' || $reviewedStatus === null) {
        return ['회원가입 완료', 'secondary'];
    }

    if ($reviewStatus === 'APPROVE' || $reviewedStatus === 'APPROVE') {
        return ['심의필 승인 완료', 'success'];
    }

    if ($reviewStatus === 'REJECT' || $reviewedStatus === 'REJECT') {
        return ['심의필 거부', 'danger'];
    }

    return ['심의필 승인 요청', 'warning'];
};

$sortUrl = static function ($value) {
    $query = $_GET;
    $query['sort'] = $value;

    return base_url('admin/fc-members') . '?' . http_build_query($query);
};
?>

<style>
    .fc-member-page {
        color: #172033;
        font-size: 14px;
    }

    .fc-member-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .fc-member-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .fc-member-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .fc-summary {
        margin-top: 10px;
        color: #64748b;
        font-weight: 700;
    }

    .fc-summary b {
        color: #0266ff;
    }

    .fc-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .fc-search-card,
    .fc-table-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .fc-search-card {
        margin-bottom: 12px;
        padding: 16px;
    }

    .fc-search-grid {
        display: grid;
        grid-template-columns: 220px minmax(280px, 1fr) 100px 100px;
        gap: 10px;
        align-items: end;
    }

    .fc-search-grid label {
        display: block;
        margin-bottom: 6px;
        color: #4b586b;
        font-size: 13px;
        font-weight: 800;
    }

    .date-range {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 6px;
        align-items: center;
    }

    .fc-sort-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .fc-table {
        width: 100%;
        margin: 0;
        table-layout: fixed;
        font-size: 13px;
    }

    .fc-table th {
        padding: 12px 8px;
        color: #4b586b;
        background: #f8fafc;
        font-weight: 800;
        white-space: nowrap;
    }

    .fc-table td {
        padding: 12px 8px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .fc-table .col-check {
        width: 44px;
    }

    .fc-table .col-name {
        width: 110px;
    }

    .fc-table .col-phone {
        width: 135px;
    }

    .fc-table .col-license {
        width: 150px;
    }

    .fc-table .col-company {
        width: 150px;
    }

    .fc-table .col-view {
        width: 90px;
    }

    .fc-table .col-status {
        width: 150px;
    }

    .fc-table .col-date {
        width: 150px;
    }

    .fc-table .col-action {
        width: 86px;
    }

    .fc-table .ellipsis {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @media (max-width: 1200px) {
        .fc-search-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .fc-search-grid {
            grid-template-columns: 1fr;
        }

        .fc-member-header {
            flex-direction: column;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header fc-member-header fc-member-page">
        <div>
            <div class="screen-id">AMFC003_02</div>
            <h1>FC 회원</h1>
            <div class="fc-summary">FC 회원 <b><?= number_format($total) ?></b></div>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; FC회원</div>
    </section>

    <section class="content fc-member-page">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-warning"><?= esc($error) ?></div>
            <?php endif; ?>

            <div class="fc-toolbar">
                <a href="<?= base_url('admin/fc-members/create') ?>" class="btn btn-outline-primary btn-sm">
                    신규 등록
                </a>
                <a href="/admin/fc-members/export?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm">
                    EXCEL
                </a>
            </div>

            <form method="get" class="fc-search-card" id="fcSearchForm">
                <input type="hidden" name="sort" value="<?= esc($sort) ?>">
                <div class="fc-search-grid">
                    <div>
                        <label>가입일자</label>
                        <div class="date-range">
                            <input type="date" name="start_date" class="form-control" value="<?= esc($startDate) ?>">
                            <span>~</span>
                            <input type="date" name="end_date" class="form-control" value="<?= esc($endDate) ?>">
                        </div>
                    </div>
                    <div>
                        <label>검색어</label>
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            placeholder="회원명, 이메일주소, 휴대폰번호로 검색"
                            value="<?= esc($keyword) ?>"
                        >
                    </div>
                    <button class="btn btn-primary" type="submit">검색</button>
                    <a href="<?= base_url('admin/fc-members') ?>" class="btn btn-outline-secondary">초기화</a>
                </div>
            </form>

            <div class="fc-sort-tabs">
                <a href="<?= esc($sortUrl('recent_join')) ?>" class="btn btn-sm <?= $sort === 'recent_join' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    최근 가입 순
                </a>
                <a href="<?= esc($sortUrl('recent_login')) ?>" class="btn btn-sm <?= $sort === 'recent_login' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    최근 접속 순
                </a>
                <a href="<?= esc($sortUrl('view_count')) ?>" class="btn btn-sm <?= $sort === 'view_count' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    조회수 많은 순
                </a>
                <a href="<?= esc($sortUrl('counsel_count')) ?>" class="btn btn-sm <?= $sort === 'counsel_count' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    상담요청 많은 순
                </a>
            </div>

            <div class="fc-table-card">
                <div class="table-responsive">
                    <table class="table table-hover text-center align-middle fc-table">
                        <thead>
                            <tr>
                                <th class="col-check"><input type="checkbox" id="checkAll"></th>
                                <th class="col-name">이름</th>
                                <th>이메일주소</th>
                                <th class="col-phone">휴대폰번호</th>
                                <th class="col-license">보험모집종사자 등록번호</th>
                                <th class="col-company">소속 보험사</th>
                                <th class="col-view">조회수</th>
                                <th class="col-status">상태값</th>
                                <th class="col-date">가입일시</th>
                                <th class="col-action">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                                <?php [$statusLabel, $statusClass] = $fcStatusLabel($m); ?>
                                <tr>
                                    <td><input type="checkbox" name="member_ids[]" value="<?= (int) $m['member_id'] ?>"></td>
                                    <td class="ellipsis" title="<?= esc($m['name']) ?>"><?= esc($m['name']) ?></td>
                                    <td class="text-start ellipsis" title="<?= esc($m['email']) ?>"><?= esc($m['email']) ?></td>
                                    <td><?= esc($m['phone']) ?></td>
                                    <td class="ellipsis" title="<?= esc($m['license_no'] ?? '-') ?>"><?= esc($m['license_no'] ?? '-') ?></td>
                                    <td class="ellipsis" title="<?= esc($m['company'] ?? '-') ?>"><?= esc($m['company'] ?? '-') ?></td>
                                    <td><?= number_format((int) ($m['view_count'] ?? 0)) ?></td>
                                    <td><span class="badge bg-<?= esc($statusClass) ?>"><?= esc($statusLabel) ?></span></td>
                                    <td><?= esc(date('Ymd H:i:s', strtotime($m['created_at']))) ?></td>
                                    <td>
                                        <a href="<?= base_url('admin/fc-members/' . (int) $m['member_id']) ?>" class="btn btn-primary btn-sm">
                                            상세
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="10" class="text-muted py-4">등록된 FC 회원이 없습니다.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                <?= $pager->makeLinks($page, $perPage, $total, 'default_full') ?>
            </div>
        </div>
    </section>
</div>

<script>
document.getElementById('fcSearchForm').addEventListener('submit', function (event) {
    var keyword = this.querySelector('[name="q"]').value.trim();
    if (keyword !== '' && keyword.length < 2) {
        event.preventDefault();
        alert('검색어 입력은 최소 2자 이상 입력하셔야 됩니다.');
    }
});

document.getElementById('checkAll').addEventListener('change', function (event) {
    document.querySelectorAll('[name="member_ids[]"]').forEach(function (checkbox) {
        checkbox.checked = event.target.checked;
    });
});
</script>

<?= $this->include('admin/layout/footer') ?>
