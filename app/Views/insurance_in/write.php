<?php $editing = !empty($question); ?>
<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">보험IN</h1>
        <p class="page-main-lead">보험에 대한 궁금한 문의사항을 등록하시면 <br class="br-mo">FC님들이 실시간 답변 해드려요.</p>
        <?php if (session('error')): ?><p class="insurance-in-alert warn"><?= esc(session('error')) ?></p><?php endif; ?>
        <form class="form-box" method="post" enctype="multipart/form-data" action="<?= $editing ? base_url('insurance-in/' . (int) $question['question_id'] . '/edit') : base_url('insurance-in/write') ?>">
            <?= csrf_field() ?>
            <div class="form-field"><label class="form-label" for="insurance-in-title">제목 (10자 이상 50자 이하)</label>
                <input class="form-input" id="insurance-in-title" name="title" value="<?= esc(old('title', $question['title'] ?? '')) ?>" minlength="10" maxlength="50" required placeholder="질문 제목을 입력해 주세요.">
            </div>
            <div class="form-field"><label class="form-label" for="insurance-in-body">내용 (10자 이상)</label>
                <textarea class="form-textarea" id="insurance-in-body" name="body" minlength="10" required placeholder="궁금한 내용을 자세히 입력해 주세요."><?= esc(old('body', $question['body'] ?? '')) ?></textarea>
            </div>
            <div class="form-field"><label class="form-label" for="insurance-in-file">첨부파일</label>
                <div class="file-upload" data-insurance-upload>
                <div class="file-upload-rows">
                  <div data-upload-row>
                        <input class="form-input" data-file-name readonly placeholder="파일을 선택해 주세요"><input class="visually-hidden" id="insurance-in-file" name="attach_file" type="file" accept=".pdf,.xls,.xlsx,.hwp,.jpg,.jpeg,.png,.gif"><button type="button" class="file-upload-file-trigger" onclick="document.getElementById('insurance-in-file').click()">파일찾기</button>
                    </div>
                </div>
                </div>
                <ul class="dash-list">
                    <li>첨부 가능한 파일은 20MB 이하의 <strong class="warn">.pdf .xls .xlsx .hwp .jpg .png .gif</strong>입니다.</li>
                    <li>첨부파일로 인한 개인정보 유출에 주의해주세요. MyFC는 법적 책임을 갖지 않습니다.</li>
                </ul>
            </div>
            <section class="gray-box">
                <p><b>*</b> 필수 주의사항</p>
                <ul class="dash-list">
                    <li>상담글에 FC 답변 등록 시 글 수정/삭제가 불가합니다.</li>
                    <li>아래 사항을 꼭 주의해주세요. 문제 시 서비스 이용에 제한될 수 있습니다.</li>
                </ul>
                <ol>
                    <li>개인정보 및 외부 링크 포함 노출</li>
                    <li>보험 관련 상담글이 아닌 경우</li>
                    <li>동일/유사 게시물의 반복 게재</li>
                    <li>의미 없는 문자의 나열</li>
                    <li>욕설·혐오·비하 또는 타인의 권리를 침해하는 내용</li>
                </ol>
            </section>
            <section class="insurance-in-agree">
                <p>모든 작성자는 본인이 작성한 내용에 대해 법적 책임을 갖습니다.</p><label class="c-check"><input type="checkbox" name="agree_notice" value="1" required><span>위 안내 사항을 모두 확인했으며, 이에 동의합니다.</span></label>
            </section>
            <div class="form-actions"><button type="submit" class="btn btn-primary">질문 <?= $editing ? '수정' : '등록' ?></button></div>
        </form>
    </div>
</main>
<script>
    document.getElementById('insurance-in-file')?.addEventListener('change', function() {
        document.querySelector('[data-file-name]').value = this.files[0]?.name || '';
    });
</script>
