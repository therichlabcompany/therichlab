<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$ad = $ad ?? [];
$kind = $kind ?? 'top';
$hasBannerImage = (bool) ($hasBannerImage ?? false);
?>

<style>
    .banner-edit-page { color: #172033; font-size: 14px; }
    .banner-edit-head { padding: 18px 8px 14px; }
    .banner-edit-head h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .banner-edit-card { max-width: 860px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .banner-edit-card-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .banner-edit-card-body { padding: 16px; }
    .banner-edit-field + .banner-edit-field { margin-top: 18px; }
    .banner-edit-field > label { display: block; margin-bottom: 7px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .banner-edit-preview { display: block; width: 100%; max-width: 720px; margin-top: 10px; border: 1px solid #d8e0ea; border-radius: 6px; }
    .banner-edit-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 22px; }
</style>

<div class="content-wrapper">
    <section class="content-header banner-edit-head banner-edit-page"><div class="container-fluid">
        <h1><?= esc($title ?? '배너 광고 수정') ?></h1>
        <div class="text-muted mt-1">Main &gt; 광고 관리 &gt; <?= $kind === 'top' ? '상단 배너 광고' : '하단 배너 광고' ?> &gt; 수정</div>
    </div></section>
    <section class="content banner-edit-page"><div class="container-fluid">
        <?php if (session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>
        <form action="<?= base_url('admin/ads/' . $kind . '/' . (int) ($ad['id'] ?? 0) . '/edit') ?>" method="post" enctype="multipart/form-data" class="banner-edit-card">
            <?= csrf_field() ?>
            <div class="banner-edit-card-head">배너 광고 #<?= (int) ($ad['id'] ?? 0) ?></div>
            <div class="banner-edit-card-body">
                <div class="banner-edit-field"><label>광고 기간</label><div class="form-control bg-light"><?= esc(($ad['start_date'] ?? '-') . ' ~ ' . ($ad['end_date'] ?? '-')) ?></div></div>
                <div class="banner-edit-field">
                    <label for="banner_image">배너 이미지 (권장 964 × 180px)</label>
                    <input id="banner_image" name="banner_image" type="file" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" <?= $hasBannerImage ? '' : 'required' ?>>
                    <small class="text-muted d-block mt-1"><?= $hasBannerImage ? '새 이미지를 선택하면 기존 이미지를 교체합니다.' : '현재 공개 가능한 이미지가 없습니다. 메인 노출을 위해 이미지를 등록해주세요.' ?></small>
                    <?php if ($hasBannerImage): ?><img class="banner-edit-preview" src="<?= base_url('ad/banner/' . (int) ($ad['id'] ?? 0)) ?>" alt="현재 배너 이미지"><?php endif; ?>
                </div>
                <div class="banner-edit-field"><label for="banner_link_url">배너 링크 URL</label><input id="banner_link_url" name="banner_link_url" type="url" class="form-control" value="<?= esc(old('banner_link_url', $ad['banner_link_url'] ?? '')) ?>" placeholder="https://"></div>
                <div class="banner-edit-actions"><a href="<?= base_url('admin/ads/' . $kind) ?>" class="btn btn-outline-secondary">취소</a><button type="submit" class="btn btn-primary">배너 저장</button></div>
            </div>
        </form>
    </div></section>
</div>

<?= $this->include('admin/layout/footer') ?>
