<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$gender = old('gender', $m['gender'] ?? '');
$status = old('status', $m['status'] ?? 'ACTIVE');
$phoneVerified = old('phone_verified', $m['phone_verified'] ?? 'N');
$error = session()->getFlashdata('error');
?>

<style>
    .member-edit-page {
        color: #172033;
        font-size: 14px;
    }

    .member-edit-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .member-edit-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .member-edit-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .edit-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .edit-card-head {
        padding: 12px 16px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .edit-card-body {
        padding: 16px;
    }

    .edit-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .edit-field.full {
        grid-column: 1 / -1;
    }

    .edit-field label {
        display: block;
        margin-bottom: 6px;
        color: #4b586b;
        font-size: 13px;
        font-weight: 800;
    }

    .edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 16px;
    }

    @media (max-width: 768px) {
        .edit-grid {
            grid-template-columns: 1fr;
        }

        .edit-field.full {
            grid-column: auto;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header member-edit-header member-edit-page">
        <div>
            <div class="screen-id">AMFC003_02</div>
            <h1>개인회원 수정</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; 개인회원 &gt; 수정</div>
    </section>

    <section class="content member-edit-page">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('admin/members/' . (int) $m['member_id'] . '/update') ?>" method="post" class="edit-card">
                <div class="edit-card-head">회원 기본정보</div>
                <div class="edit-card-body">
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label>회원번호</label>
                            <input type="text" class="form-control" value="<?= esc($m['member_id']) ?>" readonly>
                        </div>
                        <div class="edit-field">
                            <label>이메일</label>
                            <input type="email" class="form-control" value="<?= esc($m['email']) ?>" readonly>
                        </div>
                        <div class="edit-field">
                            <label for="name">이름</label>
                            <input id="name" name="name" type="text" class="form-control" value="<?= esc(old('name', $m['name'] ?? '')) ?>" required>
                        </div>
                        <div class="edit-field">
                            <label for="phone">휴대폰 번호</label>
                            <input id="phone" name="phone" type="text" class="form-control" value="<?= esc(old('phone', $m['phone'] ?? '')) ?>" required>
                        </div>
                        <div class="edit-field">
                            <label for="phone_verified">휴대폰 인증여부</label>
                            <select id="phone_verified" name="phone_verified" class="form-select">
                                <option value="Y" <?= $phoneVerified === 'Y' ? 'selected' : '' ?>>인증</option>
                                <option value="N" <?= $phoneVerified === 'N' ? 'selected' : '' ?>>미인증</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label for="status">회원상태</label>
                            <select id="status" name="status" class="form-select">
                                <option value="ACTIVE" <?= $status === 'ACTIVE' ? 'selected' : '' ?>>정상</option>
                                <option value="WAIT" <?= $status === 'WAIT' ? 'selected' : '' ?>>대기</option>
                                <option value="BLOCK" <?= $status === 'BLOCK' ? 'selected' : '' ?>>차단</option>
                                <option value="LEAVE" <?= $status === 'LEAVE' ? 'selected' : '' ?>>탈퇴</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label for="birth">생년월일</label>
                            <input id="birth" name="birth" type="text" class="form-control" maxlength="8" value="<?= esc(old('birth', $m['birth'] ?? '')) ?>" placeholder="YYYYMMDD">
                        </div>
                        <div class="edit-field">
                            <label for="gender">성별</label>
                            <select id="gender" name="gender" class="form-select">
                                <option value="" <?= $gender === '' ? 'selected' : '' ?>>선택 안함</option>
                                <option value="M" <?= $gender === 'M' ? 'selected' : '' ?>>남성</option>
                                <option value="F" <?= $gender === 'F' ? 'selected' : '' ?>>여성</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label for="nickname">닉네임</label>
                            <input id="nickname" name="nickname" type="text" class="form-control" value="<?= esc(old('nickname', $m['nickname'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label>회원 UID</label>
                            <input type="text" class="form-control" value="<?= esc($m['member_uid']) ?>" readonly>
                        </div>
                        <div class="edit-field full">
                            <div class="form-check">
                                <input id="agree_marketing" name="agree_marketing" value="1" type="checkbox" class="form-check-input" <?= old('agree_marketing', $m['agree_marketing'] ?? 0) ? 'checked' : '' ?>>
                                <label for="agree_marketing" class="form-check-label">마케팅 수신 동의</label>
                            </div>
                        </div>
                        <div class="edit-field full">
                            <label for="admin_memo">운영진 메모</label>
                            <textarea id="admin_memo" name="admin_memo" class="form-control" rows="6"><?= esc(old('admin_memo', $m['admin_memo'] ?? '')) ?></textarea>
                        </div>
                    </div>

                    <div class="edit-actions">
                        <a href="<?= base_url('admin/members/' . (int) $m['member_id']) ?>" class="btn btn-outline-secondary">취소</a>
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
