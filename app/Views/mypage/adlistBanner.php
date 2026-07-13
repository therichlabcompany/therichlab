<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">광고 신청</h1>
        <p class="page-main-lead">
            우선 노출을 희망하는 영역을 선택해 주세요.<br />
            선택한 섹션은 광고 기간 동안 노출 우선권이 적용됩니다.
        </p>

        <?php
              $menu_step = "menu2";
              include_once (COMPONENT_PATH . '/fc_ad_tab_nav.php'); 
          ?>

        <form class="form-box" method="post" action="#" enctype="multipart/form-data">
            <div class="form-field">
                <div class="file-upload" data-banner-upload>
                    <div class="file-upload-head">
                        <label class="form-label" for="banner-filename">광고 배너 업로드 <b>*</b> <span class="form-guide">권장 964 × 180px</span></label>
                        <button type="button" class="btn btn-line btn-sm">노출 영역 확인</button>
                    </div>
                    <div class="file-upload-rows" data-upload-rows>
                        <div data-upload-row>
                            <input
                                id="banner-filename"
                                class="form-input"
                                type="text"
                                readonly
                                placeholder="파일을 선택해 주세요"
                                value=""
                                autocomplete="off" />
                            <input
                                class="visually-hidden"
                                name="banner_file"
                                type="file"
                                tabindex="-1"
                                accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif" />
                            <button type="button" class="file-upload-file-trigger">파일찾기</button>
                        </div>
                    </div>
                </div>
                <ul class="dash-list">
                    <li>배너 이미지 권장 사이즈는 <strong>964 × 180px</strong>입니다.</li>
                    <li>모바일에서는 동일 이미지가 영역 높이 <strong>140px</strong>에 맞춰 중앙 기준으로 노출됩니다.</li>
                    <li>첨부 가능한 파일 형식은 <strong class="warn">.png .jpg .gif</strong> 로 등록해주세요.</li>
                </ul>
                <label class="c-check">
                    <input type="checkbox" name="banner_need_design" value="1" />
                    <span>배너 제작이 필요한 경우 체크박스 선택해주세요. <strong class="warn">별도 안내</strong> 드립니다.</span>
                </label>
            </div>

            <div class="form-field">
                <span class="form-label">광고 기간 및 금액 선택 <b>*</b></span>
                <div class="ad-apply-plan-row">
                    <label class="c-radio">
                        <input type="radio" name="ad_plan" value="1m" checked />
                        <span>1개월</span>
                    </label>
                    <span class="ad-apply-price">500,000 원</span>
                </div>
            </div>

            <section class="gray-box">
                <ul class="dash-list">
                    <li>
                        광고를 신청하시면 담당자 확인 후
                        <strong class="warn">이메일/카카오톡 메시지로 안내장을 발송</strong>
                        해드립니다.
                    </li>
                    <li>섹션별 <span class="uline">노출 순서</span>는 <strong class="warn">랜덤</strong>으로 노출됩니다.</li>
                    <li>광고 노출 기간 종료 시 자동으로 비활성화 됩니다.</li>
                    <li>결제 완료 후에는 광고 <strong class="warn">취소 및 환불이 불가</strong>합니다.</li>
                </ul>
            </section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" disabled>신청하기</button>
            </div>
        </form>
    </div>
</main>

<script>
    (function() {
        var block = document.querySelector('[data-banner-upload]');
        if (!block) return;
        var row = block.querySelector('[data-upload-row]');
        if (!row) return;
        var display = row.querySelector('input[readonly]');
        var fileInput = row.querySelector('input[type="file"]');
        var trigger = row.querySelector('.file-upload-file-trigger');
        if (trigger && fileInput) {
            trigger.addEventListener('click', function() {
                fileInput.click();
            });
        }
        if (fileInput && display) {
            fileInput.addEventListener('change', function() {
                var f = fileInput.files && fileInput.files[0];
                display.value = f ? f.name : '';
            });
        }
    })();
</script>
<script>
(function () {

    const form = document.querySelector('.form-box');
    const submitBtn = form.querySelector('.btn-primary');

    const planEl = document.querySelectorAll('input[name="ad_plan"]');
    const fileEl = document.querySelector('input[name="banner_file"]');
    const needDesignEl = document.querySelector('input[name="banner_need_design"]');

    function getPlan() {
        return document.querySelector('input[name="ad_plan"]:checked')?.value;
    }

    function getNeedDesign() {
        return needDesignEl?.checked;
    }

    function getFile() {
        return fileEl?.files?.[0];
    }

    // =========================
    // 버튼 활성화 체크
    // =========================
    function checkValid() {
        const plan = getPlan();
        const needDesign = getNeedDesign();
        const file = getFile();

        // 핵심 조건
        const fileOk = needDesign ? true : !!file; // ⭐ 여기 핵심

        submitBtn.disabled = !(plan && fileOk);
    }

    // 이벤트 연결
    planEl.forEach(el => el.addEventListener('change', checkValid));
    needDesignEl.addEventListener('change', checkValid);
    fileEl.addEventListener('change', checkValid);

    // =========================
    // submit
    // =========================
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const plan = getPlan();
        const needDesign = getNeedDesign();
        const file = getFile();

        if (!plan) {
            alert('광고 기간을 선택해주세요.');
            return;
        }

        // ⭐ 핵심: 체크 안했을 때만 파일 필수
        if (!needDesign && !file) {
            alert('배너 파일을 선택해주세요.');
            return;
        }

        const formData = new FormData();
        formData.append('ad_plan', plan);
        formData.append('banner_need_design', needDesign ? 1 : 0);

        if (file) {
            formData.append('banner_file', file);
        }

        submitBtn.disabled = true;

        fetch('/mypage/ad/banner', {
            method: 'POST',
            body: formData
        })
        .then(async (res) => {
            const data = await res.json(); // ⭐ 여기만 사용
            return data;
        })
        .then((data) => {

            if (data.result === 'success') {
                
                location.href = '/mypage/adLast';
                return;
            }

            alert(data.msg || '실패');
        })
        .catch(() => {
            alert('서버 오류');
        });

    });

    checkValid(); // 초기 상태

})();
</script>
