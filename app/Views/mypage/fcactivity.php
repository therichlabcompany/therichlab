<main>
        <div class="page-inner-narrow">
          <h1 class="page-main-title">FC 회원정보</h1>
          <p class="page-main-lead">
            입력하신 정보는 관리자가 확인 후<br class="br-mo" />
            승인 절차를 거쳐 고객에게 노출됩니다.<br />
            정확하고 신뢰할 수 있는 정보를 등록해 주시기 바랍니다.
          </p>

          <?php
              $menu_step = "step3";
              include_once (COMPONENT_PATH . '/fc_info_tab_nav.php'); 
          ?>
          <p class="signup-step-lead">
            전문성을 보여줄 수 있는 정보를 입력해주세요.<br />
            고객은 이를 통해 상담 파트너를 선택합니다.
          </p>

          <form class="form-box" method="post" enctype="multipart/form-data">
            <?php include_once (COMPONENT_PATH . '/fc_activity_input.php');  ?>

            <div class="form-actions form-actions-split">
              <a href="/mypage/fcprofile" class="btn">내 프로필 페이지로 이동</a>
              <button type="submit" class="btn btn-primary">수정 완료</button>
            </div>
          </form>
        </div>
      </main>

<?php include APPPATH . 'Views/modal/fc_profile_update_modal.php'; ?>


<script>
        (function () {

    'use strict';

    const proofBlock = document.getElementById('fc-proof-block');

    if (!proofBlock) return;

    const addBtn = proofBlock.querySelector('[data-proof-add]');
    const modeEl = proofBlock.querySelector('.proof-mode');
    const panelsRoot = proofBlock.querySelector('.proof-panels');
    const panels = proofBlock.querySelectorAll('[data-proof-panel]');
    const deleteInput = document.getElementById('proof-delete-items');

    let deleteIds = [];

    function getType() {

        const checked = modeEl.querySelector('input[type=radio]:checked');

        return checked ? checked.value : 'file';

    }

    function showPanel(type) {

        panels.forEach(panel => {

            panel.hidden = panel.dataset.proofPanel !== type;

        });

    }

    function addRow(type) {

        const tpl = document.querySelector(
            'template[data-proof-template="' + type + '"]'
        );

        if (!tpl) return;

        const row = tpl.content.firstElementChild.cloneNode(true);

        row.dataset.new = "1";

        proofBlock
            .querySelector('[data-proof-rows="' + type + '"]')
            .appendChild(row);

    }

    modeEl.addEventListener('change', function () {

        showPanel(getType());

    });

    if (addBtn) {

        addBtn.addEventListener('click', function () {

            addRow(getType());

        });

    }

    panelsRoot.addEventListener('click', function (e) {

        const fileBtn = e.target.closest('[data-file-trigger]');

        if (fileBtn) {

            const row = fileBtn.closest('[data-proof-row]');

            row.querySelector('input[type=file]').click();

            return;

        }

        const removeBtn = e.target.closest('[data-row-remove]');

        if (removeBtn) {

            const row = removeBtn.closest('[data-proof-row]');

            const idInput = row.querySelector('input[name="proof_item_id"]');

            if (idInput) {

                deleteIds.push(idInput.value);

                deleteInput.value = deleteIds.join(',');

            }

            row.remove();

            return;

        }

    });

    panelsRoot.addEventListener('change', function (e) {

        if (e.target.type !== 'file') return;

        const file = e.target.files[0];

        const row = e.target.closest('[data-proof-row]');

        if (!row) return;

        const display = row.querySelector('input[readonly]');

        if (display) {

            display.value = file ? file.name : '';

        }

        let preview = row.querySelector('.proof-preview img');

        if (!preview) {

            const wrap = document.createElement('div');

            wrap.className = 'proof-preview';

            preview = document.createElement('img');

            preview.style.maxWidth = "180px";

            wrap.appendChild(preview);

            row.appendChild(wrap);

        }

        if (file) {

            preview.src = URL.createObjectURL(file);

        }

    });

    // 수정모드 : 기존 데이터 없을 때만 1개 생성

    ['file','link','text'].forEach(function(type){

        const list = proofBlock.querySelector('[data-proof-rows="'+type+'"]');

        if(list.children.length===0){

            addRow(type);

        }

    });

    showPanel(getType());

})();
    </script>      


<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('.form-box');


    const region = document.getElementById('fc-region-value');
    const insurance = document.getElementById('fc-insurance-value');
    const history = document.getElementById('fc-history');
    const intro = document.getElementById('fc-intro');
    const career = document.getElementById('fc-career');

    const proofBlock = document.getElementById('fc-proof-block');

    if (!form || !proofBlock) return;

    // =========================
    // 완성된 이력/인증 행만 저장한다. 미완성 행은 중간 저장을 막지 않는다.
    // =========================
    function getCompleteRows() {

        const allRows = proofBlock.querySelectorAll('[data-proof-row]');

        return [...allRows].filter(row => {
            const type = getRowType(row);
            const itemId = row.querySelector('input[name="proof_item_id"]')?.value || '';

            if (type === 'file') {
                const title = row.querySelector('input[name="proof_name"]')?.value.trim() || '';
                const file = row.querySelector('input[name="proof_file"]');
                return title !== '' && (itemId !== '' || (file?.files?.length ?? 0) > 0);
            }
            if (type === 'link') {
                return (row.querySelector('input[name="proof_link_name"]')?.value.trim() || '') !== ''
                    && (row.querySelector('input[name="proof_link_url"]')?.value.trim() || '') !== '';
            }

            return (row.querySelector('input[name="proof_other_name"]')?.value.trim() || '') !== ''
                && (row.querySelector('input[name="proof_other_text"]')?.value.trim() || '') !== '';
        });
    }

    // =========================
    // type 판별
    // =========================
    function getRowType(row) {

        if (row.querySelector('input[name="proof_file"]')) return 'file';
        if (row.querySelector('input[name="proof_link_url"]')) return 'link';
        if (row.querySelector('input[name="proof_other_text"]')) return 'text';

        return '';
    }

    // =========================
    // submit
    // =========================
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData();

            
        const deleteItems = document.getElementById('proof-delete-items');

        if (deleteItems && deleteItems.value) {
            formData.append('delete_items', deleteItems.value);
        }

        formData.append('region', region.value);
        formData.append('insurance_types', insurance.value);
        formData.append('history', history.value.trim());
        formData.append('intro', intro.value.trim());
        formData.append('career', career.value.trim());

        const rows = getCompleteRows();

        rows.forEach((row, index) => {

            const type = getRowType(row);

            const itemId =
                row.querySelector('input[name="proof_item_id"]')?.value || '';

            if (itemId) {
                formData.append(`items[${index}][item_id]`, itemId);
            }

            formData.append(`items[${index}][type]`, type);

            if (type === 'file') {

                const title =
                    row.querySelector('input[name="proof_name"]')?.value.trim() || '';

                formData.append(`items[${index}][title]`, title);

                const fileInput =
                    row.querySelector('input[name="proof_file"]');

                if (fileInput.files.length > 0) {

                    formData.append(
                        `items[${index}][file]`,
                        fileInput.files[0]
                    );

                }

            }

            else if (type === 'link') {

                const title =
                    row.querySelector('input[name="proof_link_name"]').value.trim();

                const url =
                    row.querySelector('input[name="proof_link_url"]').value.trim();

                formData.append(`items[${index}][title]`, title);
                formData.append(`items[${index}][url]`, url);

            }

            else {

                const title =
                    row.querySelector('input[name="proof_other_name"]').value.trim();

                const content =
                    row.querySelector('input[name="proof_other_text"]').value.trim();

                formData.append(`items[${index}][title]`, title);
                formData.append(`items[${index}][content]`, content);

            }

        });

        if (!(await window.MyFCProfileUpdateModal.confirm())) return;

        try {

            const res = await fetch('/member/fc/activity/save', {
                method: 'POST',
                body: formData
            });

            const result = await res.json();

            if (result.status === 'success') {
                window.MyFCProfileUpdateModal.showComplete();
            } else {
                alert(result.message || '저장 실패');
            }

        } catch (err) {
            console.error(err);
            alert('서버 오류가 발생했습니다.');
        }

    });

});
</script>
