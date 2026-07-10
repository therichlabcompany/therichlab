<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$code = $code ?? [];
$title = $title ?? '코드';
$breadcrumb = $breadcrumb ?? 'Main > 코드관리';
$backUrl = $backUrl ?? base_url('admin/codes');
$actionUrl = $actionUrl ?? '#';
$buttonLabel = $buttonLabel ?? '저장';
$codeGroups = $codeGroups ?? [];
$codeGroup = old('code_group', $code['code_group'] ?? 'INSURANCE');
$displayStatus = old('display_status', $code['display_status'] ?? 'Y');
$error = session()->getFlashdata('error');
?>

<style>
    .code-admin-page { color: #172033; font-size: 14px; }
    .code-admin-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .code-admin-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .code-admin-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .code-form-card { border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .code-form-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .code-form-body { padding: 16px; }
    .code-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .code-field.full { grid-column: 1 / -1; }
    .code-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .code-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 16px 0 24px; }
    @media (max-width: 768px) {
        .code-grid { grid-template-columns: 1fr; }
        .code-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header code-admin-header code-admin-page">
        <div>
            <div class="screen-id">AMFC005_01</div>
            <h1><?= esc($title) ?></h1>
        </div>
        <div class="text-muted"><?= esc($breadcrumb) ?></div>
    </section>

    <section class="content code-admin-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= esc($actionUrl) ?>" method="post">
                <?= csrf_field() ?>
                <div class="code-form-card">
                    <div class="code-form-head">코드 정보</div>
                    <div class="code-form-body code-grid">
                        <div class="code-field">
                            <label for="code_group">구분</label>
                            <select id="code_group" name="code_group" class="form-select" required>
                                <?php foreach ($codeGroups as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $codeGroup === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="code-field">
                            <label for="code_value">코드값</label>
                            <input id="code_value" name="code_value" type="text" class="form-control" maxlength="80" value="<?= esc(old('code_value', $code['code_value'] ?? '')) ?>" required>
                        </div>

                        <div class="code-field full">
                            <label for="code_label">코드명</label>
                            <input id="code_label" name="code_label" type="text" class="form-control" maxlength="120" value="<?= esc(old('code_label', $code['code_label'] ?? '')) ?>" required>
                        </div>

                        <div class="code-field">
                            <label for="parent_code">상위 코드</label>
                            <input id="parent_code" name="parent_code" type="text" class="form-control" maxlength="80" value="<?= esc(old('parent_code', $code['parent_code'] ?? '')) ?>">
                        </div>

                        <div class="code-field">
                            <label for="sort_order">정렬 순서</label>
                            <input id="sort_order" name="sort_order" type="number" class="form-control" value="<?= esc(old('sort_order', $code['sort_order'] ?? 0)) ?>">
                        </div>

                        <div class="code-field">
                            <label for="display_status">노출 상태</label>
                            <select id="display_status" name="display_status" class="form-select">
                                <option value="Y" <?= $displayStatus === 'Y' ? 'selected' : '' ?>>노출</option>
                                <option value="N" <?= $displayStatus === 'N' ? 'selected' : '' ?>>중지</option>
                            </select>
                        </div>

                        <div class="code-field full">
                            <label for="description">설명</label>
                            <textarea id="description" name="description" class="form-control" rows="5"><?= esc(old('description', $code['description'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="code-actions">
                    <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">취소</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= esc($buttonLabel) ?></button>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
