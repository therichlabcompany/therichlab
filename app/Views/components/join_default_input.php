<?php
    $mode = $mode ?? 'create'; // create | edit

    $isEdit = ($mode === 'edit');
?>
<div class="form-field">
    <label class="form-label" for="email">이메일 <b>*</b></label>
    <div class="combo">

        <input
            class="form-input"
            id="email"
            name="email"
            type="email"
            placeholder="이메일을 입력해주세요."
            value="<?= esc($user['email'] ?? '') ?>"
            <?= $isEdit ? 'readonly' : '' ?>
        />

        <button type="button" id="btnEmailCheck"
            <?= $isEdit ? 'disabled' : '' ?>>
            중복 확인
        </button>

    </div>
</div>

<div class="form-field">
    <label class="form-label" for="password">비밀번호 <b>*</b></label>

    <?php if (!$isEdit): ?>
        <!-- 가입 -->
        <input
            class="form-input"
            id="password"
            name="password"
            type="password"
            placeholder="비밀번호를 입력해주세요."
        />

    <?php else: ?>
        <!-- 수정 -->
        <div class="combo">
            <input
                class="form-input"
                type="password"
                value="********"
                readonly
            />
            <button type="button" id="btnPasswordReset">
                비밀번호 재설정
            </button>
        </div>
    <?php endif; ?>
</div>

<?php if (!$isEdit): ?>
<div class="form-field">
    <label class="form-label" for="password-confirm">비밀번호 확인 <b>*</b></label>
    <input
        class="form-input"
        id="password-confirm"
        name="password_confirm"
        type="password"
        placeholder="비밀번호를 다시한번 입력해주세요."
    />
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
            placeholder="휴대폰 번호를 입력해주세요."
            value="<?= esc($user['phone'] ?? '') ?>"
            <?= $isEdit ? 'readonly' : '' ?>
        />

        <button type="button" id="btnPhoneCheck">
            <?= $isEdit ? '변경/재인증' : '인증' ?>
        </button>

    </div>
</div>

<div class="form-field">
    <label class="form-label" for="name">이름 <b>*</b></label>
    <input
        class="form-input"
        id="name"
        name="name"
        type="text"
        placeholder="이름을 입력해주세요."
        value="<?= esc($user['name'] ?? '') ?>"
        <?= $isEdit ? 'readonly' : '' ?>
    />
</div>