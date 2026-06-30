<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">광고 신청</h1>
        <p class="page-main-lead">
            우선 노출을 희망하는 영역을 선택해 주세요.<br />
            선택한 섹션은 광고 기간 동안 노출 우선권이 적용됩니다.
        </p>

        

        <?php
              $menu_step = "menu1";
              include_once (COMPONENT_PATH . '/fc_ad_tab_nav.php'); 
          ?>

        <form class="form-box" id="adRegionForm">
            <div class="form-field">
                <label class="form-label" for="ad-region-trigger">광고 지역 <b>*</b></label>
                <button
                    type="button"
                    class="directory-select"
                    id="ad-region-trigger"
                    data-popup-target="#popup-ad-region"
                    data-popup-sync="#ad-region-value">
                    <span class="is-placeholder">광고 지역을 선택해주세요.</span>
                </button>
                <input id="ad-region-value" type="hidden" name="ad_region" value="" />
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
                    <li>섹션별 노출 순서는 <span class="uline">랜덤</span>으로 노출됩니다.</li>
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

<div id="popup-ad-region" class="c-modal">
    <button type="button" class="c-modal-backdrop" data-popup-close></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title">광고 지역</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <ul class="c-modal-list">
                <li>
                    <button type="button" class="c-modal-option" data-value="seoul">
                        <span class="c-modal-option-label">서울</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="gyeonggi">
                        <span class="c-modal-option-label">경기</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="incheon_bucheon">
                        <span class="c-modal-option-label">인천/부천</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="busan_ulsan_gyeongnam">
                        <span class="c-modal-option-label">부산/울산/경남</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="daegu_gyeongbuk">
                        <span class="c-modal-option-label">대구/경북</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="daejeon_sejong_chungnam">
                        <span class="c-modal-option-label">대전/세종/충남</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>

                 <li>
                    <button type="button" class="c-modal-option" data-value="cheongju_chungbuk">
                        <span class="c-modal-option-label">청주/충북</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>

                 <li>
                    <button type="button" class="c-modal-option" data-value="gwangju_jeonnam">
                        <span class="c-modal-option-label">광주/전남</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="jeonju_jeonbuk">
                        <span class="c-modal-option-label">전주/전북</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="c-modal-option" data-value="chuncheon_gangwon">
                        <span class="c-modal-option-label">춘천/강원</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>

                <li>
                    <button type="button" class="c-modal-option" data-value="jeju">
                        <span class="c-modal-option-label">제주</span>
                        <span class="c-modal-option-ico"></span>
                    </button>
                </li>

            </ul>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-confirm>확인</button>
        </div>
    </div>
</div>
<script>
(function () {

    const trigger = document.getElementById('ad-region-trigger');
    const hidden = document.getElementById('ad-region-value');
    const form = document.getElementById('adRegionForm');
    const submitBtn = form.querySelector('.btn-primary');

    // =========================
    // 1. 지역 선택 이벤트
    // =========================
    document.querySelectorAll('.c-modal-option').forEach(btn => {
        btn.addEventListener('click', function () {
            const value = this.dataset.value;
            const label = this.querySelector('.c-modal-option-label')?.innerText;

            if (!value) return;

            // hidden 값 세팅
            hidden.value = value;

            // 버튼 UI 변경
            trigger.innerHTML = `
                <span>${label}</span>
            `;

            // 버튼 활성화 체크
            checkValid();
        });
    });

    // =========================
    // 2. submit 가능 여부 체크
    // =========================
    function checkValid() {
        const region = hidden.value;
        const plan = document.querySelector('input[name="ad_plan"]:checked');

        submitBtn.disabled = !(region && plan);
    }

    document.addEventListener('change', checkValid);

    // =========================
    // 3. AJAX submit
    // =========================
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const ad_region = hidden.value;
        const ad_plan = document.querySelector('input[name="ad_plan"]:checked')?.value;

        if (!ad_region || !ad_plan) {
            alert('필수값을 선택해주세요.');
            return;
        }

        submitBtn.disabled = true;

        fetch('/mypage/ad/region-fc', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({
                ad_region,
                ad_plan
            })
        })
        .then(async (res) => {

            const data = await res.json().catch(() => null);

            if (!data) {
                const text = await res.text(); // fallback
                throw new Error(text);
            }

            return data;
        })
        .then((data) => {

            if (data.result === 'success') {
                location.href = '/mypage/adLast';
                return;
            }

            alert(data.msg || '실패');
        })
        .catch((err) => {
            console.error(err);
            alert(err.message || '서버 오류');
        });
    });

})();
</script>