<?php
$profileImage = !empty($profile['profile_image'])
    ? '/uploads/profile/' . $profile['profile_image']
    : '/assets/images/temp/@profile-w.png';
?>
<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">FC 회원정보</h1>

        <p class="page-main-lead">
            입력하신 정보는 관리자가 확인 후<br class="br-mo" />
            승인 절차를 거쳐 고객에게 노출됩니다.<br />
            정확하고 신뢰할 수 있는 정보를 등록해 주시기 바랍니다.
        </p>

        <?php
              $menu_step = "step1";
              include_once (COMPONENT_PATH . '/fc_info_tab_nav.php'); 
          ?>
        

        <p class="signup-step-lead">
            고객이 확인할 프로필 정보를 입력해주세요.<br />
            정확한 정보는 신뢰도를 높입니다.
        </p>

        <form class="form-box" method="post" id="fc-member-basic-form"  enctype="multipart/form-data">
            <div class="fc-profile-thumb">
                <button type="button" aria-label="프로필 이미지 등록" id="btnProfileUpload">
                    <img src="<?= esc($profileImage) ?>" alt="프로필 이미지">
                </button>

                <input type="file" name="profile_image" id="profile_image" hidden />
            </div>

            <?php include_once (COMPONENT_PATH . '/join_default_input.php');  ?>

            <?php $agreeMarketing = $user['agree_marketing'] ?? 0; ?>

            <div class="form-field form-field--label-inline">
                <span class="form-label">마케팅 목적의 개인정보 수집 및 이용 동의</span>

                <label class="c-radio">
                    <input type="radio" name="agree_marketing" value="1"
                        <?= ($agreeMarketing == 1) ? 'checked' : '' ?> />
                    <span>예</span>
                </label>

                <label class="c-radio">
                    <input type="radio" name="agree_marketing" value="0"
                        <?= ($agreeMarketing == 0) ? 'checked' : '' ?> />
                    <span>아니오</span>
                </label>
            </div>

            <div class="form-actions form-actions-split">
                <a href="MFC003_01.html" class="btn">내 프로필 페이지로 이동</a>
                <button type="submit" class="btn btn-primary">수정 완료</button>
            </div>
        </form>

        <p class="login-reset">
            <a href="MFC005_L01_01_05.html">회원탈퇴</a>
        </p>
    </div>
</main>

<script>
document.getElementById('btnProfileUpload').addEventListener('click', () => {
    document.getElementById('profile_image').click();
});

document.getElementById('profile_image').addEventListener('change', async function () {

    const file = this.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('profile_image', file);

    const res = await fetch('/mypage/updateProfileImage', {
        method: 'POST',
        body: formData
    });

    const result = await res.json();

    if (result.status === 'success') {

        document.querySelector('.fc-profile-thumb img').src =
            '/uploads/profile/' + result.data.profile_image;

    } else {
        alert(result.message);
    }
});

document.getElementById('fc-member-basic-form').addEventListener('submit', async (e) => {

    e.preventDefault();

    const formData = {
        phone: document.getElementById('phone').value.trim(),
        agree_marketing: document.querySelector('input[name="agree_marketing"]:checked').value
    };

    try {

        const response = await fetch('/member/updateBasicInfo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert('회원정보가 수정되었습니다.');
            location.reload();
        } else {
            alert(result.message);
        }

    } catch (e) {
        alert('처리 중 오류가 발생했습니다.');
    }

});


</script>

