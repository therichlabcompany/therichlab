<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$profile = $profile ?? [];
$activity = $activity ?? [];
$review = $review ?? [];
$counselCount = (int) ($counselCount ?? 0);
$reviewCount = (int) ($reviewCount ?? 0);
$viewCount = (int) ($profile['view_count'] ?? 0);

$value = static fn ($array, $key) => isset($array[$key]) && $array[$key] !== '' && $array[$key] !== null
    ? esc((string) $array[$key])
    : '-';

$profileImageUrl = profile_image_url($profile['profile_image'] ?? '');

$licenseYears = '-';
if (!empty($profile['license_date'])) {
    $year = (int) substr((string) $profile['license_date'], 0, 4);
    if ($year > 1900) {
        $licenseYears = $profile['license_date'] . ' (' . max(0, (int) date('Y') - $year) . '년)';
    } else {
        $licenseYears = $profile['license_date'];
    }
}

$companies = array_filter([
    $profile['company'] ?? '',
    $profile['company_sub'] ?? '',
]);

$isHomepageVisible = (($m['status'] ?? '') === 'ACTIVE')
    && (($m['fc_review_status'] ?? '') === 'APPROVE')
    && !empty($profile);
$mainExposureAreas = [
    'region' => ['label' => '지역별 추천', 'exposed' => (($profile['main_region_exposure'] ?? 'N') === 'Y')],
    'product' => ['label' => '상황별 추천', 'exposed' => (($profile['main_product_exposure'] ?? 'N') === 'Y')],
    'language' => ['label' => '언어별 추천', 'exposed' => (($profile['main_language_exposure'] ?? 'N') === 'Y')],
];
$activeMainAdAreas = $activeMainAdAreas ?? ['region' => false, 'product' => false, 'language' => false];

$activityItemGroups = [];
foreach ($activityItems ?? [] as $item) {
    $category = $item['category'] ?: '이력 및 인증';
    $activityItemGroups[$category][] = $item;
}
?>

<style>
    .fc-detail-page {
        color: #172033;
        font-size: 14px;
    }

    .fc-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .fc-detail-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .fc-detail-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .top-actions,
    .sub-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
        margin-bottom: 12px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: minmax(340px, 1fr) minmax(340px, 1fr);
        gap: 16px;
        margin-bottom: 16px;
    }

    .wire-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .wire-head {
        padding: 12px 16px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .wire-body {
        padding: 16px;
    }

    .basic-layout {
        display: grid;
        grid-template-columns: 96px minmax(0, 1fr);
        gap: 14px;
    }

    .profile-thumb {
        width: 88px;
        height: 88px;
        overflow: hidden;
        border-radius: 50%;
        background: #e5e7eb;
    }

    .profile-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-thumb .fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background: #e5e7eb;
    }

    .field-list {
        display: grid;
        gap: 9px;
    }

    .field-row {
        display: grid;
        grid-template-columns: 140px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
    }

    .field-label {
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }

    .field-value {
        min-width: 0;
        word-break: break-all;
    }

    .memo-box {
        width: 100%;
        min-height: 116px;
        padding: 12px;
        border: 1px solid #d8e0ea;
        border-radius: 6px;
        resize: vertical;
    }

    .activity-list {
        display: grid;
        gap: 12px;
    }

    .activity-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .activity-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .activity-row .label {
        color: #64748b;
        font-weight: 800;
    }

    .activity-row .value {
        color: #0266ff;
        font-weight: 800;
    }

    .tab-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 12px 16px 0;
    }

    .tab-panel {
        display: none;
    }

    .tab-panel.active {
        display: block;
    }

    .main-exposure-panel {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        gap: 10px;
        margin: 16px 0;
        padding: 14px 16px;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        background: #f8fbff;
    }

    .main-exposure-title {
        display: flex;
        align-items: center;
        margin-right: 4px;
        color: #1e3a5f;
        font-weight: 800;
    }

    .main-exposure-input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
    }

    .main-exposure-toggle {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 38px;
        padding: 8px 11px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #fff;
        color: #64748b;
        cursor: pointer;
        font-size: 13px;
        font-weight: 800;
    }

    .main-exposure-input:checked + .main-exposure-toggle {
        border-color: #2563eb;
        background: #2563eb;
        color: #fff;
    }

    .main-exposure-toggle .state-on { display: none; }
    .main-exposure-input:checked + .main-exposure-toggle .state-on { display: inline; }
    .main-exposure-input:checked + .main-exposure-toggle .state-off { display: none; }
    .main-exposure-toggle.is-locked { border-color: #f59e0b; background: #fffbeb; color: #92400e; cursor: not-allowed; }
    .main-exposure-save { align-self: center; }
    }

    .activity-cert-list {
        display: grid;
        gap: 10px;
    }

    .activity-cert-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 12px;
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        background: #f8fafc;
    }

    .activity-cert-title {
        font-weight: 800;
    }

    .activity-cert-meta {
        margin-top: 4px;
        color: #64748b;
        font-size: 13px;
    }

    .activity-cert-actions {
        display: flex;
        gap: 6px;
    }

    .story-section {
        display: grid;
        gap: 18px;
    }

    .story-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(120px, 1fr));
        gap: 12px;
    }

    .story-thumb {
        position: relative;
        min-height: 118px;
        overflow: hidden;
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        background: #f8fafc;
    }

    .story-thumb button.preview {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 118px;
        padding: 0;
        border: 0;
        background: transparent;
    }

    .story-thumb img {
        width: 100%;
        height: 118px;
        object-fit: cover;
    }

    .story-play {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        color: #fff;
        background: #1f7aff;
        line-height: 44px;
        text-align: center;
    }

    .story-badge {
        position: absolute;
        left: 6px;
        top: 6px;
        padding: 3px 8px;
        border-radius: 999px;
        color: #fff;
        background: rgba(15, 23, 42, 0.78);
        font-size: 12px;
        font-weight: 800;
    }

    .story-delete {
        position: absolute;
        right: 6px;
        bottom: 6px;
    }

    .review-status {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }

    .review-status.wait {
        color: #92400e;
        background: #fef3c7;
    }

    .review-status.approve {
        color: #166534;
        background: #dcfce7;
    }

    .review-status.reject {
        color: #991b1b;
        background: #fee2e2;
    }

    .media-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.72);
    }

    .media-modal.is-open {
        display: flex;
    }

    .media-modal-body {
        max-width: min(900px, 100%);
        max-height: 90vh;
        overflow: auto;
        padding: 16px;
        border-radius: 8px;
        background: #fff;
    }

    .media-modal-body img,
    .media-modal-body video {
        max-width: 100%;
        max-height: 76vh;
        display: block;
    }

    @media (max-width: 992px) {
        .story-grid {
            grid-template-columns: repeat(3, minmax(120px, 1fr));
        }
    }

    @media (max-width: 576px) {
        .story-grid {
            grid-template-columns: repeat(2, minmax(120px, 1fr));
        }
    }

    @media (max-width: 992px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .fc-detail-header {
            flex-direction: column;
        }
    }

    @media (max-width: 576px) {
        .basic-layout,
        .field-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header fc-detail-header fc-detail-page">
        <div>
            <div class="screen-id">AMFC003_02_01</div>
            <h1>FC회원 상세</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; FC회원 &gt; 상세</div>
    </section>

    <section class="content fc-detail-page">
        <div class="container-fluid">
            <div class="top-actions">
                <a href="<?= base_url('admin/fc-members') ?>" class="btn btn-outline-secondary btn-sm">리스트 돌아가기</a>
                <a href="<?= base_url('admin/fc-members/' . (int) $m['member_id'] . '/preview') ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">FC 미리보기</a>
                <span class="review-status <?= $isHomepageVisible ? 'approve' : 'wait' ?>">
                    <?= $isHomepageVisible ? '홈페이지 노출중' : '홈페이지 비노출' ?>
                </span>
                <?php if (($m['fc_review_status'] ?? '') !== 'APPROVE'): ?>
                    <form method="post" action="<?= base_url('admin/fc-members/approve') ?>" class="d-inline" onsubmit="return confirm('이 FC를 승인하고 홈페이지 FC 목록에 노출하시겠습니까?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="member_id" value="<?= (int) $m['member_id'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">FC 승인</button>
                    </form>
                <?php else: ?>
                    <span class="review-status approve">FC 승인완료</span>
                <?php endif; ?>
                <a href="<?= base_url('admin/ads/normal?member_id=' . (int) $m['member_id']) ?>" class="btn btn-outline-secondary btn-sm">광고 현황</a>
                <a href="<?= base_url('admin/fc-members/' . (int) $m['member_id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm">회원 수정</a>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteFcMember(<?= (int) $m['member_id'] ?>)">회원 탈퇴</button>
            </div>

            <div class="summary-grid">
                <div class="wire-card">
                    <div class="wire-head">기본 정보</div>
                    <div class="wire-body">
                        <div class="basic-layout">
                            <div>
                                <div class="profile-thumb">
                                    <?php if ($profileImageUrl !== ''): ?>
                                        <img
                                            src="<?= esc($profileImageUrl) ?>"
                                            alt=""
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >
                                    <?php else: ?>
                                        <div class="fallback" aria-hidden="true"></div>
                                    <?php endif; ?>
                                    <?php if ($profileImageUrl !== ''): ?>
                                        <div class="fallback" aria-hidden="true" style="display:none;"></div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm mt-2" disabled>삭제</button>
                            </div>
                            <div class="field-list">
                                <div class="field-row">
                                    <div class="field-label">ID(이메일 주소)</div>
                                    <div class="field-value"><?= esc($m['email']) ?></div>
                                </div>
                                <div class="field-row">
                                    <div class="field-label">비밀번호</div>
                                    <div class="field-value">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFcPassword(<?= (int) $m['member_id'] ?>)">비밀번호 재설정 메일발송</button>
                                    </div>
                                </div>
                                <div class="field-row">
                                    <div class="field-label">이름</div>
                                    <div class="field-value"><?= esc($m['name']) ?></div>
                                </div>
                                <div class="field-row">
                                    <div class="field-label">휴대폰번호</div>
                                    <div class="field-value"><?= esc($m['phone']) ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="field-label mb-2">운영진 메모</div>
                            <textarea id="fcAdminMemo" class="memo-box"><?= esc((string) ($m['admin_memo'] ?? '')) ?></textarea>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-primary btn-sm" onclick="saveFcMemo(<?= (int) $m['member_id'] ?>)">메모 저장</button>
                            </div>
                        </div>
            </div>
            </div>

            <div class="wire-card">
                <div class="wire-head">메인 FC 노출 설정</div>
                <div class="wire-body">
                    <form method="post" action="<?= base_url('admin/fc-members/profile-main-exposure') ?>" class="main-exposure-panel" onsubmit="return confirm('선택한 메인 추천 광고 영역의 노출 설정을 저장하시겠습니까?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="member_id" value="<?= (int) $m['member_id'] ?>">
                        <span class="main-exposure-title">노출 영역 선택</span>
                        <?php foreach ($mainExposureAreas as $area => $areaInfo): ?>
                            <?php if (!empty($activeMainAdAreas[$area])): ?>
                                <span class="main-exposure-toggle is-locked"><?= esc($areaInfo['label']) ?> <strong>광고 진행중</strong></span>
                            <?php else: ?>
                                <input class="main-exposure-input" id="mainExposure<?= esc(ucfirst($area)) ?>" type="checkbox" name="exposure_areas[]" value="<?= esc($area) ?>" <?= $areaInfo['exposed'] ? 'checked' : '' ?>>
                                <label class="main-exposure-toggle" for="mainExposure<?= esc(ucfirst($area)) ?>">
                                    <span><?= esc($areaInfo['label']) ?></span>
                                    <strong class="state-off">비활성</strong><strong class="state-on">활성</strong>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary btn-sm main-exposure-save">메인 노출 설정 저장</button>
                    </form>
                </div>
            </div>

            <div class="wire-card">
                    <div class="wire-head">활동 정보</div>
                    <div class="wire-body">
                        <div class="activity-list">
                            <div class="activity-row">
                                <span class="label">가입일자</span>
                                <span><?= esc($m['created_at'] ?? '-') ?></span>
                            </div>
                            <div class="activity-row">
                                <span class="label">최종 로그인 일자</span>
                                <span><?= esc($m['last_login_at'] ?? '-') ?></span>
                            </div>
                            <div class="activity-row">
                                <span class="label">조회 수</span>
                                <span class="value"><?= number_format($viewCount) ?></span>
                            </div>
                            <div class="activity-row">
                                <span class="label">상담 요청 건수</span>
                                <a href="<?= base_url('admin/contents/counsels?fc_member_id=' . (int) $m['member_id']) ?>" class="btn btn-outline-primary btn-sm">
                                    <?= number_format($counselCount) ?>건
                                </a>
                            </div>
                            <div class="activity-row">
                                <span class="label">후기 등록 건수</span>
                                <a href="<?= base_url('admin/contents/reviews?fc_member_id=' . (int) $m['member_id']) ?>" class="btn btn-outline-primary btn-sm">
                                    <?= number_format($reviewCount) ?>건
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wire-card">
                <div class="tab-bar">
                    <button type="button" class="btn btn-primary btn-sm js-tab" data-tab="profile">프로필</button>
                    <button type="button" class="btn btn-outline-primary btn-sm js-tab" data-tab="activity">활동 정보</button>
                    <button type="button" class="btn btn-outline-primary btn-sm js-tab" data-tab="story">활동 스토리</button>
                    <button type="button" class="btn btn-outline-primary btn-sm js-tab" data-tab="review">심의필 정보</button>
                </div>
                <div class="wire-body">
                    <div id="tab-profile" class="tab-panel active">
                        <div class="field-list">
                            <div class="field-row">
                                <div class="field-label">소속 원수사</div>
                                <div class="field-value"><?= esc(implode(' / ', $companies) ?: '-') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">소속 GA</div>
                                <div class="field-value"><?= $value($profile, 'ga') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">직책</div>
                                <div class="field-value"><?= $value($profile, 'position') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">보험 자격 취득일</div>
                                <div class="field-value"><?= esc($licenseYears) ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">보험모집종사자 등록번호</div>
                                <div class="field-value"><?= $value($profile, 'license_no') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">상담 가능 시간</div>
                                <div class="field-value"><?= esc(($profile['time_from'] ?? '-') . ' : 00 ~ ' . ($profile['time_to'] ?? '-') . ' : 00') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">상담 가능한 언어</div>
                                <div class="field-value"><?= $value($profile, 'language') ?></div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-activity" class="tab-panel">
                        <div class="field-list">
                            <div class="field-row">
                                <div class="field-label">본인 활동 지역</div>
                                <div class="field-value"><?= $value($activity, 'region') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">운영 가능 보험 항목</div>
                                <div class="field-value"><?= $value($activity, 'insurance_types') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">전문 분야</div>
                                <div class="field-value"><?= $value($activity, 'hero_line') ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">자기소개</div>
                                <div class="field-value"><?= nl2br(esc((string) ($activity['intro'] ?? '-'))) ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">경력사항</div>
                                <div class="field-value"><?= nl2br(esc((string) ($activity['career'] ?? '-'))) ?></div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">이력 및 인증</div>
                                <div class="field-value">
                                    <?php if (!empty($activityItemGroups)): ?>
                                        <div class="activity-cert-list">
                                            <?php foreach ($activityItemGroups as $category => $items): ?>
                                                <?php foreach ($items as $item): ?>
                                                    <div class="activity-cert-item">
                                                        <div>
                                                            <div class="activity-cert-title">
                                                                <?= esc($item['title'] ?? '-') ?>
                                                            </div>
                                                            <div class="activity-cert-meta">
                                                                <?= esc($category) ?>
                                                                <?php if (!empty($item['start_date']) || !empty($item['end_date'])): ?>
                                                                    · <?= esc(($item['start_date'] ?? '-') . ' ~ ' . ($item['end_date'] ?? '-')) ?>
                                                                <?php endif; ?>
                                                                <?php if (!empty($item['content'])): ?>
                                                                    · <?= esc($item['content']) ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="activity-cert-actions">
                                                            <?php if (!empty($item['file_path'])): ?>
                                                                <a href="<?= base_url(ltrim($item['file_path'], '/')) ?>" class="btn btn-outline-primary btn-sm" download>
                                                                    다운로드
                                                                </a>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['url'])): ?>
                                                                <a href="<?= esc($item['url']) ?>" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">
                                                                    새창
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-story" class="tab-panel">
                        <div class="story-section">
                            <div class="field-row">
                                <div class="field-label">활동 영상</div>
                                <div class="field-value">
                                    <?php if (!empty($story['story_video'])): ?>
                                        <?php $videoUrl = base_url('fc/story/video/' . rawurlencode(basename((string) $story['story_video']))); ?>
                                        <div class="story-grid">
                                            <div class="story-thumb">
                                                <button type="button" class="preview" onclick="openMediaModal('video', <?= json_encode($videoUrl) ?>)">
                                                    <span class="story-play">▶</span>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm story-delete" onclick="deleteStoryFile('video')">삭제</button>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">등록된 활동 영상이 없습니다.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="field-row">
                                <div class="field-label">활동 이미지</div>
                                <div class="field-value">
                                    <?php if (!empty($story['story_image']) || !empty($storyImages)): ?>
                                        <div class="story-grid">
                                            <?php if (!empty($story['story_image'])): ?>
                                                <?php $mainImageUrl = base_url('uploads/story/main/' . ltrim($story['story_image'], '/')); ?>
                                                <div class="story-thumb">
                                                    <button type="button" class="preview" onclick="openMediaModal('image', <?= json_encode($mainImageUrl) ?>)">
                                                        <img src="<?= esc($mainImageUrl) ?>" alt="">
                                                    </button>
                                                    <span class="story-badge">대표</span>
                                                    <button type="button" class="btn btn-outline-danger btn-sm story-delete" onclick="deleteStoryFile('main_image')">삭제</button>
                                                </div>
                                            <?php endif; ?>
                                            <?php foreach ($storyImages as $img): ?>
                                                <?php $imageUrl = base_url('uploads/story/images/' . ltrim($img['image_path'], '/')); ?>
                                                <div class="story-thumb">
                                                    <button type="button" class="preview" onclick="openMediaModal('image', <?= json_encode($imageUrl) ?>)">
                                                        <img src="<?= esc($imageUrl) ?>" alt="">
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm story-delete" onclick="deleteStoryFile('image', <?= (int) $img['id'] ?>)">삭제</button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">등록된 활동 이미지가 없습니다.</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-review" class="tab-panel">
                        <?php if ($review): ?>
                            <div class="field-list">
                                <div class="field-row">
                                    <div class="field-label">심의필 번호</div>
                                    <div class="field-value"><?= esc($review['deliberation_no'] ?? '-') ?></div>
                                </div>
                                <div class="field-row">
                                    <div class="field-label">심의필 승인 기간</div>
                                    <div class="field-value"><?= esc(($review['approval_start'] ?? '-') . ' ~ ' . ($review['approval_end'] ?? '-')) ?></div>
                                </div>
                                <div class="field-row">
                                    <div class="field-label">심의 의견</div>
                                    <div class="field-value"><?= nl2br(esc((string) ($review['deliberation_opinion'] ?? '-'))) ?></div>
                                </div>
                                <div class="field-row">
                                    <div class="field-label">심의결과 회신문 파일</div>
                                    <div class="field-value">
                                        <?php if (!empty($review['deliberation_file'])): ?>
                                            <a href="<?= base_url('admin/contents/deliberations/' . (int) ($review['id'] ?? 0) . '/download') ?>" class="btn btn-outline-primary btn-sm">
                                                파일 다운로드
                                            </a>
                                            <span class="ms-2"><?= esc(basename($review['deliberation_file'])) ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="field-row">
                                    <div class="field-label">승인 현황</div>
                                    <div class="field-value">
                                        <?php if (($review['status'] ?? '') === 'APPROVE'): ?>
                                            <span class="review-status approve">승인완료</span>
                                            <span class="ms-2"><?= esc($review['approve_at'] ?? '-') ?></span>
                                        <?php elseif (($review['status'] ?? '') === 'REJECT'): ?>
                                            <span class="review-status reject">승인거부</span>
                                            <span class="ms-2"><?= esc($review['reject_reason'] ?? '거부사유 없음') ?> / <?= esc($review['approve_at'] ?? '-') ?></span>
                                        <?php else: ?>
                                            <span class="review-status wait">승인요청중</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted">심의필 정보가 없습니다.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="mediaModal" class="media-modal" onclick="closeMediaModal()">
    <div class="media-modal-body" onclick="event.stopPropagation()">
        <div class="text-end mb-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeMediaModal()">닫기</button>
        </div>
        <div id="mediaModalContent"></div>
    </div>
</div>

<script>
document.querySelectorAll('.js-tab').forEach(function (button) {
    button.addEventListener('click', function () {
        document.querySelectorAll('.js-tab').forEach(function (tab) {
            tab.classList.remove('btn-primary');
            tab.classList.add('btn-outline-primary');
        });
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-primary');

        document.querySelectorAll('.tab-panel').forEach(function (panel) {
            panel.classList.remove('active');
        });
        document.getElementById('tab-' + button.dataset.tab).classList.add('active');
    });
});

function saveFcMemo(memberId) {
    fetch('<?= base_url('admin/fc-members/memo-save') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({
            member_id: memberId,
            admin_memo: document.getElementById('fcAdminMemo').value
        })
    })
        .then(function (response) { return response.json(); })
        .then(function (json) {
            alert(json.status === 'success' ? '입력하신 정보가 저장되었습니다.' : '메모 저장에 실패했습니다.');
        })
        .catch(function () { alert('메모 저장에 실패했습니다.'); });
}

function resetFcPassword(memberId) {
    if (!confirm('해당 회원에게 비밀번호 재설정 메일을 보내시겠습니까?')) {
        return;
    }

    fetch('<?= base_url('admin/fc-members/password-reset') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({member_id: memberId})
    })
        .then(function (response) { return response.json(); })
        .then(function (json) {
            if (json.status !== 'success') {
                alert(json.message || '비밀번호 재설정 메일 발송에 실패했습니다.');
                return;
            }
            alert(json.message || '비밀번호 재설정 안내 메일을 발송했습니다.');
        })
        .catch(function () { alert('비밀번호 재설정에 실패했습니다.'); });
}

function deleteFcMember(memberId) {
    if (!confirm('해당회원을 탈퇴처리 하시겠습니까?\n탈퇴처리 시 모든 정보가 삭제되며 복구하실 수 없습니다.')) {
        return;
    }

    fetch('<?= base_url('admin/fc-members/delete') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({member_id: memberId})
    })
        .then(function (response) { return response.json(); })
        .then(function (json) {
            if (json.status === 'success') {
                alert('탈퇴처리 완료되었습니다.');
                window.location.href = '<?= base_url('admin/fc-members') ?>';
                return;
            }
            alert('탈퇴 처리에 실패했습니다.');
        })
        .catch(function () { alert('탈퇴 처리에 실패했습니다.'); });
}

function openMediaModal(type, url) {
    var content = document.getElementById('mediaModalContent');
    if (type === 'video') {
        content.innerHTML = '';
        var video = document.createElement('video');
        var source = document.createElement('source');
        video.controls = true;
        video.autoplay = true;
        video.playsInline = true;
        source.src = url;
        video.appendChild(source);
        content.appendChild(video);
        video.addEventListener('error', function () {
            content.innerHTML = '';
            var message = document.createElement('p');
            message.className = 'text-muted';
            message.textContent = '영상을 재생할 수 없습니다. MP4(H.264/AAC) 형식의 파일인지 확인해주세요.';
            content.appendChild(message);
        });
    } else {
        content.innerHTML = '<img src="' + url + '" alt="">';
    }

    document.getElementById('mediaModal').classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function closeMediaModal() {
    document.getElementById('mediaModal').classList.remove('is-open');
    document.getElementById('mediaModalContent').innerHTML = '';
    document.body.style.overflow = '';
}

function deleteStoryFile(type, imageId) {
    if (!confirm('해당 파일을 삭제하시겠습니까?\n삭제 시 복구하실 수 없습니다.')) {
        return;
    }

    fetch('<?= base_url('admin/fc-members/story/delete') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({
            member_id: <?= (int) $m['member_id'] ?>,
            type: type,
            image_id: imageId || 0
        })
    })
        .then(function (response) { return response.json(); })
        .then(function (json) {
            if (json.status === 'success') {
                alert('삭제 완료 되었습니다.');
                window.location.reload();
                return;
            }
            alert('삭제 처리에 실패했습니다.');
        })
        .catch(function () { alert('삭제 처리에 실패했습니다.'); });
}
</script>

<?= $this->include('admin/layout/footer') ?>
