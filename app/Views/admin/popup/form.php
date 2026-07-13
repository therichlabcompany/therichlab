<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$popup = $popup ?? [];
$title = $title ?? '팝업';
$breadcrumb = $breadcrumb ?? 'Main > 알림 관리 > 팝업관리';
$backUrl = $backUrl ?? base_url('admin/popups');
$actionUrl = $actionUrl ?? '#';
$buttonLabel = $buttonLabel ?? '저장';
$imageRequired = (bool) ($imageRequired ?? false);
$error = session()->getFlashdata('error');

$dtValue = static function (string $value): string {
    if ($value === '') {
        return '';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
};

$displayStatus = old('display_status', $popup['display_status'] ?? 'Y');
$linkTarget = old('link_target', $popup['link_target'] ?? '_self');
?>

<style>
    .popup-admin-page {
        color: #172033;
        font-size: 14px;
    }

    .popup-admin-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .popup-admin-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .popup-admin-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .popup-form-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
    }

    .popup-form-head {
        padding: 12px 16px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .popup-form-body {
        padding: 16px;
    }

    .popup-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .popup-field.full {
        grid-column: 1 / -1;
    }

    .popup-field label {
        display: block;
        margin-bottom: 6px;
        color: #4b586b;
        font-size: 13px;
        font-weight: 800;
    }

    .popup-preview {
        margin-top: 10px;
        max-width: 220px;
    }

    .popup-preview img {
        width: 100%;
        display: block;
        border-radius: 8px;
        border: 1px solid #d8e0ea;
    }

    .popup-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin: 16px 0 24px;
    }

    @media (max-width: 768px) {
        .popup-grid {
            grid-template-columns: 1fr;
        }

        .popup-field.full {
            grid-column: auto;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header popup-admin-header popup-admin-page">
        <div>
            <div class="screen-id">AMFC002_01</div>
            <h1><?= esc($title) ?></h1>
        </div>
        <div class="text-muted"><?= esc($breadcrumb) ?></div>
    </section>

    <section class="content popup-admin-page">
        <div class="container-fluid">
            <div class="mb-3">
                <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">리스트로 돌아가기</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= esc($actionUrl) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="popup-form-card">
                    <div class="popup-form-head">팝업 정보</div>
                    <div class="popup-form-body popup-grid">
                        <div class="popup-field full">
                            <label for="title">팝업 제목</label>
                            <input id="title" name="title" type="text" class="form-control" value="<?= esc(old('title', $popup['title'] ?? '')) ?>" required>
                        </div>

                        <div class="popup-field full">
                            <label for="popup_image">팝업 이미지<?= $imageRequired ? ' *' : '' ?></label>
                            <input id="popup_image" name="popup_image" type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,image/*" <?= $imageRequired ? 'required' : '' ?>>
                            <?php if (!empty($popup['image_path'])): ?>
                                <div class="popup-preview">
                                    <img id="popupImagePreview" src="<?= esc(base_url(ltrim($popup['image_path'], '/'))) ?>" alt="">
                                </div>
                            <?php else: ?>
                                <div class="popup-preview" style="display:none;">
                                    <img id="popupImagePreview" src="" alt="">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="popup-field">
                            <label for="start_at">노출 시작일</label>
                            <input id="start_at" name="start_at" type="datetime-local" class="form-control" value="<?= esc(old('start_at', $dtValue($popup['start_at'] ?? ''))) ?>" required>
                        </div>

                        <div class="popup-field">
                            <label for="end_at">노출 종료일</label>
                            <input id="end_at" name="end_at" type="datetime-local" class="form-control" value="<?= esc(old('end_at', $dtValue($popup['end_at'] ?? ''))) ?>" required>
                        </div>

                        <div class="popup-field">
                            <label for="display_status">노출 여부</label>
                            <select id="display_status" name="display_status" class="form-select">
                                <option value="Y" <?= $displayStatus === 'Y' ? 'selected' : '' ?>>노출</option>
                                <option value="N" <?= $displayStatus === 'N' ? 'selected' : '' ?>>비노출</option>
                            </select>
                        </div>

                        <div class="popup-field">
                            <label for="sort_order">정렬 순서</label>
                            <input id="sort_order" name="sort_order" type="number" class="form-control" value="<?= esc(old('sort_order', $popup['sort_order'] ?? 0)) ?>">
                        </div>

                        <div class="popup-field">
                            <label for="link_url">링크 URL</label>
                            <input id="link_url" name="link_url" type="text" class="form-control" value="<?= esc(old('link_url', $popup['link_url'] ?? '')) ?>" placeholder="https://...">
                        </div>

                        <div class="popup-field">
                            <label for="link_target">링크 열기 방식</label>
                            <select id="link_target" name="link_target" class="form-select">
                                <option value="_self" <?= $linkTarget === '_self' ? 'selected' : '' ?>>현재 창</option>
                                <option value="_blank" <?= $linkTarget === '_blank' ? 'selected' : '' ?>>새 창</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="popup-actions">
                    <a href="<?= esc($backUrl) ?>" class="btn btn-outline-secondary btn-sm">취소</a>
                    <button type="submit" class="btn btn-primary btn-sm"><?= esc($buttonLabel) ?></button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
(function () {
    const input = document.getElementById('popup_image');
    const previewWrap = document.querySelector('.popup-preview');
    const preview = document.getElementById('popupImagePreview');

    if (!input || !previewWrap || !preview) {
        return;
    }

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        previewWrap.style.display = 'block';
    });
})();
</script>

<?= $this->include('admin/layout/footer') ?>
