<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$word = $word ?? [];
$title = $title ?? '금칙어';
$breadcrumb = $breadcrumb ?? 'Main > 금칙어 관리';
$backUrl = $backUrl ?? base_url('admin/forbidden-words');
$actionUrl = $actionUrl ?? '#';
$buttonLabel = $buttonLabel ?? '저장';
$matchTypes = $matchTypes ?? [];
$scopes = $scopes ?? [];
$matchType = old('match_type', $word['match_type'] ?? 'PARTIAL');
$scope = old('apply_scope', $word['apply_scope'] ?? 'ALL');
$displayStatus = old('display_status', $word['display_status'] ?? 'Y');
$error = session()->getFlashdata('error');
?>

<style>
    .forbidden-admin-page { color: #172033; font-size: 14px; }
    .forbidden-admin-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .forbidden-admin-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .forbidden-admin-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .forbidden-form-card { border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .forbidden-form-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .forbidden-form-body { padding: 16px; }
    .forbidden-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .forbidden-field.full { grid-column: 1 / -1; }
    .forbidden-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .forbidden-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 16px 0 24px; }
    @media (max-width: 768px) {
        .forbidden-grid { grid-template-columns: 1fr; }
        .forbidden-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header forbidden-admin-header forbidden-admin-page">
        <div>
            <div class="screen-id">AMFC006_01</div>
            <h1><?= esc($title) ?></h1>
        </div>
        <div class="text-muted"><?= esc($breadcrumb) ?></div>
    </section>

    <section class="content forbidden-admin-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= esc($actionUrl) ?>" method="post">
                <?= csrf_field() ?>
                <div class="forbidden-form-card">
                    <div class="forbidden-form-head">금칙어 정보</div>
                    <div class="forbidden-form-body forbidden-grid">
                        <div class="forbidden-field full">
                            <label for="keyword">금칙어</label>
                            <input id="keyword" name="keyword" type="text" class="form-control" maxlength="120" value="<?= esc(old('keyword', $word['keyword'] ?? '')) ?>" required>
                        </div>

                        <div class="forbidden-field">
                            <label for="match_type">매칭 방식</label>
                            <select id="match_type" name="match_type" class="form-select">
                                <?php foreach ($matchTypes as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $matchType === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="forbidden-field">
                            <label for="apply_scope">적용 범위</label>
                            <select id="apply_scope" name="apply_scope" class="form-select">
                                <?php foreach ($scopes as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $scope === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="forbidden-field">
                            <label for="display_status">노출 상태</label>
                            <select id="display_status" name="display_status" class="form-select">
                                <option value="Y" <?= $displayStatus === 'Y' ? 'selected' : '' ?>>노출</option>
                                <option value="N" <?= $displayStatus === 'N' ? 'selected' : '' ?>>중지</option>
                            </select>
                        </div>

                        <div class="forbidden-field full">
                            <label for="memo">메모</label>
                            <textarea id="memo" name="memo" class="form-control" rows="5"><?= esc(old('memo', $word['memo'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="forbidden-actions">
                    <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">취소</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= esc($buttonLabel) ?></button>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
