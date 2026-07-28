<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link rel="stylesheet" href="<?= asset_url('assets/css/content.css') ?>" />
<?php
$companyLine = [];
$fcProfileImageUrl = profile_image_url($profile['profile_image'] ?? '');

if (!empty($profile['ga'])) {
    $companyLine[] = $profile['ga'];
} else {
    if (!empty($profile['company'])) $companyLine[] = $profile['company'];
    if (!empty($profile['company_sub'])) $companyLine[] = $profile['company_sub'];
}
?>
<main>
    <div class="page-inner consult-request">
        <h1 class="page-main-title">상담 요청하기</h1>
        <p class="page-main-lead">
            맞춤형 상담을 위해 아래정보를 입력해 주세요.<br />
            입력하신 내용은 선택하신 FC에게 전달되며,<br />
            상담 진행을 위해서만 사용됩니다.
        </p>

        <article class="consult-request-fc">
            <?php if ($fcProfileImageUrl !== ''): ?>
                <img src="<?= esc($fcProfileImageUrl) ?>" alt="FC 프로필 사진" onerror="this.removeAttribute('src'); this.classList.add('is-empty');" />
            <?php else: ?>
                <span class="consult-request-fc-profile-empty" aria-label="FC 프로필 사진 없음"></span>
            <?php endif; ?>
            <div>
                <h2><?= $member["name"] ?></h2>
                <p>
                    <span class="c-rate"><span class="c-rate-star">★</span> 5.0</span>
                    <span class="c-rate-count">(2,018)</span>
                </p>
                <p><?php if (!empty($companyLine)): ?>
                <p><?= esc(implode(' · ', array_slice($companyLine, 0, 2))) ?></p>
            <?php endif; ?> ·
            <?php if (!empty($activity['region'])): ?>
                <?= implode(' ', array_map(function ($r) {
                    return fc_region_label(trim($r));
                }, explode(',', $activity['region']))) ?>
            <?php endif; ?>
            </p>
            <p><?= esc($activity['hero_line']) ?></p>
            </div>
        </article>

        <form
        class="form-box"
        id="consultForm"
        method="post"
        enctype="multipart/form-data">
        <input
        type="hidden"
        name="fc_member_uid"
        value="<?= esc($member['member_uid']) ?>">
            <div class="consult-request-read">
                <p><span>이름</span><strong><?= $my_member["name"] ?></strong></p>
                <p><span>이메일</span><strong><?= $my_member["email"] ?></strong></p>
                <p><span>휴대폰</span><strong><?= $my_member["phone"] ?></strong></p>
            </div>

            <a href="<?= base_url('mypage/info') ?>" class="consult-request-edit">이름/휴대폰번호 수정</a>

            <div class="consult-date">
                <label class="form-label" for="consult-date">상담 요청 일시 <b>*</b></label>
                <input class="form-input" type="text" id="consult-date" placeholder="상담 요청 일시를 선택해주세요." autocomplete="off" readonly required />
                <input type="hidden" id="consult-date-value" name="reserve_datetime" value="">
                <div class="consult-date-picker" id="consult-date-picker" hidden>
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
                <label class="form-label" for="consult-content">상담 요청 내용</label>
                <p class="form-text">상담요청 내용을 입력해 주세요</p>
                <textarea
                    class="form-textarea"
                    id="consult-content"
                    name="content"
                    placeholder="상세 요청 내용이 있으면 함께 입력해 주세요.&#10;보험추가, 기존 보험 점검 요청, 신규 상담, 보험 비교 요청 등"></textarea>
            </div>

            <div class="file-upload" id="consult-file-block">
                <label class="form-label" for="consult-file-display-1">보험 자료 및 증권 파일 첨부</label>
                <p class="form-text">가지고 계신 보험 자료(증권, 기타 참고 자료)가 있다면 첨부해 주세요.</p>
                <div class="file-upload-rows" data-upload-rows>
                    <div data-upload-row>
                        <input class="form-input" id="consult-file-display-1" type="text" readonly placeholder="첨부 파일을 선택해 주세요" />
                        <input id="consult-file-1" class="visually-hidden" name="consult_file[]" type="file" tabindex="-1" />
                        <button type="button" class="file-upload-file-trigger">파일 업로드</button>
                        <button type="button" class="file-upload-row-remove" data-row-remove aria-label="첨부 항목 삭제">삭제</button>
                    </div>
                </div>
                <button type="button" class="file-upload-add" data-upload-add aria-label="첨부 항목 추가"></button>
            </div>

            <!-- <input type="hidden" id="consult-files-picker-value" name="consult-files-picker" value="" /> -->
            <button
                type="button"
                class="consult-file-load-btn"
                data-popup-target="#popup-consult-files"
                data-popup-sync="#security_ids">
                내 파일 불러오기
            </button>

            <div class="form-actions">
                <button type="submit" disabled>상담 요청하기</button>
            </div>
            <input type="hidden" name="security_ids" id="security_ids">
        </form>

        <template id="consult-upload-row-template">
            <div data-upload-row>
                <input type="text" readonly placeholder="첨부 파일을 선택해 주세요" />
                <input class="visually-hidden" name="consult_file[]" type="file" tabindex="-1" />
                <button type="button" class="file-upload-file-trigger">파일 업로드</button>
                <button type="button" class="file-upload-row-remove" data-row-remove aria-label="첨부 항목 삭제">삭제</button>
            </div>
        </template>
    </div>
</main>


<div id="popup-consult-files" class="c-modal" data-popup-multiselect>
    
    <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title">내 파일 불러오기</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <ul class="c-modal-list">

                <?php if (!empty($securityList)): ?>
                    <?php foreach ($securityList as $row): ?>
                        <li>
                            <button
                            type="button"
                            class="c-modal-option"
                            data-security-id="<?= $row['security_id'] ?>"
                            data-value="<?= $row['security_id'] ?>"
                            data-label="<?= esc($row['original_name']) ?>">

                                <span class="c-modal-option-label">
                                    <?= esc($row['original_name']) ?>
                                </span>

                                <span class="c-modal-option-ico"></span>
                            </button>
                        </li>
                    <?php endforeach; ?>

                <?php else: ?>

                    <li>
                        <div class="c-modal-empty">
                            등록된 증권 파일이 없습니다.
                        </div>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-confirm>확인</button>
        </div>
    </div>
</div>

<style>
    .c-modal-empty {
        padding: 30px 20px;
        text-align: center;
        color: #999;
    }

    .my-security-row{
        display:flex;
        gap:10px;
        margin-top:10px;
    }

    .my-security-row input{
        flex:1;
    }

    .my-security-row button{
        width:90px;
    }
</style>
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

        document.querySelector('#consultForm button[type="submit"]').disabled = false;
    })();

    // ===============================
    // 상담 신청
    // ===============================
    document.getElementById('consultForm').addEventListener('submit', function(e){

        e.preventDefault();

        const reserveDateInput = document.getElementById('consult-date');
        const reserveDateValue = document.getElementById('consult-date-value');
        if (!reserveDateValue.value) {
            alert('상담 요청 일시를 선택해주세요.');
            reserveDateInput.focus();
            return;
        }

        const btn = this.querySelector('[type=submit]');

        btn.disabled = true;

        const formData = new FormData(this);

        // security_ids
        formData.set(
            'security_ids',
            document.getElementById('security_ids').value
        );

        fetch('/fc/counsel/save',{

            method:'POST',

            body:formData

        })
        .then(res=>res.json())
        .then(function(result){

            btn.disabled=false;

            if(result.result!='ok'){

                alert(result.message);

                if (result.redirect) {
                    location.href = result.redirect;
                }

                return;

            }

            //alert(result.message);

            location.href='/fc/counselLast';

        })
        .catch(function(){

            btn.disabled=false;

            alert('서버 오류가 발생했습니다.');

        });

    });

    document.getElementById('consult-date').addEventListener('change', function () {
        document.getElementById('consult-date-value').value = this.dataset.dateValue || '';
    });

    // ======================================
    // 내 증권 선택
    // ======================================
    (function () {

        const hidden = document.getElementById('security_ids');

        if (!hidden) return;

        // 선택/해제
        // document.addEventListener('click', function (e) {

        //     const btn = e.target.closest('.c-modal-option');

        //     if (!btn) return;

        //     btn.classList.toggle('is-selected');

        // });

        // 확인 버튼
        const confirmBtn = document.querySelector('#popup-consult-files [data-popup-confirm]');

        confirmBtn.addEventListener('click', function () {

            const ids = [];
            const names = [];

            document.querySelectorAll('#popup-consult-files .c-modal-option.is-selected')
                .forEach(function(btn){

                    ids.push(btn.dataset.securityId);
                    names.push(btn.dataset.label);

                });

            document.getElementById('security_ids').value = ids.join(',');

            renderSelectedFiles(names);

        });

    })();

    function renderSelectedFiles(names)
    {
        const rows = document.querySelector('[data-upload-rows]');

        if (!rows) return;

        // 기존 "내 증권" 목록 삭제
        rows.querySelectorAll('.my-security-row').forEach(function(el){
            el.remove();
        });

        names.forEach(function(name){

            const div = document.createElement('div');

            div.className = 'my-security-row';

            div.innerHTML =
                '<input class="form-input" type="text" readonly value="' + name + '">' +
                '<button type="button" class="file-upload-row-remove">선택됨</button>';

            rows.appendChild(div);

        });

    }

    document.querySelector('.consult-file-load-btn').addEventListener('click', function () {

        const ids = document.getElementById('security_ids')
            .value
            .split(',')
            .filter(Boolean);

        document.querySelectorAll('#popup-consult-files .c-modal-option')
            .forEach(function(btn){

                btn.classList.remove('is-selected');

                if (ids.includes(btn.dataset.securityId)) {
                    btn.classList.add('is-selected');
                }

            });

    });

</script>
