<?php

$mobileOkEnabled = (bool) ($mobileOkEnabled ?? false);
$isEdit = (bool) ($isEdit ?? false);

$emailValue = old('email', $user['email'] ?? '');
$phoneValue = old('phone', $user['phone'] ?? '');
$nameValue = old('name', $user['name'] ?? '');
$birthValue = preg_replace('/[^0-9]/', '', (string) old('birth', $user['birth'] ?? ''));
$genderValue = strtoupper(trim((string) old('gender', $user['gender'] ?? '')));

$genderLabel = match ($genderValue) {
    'M' => '남성',
    'F' => '여성',
    default => '',
};

$phoneButtonLabel = $isEdit ? '변경/재인증' : '변경/인증';
$phoneReadonly = $isEdit;
$profileReadonly = true;
?>

<div class="form-field">
    <label class="form-label" for="email">이메일 <b>*</b></label>
    <div class="combo">
        <input
            class="form-input"
            id="email"
            name="email"
            type="email"
            autocomplete="email"
            placeholder="이메일을 입력해주세요."
            value="<?= esc($emailValue) ?>"
            <?= $isEdit ? 'readonly' : '' ?>
        />
        <button type="button" id="btnEmailCheck" <?= $isEdit ? 'disabled' : '' ?>>중복 확인</button>
    </div>
</div>

<?php if (!$isEdit): ?>
<div class="form-field">
    <label class="form-label" for="password">비밀번호 <b>*</b></label>
    <input
        class="form-input"
        id="password"
        name="password"
        type="password"
        minlength="8"
        maxlength="20"
        autocomplete="new-password"
        placeholder="비밀번호를 입력해주세요."
    />
</div>

<div class="form-field">
    <label class="form-label" for="password_confirm">비밀번호 확인 <b>*</b></label>
    <input
        class="form-input"
        id="password_confirm"
        name="password_confirm"
        type="password"
        minlength="8"
        maxlength="20"
        autocomplete="new-password"
        placeholder="비밀번호를 다시한번 입력해주세요."
    />
    <p class="form-text">8자~20자 내로 영문 대문자, 영문 소문자, 숫자, 특수문자를 각각 1개 이상 포함해주세요.</p>
</div>
<?php endif; ?>

<div class="form-field">
    <label class="form-label" for="phone">휴대폰 번호 <b>*</b></label>
    <div class="combo">
        <input
            class="form-input"
            id="phone"
            name="phone"
            type="tel"
            autocomplete="tel"
            placeholder="휴대폰 번호를 입력해주세요."
            value="<?= esc($phoneValue) ?>"
            <?= $phoneReadonly ? 'readonly' : '' ?>
        />
        <button
            type="button"
            id="btnPhoneCheck"
            data-default-label="<?= esc($phoneButtonLabel) ?>"
            data-complete-label="다시 인증"
        ><?= esc($phoneButtonLabel) ?></button>
    </div>
    <p class="form-text">본인인증 후 이름, 생년월일, 성별이 자동 입력됩니다.</p>
</div>

<div class="form-field">
    <label class="form-label" for="name">이름 <b>*</b></label>
    <input
        class="form-input"
        id="name"
        name="name"
        type="text"
        value="<?= esc($nameValue) ?>"
        placeholder="본인인증 후 자동 입력됩니다."
        <?= $profileReadonly ? 'readonly' : '' ?>
    />
    <p class="form-text">이름은 휴대폰 본인인증 결과로만 등록·변경됩니다.</p>
</div>

<div class="form-field">
    <label class="form-label" for="birth">생년월일 <b>*</b></label>
    <input
        class="form-input"
        id="birth"
        name="birth"
        type="text"
        maxlength="8"
        value="<?= esc($birthValue) ?>"
        placeholder="본인인증 후 자동 입력됩니다."
        <?= $profileReadonly ? 'readonly' : '' ?>
    />
    <p class="form-text">생년월일은 본인인증 결과를 기준으로 자동 입력됩니다.</p>
</div>

<div class="form-field">
    <label class="form-label" for="gender-display">성별 <b>*</b></label>
    <input class="form-input" id="gender-display" type="text" value="<?= esc($genderLabel) ?>" placeholder="본인인증 후 자동 입력됩니다." readonly />
    <input type="hidden" id="gender" name="gender" value="<?= esc($genderValue) ?>">
    <p class="form-text">이름, 생년월일, 성별은 휴대폰 본인인증 결과로만 등록·변경됩니다.</p>
</div>
