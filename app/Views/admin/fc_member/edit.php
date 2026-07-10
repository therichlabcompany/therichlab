<?= $this->include('admin/layout/header') ?>
<?= $this->include('admin/layout/sidebar') ?>

<?php
$profile = $profile ?? [];
$activity = $activity ?? [];
$activityItems = $activityItems ?? [];
$story = $story ?? [];
$storyImages = $storyImages ?? [];
$error = session()->getFlashdata('error');
$status = old('status', $m['status'] ?? 'ACTIVE');
$phoneVerified = old('phone_verified', $m['phone_verified'] ?? 'N');
$reviewStatus = old('fc_review_status', $m['fc_review_status'] ?? 'WAIT');
?>

<style>
    .fc-edit-page { color: #172033; font-size: 14px; }
    .fc-edit-header { display: flex; justify-content: space-between; gap: 16px; padding: 18px 8px 14px; }
    .fc-edit-header .screen-id { margin-bottom: 6px; color: #64748b; font-size: 13px; font-weight: 700; }
    .fc-edit-header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .edit-card { margin-bottom: 16px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; }
    .edit-card-head { padding: 12px 16px; border-bottom: 1px solid #cbd5e1; background: #eef3f8; font-weight: 800; }
    .edit-card-body { padding: 16px; }
    .edit-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .edit-field.full { grid-column: 1 / -1; }
    .edit-field label { display: block; margin-bottom: 6px; color: #4b586b; font-size: 13px; font-weight: 800; }
    .activity-item-row, .story-image-row { display: grid; grid-template-columns: 90px minmax(0, 1fr) minmax(0, 1fr) 90px; gap: 8px; align-items: end; margin-bottom: 10px; }
    .story-image-row { grid-template-columns: 90px 120px 1fr; align-items: center; }
    .story-image-row img { width: 84px; height: 84px; object-fit: cover; border-radius: 6px; border: 1px solid #d8e0ea; }
    .edit-actions { display: flex; justify-content: flex-end; gap: 8px; margin: 16px 0 24px; }
    @media (max-width: 768px) {
        .edit-grid, .activity-item-row, .story-image-row { grid-template-columns: 1fr; }
        .edit-field.full { grid-column: auto; }
    }
</style>

<div class="content-wrapper">
    <section class="content-header fc-edit-header fc-edit-page">
        <div>
            <div class="screen-id">AMFC003_02</div>
            <h1>FC 회원 수정</h1>
        </div>
        <div class="text-muted">Main &gt; 대시보드 &gt; FC회원 &gt; 수정</div>
    </section>

    <section class="content fc-edit-page">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= esc($error) ?></div>
            <?php endif; ?>

            <form action="<?= base_url('admin/fc-members/' . (int) $m['member_id'] . '/update') ?>" method="post" enctype="multipart/form-data">
                <div class="edit-card">
                    <div class="edit-card-head">기본정보</div>
                    <div class="edit-card-body edit-grid">
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
                            <label for="fc_review_status">심사상태</label>
                            <select id="fc_review_status" name="fc_review_status" class="form-select">
                                <option value="WAIT" <?= $reviewStatus === 'WAIT' ? 'selected' : '' ?>>승인 대기</option>
                                <option value="APPROVE" <?= $reviewStatus === 'APPROVE' ? 'selected' : '' ?>>승인</option>
                                <option value="REJECT" <?= $reviewStatus === 'REJECT' ? 'selected' : '' ?>>거절</option>
                            </select>
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
                            <textarea id="admin_memo" name="admin_memo" class="form-control" rows="4"><?= esc(old('admin_memo', $m['admin_memo'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="edit-card">
                    <div class="edit-card-head">프로필</div>
                    <div class="edit-card-body edit-grid">
                        <div class="edit-field">
                            <label for="profile_image">프로필 이미지</label>
                            <input id="profile_image" name="profile_image" type="file" class="form-control" accept="image/*">
                            <?php if (!empty($profile['profile_image'])): ?>
                                <small class="text-muted">현재 파일: <?= esc($profile['profile_image']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="edit-field">
                            <label for="company">소속 원수사</label>
                            <input id="company" name="company" type="text" class="form-control" value="<?= esc(old('company', $profile['company'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="company_sub">추가 보험사</label>
                            <input id="company_sub" name="company_sub" type="text" class="form-control" value="<?= esc(old('company_sub', $profile['company_sub'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="ga">소속 GA</label>
                            <input id="ga" name="ga" type="text" class="form-control" value="<?= esc(old('ga', $profile['ga'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="position">직책</label>
                            <input id="position" name="position" type="text" class="form-control" value="<?= esc(old('position', $profile['position'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="license_date">보험 자격 취득일</label>
                            <input id="license_date" name="license_date" type="text" class="form-control" value="<?= esc(old('license_date', $profile['license_date'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="license_no">등록번호</label>
                            <input id="license_no" name="license_no" type="text" class="form-control" value="<?= esc(old('license_no', $profile['license_no'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="time_from">상담 시작 시간</label>
                            <input id="time_from" name="time_from" type="number" min="0" max="23" class="form-control" value="<?= esc(old('time_from', $profile['time_from'] ?? '')) ?>">
                        </div>
                        <div class="edit-field">
                            <label for="time_to">상담 종료 시간</label>
                            <input id="time_to" name="time_to" type="number" min="0" max="23" class="form-control" value="<?= esc(old('time_to', $profile['time_to'] ?? '')) ?>">
                        </div>
                        <div class="edit-field full">
                            <label for="language">상담 가능 언어</label>
                            <input id="language" name="language" type="text" class="form-control" value="<?= esc(old('language', $profile['language'] ?? '')) ?>">
                        </div>
                    </div>
                </div>

                <div class="edit-card">
                    <div class="edit-card-head">활동정보</div>
                    <div class="edit-card-body">
                        <div class="edit-grid">
                            <div class="edit-field">
                                <label for="region">활동 지역</label>
                                <input id="region" name="region" type="text" class="form-control" value="<?= esc(old('region', $activity['region'] ?? '')) ?>">
                            </div>
                            <div class="edit-field">
                                <label for="insurance_types">보험 항목</label>
                                <input id="insurance_types" name="insurance_types" type="text" class="form-control" value="<?= esc(old('insurance_types', $activity['insurance_types'] ?? '')) ?>">
                            </div>
                            <div class="edit-field full">
                                <label for="hero_line">한 줄 히어로</label>
                                <input id="hero_line" name="hero_line" type="text" class="form-control" value="<?= esc(old('hero_line', $activity['hero_line'] ?? '')) ?>">
                            </div>
                            <div class="edit-field full">
                                <label for="intro">자기소개</label>
                                <textarea id="intro" name="intro" class="form-control" rows="4"><?= esc(old('intro', $activity['intro'] ?? '')) ?></textarea>
                            </div>
                            <div class="edit-field full">
                                <label for="career">경력사항</label>
                                <textarea id="career" name="career" class="form-control" rows="4"><?= esc(old('career', $activity['career'] ?? '')) ?></textarea>
                            </div>
                        </div>

                        <hr>
                        <strong>이력 및 인증</strong>
                        <?php foreach ($activityItems as $i => $item): ?>
                            <div class="activity-item-row">
                                <input type="hidden" name="activity_items[<?= $i ?>][item_id]" value="<?= (int) $item['item_id'] ?>">
                                <div>
                                    <label>유형</label>
                                    <select name="activity_items[<?= $i ?>][type]" class="form-select">
                                        <option value="file" <?= $item['type'] === 'file' ? 'selected' : '' ?>>파일</option>
                                        <option value="link" <?= $item['type'] === 'link' ? 'selected' : '' ?>>링크</option>
                                        <option value="text" <?= $item['type'] === 'text' ? 'selected' : '' ?>>텍스트</option>
                                    </select>
                                </div>
                                <div>
                                    <label>이력명</label>
                                    <input name="activity_items[<?= $i ?>][title]" type="text" class="form-control" value="<?= esc($item['title'] ?? '') ?>" placeholder="비우면 삭제">
                                </div>
                                <div>
                                    <label>내용/URL</label>
                                    <input name="activity_items[<?= $i ?>][content]" type="text" class="form-control" value="<?= esc($item['content'] ?: ($item['url'] ?? '')) ?>">
                                    <input name="activity_items[<?= $i ?>][url]" type="hidden" value="<?= esc($item['url'] ?? '') ?>">
                                    <input name="activity_items[<?= $i ?>][file]" type="file" class="form-control mt-1">
                                    <?php if (!empty($item['file_path'])): ?>
                                        <small class="text-muted">현재 파일: <?= esc($item['file_path']) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <label>정렬</label>
                                    <input name="activity_items[<?= $i ?>][sort_order]" type="number" class="form-control" value="<?= esc($item['sort_order'] ?? $i) ?>">
                                    <input name="activity_items[<?= $i ?>][is_visible]" type="hidden" value="1">
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php $newIndex = count($activityItems); ?>
                        <div class="activity-item-row">
                            <div>
                                <label>신규 유형</label>
                                <select name="activity_items[<?= $newIndex ?>][type]" class="form-select">
                                    <option value="text">텍스트</option>
                                    <option value="link">링크</option>
                                    <option value="file">파일</option>
                                </select>
                            </div>
                            <div>
                                <label>신규 이력명</label>
                                <input name="activity_items[<?= $newIndex ?>][title]" type="text" class="form-control">
                            </div>
                            <div>
                                <label>내용</label>
                                <input name="activity_items[<?= $newIndex ?>][content]" type="text" class="form-control">
                            </div>
                            <div>
                                <label>정렬</label>
                                <input name="activity_items[<?= $newIndex ?>][sort_order]" type="number" class="form-control" value="<?= $newIndex ?>">
                                <input name="activity_items[<?= $newIndex ?>][is_visible]" type="hidden" value="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="edit-card">
                    <div class="edit-card-head">활동스토리</div>
                    <div class="edit-card-body">
                        <div class="edit-grid">
                            <div class="edit-field">
                                <label for="story_video">활동 스토리 영상</label>
                                <input id="story_video" name="story_video" type="file" class="form-control" accept="video/*">
                                <?php if (!empty($story['story_video'])): ?>
                                    <small class="text-muted">현재 파일: <?= esc($story['story_video']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="edit-field">
                                <label for="story_image">대표 이미지</label>
                                <input id="story_image" name="story_image" type="file" class="form-control" accept="image/*">
                                <?php if (!empty($story['story_image'])): ?>
                                    <small class="text-muted">현재 파일: <?= esc($story['story_image']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="edit-field full">
                                <label for="story_images">스토리 이미지 추가</label>
                                <input id="story_images" name="story_images[]" type="file" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>

                        <?php foreach ($storyImages as $image): ?>
                            <div class="story-image-row">
                                <div class="form-check">
                                    <input id="keep_story_image_<?= (int) $image['id'] ?>" class="form-check-input" type="checkbox" name="keep_story_images[]" value="<?= (int) $image['id'] ?>" checked>
                                    <label class="form-check-label" for="keep_story_image_<?= (int) $image['id'] ?>">유지</label>
                                </div>
                                <img src="<?= esc(base_url('uploads/story/images/' . $image['image_path'])) ?>" alt="">
                                <div>
                                    <strong><?= esc($image['image_path']) ?></strong><br>
                                    <span class="text-muted">삭제하려면 체크를 해제하세요.</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="edit-actions">
                    <a href="<?= base_url('admin/fc-members/' . (int) $m['member_id']) ?>" class="btn btn-outline-secondary">취소</a>
                    <button type="submit" class="btn btn-primary">저장</button>
                </div>
            </form>
        </div>
    </section>
</div>

<?= $this->include('admin/layout/footer') ?>
