<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$title = $title ?? '관리자';
$breadcrumb = $breadcrumb ?? 'Main';
$headers = $headers ?? [];
$rows = $rows ?? [];
$actions = $actions ?? [];
$tabs = $tabs ?? [];
$summary = $summary ?? [];
$detail = $detail ?? [];
$missing = $missing ?? [];
$searchAction = $searchAction ?? current_url();
$searchValue = $searchValue ?? '';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
$searchHidden = $searchHidden ?? [];
$readyAlert = (bool) ($readyAlert ?? false);
$pageClass = trim((string) ($pageClass ?? ''));
$bulkForm = $bulkForm ?? [];
$page = max(1, (int) ($page ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$pageQuery = is_array($pageQuery ?? null) ? $pageQuery : [];
$perPage = max(1, (int) ($perPage ?? 20));
?>

<style>
    .admin-page {
        padding: 18px 8px 30px;
    }

    .admin-page h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
    }

    .admin-page .breadcrumb-text {
        margin-top: 6px;
        color: #6b7280;
        font-size: 13px;
    }

    .page-toolbar,
    .summary-grid,
    .tab-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-top: 16px;
    }

    .page-card {
        margin-top: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
    }

    .page-card-head {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        font-weight: 800;
    }

    .page-card-body {
        padding: 16px;
    }

    .summary-item {
        min-width: 220px;
        padding: 12px 14px;
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        background: #f8fafc;
    }

    .summary-item strong {
        display: block;
        color: #64748b;
        font-size: 13px;
    }

    .summary-item span {
        display: block;
        margin-top: 4px;
        font-size: 18px;
        font-weight: 800;
    }

    .detail-list {
        display: grid;
        border-top: 1px solid #e5e7eb;
    }

    .detail-row {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        border-bottom: 1px solid #e5e7eb;
    }

    .detail-label {
        padding: 12px 14px;
        background: #f8fafc;
        font-weight: 800;
    }

    .detail-value {
        padding: 12px 14px;
    }

    .ready-box {
        margin-top: 16px;
        padding: 14px 16px;
        border: 1px solid #facc15;
        border-radius: 8px;
        color: #713f12;
        background: #fefce8;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .review-management-page .table th,
    .review-management-page .table td {
        padding: 15px 16px;
    }

    @media (max-width: 768px) {
        .detail-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header admin-page <?= esc($pageClass) ?>">
        <div class="container-fluid">
            <h1><?= esc($title) ?></h1>
            <div class="breadcrumb-text"><?= esc($breadcrumb) ?></div>

            <?php if (!empty($backUrl)): ?>
                <div class="page-toolbar">
                    <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">&lt;- 리스트로 돌아가기</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($missing)): ?>
                <div class="ready-box">
                    <?php foreach ($missing as $message): ?>
                        <div><?= esc($message) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="page-toolbar">
                <?php if (isset($countLabel)): ?>
                    <strong><?= esc($countLabel) ?> <?= number_format((int) ($count ?? 0)) ?></strong>
                <?php endif; ?>

                <?php foreach ($actions as $action): ?>
                    <a
                        href="<?= esc($action['url'] ?? '#') ?>"
                        class="btn btn-outline-primary btn-sm"
                        <?= !empty($action['ready']) ? 'onclick="readyNotice(); return false;"' : '' ?>
                    ><?= esc($action['label'] ?? '버튼') ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($searchPlaceholder)): ?>
                <div class="admin-search-card">
                    <form action="<?= esc($searchAction) ?>" method="get" class="admin-search-form">
                        <?php foreach ($searchHidden as $name => $value): ?>
                            <?php if ((string) $value !== ''): ?>
                                <input type="hidden" name="<?= esc($name) ?>" value="<?= esc($value) ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <label class="admin-search-field admin-search-field--date">
                            <span>시작일</span>
                            <input type="date" name="start_date" value="<?= esc($dateFrom) ?>" class="form-control form-control-sm">
                        </label>
                        <label class="admin-search-field admin-search-field--date">
                            <span>종료일</span>
                            <input type="date" name="end_date" value="<?= esc($dateTo) ?>" class="form-control form-control-sm">
                        </label>
                        <label class="admin-search-field admin-search-field--keyword">
                            <span>검색어</span>
                            <input type="text" name="q" value="<?= esc($searchValue) ?>" class="form-control form-control-sm" placeholder="<?= esc($searchPlaceholder) ?>">
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm admin-search-button">검색</button>
                        <a href="<?= esc($searchAction) ?>" class="btn btn-outline-secondary btn-sm admin-search-button">초기화</a>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (!empty($summary)): ?>
                <div class="summary-grid">
                    <?php foreach ($summary as $label => $value): ?>
                        <div class="summary-item">
                            <strong><?= esc($label) ?></strong>
                            <span><?= esc($value) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($tabs)): ?>
                <div class="tab-row">
                    <?php foreach ($tabs as $index => $tab): ?>
                        <?php if (is_array($tab)): ?>
                            <a href="<?= esc($tab['url'] ?? '#') ?>" class="btn btn-sm <?= !empty($tab['active']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <?= esc($tab['label'] ?? '탭') ?>
                            </a>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm <?= $index === 0 ? 'btn-primary' : 'btn-outline-primary' ?>" onclick="readyNotice()">
                                <?= esc($tab) ?>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($detail)): ?>
                <div class="page-card">
                    <div class="page-card-head">
                        <span>상세 정보</span>
                    </div>
                    <div class="page-card-body">
                        <div class="detail-list">
                            <?php foreach ($detail as $label => $value): ?>
                                <div class="detail-row">
                                    <div class="detail-label"><?= esc($label) ?></div>
                                    <div class="detail-value"><?= $value ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="page-card">
                <div class="page-card-head">
                    <span><?= esc($title) ?></span>
                    <span><?= number_format($perPage) ?>개 노출</span>
                </div>
                <div class="page-card-body table-responsive">
                    <?php if (!empty($bulkForm)): ?>
                        <form id="adminBulkForm" action="<?= esc($bulkForm['action'] ?? current_url()) ?>" method="post" class="admin-bulk-toolbar" onsubmit="return prepareAdminBulkForm(this)">
                            <?= csrf_field() ?>
                            <strong>선택 항목 관리</strong>
                            <span class="admin-bulk-count">0개 선택</span>
                            <button type="submit" class="btn btn-outline-danger btn-sm" data-confirm="<?= esc($bulkForm['confirm'] ?? '선택 항목을 처리하시겠습니까?') ?>"><?= esc($bulkForm['label'] ?? '선택 처리') ?></button>
                        </form>
                    <?php endif; ?>
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <?php foreach ($headers as $headerIndex => $header): ?>
                                    <th><?php if ($headerIndex === 0 && !empty($bulkForm)): ?><input type="checkbox" class="form-check-input" id="adminCheckAll" aria-label="전체 선택"><?php else: ?><?= esc($header) ?><?php endif; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <?php foreach ($row as $column): ?>
                                        <td><?= $column ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($rows)): ?>
                                <tr>
                                    <td colspan="<?= max(1, count($headers)) ?>" class="text-center text-muted py-5">
                                        표시할 데이터가 없습니다.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if ($totalPages > 1): ?><nav class="mt-3"><ul class="pagination pagination-sm justify-content-end mb-0">
                        <?php for ($pageNo = 1; $pageNo <= $totalPages; $pageNo++): $pageUrl = current_url() . '?' . http_build_query(array_merge($pageQuery, ['page' => $pageNo])); ?>
                            <li class="page-item <?= $pageNo === $page ? 'active' : '' ?>"><a class="page-link" href="<?= esc($pageUrl) ?>"><?= $pageNo ?></a></li>
                        <?php endfor; ?>
                    </ul></nav><?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function readyNotice() {
    alert('해당 기능은 페이지 구성 및 링크 준비중입니다.');
}

const adminCheckAll = document.getElementById('adminCheckAll');
const adminRowChecks = Array.from(document.querySelectorAll('.ad-row-check'));
const adminBulkCount = document.querySelector('.admin-bulk-count');
function updateAdminBulkCount() {
    const count = adminRowChecks.filter((item) => item.checked).length;
    if (adminBulkCount) adminBulkCount.textContent = count + '개 선택';
    if (adminCheckAll) adminCheckAll.checked = adminRowChecks.length > 0 && count === adminRowChecks.length;
}
if (adminCheckAll) adminCheckAll.addEventListener('change', function () { adminRowChecks.forEach((item) => item.checked = this.checked); updateAdminBulkCount(); });
adminRowChecks.forEach((item) => item.addEventListener('change', updateAdminBulkCount));
function prepareAdminBulkForm(form) {
    const selected = adminRowChecks.filter((item) => item.checked);
    if (!selected.length) { alert('처리할 광고를 선택해주세요.'); return false; }
    if (!confirm(form.querySelector('[data-confirm]')?.dataset.confirm || '선택 항목을 처리하시겠습니까?')) return false;
    selected.forEach((item) => { const input = document.createElement('input'); input.type = 'hidden'; input.name = item.name; input.value = item.value; form.appendChild(input); });
    return true;
}

<?php if ($readyAlert): ?>
document.addEventListener('DOMContentLoaded', function () {
    readyNotice();
});
<?php endif; ?>
</script>

<?= $this->include('admin/layout/footer') ?>
