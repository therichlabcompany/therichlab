<main>
        <div class="page-inner-narrow">
          <h1 class="page-main-title">FC 회원정보</h1>

          <p class="page-main-lead">
            입력하신 정보는 관리자가 확인 후<br class="br-mo" />
            승인 절차를 거쳐 고객에게 노출됩니다.<br />
            정확하고 신뢰할 수 있는 정보를 등록해 주시기 바랍니다.
          </p>

          <?php
              $menu_step = "step2";
              include_once (COMPONENT_PATH . '/fc_info_tab_nav.php'); 
          ?>

          <p class="signup-step-lead">
            고객이 확인할 프로필 정보를 입력해주세요.<br />
            정확한 정보는 신뢰도를 높입니다.
          </p>

          <form class="form-box" method="post" id="fc-member-basic-form" enctype="multipart/form-data">
            <?php include_once (COMPONENT_PATH . '/fc_profile_input.php');  ?>

            <div class="form-actions">
                <button type="submit">저장</button>
            </div>
          </form>
        </div>
      </main>


<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.querySelector('.form-box');

    const company = document.getElementById('fc-company');
    const companySub = document.querySelector('input[name="company_sub"]');
    const ga = document.getElementById('fc-ga');
    const position = document.getElementById('fc-position');

    const licenseDate = document.getElementById('fc-license-date');
    const licenseNo = document.getElementById('fc-license-no');

    const timeFrom = document.getElementById('fc-time-from');
    const timeTo = document.getElementById('fc-time-to');

    const language = document.getElementById('fc-language-value');

    
    const fileInput = document.getElementById('profile-file');
    const btn = document.getElementById('profile-btn');
    const preview = document.getElementById('profile-preview');


    let  selectedFile = null;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const companyVal = company.value.trim();
        const companySubVal = companySub.value.trim();
        const gaVal = ga.value.trim();

        const positionVal = position.value.trim();
        const licenseDateVal = licenseDate.value.trim();
        const licenseNoVal = licenseNo.value.trim();
        const timeFromVal = timeFrom.value;
        const timeToVal = timeTo.value;
        const languageVal = language.value;

        // =========================
        // 1. 필수 체크 (회사/GA 관계)
        // =========================
        if (!companyVal && !gaVal) {
            alert('소속 원수사 또는 소속 GA 중 하나는 반드시 입력해야 합니다.');
            return;
        }

        // =========================
        // 2. GA 입력 시 company_sub 금지
        // =========================
        if (gaVal && companySubVal) {
            alert('소속 GA를 입력한 경우, 추가 소속 보험사는 입력할 수 없습니다.');
            return;
        }

        // =========================
        // 3. 나머지 필수값 체크
        // =========================
        if (!positionVal) return alert('직책을 입력해주세요.');
        if (!licenseDateVal) return alert('보험 자격 취득일을 선택해주세요.');
        if (!licenseNoVal) return alert('보험모집종사자 등록번호를 입력해주세요.');
        if (!timeFromVal || !timeToVal) return alert('상담 가능 시간을 선택해주세요.');
        if (!languageVal) return alert('상담 가능한 언어를 선택해주세요.');

        const formData = new FormData();

        formData.append('profile_image', selectedFile || '');
        formData.append('company', companyVal);
        formData.append('company_sub', companySubVal);
        formData.append('ga', gaVal);
        formData.append('position', positionVal);
        formData.append('license_date', licenseDateVal);
        formData.append('license_no', licenseNoVal);
        formData.append('time_from', timeFromVal);
        formData.append('time_to', timeToVal);
        formData.append('language', languageVal);

        try {
            const res = await fetch('/member/fc/profile/update', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();

            if (result.status === 'success') {
                alert('프로필 정보가 수정되었습니다.');
            } else {
                alert(result.message || '저장 실패');
            }

        } catch (err) {
            alert('서버 오류');
        }
    });


    // =========================
    // 실시간 숫자 필터 (등록번호)
    // =========================
    licenseNo.addEventListener('input', () => {
        licenseNo.value = licenseNo.value.replace(/[^0-9]/g, '');
    });
    
    
    // =========================
    // 파일 선택 열기
    // =========================
    btn.addEventListener('click', () => {
        fileInput.click();
    });

    // =========================
    // 파일 변경 → 미리보기
    // =========================
    fileInput.addEventListener('change', (e) => {

        const file = e.target.files[0];

        if (!file) return;

        // 🔥 이미지 검증
        if (!file.type.startsWith('image/')) {
            alert('이미지 파일만 업로드 가능합니다.');
            fileInput.value = '';
            return;
        }

        // 1. 용량 체크 (예: 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('이미지 용량은 5MB 이하만 가능합니다.');
            fileInput.value = '';
            return;
        }

        selectedFile = file;

        // 2. 미리보기
        const reader = new FileReader();

        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.classList.remove('is-empty');
        };

        reader.readAsDataURL(file);
    });

});

</script>      
