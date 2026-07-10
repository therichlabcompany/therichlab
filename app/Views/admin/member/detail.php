<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
[$statusLabel, $statusClass] = member_status_label($m['status']);

$value = static fn ($key) => isset($m[$key]) && $m[$key] !== '' && $m[$key] !== null
    ? esc((string) $m[$key])
    : '-';

$dateValue = static fn ($key) => isset($m[$key]) && $m[$key] !== '' && $m[$key] !== null
    ? esc((string) $m[$key])
    : '-';

$genderLabel = match ($m['gender'] ?? '') {
    'M' => '남성',
    'F' => '여성',
    default => '-',
};

$fileSize = static function ($bytes) {
    $bytes = (int) $bytes;

    if ($bytes <= 0) {
        return '-';
    }

    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    }

    return number_format($bytes / 1024, 1) . ' KB';
};

$securityFiles = $securityFiles ?? [];
$reviews = $reviews ?? [];
$counselCount = (int) ($counselCount ?? 0);
$reviewCount = (int) ($reviewCount ?? 0);
$message = session()->getFlashdata('message');
$error = session()->getFlashdata('error');
?>

<style>
    .member-detail-page {
        color: #172033;
        font-size: 14px;
    }

    .member-detail-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 18px 8px 14px;
    }

    .member-detail-header .screen-id {
        margin-bottom: 6px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .member-detail-header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
    }

    .member-detail-header .breadcrumb-text {
        color: #64748b;
        font-size: 13px;
        white-space: nowrap;
    }

    .detail-wire-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }

    .detail-section {
        margin-bottom: 16px;
    }

    .detail-section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .detail-section-body {
        padding: 16px;
    }

    .detail-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
        margin-bottom: 12px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-top: 1px solid #d8e0ea;
        border-left: 1px solid #d8e0ea;
    }

    .detail-field {
        display: grid;
        grid-template-columns: 160px minmax(0, 1fr);
        min-height: 48px;
        border-right: 1px solid #d8e0ea;
        border-bottom: 1px solid #d8e0ea;
    }

    .detail-field.full {
        grid-column: 1 / -1;
    }

    .detail-label {
        display: flex;
        align-items: center;
        padding: 12px 14px;
        color: #4b586b;
        background: #f8fafc;
        font-size: 13px;
        font-weight: 800;
    }

    .detail-value {
        display: flex;
        align-items: center;
        min-width: 0;
        padding: 12px 14px;
        word-break: break-all;
    }

    .detail-value.stack {
        display: block;
    }

    .activity-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(150px, 1fr));
        border-top: 1px solid #d8e0ea;
        border-left: 1px solid #d8e0ea;
    }

    .activity-item {
        min-height: 86px;
        padding: 14px;
        border-right: 1px solid #d8e0ea;
        border-bottom: 1px solid #d8e0ea;
        background: #fff;
    }

    .activity-item .label {
        margin-bottom: 8px;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
    }

    .activity-item .value {
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
    }

    .file-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .file-table th,
    .file-table td {
        padding: 12px 10px;
        border: 1px solid #d8e0ea;
        vertical-align: middle;
    }

    .file-table th {
        color: #4b586b;
        background: #f8fafc;
        font-size: 13px;
        font-weight: 800;
        text-align: center;
    }

    .file-table .col-no {
        width: 70px;
    }

    .file-table .col-ext {
        width: 90px;
    }

    .file-table .col-size {
        width: 120px;
    }

    .file-table .col-date {
        width: 170px;
    }

    .file-table .col-action {
        width: 88px;
    }

    .memo-box {
        width: 100%;
        min-height: 120px;
        padding: 12px;
        border: 1px solid #d8e0ea;
        border-radius: 6px;
        resize: vertical;
    }

    .detail-help {
        margin-top: 8px;
        color: #64748b;
        font-size: 12px;
    }

    .review-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.58);
    }

    .review-modal.is-open {
        display: flex;
    }

    .review-modal-panel {
        width: min(920px, 100%);
        max-height: calc(100vh - 48px);
        overflow: hidden;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.24);
    }

    .review-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid #cbd5e1;
        background: #eef3f8;
        font-weight: 800;
    }

    .review-modal-close {
        border: 0;
        background: transparent;
        color: #475569;
        font-size: 22px;
        line-height: 1;
    }

    .review-modal-body {
        max-height: calc(100vh - 150px);
        overflow-y: auto;
        padding: 18px;
    }

    .review-list-item {
        display: block;
        width: 100%;
        padding: 16px;
        color: inherit;
        text-align: left;
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        background: #fff;
    }

    .review-list-item + .review-list-item {
        margin-top: 12px;
    }

    .review-list-item:hover {
        border-color: #1f7aff;
        background: #f8fbff;
    }

    .review-list-meta,
    .review-detail-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        margin-bottom: 12px;
        color: #64748b;
        font-size: 13px;
    }

    .review-rating {
        color: #f59e0b;
        font-weight: 800;
    }

    .review-list-title {
        display: -webkit-box;
        overflow: hidden;
        margin-bottom: 12px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.45;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .review-list-body {
        display: -webkit-box;
        overflow: hidden;
        color: #334155;
        line-height: 1.7;
        white-space: pre-wrap;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 5;
    }

    .review-detail-fc {
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr);
        gap: 12px;
        margin-bottom: 16px;
        padding: 14px;
        border: 1px solid #d8e0ea;
        border-radius: 8px;
        background: #f8fafc;
    }

    .review-detail-avatar {
        width: 64px;
        height: 64px;
        overflow: hidden;
        border-radius: 50%;
        background: #e5e7eb;
    }

    .review-detail-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .review-detail-avatar .fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        color: #64748b;
        font-weight: 800;
    }

    .review-detail-fc-name {
        margin-bottom: 4px;
        font-weight: 800;
    }

    .review-detail-intro {
        overflow: hidden;
        color: #64748b;
        font-size: 13px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .review-detail-title {
        margin-bottom: 12px;
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
        line-height: 1.45;
    }

    .review-detail-body {
        color: #334155;
        line-height: 1.7;
        white-space: pre-wrap;
    }

    .review-empty {
        padding: 80px 16px;
        color: #64748b;
        text-align: center;
    }

    .review-modal-foot {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        padding: 14px 18px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 13px;
    }

    @media (max-width: 992px) {
        .detail-grid,
        .activity-grid {
            grid-template-columns: 1fr;
        }

        .member-detail-header {
            flex-direction: column;
        }

        .member-detail-header .breadcrumb-text {
            white-space: normal;
        }
    }

    @media (max-width: 576px) {
        .detail-field {
            grid-template-columns: 1fr;
        }

        .review-modal {
            padding: 12px;
        }

        .review-detail-fc {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header member-detail-header member-detail-page">
        <div>
            <div class="screen-id">AMFC003_01</div>
            <h1>개인회원 상세</h1>
        </div>

        <div class="breadcrumb-text">Main &gt; 대시보드 &gt; 개인회원 &gt; 상세</div>
    </section>

    <section class="content member-detail-page">
        <div class="container-fluid">
            <div class="detail-actions">
                <a href="<?= base_url('admin/members') ?>" class="btn btn-outline-secondary btn-sm">
                    리스트 돌아가기
                </a>
                <a href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/edit') ?>" class="btn btn-outline-secondary btn-sm">
                    회원수정
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteMember(<?= (int) $m['member_id'] ?>)">
                    회원 탈퇴
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= esc($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <div class="detail-section detail-wire-card">
                <div class="detail-section-head">
                    <span>회원상세정보</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetPassword(<?= (int) $m['member_id'] ?>)">
                        비밀번호 재설정 메일발송
                    </button>
                </div>
                <div class="detail-section-body">
                    <div class="detail-grid">
                        <div class="detail-field">
                            <div class="detail-label">회원번호</div>
                            <div class="detail-value"><?= $value('member_id') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">회원상태</div>
                            <div class="detail-value">
                                <span class="badge rounded-pill bg-<?= $statusClass ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">이메일</div>
                            <div class="detail-value"><?= $value('email') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">이름</div>
                            <div class="detail-value"><?= $value('name') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">휴대폰 번호</div>
                            <div class="detail-value">
                                <?= $value('phone') ?>
                                <span class="ms-2 badge rounded-pill bg-<?= ($m['phone_verified'] ?? 'N') === 'Y' ? 'success' : 'secondary' ?>">
                                    <?= ($m['phone_verified'] ?? 'N') === 'Y' ? '인증' : '미인증' ?>
                                </span>
                            </div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">생년월일</div>
                            <div class="detail-value"><?= $value('birth') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">성별</div>
                            <div class="detail-value"><?= esc($genderLabel) ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">닉네임</div>
                            <div class="detail-value"><?= $value('nickname') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">회원 UID</div>
                            <div class="detail-value"><?= $value('member_uid') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">가입 IP</div>
                            <div class="detail-value"><?= $value('join_ip') ?></div>
                        </div>
                        <div class="detail-field full">
                            <div class="detail-label">운영진 메모</div>
                            <div class="detail-value stack">
                                <textarea id="adminMemo" class="memo-box" placeholder="운영진 메모를 입력하세요."><?= esc((string) ($m['admin_memo'] ?? '')) ?></textarea>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="saveMemo(<?= (int) $m['member_id'] ?>)">메모 저장</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section detail-wire-card">
                <div class="detail-section-head">활동 정보</div>
                <div class="detail-section-body">
                    <div class="activity-grid">
                        <div class="activity-item">
                            <div class="label">가입일자</div>
                            <div class="value"><?= $dateValue('created_at') ?></div>
                        </div>
                        <div class="activity-item">
                            <div class="label">최종 로그인 일자</div>
                            <div class="value"><?= $dateValue('last_login_at') ?></div>
                        </div>
                        <div class="activity-item">
                            <div class="label">상담 요청 건수</div>
                            <div class="value">
                                <a href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/counsels') ?>" class="btn btn-outline-primary btn-sm">
                                    <?= number_format($counselCount) ?>건
                                </a>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="label">후기 작성 건수</div>
                            <div class="value">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openReviewModal()">
                                    <?= number_format($reviewCount) ?>건
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-section detail-wire-card">
                <div class="detail-section-head">
                    <span>첨부파일</span>
                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/members/' . (int) $m['member_id'] . '/files/download-all') ?>" class="btn btn-outline-secondary btn-sm">모두 내려받기</a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('adminFileInput').click()">파일추가</button>
                    </div>
                </div>
                <div class="detail-section-body">
                    <form id="adminFileForm" action="<?= base_url('admin/members/' . (int) $m['member_id'] . '/files/upload') ?>" method="post" enctype="multipart/form-data" class="d-none">
                        <input id="adminFileInput" type="file" name="admin_files[]" multiple onchange="submitAdminFileForm()">
                    </form>
                    <div class="table-responsive">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th class="col-no">NO</th>
                                    <th>파일명</th>
                                    <th class="col-ext">확장자</th>
                                    <th class="col-size">용량</th>
                                    <th class="col-date">등록일</th>
                                    <th class="col-action">삭제</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($securityFiles)): ?>
                                    <?php foreach ($securityFiles as $index => $file): ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <?php if (!empty($file['file_path'])): ?>
                                                    <a href="<?= base_url('admin/members/files/' . (int) $file['security_id'] . '/download') ?>">
                                                        <?= esc($file['original_name'] ?? $file['saved_name'] ?? '-') ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?= esc($file['original_name'] ?? '-') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= esc($file['file_ext'] ?? '-') ?></td>
                                            <td class="text-end"><?= esc($fileSize($file['file_size'] ?? 0)) ?></td>
                                            <td class="text-center"><?= esc($file['created_at'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteFile(<?= (int) $file['security_id'] ?>)">X</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">등록된 첨부파일이 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="detail-section detail-wire-card">
                <div class="detail-section-head">앱 및 동의 정보</div>
                <div class="detail-section-body">
                    <div class="detail-grid">
                        <div class="detail-field">
                            <div class="detail-label">앱 플랫폼</div>
                            <div class="detail-value"><?= $value('app_platform') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">FCM 갱신일</div>
                            <div class="detail-value"><?= $dateValue('fcm_token_updated_at') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">앱 토큰 만료</div>
                            <div class="detail-value"><?= $dateValue('app_token_expire_at') ?></div>
                        </div>
                        <div class="detail-field">
                            <div class="detail-label">앱 토큰 갱신</div>
                            <div class="detail-value"><?= $dateValue('app_token_updated_at') ?></div>
                        </div>
                        <div class="detail-field full">
                            <div class="detail-label">약관 동의</div>
                            <div class="detail-value">
                                <span class="me-3">만 14세 이상: <?= !empty($m['agree_age']) ? '동의' : '미동의' ?></span>
                                <span class="me-3">이용약관: <?= !empty($m['agree_terms']) ? '동의' : '미동의' ?></span>
                                <span class="me-3">개인정보: <?= !empty($m['agree_privacy']) ? '동의' : '미동의' ?></span>
                                <span>마케팅: <?= !empty($m['agree_marketing']) ? '동의' : '미동의' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div id="reviewListModal" class="review-modal" role="dialog" aria-modal="true" aria-labelledby="reviewListModalTitle">
    <div class="review-modal-panel">
        <div class="review-modal-head">
            <span id="reviewListModalTitle">후기리스트</span>
            <button type="button" class="review-modal-close" onclick="closeReviewModals()" aria-label="닫기">×</button>
        </div>
        <div class="review-modal-body">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $index => $review): ?>
                    <button type="button" class="review-list-item" onclick="openReviewDetailModal(<?= (int) $index ?>, <?= (int) ($review['review_id'] ?? 0) ?>)">
                        <div class="review-list-meta">
                            <span class="review-rating">★ <?= number_format((float) ($review['rating'] ?? 0), 1) ?></span>
                            <span>작성일자 <?= esc($review['created_at'] ?? '-') ?></span>
                            <span>FC <?= esc($review['fc_name'] ?? '-') ?></span>
                        </div>
                        <div class="review-list-title"><?= esc($review['title'] ?? '-') ?></div>
                        <div class="review-list-body"><?= esc($review['body'] ?? '') ?></div>
                    </button>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="review-empty">작성된 후기가 없습니다.</div>
            <?php endif; ?>
        </div>
        <div class="review-modal-foot">
            <span><?= number_format(count($reviews)) ?>건</span>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeReviewModals()">닫기</button>
        </div>
    </div>
</div>

<div id="reviewDetailModal" class="review-modal" role="dialog" aria-modal="true" aria-labelledby="reviewDetailModalTitle">
    <div class="review-modal-panel">
        <div class="review-modal-head">
            <span id="reviewDetailModalTitle">후기상세</span>
            <button type="button" class="review-modal-close" onclick="closeReviewModals()" aria-label="닫기">×</button>
        </div>
        <div class="review-modal-body">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $index => $review): ?>
                    <?php
                    $profileImage = trim((string) ($review['profile_image'] ?? ''));
                    $profileImageUrl = $profileImage !== '' ? base_url(ltrim($profileImage, '/')) : '';
                    ?>
                    <article class="review-detail-panel" data-review-detail="<?= (int) $index ?>" style="display:none;">
                        <div class="review-detail-fc">
                            <div class="review-detail-avatar">
                                <?php if ($profileImageUrl !== ''): ?>
                                    <img src="<?= esc($profileImageUrl) ?>" alt="">
                                <?php else: ?>
                                    <div class="fallback">FC</div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="review-detail-fc-name"><?= esc($review['fc_name'] ?? '-') ?></div>
                                <div class="review-detail-meta">
                                    <span>평균별점 <?= number_format((float) ($review['avg_rating'] ?? 0), 1) ?></span>
                                    <span><?= esc($review['company'] ?? '-') ?></span>
                                    <span><?= esc($review['region'] ?? '-') ?></span>
                                </div>
                                <div class="review-detail-intro"><?= esc($review['intro'] ?: '소개글이 없습니다.') ?></div>
                            </div>
                        </div>

                        <div class="review-detail-meta">
                            <span class="review-rating">★ <?= number_format((float) ($review['rating'] ?? 0), 1) ?></span>
                            <span>작성일자 <?= esc($review['created_at'] ?? '-') ?></span>
                        </div>
                        <div class="review-detail-title"><?= esc($review['title'] ?? '-') ?></div>
                        <div class="review-detail-body"><?= esc($review['body'] ?? '') ?></div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="review-empty">선택된 후기가 없습니다.</div>
            <?php endif; ?>
        </div>
        <div class="review-modal-foot">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="backToReviewList()">리스트로 돌아가기</button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteReview()">삭제 하기</button>
        </div>
    </div>
</div>

<script>
var currentReviewId = 0;

function openReviewModal() {
    document.getElementById('reviewListModal').classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function openReviewDetailModal(index, reviewId) {
    currentReviewId = reviewId;

    document.querySelectorAll('[data-review-detail]').forEach(function (panel) {
        panel.style.display = panel.dataset.reviewDetail === String(index) ? 'block' : 'none';
    });

    document.getElementById('reviewListModal').classList.remove('is-open');
    document.getElementById('reviewDetailModal').classList.add('is-open');
}

function backToReviewList() {
    document.getElementById('reviewDetailModal').classList.remove('is-open');
    document.getElementById('reviewListModal').classList.add('is-open');
}

function closeReviewModals() {
    document.getElementById('reviewListModal').classList.remove('is-open');
    document.getElementById('reviewDetailModal').classList.remove('is-open');
    document.body.style.overflow = '';
}

['reviewListModal', 'reviewDetailModal'].forEach(function (id) {
    document.getElementById(id).addEventListener('click', function (event) {
        if (event.target === this) {
            closeReviewModals();
        }
    });
});

document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
        return;
    }

    if (
        document.getElementById('reviewListModal').classList.contains('is-open')
        || document.getElementById('reviewDetailModal').classList.contains('is-open')
    ) {
        closeReviewModals();
    }
});

function deleteReview() {
    if (!currentReviewId) {
        alert('삭제할 후기를 찾을 수 없습니다.');
        return;
    }

    if (!confirm('해당 후기를 삭제하시겠습니까?\n삭제 하신 후기는 복구하실 수 없습니다.')) {
        return;
    }

    fetch('<?= base_url('admin/members/reviews/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({
            review_id: currentReviewId
        })
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (json) {
            if (json.status === 'success') {
                alert('후기가 삭제 되었습니다.');
                window.location.reload();
                return;
            }

            alert('후기 삭제에 실패했습니다.');
        })
        .catch(function () {
            alert('후기 삭제에 실패했습니다.');
        });
}

function deleteMember(memberId) {
    if (!confirm('해당 회원을 탈퇴처리 하시겠습니까?\n한번 탈퇴시킨 회원정보는 복구하실 수 없습니다.')) {
        return;
    }

    fetch('<?= base_url('admin/members/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({
            member_id: memberId
        })
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (json) {
            if (json.status === 'success') {
                alert('탈퇴처리 되었습니다.');
                window.location.href = '<?= base_url('admin/members') ?>';
                return;
            }

            alert('탈퇴 처리에 실패했습니다.');
        })
        .catch(function () {
            alert('탈퇴 처리에 실패했습니다.');
        });
}

function saveMemo(memberId) {
    fetch('<?= base_url('admin/members/memo-save') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({
            member_id: memberId,
            admin_memo: document.getElementById('adminMemo').value
        })
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (json) {
            alert(json.status === 'success' ? '메모가 저장되었습니다.' : '메모 저장에 실패했습니다.');
        })
        .catch(function () {
            alert('메모 저장에 실패했습니다.');
        });
}

function resetPassword(memberId) {
    if (!confirm('임시 비밀번호를 발급하시겠습니까?')) {
        return;
    }

    fetch('<?= base_url('admin/members/password-reset') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({
            member_id: memberId
        })
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (json) {
            if (json.status !== 'success') {
                alert('비밀번호 재설정에 실패했습니다.');
                return;
            }

            if (json.mail_sent) {
                alert('임시 비밀번호 메일을 발송했습니다.');
                return;
            }

            alert('메일 설정이 없어 임시 비밀번호를 화면에 표시합니다.\n임시 비밀번호: ' + json.temporary_password);
        })
        .catch(function () {
            alert('비밀번호 재설정에 실패했습니다.');
        });
}

function submitAdminFileForm() {
    var input = document.getElementById('adminFileInput');
    if (input.files.length > 0) {
        document.getElementById('adminFileForm').submit();
    }
}

function deleteFile(securityId) {
    if (!confirm('파일을 삭제하시겠습니까?')) {
        return;
    }

    fetch('<?= base_url('admin/members/files/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: new URLSearchParams({
            security_id: securityId
        })
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (json) {
            if (json.status === 'success') {
                alert('파일이 삭제되었습니다.');
                window.location.reload();
                return;
            }

            alert('파일 삭제에 실패했습니다.');
        })
        .catch(function () {
            alert('파일 삭제에 실패했습니다.');
        });
}
</script>

<?= $this->include('admin/layout/footer') ?>
