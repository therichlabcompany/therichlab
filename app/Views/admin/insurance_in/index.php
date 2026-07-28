<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$questions = $questions ?? [];
$page = (int) ($page ?? 1);
$totalPages = (int) ($totalPages ?? 1);
$queryBase = array_filter([
    'q' => $q ?? '', 'start_date' => $startDate ?? '', 'end_date' => $endDate ?? '',
], static fn($value) => (string) $value !== '');
?>

<style>
.insurance-admin { padding:18px 8px 30px; color:#172033; }
.insurance-admin h1 { margin:0; font-size:24px; font-weight:800; }
.insurance-admin .breadcrumb-text { margin-top:6px; color:#6b7280; font-size:13px; }
.insurance-admin-toolbar { display:flex; align-items:center; gap:10px; margin:18px 0 10px; }
.insurance-admin-card { border:1px solid #e5e7eb; border-radius:8px; background:#fff; overflow:hidden; }
.insurance-admin-card-head { display:flex; justify-content:space-between; padding:14px 16px; border-bottom:1px solid #e5e7eb; font-weight:800; }
.insurance-admin .table th, .insurance-admin .table td { padding:15px 16px; vertical-align:middle; }
.insurance-admin .table th { white-space:nowrap; }
.insurance-admin .table-responsive { width:100%; max-width:100%; overflow-x:hidden !important; }
.insurance-admin-table { width:100% !important; max-width:100% !important; min-width:0 !important; table-layout:fixed; }
.insurance-admin-table th, .insurance-admin-table td { min-width:0; overflow:hidden; }
.insurance-admin-table .question-title { display:block !important; box-sizing:border-box; width:100% !important; max-width:none !important; color:#0d6efd; font-weight:700; text-decoration:none; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.insurance-admin-table .question-excerpt { display:block; width:100%; max-width:none; margin-top:4px; color:#64748b; font-size:12px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.insurance-admin-table .writer-cell { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.insurance-admin-table .manage-cell > .d-flex { flex-wrap:wrap; }
.insurance-admin .pagination { margin:16px 0 0; justify-content:flex-end; }
</style>

<div class="content-wrapper"><section class="content-header insurance-admin"><div class="container-fluid">
    <h1>보험IN 관리</h1><div class="breadcrumb-text"><?= esc($breadcrumb ?? '') ?></div>
    <?php if (session('success') || session('error')): ?><div class="alert <?= session('error') ? 'alert-danger' : 'alert-success' ?> mt-3"><?= esc(session('error') ?: session('success')) ?></div><?php endif; ?>
    <div class="insurance-admin-toolbar"><strong>보험IN 게시물 <?= number_format((int) ($count ?? 0)) ?></strong></div>
    <div class="admin-search-card"><form method="get" action="<?= base_url('admin/contents/insurance-in') ?>" class="admin-search-form">
        <label class="admin-search-field admin-search-field--date"><span>시작일</span><input type="date" name="start_date" value="<?= esc($startDate ?? '') ?>" class="form-control form-control-sm"></label>
        <label class="admin-search-field admin-search-field--date"><span>종료일</span><input type="date" name="end_date" value="<?= esc($endDate ?? '') ?>" class="form-control form-control-sm"></label>
        <label class="admin-search-field admin-search-field--keyword"><span>검색어</span><input type="text" name="q" value="<?= esc($q ?? '') ?>" class="form-control form-control-sm" placeholder="제목, 내용, 작성자명, 이메일 검색"></label>
        <button class="btn btn-primary btn-sm admin-search-button">검색</button><a href="<?= base_url('admin/contents/insurance-in') ?>" class="btn btn-outline-secondary btn-sm admin-search-button">초기화</a>
    </form></div>
    <div class="insurance-admin-card">
        <div class="insurance-admin-card-head"><span>보험IN 게시물 목록</span><span>20개 노출</span></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0 insurance-admin-table"><colgroup>
            <col style="width:6%"><col style="width:34%"><col style="width:18%"><col style="width:7%"><col style="width:7%"><col style="width:14%"><col style="width:14%">
        </colgroup><thead><tr>
            <th>번호</th><th>게시물</th><th>작성자</th><th>답변</th><th>조회수</th><th>작성일</th><th>관리</th>
        </tr></thead><tbody>
        <?php foreach ($questions as $row): ?><tr>
            <td><?= (int) $row['question_id'] ?></td>
            <td><a class="question-title" href="<?= base_url('admin/contents/insurance-in/' . (int) $row['question_id']) ?>"><?= esc($row['title'] ?? '-') ?></a><span class="question-excerpt"><?= esc($row['body'] ?? '') ?></span></td>
            <td class="writer-cell"><?= esc(($row['writer_name'] ?? '-') . ' (' . ($row['writer_email'] ?? '-') . ')') ?></td>
            <td><?= number_format((int) ($row['answer_count'] ?? 0)) ?>건</td><td><?= number_format((int) ($row['view_count'] ?? 0)) ?></td><td><?= esc($row['created_at'] ?? '-') ?></td>
            <td class="manage-cell"><div class="d-flex gap-1"><a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/contents/insurance-in/' . (int) $row['question_id']) ?>">상세</a><form method="post" action="<?= base_url('admin/contents/insurance-in/' . (int) $row['question_id'] . '/delete') ?>" onsubmit="return confirm('게시물과 연결된 답변이 사용자 페이지에서 모두 숨겨집니다. 삭제하시겠습니까?')"><?= csrf_field() ?><button class="btn btn-outline-danger btn-sm">삭제</button></form></div></td>
        </tr><?php endforeach; ?>
        <?php if (!$questions): ?><tr><td colspan="7" class="text-center text-muted py-5">표시할 보험IN 게시물이 없습니다.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <?php if ($totalPages > 1): ?><nav><ul class="pagination pagination-sm">
        <?php for ($i=1; $i<=$totalPages; $i++): $url = base_url('admin/contents/insurance-in') . '?' . http_build_query(array_merge($queryBase, ['page'=>$i])); ?><li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= esc($url) ?>"><?= $i ?></a></li><?php endfor; ?>
    </ul></nav><?php endif; ?>
</div></section></div>
<?= $this->include('admin/layout/footer') ?>
