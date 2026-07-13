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
            <!-- <div class="form-actions form-actions-split">
                <a href="/mypage/fcprofile" class="btn">내 프로필 페이지로 이동</a>
                <button type="submit" class="btn btn-primary">저장하고 내 FC 페이지 보기</button>
            </div> -->

            <div class="form-actions">
                <button type="submit">저장하고 내 FC 페이지 보기</button>
            </div>
        </form>
    </div>
</main>


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

    // 신규 파일
    let fileStore = [];

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

        try{

            const res=await fetch('/member/fc/story/save',{

                method:'POST',

                body:formData

            });

            const result=await res.json();

            if(result.status==='success'){

                location.href=result.redirect_url || '/fc/view?uid=<?= rawurlencode((string) session()->get('member_uid')) ?>';

            }else{

                alert(result.message || '저장 실패');

            }

        }catch(err){

            console.error(err);

            alert('서버 오류');

        }

    });

})();
</script>
