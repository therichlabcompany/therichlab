<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$error = session()->getFlashdata('error');
$status = old('status', 'ACTIVE');
$phoneVerified = old('phone_verified', 'N');
$reviewStatus = old('fc_review_status', 'WAIT');
?>

<style>
    .fc-create-page { color: #172033; font-size: 14px; }
    .fc-create-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .fc-create-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .fc-create-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .create-card { margin-bottom: 16px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .create-card-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .create-card-body { padding: 16px; }
    .create-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .create-field.full { grid-column: 1 / -1; }
    .create-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .create-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 16px 0 24px; }
    @media (max-width: 768px) {
        .create-grid { grid-template-columns: 1fr; }
        .create-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header fc-create-header fc-create-page">
        <div>
            <div class="screen-id">AMFC003_01</div>
            <h1>FC 회원 신규 등록</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; FC회원 &gt; 신규 등록</div>
    </section>

    <section class="content fc-create-page">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('admin/fc-members/create') ?>" method="post" enctype="multipart/form-data">
                <div class="create-card">
                    <div class="create-card-head">기본정보</div>
                    <div class="create-card-body create-grid">
                        <div class="create-field">
                            <label for="email">이메일</label>
                            <input id="email" name="email" type="email" class="form-control" value="<?= esc(old('email')) ?>" required>
                        </div>
                        <div class="create-field">
                            <label for="name">이름</label>
                            <input id="name" name="name" type="text" class="form-control" value="<?= esc(old('name')) ?>" required>
                        </div>
                        <div class="create-field">
                            <label for="password">비밀번호</label>
                            <input id="password" name="password" type="password" class="form-control" required>
                        </div>
                        <div class="create-field">
                            <label for="password_confirm">비밀번호 확인</label>
                            <input id="password_confirm" name="password_confirm" type="password" class="form-control" required>
                        </div>
                        <div class="create-field">
                            <label for="phone">휴대폰 번호</label>
                            <input id="phone" name="phone" type="text" class="form-control" value="<?= esc(old('phone')) ?>" required>
                        </div>
                        <div class="create-field">
                            <label for="phone_verified">휴대폰 인증여부</label>
                            <select id="phone_verified" name="phone_verified" class="form-select">
                                <option value="Y" <?= $phoneVerified === 'Y' ? 'selected' : '' ?>>인증</option>
                                <option value="N" <?= $phoneVerified === 'N' ? 'selected' : '' ?>>미인증</option>
                            </select>
                        </div>
                        <div class="create-field">
                            <label for="status">회원상태</label>
                            <select id="status" name="status" class="form-select">
                                <option value="ACTIVE" <?= $status === 'ACTIVE' ? 'selected' : '' ?>>정상</option>
                                <option value="WAIT" <?= $status === 'WAIT' ? 'selected' : '' ?>>대기</option>
                                <option value="BLOCK" <?= $status === 'BLOCK' ? 'selected' : '' ?>>차단</option>
                                <option value="LEAVE" <?= $status === 'LEAVE' ? 'selected' : '' ?>>탈퇴</option>
                            </select>
                        </div>
                        <div class="create-field">
                            <label for="fc_review_status">심사상태</label>
                            <select id="fc_review_status" name="fc_review_status" class="form-select">
                                <option value="WAIT" <?= $reviewStatus === 'WAIT' ? 'selected' : '' ?>>승인 대기</option>
                                <option value="APPROVE" <?= $reviewStatus === 'APPROVE' ? 'selected' : '' ?>>승인</option>
                                <option value="REJECT" <?= $reviewStatus === 'REJECT' ? 'selected' : '' ?>>거절</option>
                            </select>
                        </div>
                        <div class="create-field full">
                            <div class="form-check">
                                <input id="agree_marketing" name="agree_marketing" value="1" type="checkbox" class="form-check-input" <?= old('agree_marketing') ? 'checked' : '' ?>>
                                <label for="agree_marketing" class="form-check-label">마케팅 수신 동의</label>
                            </div>
                        </div>
                        <div class="create-field full">
                            <label for="admin_memo">운영진 메모</label>
                            <textarea id="admin_memo" name="admin_memo" class="form-control" rows="4"><?= esc(old('admin_memo')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="create-card">
                    <div class="create-card-head">프로필</div>
                    <div class="create-card-body create-grid">
                        <div class="create-field">
                            <label for="profile_image">프로필 이미지</label>
                            <input id="profile_image" name="profile_image" type="file" class="form-control" accept="image/*">
                        </div>
                        <div class="create-field">
                            <label for="company">소속 원수사</label>
                            <input id="company" name="company" type="text" class="form-control" value="<?= esc(old('company')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="company_sub">추가 보험사</label>
                            <input id="company_sub" name="company_sub" type="text" class="form-control" value="<?= esc(old('company_sub')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="ga">소속 GA</label>
                            <input id="ga" name="ga" type="text" class="form-control" value="<?= esc(old('ga')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="position">직책</label>
                            <input id="position" name="position" type="text" class="form-control" value="<?= esc(old('position')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="license_date">보험 자격 취득일</label>
                            <input id="license_date" name="license_date" type="text" class="form-control" value="<?= esc(old('license_date')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="license_no">등록번호</label>
                            <input id="license_no" name="license_no" type="text" class="form-control" value="<?= esc(old('license_no')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="time_from">상담 시작 시간</label>
                            <input id="time_from" name="time_from" type="number" min="0" max="23" class="form-control" value="<?= esc(old('time_from')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="time_to">상담 종료 시간</label>
                            <input id="time_to" name="time_to" type="number" min="0" max="23" class="form-control" value="<?= esc(old('time_to')) ?>">
                        </div>
                        <div class="create-field full">
                            <label for="language">상담 가능 언어</label>
                            <input id="language" name="language" type="text" class="form-control" value="<?= esc(old('language')) ?>">
                        </div>
                    </div>
                </div>

                <div class="create-card">
                    <div class="create-card-head">활동정보</div>
                    <div class="create-card-body create-grid">
                        <div class="create-field">
                            <label for="region">활동 지역</label>
                            <input id="region" name="region" type="text" class="form-control" value="<?= esc(old('region')) ?>">
                        </div>
                        <div class="create-field">
                            <label for="insurance_types">보험 항목</label>
                            <input id="insurance_types" name="insurance_types" type="text" class="form-control" value="<?= esc(old('insurance_types')) ?>">
                        </div>
                        <div class="create-field full">
                            <label for="hero_line">한 줄 히어로</label>
                            <input id="hero_line" name="hero_line" type="text" class="form-control" value="<?= esc(old('hero_line')) ?>">
                        </div>
                        <div class="create-field full">
                            <label for="intro">자기소개</label>
                            <textarea id="intro" name="intro" class="form-control" rows="4"><?= esc(old('intro')) ?></textarea>
                        </div>
                        <div class="create-field full">
                            <label for="career">경력사항</label>
                            <textarea id="career" name="career" class="form-control" rows="4"><?= esc(old('career')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="create-actions">
                    <a href="<?= base_url('admin/fc-members') ?>" class="btn btn-outline-secondary">취소</a>
                    <button type="submit" class="btn btn-primary">등록</button>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
