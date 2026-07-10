<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$account = $account ?? [];
$title = $title ?? '관리자 계정';
$breadcrumb = $breadcrumb ?? 'Main > 계정관리';
$backUrl = $backUrl ?? base_url('admin/accounts');
$actionUrl = $actionUrl ?? '#';
$buttonLabel = $buttonLabel ?? '저장';
$passwordRequired = (bool) ($passwordRequired ?? false);
$roleOptions = $roleOptions ?? [];
$role = old('role', $account['role'] ?? 'admin');
$status = old('status', $account['status'] ?? 'Y');
$error = session()->getFlashdata('error');
?>

<style>
    .account-admin-page { color: #172033; font-size: 14px; }
    .account-admin-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .account-admin-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .account-admin-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .account-form-card { border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .account-form-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .account-form-body { padding: 16px; }
    .account-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .account-field.full { grid-column: 1 / -1; }
    .account-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .account-help { margin-top: 6px; color: #64748b; font-size: 12px; }
    .account-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 16px 0 24px; }
    @media (max-width: 768px) {
        .account-grid { grid-template-columns: 1fr; }
        .account-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header account-admin-header account-admin-page">
        <div>
            <div class="screen-id">AMFC004_01</div>
            <h1><?= esc($title) ?></h1>
        </div>
        <div class="text-muted"><?= esc($breadcrumb) ?></div>
    </section>

    <section class="content account-admin-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= esc($actionUrl) ?>" method="post">
                <?= csrf_field() ?>
                <div class="account-form-card">
                    <div class="account-form-head">계정 정보</div>
                    <div class="account-form-body account-grid">
                        <div class="account-field">
                            <label for="username">ID</label>
                            <input id="username" name="username" type="text" class="form-control" maxlength="50" value="<?= esc(old('username', $account['username'] ?? '')) ?>" <?= !empty($account) ? 'readonly' : 'required' ?>>
                        </div>

                        <div class="account-field">
                            <label for="password">비밀번호<?= $passwordRequired ? '' : ' 변경' ?></label>
                            <input id="password" name="password" type="password" class="form-control" minlength="8" autocomplete="new-password" <?= $passwordRequired ? 'required' : '' ?>>
                            <?php if (!$passwordRequired): ?>
                                <div class="account-help">변경하지 않으려면 비워두세요.</div>
                            <?php endif; ?>
                        </div>

                        <div class="account-field">
                            <label for="name">이름</label>
                            <input id="name" name="name" type="text" class="form-control" maxlength="100" value="<?= esc(old('name', $account['name'] ?? '')) ?>" required>
                        </div>

                        <div class="account-field">
                            <label for="email">이메일</label>
                            <input id="email" name="email" type="email" class="form-control" maxlength="100" value="<?= esc(old('email', $account['email'] ?? '')) ?>">
                        </div>

                        <div class="account-field">
                            <label for="phone">휴대폰번호</label>
                            <input id="phone" name="phone" type="text" class="form-control" maxlength="20" value="<?= esc(old('phone', $account['phone'] ?? '')) ?>">
                        </div>

                        <div class="account-field">
                            <label for="role">권한</label>
                            <select id="role" name="role" class="form-select">
                                <?php foreach ($roleOptions as $value => $label): ?>
                                    <option value="<?= esc($value) ?>" <?= $role === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="account-field">
                            <label for="status">가입 상태</label>
                            <select id="status" name="status" class="form-select">
                                <option value="Y" <?= $status === 'Y' ? 'selected' : '' ?>>정상</option>
                                <option value="N" <?= $status === 'N' ? 'selected' : '' ?>>중지</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="account-actions">
                    <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">취소</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= esc($buttonLabel) ?></button>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
