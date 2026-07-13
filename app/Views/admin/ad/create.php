<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$kind = $kind ?? 'normal';
$isBanner = in_array($kind, ['top', 'bottom'], true);
$status = old('status', 'apply');
?>

<style>
    .ad-create-page { color: #172033; font-size: 14px; }
    .ad-create-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .ad-create-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .ad-create-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .edit-card { border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .edit-card-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .edit-card-body { padding: 16px; }
    .edit-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .edit-field.full { grid-column: 1 / -1; }
    .edit-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .edit-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }
    @media (max-width: 768px) {
        .edit-grid { grid-template-columns: 1fr; }
        .edit-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header ad-create-header ad-create-page">
        <div>
            <div class="screen-id">AD_CREATE</div>
            <h1><?= esc($title ?? '광고 등록') ?></h1>
        </div>
        <div class="text-muted">Main &gt; 광고관리 &gt; 광고 등록</div>
    </section>

    <section class="content ad-create-page">
        <div class="container-fluid">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('admin/ads/' . $kind . '/create') ?>" method="post" enctype="multipart/form-data" class="edit-card">
                <div class="edit-card-head">광고 정보</div>
                <div class="edit-card-body">
                    <div class="edit-grid">
                        <div class="edit-field">
                            <label for="fc_member_id">광고 신청 FC</label>
                            <select id="fc_member_id" name="fc_member_id" class="form-select" required>
                                <option value="">선택</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?= (int) $member['member_id'] ?>" <?= (string) old('fc_member_id') === (string) $member['member_id'] ? 'selected' : '' ?>>
                                        <?= esc(($member['name'] ?? '-') . ' / ' . ($member['email'] ?? '-')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label for="status">광고 상태</label>
                            <select id="status" name="status" class="form-select">
                                <option value="apply" <?= $status === 'apply' ? 'selected' : '' ?>>신청</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>대기</option>
                                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>진행중</option>
                                <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>거절</option>
                                <option value="end" <?= $status === 'end' ? 'selected' : '' ?>>종료</option>
                            </select>
                        </div>
                        <div class="edit-field">
                            <label for="amount">광고 금액</label>
                            <input id="amount" name="amount" type="number" min="0" class="form-control" value="<?= esc(old('amount', '0')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="start_date">광고 시작일</label>
                            <input id="start_date" name="start_date" type="date" class="form-control" value="<?= esc(old('start_date')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="end_date">광고 종료일</label>
                            <input id="end_date" name="end_date" type="date" class="form-control" value="<?= esc(old('end_date')) ?>">
                        </div>

                        <?php if ($isBanner): ?>
                            <div class="edit-field">
                                <label>배너 위치</label>
                                <input type="text" class="form-control" value="<?= $kind === 'top' ? '상단 배너' : '하단 배너' ?>" readonly>
                            </div>
                            <div class="edit-field full">
                                <label for="banner_image">배너 이미지 (권장 964 × 180px)</label>
                                <input id="banner_image" name="banner_image" type="file" class="form-control" accept="image/*">
                                <small class="text-muted d-block mt-1">PC 노출 영역은 964 × 180px이며, 모바일에서는 높이 140px에 맞춰 이미지 중앙을 기준으로 잘라서 표시됩니다.</small>
                            </div>
                            <div class="edit-field">
                                <label for="banner_link_url">배너 링크 URL</label>
                                <input id="banner_link_url" name="banner_link_url" type="url" class="form-control" value="<?= esc(old('banner_link_url')) ?>" placeholder="https://">
                            </div>
                            <div class="edit-field">
                                <label>&nbsp;</label>
                                <div class="form-check">
                                    <input id="banner_need_design" name="banner_need_design" value="1" type="checkbox" class="form-check-input" <?= old('banner_need_design') ? 'checked' : '' ?>>
                                    <label for="banner_need_design" class="form-check-label">배너 제작 요청</label>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="edit-field">
                                <label for="ad_type">일반 광고 상품</label>
                                <select id="ad_type" name="ad_type" class="form-select" required>
                                    <option value="region_fc" <?= old('ad_type', 'region_fc') === 'region_fc' ? 'selected' : '' ?>>지역별 광고</option>
                                    <option value="product_fc" <?= old('ad_type') === 'product_fc' ? 'selected' : '' ?>>상담가능 상품별 광고</option>
                                    <option value="review" <?= old('ad_type') === 'review' ? 'selected' : '' ?>>후기 광고</option>
                                    <option value="language_fc" <?= old('ad_type') === 'language_fc' ? 'selected' : '' ?>>언어별 광고</option>
                                </select>
                            </div>
                            <div class="edit-field">
                                <label for="region_code">지역 코드</label>
                                <input id="region_code" name="region_code" type="text" class="form-control" value="<?= esc(old('region_code')) ?>" placeholder="seoul">
                            </div>
                            <div class="edit-field">
                                <label for="insurance_type">보험 상품 타입</label>
                                <input id="insurance_type" name="insurance_type" type="text" class="form-control" value="<?= esc(old('insurance_type')) ?>">
                            </div>
                            <div class="edit-field">
                                <label for="review_id">후기 ID</label>
                                <input id="review_id" name="review_id" type="number" min="0" class="form-control" value="<?= esc(old('review_id')) ?>">
                            </div>
                            <div class="edit-field">
                                <label for="language_code">언어 코드</label>
                                <input id="language_code" name="language_code" type="text" class="form-control" value="<?= esc(old('language_code')) ?>" placeholder="en">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="edit-actions">
                        <a href="<?= base_url('admin/ads/' . $kind) ?>" class="btn btn-outline-secondary">취소</a>
                        <button type="submit" class="btn btn-primary">등록</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
