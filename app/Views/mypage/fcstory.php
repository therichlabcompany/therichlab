<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">FC 회원정보</h1>
        <p class="page-main-lead">
            입력하신 정보는 관리자가 확인 후<br class="br-mo" />
            승인 절차를 거쳐 고객에게 노출됩니다.<br class="br-mo" />
            정확하고 신뢰할 수 있는 정보를 등록해 주시기 바랍니다.
        </p>

        <?php
              $menu_step = "step4";
              include_once (COMPONENT_PATH . '/fc_info_tab_nav.php'); 
          ?>
        <p class="signup-step-lead">
            고객은 실제로 활동하는 FC를 선별합니다.<br />
            전문성을 보여줄 수 있는 영상/사진을 업로드 하세요.
        </p>

        <form class="form-box" method="post" enctype="multipart/form-data">
            <?php include_once (COMPONENT_PATH . '/fc_stroy_input.php');  ?>
            <div class="form-actions form-actions-split">
                <a href="/mypage/fcprofile" class="btn">내 프로필 페이지로 이동</a>
                <button type="submit" class="btn btn-primary">수정 완료</button>
            </div>
        </form>
    </div>
</main>

<?php include APPPATH . 'Views/modal/fc_profile_update_modal.php'; ?>

<div class="c-modal deliberation-guide" id="fc-deliberation-registration-notice" role="dialog" aria-modal="true" aria-labelledby="fc-deliberation-registration-title" hidden>
    <button type="button" class="c-modal-backdrop" data-fc-deliberation-notice-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title" id="fc-deliberation-registration-title">심의필 절차 안내</h2>
            <button type="button" class="c-modal-close" data-fc-deliberation-notice-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body"><ul class="dash-list"><li>아래 버튼을 클릭하여 미리보기 화면을 확인해주세요. 해당 화면을 PDF로 저장하거나 캡쳐하여 근무 중인 보험사에 심의 요청을 진행해주세요.</li><li>보험사에서 발급완료한 심의필 회신문을 [마이페이지] &gt; [심의필 정보 관리] 페이지에 등록해주세요.</li><li>MyFC 관리자가 확인 후 이상 없을 시 최종 승인이 완료되며, 승인결과는 [마이페이지] &gt; [심의필 정보 관리] 페이지에서 확인 가능합니다. 또한 승인처리여부를 카카오톡 메시지로 보내드립니다.</li><li>심의필 승인처리 완료 후 <span class="warn">유료회원 멤버십</span>에 가입하셔야 최종적으로 사이트에 노출 됩니다.</li></ul></div>
        <div class="c-modal-foot"><button type="button" class="btn btn-primary" data-fc-deliberation-preview>심의 요청용 화면 미리보기</button></div>
    </div>
</div>


<style>
.thumb-preview {
    width: 100px;
    height: 100px;
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    background: #f5f5f5;
}

.thumb-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;   /* 🔥 핵심 */
    display: block;
}


/* 🔥 삭제 버튼 */
.thumb-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: none;
    background: rgba(0,0,0,0.7);
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
(function () {

    const form = document.querySelector('.signup-page .page-inner-narrow form');
    if (!form) return;

    const videoInput = document.getElementById('fc-story-video-file');
    const imageInput = document.getElementById('fc-story-image-file');
    const thumbInput = document.getElementById('fc-story-thumb-file');
    const thumbWrap = document.getElementById('fc-story-thumbs');
    const mainThumb = document.querySelector('.fc-story-thumb-main');
    const deliberationNotice = document.getElementById('fc-deliberation-registration-notice');

    // 신규 파일
    let fileStore = [];

    function closeDeliberationNotice() {
        deliberationNotice.classList.remove('is-open');
        deliberationNotice.hidden = true;
        document.body.classList.remove('popup-open');
    }

    function uid() {
        return Date.now().toString(36) + Math.random().toString(36).substring(2);
    }

    // ===========================
    // 메인 썸네일 표시
    // ===========================
    function updateMainThumb() {

        const count = thumbWrap.querySelectorAll('.thumb-preview').length;

        mainThumb.style.display = count ? 'none' : 'flex';

    }

    updateMainThumb();

    // ===========================
    // 클릭
    // ===========================
    form.addEventListener('click', function (e) {

        if (e.target.closest('.fc-story-thumb-add')) {
            thumbInput.click();
            return;
        }

        const trigger = e.target.closest('.file-upload-file-trigger');

        if (trigger) {

            const fileInput = trigger.closest('.file-upload').querySelector('input[type=file]');

            if (fileInput) fileInput.click();

        }

    });

    // ===========================
    // 이미지 추가
    // ===========================
    thumbInput.addEventListener('change', function () {

        const files = Array.from(thumbInput.files || []);

        if (!files.length) return;

        const currentCount = thumbWrap.querySelectorAll('.thumb-preview').length;

        if (currentCount + files.length > 20) {

            alert('최대 20개까지 가능합니다.');

            thumbInput.value='';

            return;

        }

        files.forEach(file => {

            if (!file.type.startsWith('image/')) return;

            const id = uid();

            const reader = new FileReader();

            reader.onload = function(ev){

                const div=document.createElement('div');

                div.className='thumb-preview';

                div.dataset.new='1';

                div.dataset.id=id;

                div.innerHTML=`
                    <img src="${ev.target.result}">
                    <button type="button" class="thumb-remove">×</button>
                `;

                thumbWrap.insertBefore(
                    div,
                    thumbWrap.querySelector('.fc-story-thumb-add')
                );

                fileStore.push({
                    id:id,
                    file:file,
                    element:div
                });

                updateMainThumb();

            };

            reader.readAsDataURL(file);

        });

        thumbInput.value='';

    });

    // ===========================
    // 삭제
    // ===========================
    thumbWrap.addEventListener('click',function(e){

        const btn=e.target.closest('.thumb-remove');

        if(!btn) return;

        const item=btn.closest('.thumb-preview');

        if(item.dataset.new){

            fileStore=fileStore.filter(v=>v.element!==item);

        }

        item.remove();

        updateMainThumb();

    });

    // ===========================
    // Drag
    // ===========================
    new Sortable(thumbWrap,{

        animation:150,

        draggable:'.thumb-preview'

    });

    // ===========================
    // 파일명 표시
    // ===========================
    form.addEventListener('change',function(e){

        const t=e.target;

        if(t.type!=='file') return;

        const display=t.closest('.file-upload')?.querySelector('input[readonly]');

        if(display && t.files.length){

            display.value=t.files[0].name;

        }

    });

    // ===========================
    // 저장
    // ===========================
    form.addEventListener('submit',async function(e){

        e.preventDefault();

        const previews=[
            ...thumbWrap.querySelectorAll('.thumb-preview')
        ];

        if(previews.length===0){

            alert('스토리 이미지를 최소 1개 이상 등록해주세요.');

            return;

        }

        const formData=new FormData();

        if(videoInput.files.length){

            formData.append(
                'story_video',
                videoInput.files[0]
            );

        }

        if(imageInput.files.length){

            formData.append(
                'story_image',
                imageInput.files[0]
            );

        }

        previews.forEach(function(el,index){

            formData.append(
                'story_images_sort[]',
                index
            );

            // 기존 이미지
            if(el.dataset.existing){

                formData.append(
                    'story_image_order[]',
                    'existing:' + el.dataset.id
                );

                formData.append(
                    'keep_images[]',
                    el.dataset.id
                );

            }

            // 신규 이미지
            if(el.dataset.new){

                const obj=fileStore.find(v=>v.element===el);

                if(obj){

                    formData.append(
                        'story_image_order[]',
                        'new'
                    );

                    formData.append(
                        'story_images[]',
                        obj.file
                    );

                }

            }

        });

        if (!(await window.MyFCProfileUpdateModal.confirm())) return;

        try{

            const res=await fetch('/member/fc/story/save',{

                method:'POST',

                body:formData

            });

            const result=await res.json();

            if(result.status==='success'){
                // 심의필을 아직 등록하지 않은 FC는 최초 등록 절차 안내를 표시한다.
                if (result.show_deliberation_registration_notice) {
                    deliberationNotice.hidden = false;
                    deliberationNotice.classList.add('is-open');
                    document.body.classList.add('popup-open');
                    return;
                }

                window.MyFCProfileUpdateModal.showComplete();
                return;

            }else{

                alert(result.message || '저장 실패');

            }

        }catch(err){

            console.error(err);

            alert('서버 오류');

        }

    });

    deliberationNotice.querySelectorAll('[data-fc-deliberation-notice-close]').forEach(function (button) {
        button.addEventListener('click', closeDeliberationNotice);
    });

})();
</script>
