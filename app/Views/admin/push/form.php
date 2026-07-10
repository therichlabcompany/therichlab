<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$push = $push ?? [];
$title = $title ?? '앱푸쉬';
$breadcrumb = $breadcrumb ?? 'Main > 알림 관리 > 앱푸쉬';
$backUrl = $backUrl ?? base_url('admin/pushes');
$actionUrl = $actionUrl ?? '#';
$buttonLabel = $buttonLabel ?? '저장';
$targetType = old('target_type', $push['target_type'] ?? 'ALL');
$sendType = old('send_type', $push['send_type'] ?? 'NOW');
$error = session()->getFlashdata('error');
?>

<style>
    .push-admin-page { color: #172033; font-size: 14px; }
    .push-admin-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .push-admin-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .push-admin-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .push-form-card { border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .push-form-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .push-form-body { padding: 16px; }
    .push-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .push-field.full { grid-column: 1 / -1; }
    .push-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .push-preview { margin-top: 10px; max-width: 220px; }
    .push-preview img { width: 100%; display: block; border-radius: 8px; border: 1px solid #d8e0ea; }
    .push-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 16px 0 24px; }
    @media (max-width: 768px) {
        .push-grid { grid-template-columns: 1fr; }
        .push-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header push-admin-header push-admin-page">
        <div>
            <div class="screen-id">AMFC002_02</div>
            <h1><?= esc($title) ?></h1>
        </div>
        <div class="text-muted"><?= esc($breadcrumb) ?></div>
    </section>

    <section class="content push-admin-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= esc($actionUrl) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="push-form-card">
                    <div class="push-form-head">앱푸쉬 정보</div>
                    <div class="push-form-body push-grid">
                        <div class="push-field full">
                            <label for="title">푸시 제목</label>
                            <input id="title" name="title" type="text" class="form-control" value="<?= esc(old('title', $push['title'] ?? '')) ?>" required>
                        </div>

                        <div class="push-field full">
                            <label for="body">푸시 내용</label>
                            <textarea id="body" name="body" class="form-control" rows="5" required><?= esc(old('body', $push['body'] ?? '')) ?></textarea>
                        </div>

                        <div class="push-field full">
                            <label for="push_image">푸시 이미지</label>
                            <input id="push_image" name="push_image" type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                            <div class="push-preview" style="display:none;">
                                <img id="pushImagePreview" src="" alt="">
                            </div>
                        </div>

                        <div class="push-field full">
                            <label for="click_url">클릭 URL</label>
                            <input id="click_url" name="click_url" type="text" class="form-control" value="<?= esc(old('click_url', $push['click_url'] ?? '')) ?>" placeholder="https://...">
                        </div>

                        <div class="push-field">
                            <label for="target_type">발송 대상</label>
                            <select id="target_type" name="target_type" class="form-select">
                                <option value="ALL" <?= $targetType === 'ALL' ? 'selected' : '' ?>>전체회원</option>
                                <option value="USER" <?= $targetType === 'USER' ? 'selected' : '' ?>>개인회원</option>
                                <option value="FC" <?= $targetType === 'FC' ? 'selected' : '' ?>>FC회원</option>
                            </select>
                        </div>

                        <div class="push-field">
                            <label for="send_type">발송 유형</label>
                            <select id="send_type" name="send_type" class="form-select">
                                <option value="NOW" <?= $sendType === 'NOW' ? 'selected' : '' ?>>즉시 발송</option>
                                <option value="RESERVED" <?= $sendType === 'RESERVED' ? 'selected' : '' ?>>예약 발송</option>
                            </select>
                        </div>

                        <div class="push-field">
                            <label for="scheduled_at">예약 발송일시</label>
                            <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="form-control" value="<?= esc(old('scheduled_at')) ?>">
                        </div>
                    </div>
                </div>

                <div class="push-actions">
                    <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">취소</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= esc($buttonLabel) ?></button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
(function () {
    const input = document.getElementById('push_image');
    const previewWrap = document.querySelector('.push-preview');
    const preview = document.getElementById('pushImagePreview');

    if (input && previewWrap && preview) {
        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) {
                return;
            }

            preview.src = URL.createObjectURL(file);
            previewWrap.style.display = 'block';
        });
    }

    const sendType = document.getElementById('send_type');
    const scheduledAt = document.getElementById('scheduled_at');

    function syncScheduleRequired() {
        scheduledAt.required = sendType.value === 'RESERVED';
    }

    if (sendType && scheduledAt) {
        sendType.addEventListener('change', syncScheduleRequired);
        syncScheduleRequired();
    }
})();
</script>

<?= $this->include('admin/layout/footer') ?>
