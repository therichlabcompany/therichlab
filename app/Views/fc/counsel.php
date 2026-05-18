<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link rel="stylesheet" href="<?= base_url('assets/css/content.css?v=3') ?>" />

<main>
    <div class="page-inner consult-request">
        <h1 class="page-main-title">상담 요청하기</h1>
        <p class="page-main-lead">
            맞춤형 상담을 위해 아래정보를 입력해 주세요.<br />
            입력하신 내용은 선택하신 FC에게 전달되며,<br />
            상담 진행을 위해서만 사용됩니다.
        </p>

        <article class="consult-request-fc">
            <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="한지윤 FC 프로필 사진" />
            <div>
                <h2>한지윤</h2>
                <p>
                    <span class="c-rate"><span class="c-rate-star">★</span> 5.0</span>
                    <span class="c-rate-count">(2,018)</span>
                </p>
                <p>현대해상 · 서울, 경기, 인천</p>
                <p>신규 고객 맞춤 설계로 빠르게 도와드립니다.</p>
            </div>
        </article>

        <form class="form-box" method="post">
            <div class="consult-request-read">
                <p><span>이름</span><strong>김소연</strong></p>
                <p><span>이메일</span><strong>username@gmail.com</strong></p>
                <p><span>휴대폰</span><strong>01012345678</strong></p>
            </div>

            <button type="button" class="consult-request-edit">이름/휴대폰번호 수정</button>

            <div class="consult-date">
                <label class="form-label" for="consult-date">상담 요청 일시 <b>*</b></label>
                <input class="form-input" type="text" id="consult-date" name="consult-date" autocomplete="off" />
                <div class="consult-date-picker" id="consult-date-picker" hidden>
                    <div class="consult-date-picker-head">
                        <button type="button" class="consult-date-picker-nav prev" data-date-nav="prev" aria-label="이전 달"></button>
                        <strong></strong>
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
                <label class="form-label" for="consult-content">상담 요청 내용</label>
                <p class="form-text">상담요청 내용을 입력해 주세요</p>
                <textarea
                    class="form-textarea"
                    id="consult-content"
                    name="consult-content"
                    placeholder="상세 요청 내용이 있으면 함께 입력해 주세요.&#10;보험추가, 기존 보험 점검 요청, 신규 상담, 보험 비교 요청 등"></textarea>
            </div>

            <div class="file-upload" id="consult-file-block">
                <label class="form-label" for="consult-file-display-1">보험 자료 및 증권 파일 첨부</label>
                <p class="form-text">가지고 계신 보험 자료(증권, 기타 참고 자료)가 있다면 첨부해 주세요.</p>
                <div class="file-upload-rows" data-upload-rows>
                    <div data-upload-row>
                        <input class="form-input" id="consult-file-display-1" type="text" readonly placeholder="첨부 파일을 선택해 주세요" />
                        <input id="consult-file-1" class="visually-hidden" name="consult-file" type="file" tabindex="-1" />
                        <button type="button" class="file-upload-file-trigger">파일 업로드</button>
                        <button type="button" class="file-upload-row-remove" data-row-remove aria-label="첨부 항목 삭제">삭제</button>
                    </div>
                </div>
                <button type="button" class="file-upload-add" data-upload-add aria-label="첨부 항목 추가"></button>
            </div>

            <input type="hidden" id="consult-files-picker-value" name="consult-files-picker" value="" />
            <button
                type="button"
                class="consult-file-load-btn"
                data-popup-target="#popup-consult-files"
                data-popup-sync="#consult-files-picker-value">
                내 파일 불러오기
            </button>

            <div class="form-actions">
                <button type="submit" disabled>상담 요청하기</button>
            </div>
        </form>

        <template id="consult-upload-row-template">
            <div data-upload-row>
                <input type="text" readonly placeholder="첨부 파일을 선택해 주세요" />
                <input class="visually-hidden" name="consult-file" type="file" tabindex="-1" />
                <button type="button" class="file-upload-file-trigger">파일 업로드</button>
                <button type="button" class="file-upload-row-remove" data-row-remove aria-label="첨부 항목 삭제">삭제</button>
            </div>
        </template>
    </div>
</main>

<script>
    (function() {
        var form = document.querySelector('.consult-request form');
        var fileBlock = document.getElementById('consult-file-block');
        var rows = fileBlock ? fileBlock.querySelector('[data-upload-rows]') : null;
        var rowTpl = document.getElementById('consult-upload-row-template');
        var rowSeq = 1;
        if (!form || !fileBlock || !rows || !rowTpl) return;

        function syncRowState() {}

        function addRow() {
            if (!rowTpl.content) return;
            rowSeq += 1;
            var sample = rowTpl.content.querySelector('[data-upload-row]');
            if (!sample) return;
            var row = sample.cloneNode(true);
            var display = row.querySelector('input[readonly]');
            var fileInput = row.querySelector('input[type="file"]');
            if (display) display.id = 'consult-file-display-' + rowSeq;
            if (fileInput) fileInput.id = 'consult-file-' + rowSeq;
            rows.appendChild(row);
            syncRowState();
        }

        form.addEventListener('click', function(e) {
            var addTrigger = e.target.closest('[data-upload-add]');
            if (addTrigger && fileBlock.contains(addTrigger)) {
                addRow();
                return;
            }

            var delBtn = e.target.closest('[data-row-remove]');
            if (delBtn && form.contains(delBtn)) {
                var delRow = delBtn.closest('[data-upload-row]');
                if (!delRow) return;
                delRow.remove();
                syncRowState();
                return;
            }

            var trigger = e.target.closest('.file-upload-file-trigger');
            if (!trigger || !form.contains(trigger)) return;
            var row = trigger.closest('[data-upload-row]');
            var fileInput = row ? row.querySelector('input[type="file"]') : null;
            if (fileInput) fileInput.click();
        });

        form.addEventListener('change', function(e) {
            var t = e.target;
            if (!t || t.tagName !== 'INPUT' || t.type !== 'file' || !form.contains(t)) return;
            var row = t.closest('[data-upload-row]');
            var display = row ? row.querySelector('input[readonly]') : null;
            var file = t.files && t.files[0];
            if (display) display.value = file ? file.name : '';
        });

        syncRowState();
    })();
</script>