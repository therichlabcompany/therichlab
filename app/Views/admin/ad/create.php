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
    .fc-search-wrap { position: relative; }
    .fc-search-result { display: none; position: absolute; z-index: 10; top: calc(100% + 4px); right: 0; left: 0; max-height: 220px; overflow-y: auto; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; box-shadow: 0 8px 18px rgba(15, 23, 42, .12); }
    .fc-search-result.is-open { display: block; }
    .fc-search-item { display: block; width: 100%; padding: 10px 12px; border: 0; border-bottom: 1px solid #eef2f7; background: #fff; color: #172033; text-align: left; }
    .fc-search-item:hover { background: #f8fafc; }
    .fc-search-item strong, .fc-search-item small { display: block; }
    .fc-search-item small, .fc-selected { color: #64748b; font-size: 12px; }
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
                            <div class="fc-search-wrap">
                                <input id="fc_member_search" type="search" class="form-control" autocomplete="off" placeholder="FC 이름, 이메일 또는 휴대폰번호로 검색" aria-describedby="fc_selected">
                                <input id="fc_member_id" name="fc_member_id" type="hidden" value="<?= esc(old('fc_member_id')) ?>" required>
                                <div id="fc_search_result" class="fc-search-result" role="listbox"></div>
                            </div>
                            <small id="fc_selected" class="fc-selected d-block mt-1"><?= old('fc_member_id') ? '선택된 FC 회원번호: ' . esc(old('fc_member_id')) : '검색 결과에서 광고 신청 FC를 선택해주세요.' ?></small>
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
                                <select id="language_code" name="language_code" class="form-select">
                                    <option value="">선택하세요</option>
                                    <?php foreach (fc_language_options() as $option): ?>
                                        <option value="<?= esc($option['value']) ?>" <?= old('language_code') === $option['value'] ? 'selected' : '' ?>><?= esc($option['label']) ?> (<?= esc($option['value']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
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

<script>
(() => {
    const input = document.getElementById('fc_member_search');
    const valueInput = document.getElementById('fc_member_id');
    const results = document.getElementById('fc_search_result');
    const selected = document.getElementById('fc_selected');
    const endpoint = <?= json_encode(base_url('admin/ads/fc-search')) ?>;
    let timer;

    const closeResults = () => results.classList.remove('is-open');
    const selectMember = (member) => {
        valueInput.value = member.member_id;
        input.value = member.name + ' / ' + member.email;
        selected.textContent = '선택됨: ' + member.name + ' (' + member.email + ')';
        closeResults();
    };

    input.addEventListener('input', () => {
        valueInput.value = '';
        selected.textContent = '검색 결과에서 광고 신청 FC를 선택해주세요.';
        clearTimeout(timer);
        const keyword = input.value.trim();
        if (!keyword) {
            results.innerHTML = '';
            closeResults();
            return;
        }
        timer = setTimeout(async () => {
            try {
                const response = await fetch(endpoint + '?q=' + encodeURIComponent(keyword), { headers: { Accept: 'application/json' } });
                const data = await response.json();
                results.innerHTML = '';
                (data.items || []).forEach((member) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'fc-search-item';
                    button.innerHTML = '<strong></strong><small></small>';
                    button.querySelector('strong').textContent = member.name || '-';
                    button.querySelector('small').textContent = (member.email || '-') + ' / ' + (member.phone || '-');
                    button.addEventListener('click', () => selectMember(member));
                    results.appendChild(button);
                });
                if (!results.children.length) {
                    results.innerHTML = '<div class="p-2 text-muted small">검색 결과가 없습니다.</div>';
                }
                results.classList.add('is-open');
            } catch (error) {
                results.innerHTML = '<div class="p-2 text-danger small">FC 검색 중 오류가 발생했습니다.</div>';
                results.classList.add('is-open');
            }
        }, 250);
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.fc-search-wrap')) closeResults();
    });
})();
</script>
