<?php $profileImage = profile_image_url($profile['profile_image'] ?? ''); ?>


<div class="fc-profile-thumb">
    <button type="button" id="profile-btn" aria-label="프로필 이미지 등록">
        <?php if ($profileImage !== ''): ?>
            <img id="profile-preview" src="<?= esc($profileImage) ?>" alt="" onerror="this.replaceWith(Object.assign(document.createElement('span'), { id: 'profile-preview', className: 'profile-image-placeholder' }));" />
        <?php else: ?>
            <span id="profile-preview" class="profile-image-placeholder" aria-hidden="true"></span>
        <?php endif; ?>
        <input type="file" id="profile-file" name="profile_image" accept="image/*" hidden />
    </button>
</div>
<?php if ($profileImage !== ''): ?>
    <div class="profile-image-actions">
        <button type="button" id="profile-remove-btn" class="btn btn-line">사진 삭제</button>
    </div>
<?php endif; ?>

<div class="form-field">
    <label class="form-label" for="fc-company">소속 원수사</label>
    <input class="form-input" id="fc-company" name="company" type="text" placeholder="소속 보험사를 입력해주세요." value="<?= esc($profile['company'] ?? '') ?>" />
    <input class="form-input" name="company_sub" type="text" placeholder="교차 보험사가 추가로 있는 경우 입력해주세요." value="<?= esc($profile['company_sub'] ?? '') ?>" />
</div>

<div class="form-field">
    <label class="form-label" for="fc-ga">또는 소속 GA</label>
    <input class="form-input" id="fc-ga" name="ga" type="text" placeholder="소속 GA를 입력해주세요." value="<?= esc($profile['ga'] ?? '') ?>" />
</div>

<div class="form-field">
    <label class="form-label" for="fc-position">직책</label>
    <input
        class="form-input"
        id="fc-position"
        name="position"
        type="text"
        placeholder="직책을 입력해주세요. (예: 지점장,팀장,FC)"  value="<?= esc($profile['position'] ?? '') ?>"/>
</div>

<div class="form-field consult-date">
    <label class="form-label" for="fc-license-date">보험 자격 취득일</label>
    <input
        class="form-input"
        id="fc-license-date"
        name="license_date"
        type="text"
        autocomplete="off"
        placeholder="날짜를 선택해주세요" value="<?= (!empty($profile['license_date'])) ? esc($profile['license_date']) : '' ?>" />
    <div class="consult-date-picker" hidden>
        <div class="consult-date-picker-head">
            <button type="button" class="consult-date-picker-nav prev" data-date-nav="prev" aria-label="이전 달"></button>

             <div class="consult-date-picker-select">
                <select class="consult-year"></select>
                <select class="consult-month"></select>
            </div>
            <button type="button" class="consult-date-picker-nav next" data-date-nav="next" aria-label="다음 달"></button>
        </div>
        <ol class="consult-date-picker-week">
            <li>일</li>
            <li>월</li>
            <li>화</li>
            <li>수</li>
            <li>목</li>
            <li>금</li>
            <li>토</li>
        </ol>
        <div class="consult-date-picker-days"></div>
    </div>
</div>

<div class="form-field">
    <label class="form-label" for="fc-license-no">보험모집종사자 등록번호(고유번호)</label>
    <input class="form-input" id="fc-license-no" name="license_no" type="text" placeholder="숫자만 입력해주세요." value="<?= esc($profile['license_no'] ?? '') ?>" />
</div>

<div>
    <label class="form-label">상담 가능 시간</label>
    <div class="form-inline time-range">
        <button type="button" class="directory-select" data-popup-target="#popup-time-from" data-popup-sync="#fc-time-from">
            <span><?= esc($profile['time_from'] ?? 0) ?>시</span>
        </button>
        <input type="hidden" id="fc-time-from" name="time_from" value="<?= esc($profile['time_from'] ?? 0) ?>" />
        <span>~</span>
        <button type="button" class="directory-select" data-popup-target="#popup-time-to" data-popup-sync="#fc-time-to">
            <span><?= esc($profile['time_to'] ?? 0) ?>시</span>
        </button>
        <input type="hidden" id="fc-time-to" name="time_to" value="<?= esc($profile['time_to'] ?? 0) ?>" />
    </div>
</div>

<div class="form-field">
    <label class="form-label" for="fc-language-value">상담 가능한 언어</label>
    <button type="button" class="directory-select" data-popup-target="#popup-language" data-popup-sync="#fc-language-value">
        <span<?= empty($profile['language']) ? ' class="is-placeholder"' : '' ?>><?= !empty($profile['language']) ? esc(fc_language_labels($profile['language'])) : '상담가능한 언어를 선택 해주세요.' ?></span>
    </button>
    <input id="fc-language-value" type="hidden" name="language" value="<?= esc(fc_language_normalize($profile['language'] ?? '')) ?>" />
</div>
