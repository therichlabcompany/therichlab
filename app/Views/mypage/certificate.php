<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">내 증권 관리</h1>
        <p class="page-main-lead">가지고 계신 보장 자료(증권, 기타 참고 자료)가 있다면 첨부해 주세요.</p>

        <section>
            <h2 class="visually-hidden">첨부 파일 목록</h2>
            <ul class="securities-files" data-securities-list>

                <?php if (!empty($securityList)): ?>
                    <?php foreach ($securityList as $index => $row): ?>
                        <li data-security-id="<?= $row['security_id'] ?>">
                            <div class="securities-row">
                                <button
                                    type="button"
                                    class="securities-file <?= $index === 0 ? 'is-selected' : '' ?>"
                                    data-url="/mypage/security/download/<?= $row['security_id'] ?>">
                                    <?= esc($row['original_name']) ?>
                                </button>

                                <button
                                    type="button"
                                    class="securities-remove"
                                    data-security-id="<?= $row['security_id'] ?>"
                                    aria-label="<?= esc($row['original_name']) ?> 삭제">
                                </button>
                            </div>
                        </li>
                    <?php endforeach; ?>

                <?php else: ?>

                    <li class="empty">
                        <div class="securities-row">
                            <span>등록된 증권 파일이 없습니다.</span>
                        </div>
                    </li>

                <?php endif; ?>

            </ul>
            <p class="securities-add-wrap">
                <button type="button" class="securities-add" data-securities-add aria-label="파일 항목 추가"></button>
            </p>
            <input type="file" data-securities-file-input accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx" multiple hidden />
        </section>

        <div class="form-actions">
            <button type="button" class="securities-submit">확인</button>
        </div>
    </div>
</main>
<script>
(function () {
    'use strict';

    const list = document.querySelector('[data-securities-list]');
    const addBtn = document.querySelector('[data-securities-add]');
    const input = document.querySelector('[data-securities-file-input]');
    const submitBtn = document.querySelector('.securities-submit');

    if (!list || !addBtn || !input || !submitBtn) {
        return;
    }

    // 새로 선택한 파일 보관
    let uploadFiles = [];

    // ===========================
    // 파일 선택
    // ===========================
    addBtn.addEventListener('click', function () {
        input.click();
    });

    // ===========================
    // 파일 추가
    // ===========================
    input.addEventListener('change', function () {

        Array.from(input.files).forEach(function (file) {

            uploadFiles.push(file);

            const li = document.createElement('li');

            li.dataset.newFile = "Y";

            li.innerHTML =
                '<div class="securities-row">' +
                    '<button type="button" class="securities-file"></button>' +
                    '<button type="button" class="securities-remove"></button>' +
                '</div>';

            li.querySelector('.securities-file').textContent = file.name;

            li.querySelector('.securities-remove').setAttribute(
                'aria-label',
                file.name + ' 삭제'
            );

            list.appendChild(li);

        });

        input.value = '';

    });

    // ===========================
    // 목록 클릭
    // ===========================
    list.addEventListener('click', function (e) {

        // 삭제
        const removeBtn = e.target.closest('.securities-remove');

        if (removeBtn) {

            const li = removeBtn.closest('li');

            // 새로 추가한 파일
            if (li.dataset.newFile === 'Y') {

                const filename = li.querySelector('.securities-file').textContent;

                uploadFiles = uploadFiles.filter(function(file){
                    return file.name !== filename;
                });

                li.remove();
                return;
            }

            // 기존 파일
            const securityId = removeBtn.dataset.securityId;

            if (!confirm('삭제하시겠습니까?')) {
                return;
            }

            fetch('/mypage/security/delete', {
                method: 'POST',
                headers: {
                    'Content-Type':'application/json'
                },
                body: JSON.stringify({
                    security_id: securityId
                })
            })
            .then(res => res.json())
            .then(function(result){

                if(result.result === 'ok'){
                    li.remove();
                }else{
                    alert(result.message);
                }

            });

            return;
        }

       // 파일 선택 및 다운로드
const btn = e.target.closest('.securities-file');

if (!btn) return;

const selected = list.querySelector('.securities-file.is-selected');

if (selected) {
    selected.classList.remove('is-selected');
}

btn.classList.add('is-selected');

// 기존 파일만 다운로드
if (btn.dataset.url) {
    window.location.href = btn.dataset.url;
}

    });

    // ===========================
    // 업로드
    // ===========================
    submitBtn.addEventListener('click', function () {

        if (uploadFiles.length === 0) {
            alert('추가된 파일이 없습니다.');
            return;
        }

        const formData = new FormData();

        uploadFiles.forEach(function(file){
            formData.append('files[]', file);
        });

        fetch('/mypage/security/upload', {

            method:'POST',

            body:formData

        })
        .then(res=>res.json())
        .then(function(result){

            if(result.result === 'ok'){

                alert('저장되었습니다.');

                location.reload();

            }else{

                alert(result.message);

            }

        });

    });

    

})();


</script>