<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$term = $term ?? [];
$title = $title ?? '약관';
$breadcrumb = $breadcrumb ?? 'Main > 약관 관리';
$backUrl = $backUrl ?? base_url('admin/terms');
$actionUrl = $actionUrl ?? '#';
$buttonLabel = $buttonLabel ?? '저장';
$termTypes = $termTypes ?? [];
$termType = old('term_type', $term['term_type'] ?? 'TERMS');
$displayStatus = old('display_status', $term['display_status'] ?? 'Y');
$error = session()->getFlashdata('error');
?>

<style>
    .terms-admin-page { color: #172033; font-size: 14px; }
    .terms-admin-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .terms-admin-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .terms-admin-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .terms-form-card { border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .terms-form-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .terms-form-body { padding: 16px; }
    .terms-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .terms-field.full { grid-column: 1 / -1; }
    .terms-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .terms-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 16px 0 24px; }
    @media (max-width: 768px) {
        .terms-grid { grid-template-columns: 1fr; }
        .terms-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header terms-admin-header terms-admin-page">
        <div>
            <div class="screen-id">AMFC003_01</div>
            <h1><?= esc($title) ?></h1>
        </div>
        <div class="text-muted"><?= esc($breadcrumb) ?></div>
    </section>

    <section class="content terms-admin-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= esc($actionUrl) ?>" method="post">
                <?= csrf_field() ?>
                <div class="terms-form-card">
                    <div class="terms-form-head">약관 정보</div>
                    <div class="terms-form-body terms-grid">
                        <div class="terms-field">
                            <label for="term_type">약관 타입</label>
                            <select id="term_type" name="term_type" class="form-select" required>
                                <?php foreach ($termTypes as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $termType === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="terms-field">
                            <label for="version">버전</label>
                            <input id="version" name="version" type="text" class="form-control" maxlength="50" value="<?= esc(old('version', $term['version'] ?? '')) ?>" required>
                        </div>

                        <div class="terms-field full">
                            <label for="title">제목</label>
                            <input id="title" name="title" type="text" class="form-control" maxlength="200" value="<?= esc(old('title', $term['title'] ?? '')) ?>" required>
                        </div>

                        <div class="terms-field">
                            <label for="display_status">노출 상태</label>
                            <select id="display_status" name="display_status" class="form-select">
                                <option value="Y" <?= $displayStatus === 'Y' ? 'selected' : '' ?>>노출</option>
                                <option value="N" <?= $displayStatus === 'N' ? 'selected' : '' ?>>중지</option>
                            </select>
                        </div>

                        <?php if (!empty($term['created_by'])): ?>
                            <div class="terms-field">
                                <label>등록자</label>
                                <input type="text" class="form-control" value="<?= esc($term['created_by']) ?>" disabled>
                            </div>
                        <?php endif; ?>

                        <div class="terms-field full">
                            <label for="content">내용</label>
                            <textarea id="content" name="content" class="form-control" rows="18" required><?= esc(old('content', $term['content'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="terms-actions">
                    <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">취소</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= esc($buttonLabel) ?></button>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
