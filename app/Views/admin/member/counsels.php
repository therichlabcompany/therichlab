<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$statusCounts = $statusCounts ?? [];
$tabs = [
    'ALL' => ['label' => '전체', 'count' => (int) ($statusCounts['all_count'] ?? 0)],
    'REQUEST' => ['label' => '상담 대기', 'count' => (int) ($statusCounts['request_count'] ?? 0)],
    'COMPLETE' => ['label' => '상담 완료', 'count' => (int) ($statusCounts['complete_count'] ?? 0)],
];

$statusLabel = static function ($value) {
    return match ($value) {
        'REQUEST' => ['상담 대기', 'warning'],
        'PROGRESS' => ['진행중', 'info'],
        'COMPLETE' => ['상담 완료', 'success'],
        'CANCEL' => ['취소', 'secondary'],
        default => [$value ?: '-', 'secondary'],
    };
};

$fileSize = static function ($bytes) {
    $bytes = (int) $bytes;

    if ($bytes <= 0) {
        return '-';
    }

    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    }

    return number_format($bytes / 1024, 1) . ' KB';
};

$formatDateTime = static function ($value) {
    $value = trim((string) $value);

    if ($value === '') {
        return '-';
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value;
    }

    return date('Y-m-d H:i:s', $timestamp);
};

$selectedUid = $selectedCounsel['counsel_uid'] ?? '';
$page = max(1, (int) ($page ?? 1));
$perPage = max(1, (int) ($perPage ?? 5));
$total = max(0, (int) ($total ?? 0));
$totalPages = max(1, (int) ceil($total / $perPage));
$currentPage = min($page, $totalPages);
?>

<style>
    .counsel-page {
        color: #172033;
        font-size: 14px;
    }

    .counsel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .counsel-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .counsel-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .counsel-layout {
        display: grid;
        grid-template-columns: minmax(320px, 39%) minmax(420px, 1fr);
        gap: 16px;
        align-items: start;
    }

    .counsel-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .counsel-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .counsel-card-body {
        padding: 16px;
    }

    .counsel-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 12px;
    }

    .counsel-list {
        display: grid;
        gap: 10px;
    }

    .counsel-list-item {
        display: grid;
        grid-template-columns: 52px minmax(0, 1fr);
        gap: 10px;
        padding: 12px;
        color: inherit;
        text-decoration: none;
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        background: #fff;
    }

    .counsel-list-item.active {
        border-color: #1f7aff;
        background: #f3f8ff;
    }

    .fc-avatar {
        width: 52px;
        height: 52px;
        overflow: hidden;
        border-radius: 50%;
        background: #e5e7eb;
    }

    .fc-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .fc-avatar .fallback {
        display: block;
        width: 100%;
        height: 100%;
        background: #e5e7eb;
    }

    .counsel-list-main {
        min-width: 0;
    }

    .counsel-list-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
        font-weight: 800;
    }

    .counsel-meta,
    .counsel-intro {
        overflow: hidden;
        color: #64748b;
        font-size: 13px;
        text-overflow: ellipsis;
        white-space: nowrap;
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
        min-width: 0;
        word-break: break-all;
    }

    .detail-value.content {
        min-height: 150px;
        white-space: pre-wrap;
    }

    .file-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .file-table th,
    .file-table td {
        padding: 12px 10px;
        border: 1px solid #d8e0ea;
        vertical-align: middle;
    }

    .file-table th {
        color: #4b586b;
        background: #f8fafc;
        font-size: 13px;
        font-weight: 800;
        text-align: center;
    }

    .file-table .col-no {
        width: 70px;
    }

    .file-table .col-type {
        width: 110px;
    }

    .file-table .col-size {
        width: 120px;
    }

    .file-table .col-action {
        width: 86px;
    }

    @media (max-width: 1100px) {
        .counsel-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header counsel-header counsel-page">
        <div>
            <div class="screen-id">AMFC003_01_02</div>
            <h1>상담현황</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; 개인회원 &gt; 상세 &gt; 상담현황</div>
    </section>

    <section class="content counsel-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= base_url('admin/members/' . (int) $m['member_id']) ?>" class="btn btn-outline-secondary btn-sm">
                    상세로 돌아가기
                </a>
            </div>

            <div class="counsel-tabs">
                <?php foreach ($tabs as $tabStatus => $tab): ?>
                    <a
                        href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/counsels?status=' . $tabStatus) ?>"
                        class="btn btn-sm <?= $status === $tabStatus ? 'btn-primary' : 'btn-outline-primary' ?>"
                    >
                        <?= esc($tab['label']) ?> <?= number_format($tab['count']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="counsel-layout">
                <div class="counsel-card">
                    <div class="counsel-card-head">
                        <span>상담 요청 리스트</span>
                        <span class="text-muted">
                            <?= number_format(count($counsels)) ?>건 / 총 <?= number_format($total) ?>건
                        </span>
                    </div>
                    <div class="counsel-card-body">
                        <div class="counsel-list">
                            <?php if (!empty($counsels)): ?>
                                <?php foreach ($counsels as $counsel): ?>
                                    <?php
                                    [$counselStatusLabel, $counselStatusClass] = $statusLabel($counsel['status'] ?? '');
                                    $profileImageUrl = profile_image_url($counsel['profile_image'] ?? '');
                                    ?>
                                    <a
                                        href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/counsels?status=' . urlencode($status) . '&counsel_uid=' . urlencode($counsel['counsel_uid'])) ?>"
                                        class="counsel-list-item <?= $selectedUid === $counsel['counsel_uid'] ? 'active' : '' ?>"
                                    >
                                        <div class="fc-avatar">
                                            <?php if ($profileImageUrl !== ''): ?>
                                                <img
                                                    src="<?= esc($profileImageUrl) ?>"
                                                    alt=""
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                                                >
                                            <?php else: ?>
                                                <div class="fallback"></div>
                                            <?php endif; ?>
                                            <?php if ($profileImageUrl !== ''): ?>
                                                <div class="fallback" style="display:none;"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="counsel-list-main">
                                            <div class="counsel-list-title">
                                                <span><?= esc($counsel['fc_name'] ?? '-') ?></span>
                                                <span class="badge bg-<?= esc($counselStatusClass) ?>"><?= esc($counselStatusLabel) ?></span>
                                            </div>
                                            <div class="counsel-meta">
                                                희망 상담요청일자 <?= esc($formatDateTime($counsel['reserve_datetime'] ?? '')) ?>
                                                ·
                                                별점 <?= number_format((float) ($counsel['avg_rating'] ?? 0), 1) ?>
                                                · <?= esc($counsel['company'] ?: '-') ?>
                                                · <?= esc($counsel['region'] ?: '-') ?>
                                            </div>
                                            <div class="counsel-intro">
                                                <?= esc($counsel['intro'] ?: '소개글이 없습니다.') ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">상담 요청 건이 없습니다.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="counsel-card">
                    <div class="counsel-card-head">
                        <span>상담요청 상세내용</span>
                        <?php if ($selectedCounsel): ?>
                            <?php [$selectedStatusLabel, $selectedStatusClass] = $statusLabel($selectedCounsel['status'] ?? ''); ?>
                            <span class="badge bg-<?= esc($selectedStatusClass) ?>"><?= esc($selectedStatusLabel) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="counsel-card-body">
                        <?php if ($selectedCounsel): ?>
                            <div class="detail-grid">
                                <div class="detail-label">상담 요청 일자</div>
                                <div class="detail-value"><?= esc($formatDateTime($selectedCounsel['created_at'] ?? '')) ?></div>
                                <div class="detail-label">희망 상담요청일자</div>
                                <div class="detail-value"><?= esc($formatDateTime($selectedCounsel['reserve_datetime'] ?? '')) ?></div>
                                <div class="detail-label">신청자</div>
                                <div class="detail-value"><?= esc($selectedCounsel['name'] ?? '-') ?></div>
                                <div class="detail-label">연락처</div>
                                <div class="detail-value"><?= esc($selectedCounsel['phone'] ?? '-') ?></div>
                                <div class="detail-label">이메일</div>
                                <div class="detail-value"><?= esc($selectedCounsel['email'] ?? '-') ?></div>
                                <div class="detail-label">상담 요청 내용</div>
                                <div class="detail-value content"><?= esc($selectedCounsel['content'] ?: '등록된 내용이 없습니다.') ?></div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">선택된 상담 요청이 없습니다.</div>
                        <?php endif; ?>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <div class="counsel-card-body pt-0">
                            <nav aria-label="상담 요청 페이지">
                                <ul class="pagination pagination-sm justify-content-center mb-0">
                                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/counsels?status=' . urlencode($status) . '&page=' . max(1, $currentPage - 1)) ?>">이전</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/counsels?status=' . urlencode($status) . '&page=' . $i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/counsels?status=' . urlencode($status) . '&page=' . min($totalPages, $currentPage + 1)) ?>">다음</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="counsel-card mt-3">
                <div class="counsel-card-head">첨부파일</div>
                <div class="counsel-card-body">
                    <div class="table-responsive">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th>파일명</th>
                                    <th class="col-type">구분</th>
                                    <th class="col-size">용량</th>
                                    <th class="col-action">삭제</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($files)): ?>
                                    <?php foreach ($files as $index => $file): ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <a href="<?= base_url('admin/members/counsel-files/' . (int) $file['file_id'] . '/download') ?>">
                                                    <?= esc($file['original_name'] ?? '-') ?>
                                                </a>
                                            </td>
                                            <td class="text-center"><?= esc($file['file_type'] ?? '-') ?></td>
                                            <td class="text-end"><?= esc($fileSize($file['file_size'] ?? 0)) ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteCounselFile(<?= (int) $file['file_id'] ?>)">X</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">등록된 첨부파일이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function deleteCounselFile(fileId) {
    if (!confirm('해당파일을 삭제하시겠습니까?\n삭제 하신 파일은 복구하실 수 없습니다.')) {
        return;
    }

    fetch('<?= base_url('admin/members/counsel-files/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({
            file_id: fileId
        })
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (json) {
            if (json.status === 'success') {
                alert('파일이 삭제 되었습니다.');
                window.location.reload();
                return;
            }

            alert('파일 삭제에 실패했습니다.');
        })
        .catch(function () {
            alert('파일 삭제에 실패했습니다.');
        });
}
</script>

<?= $this->include('admin/layout/footer') ?>
