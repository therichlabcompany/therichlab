<?php $editing = !empty($answer); ?>
<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">보험IN</h1>
        <p class="page-main-lead">보험에 대한 궁금한 문의사항을 등록하시면 <br class="br-mo">FC님들이 실시간 답변 해드려요.</p>
        <div class="insurance-in-register">
            <article class="insurance-in-question">
                <div class="insurance-in-question-head">
                    <p class="insurance-in-question-label">질문</p>
                    <h2><?= esc($question['title']) ?></h2>
                    <p><?= nl2br(esc($question['body'])) ?></p>
                    <p class="insurance-in-meta"><span>작성일(<?= date('Y-m-d', strtotime($question['created_at'])) ?>)</span><span>조회수 <?= number_format($question['view_count']) ?></span></p>
                </div>
            </article>
            <?php if (session('error')): ?><p class="insurance-in-alert warn"><?= esc(session('error')) ?></p><?php endif; ?>
            <form class="form-box" method="post" action="<?= $editing ? base_url('insurance-in/' . $question['question_id'] . '/answer/' . $answer['answer_id']) : base_url('insurance-in/' . $question['question_id'] . '/answer') ?>"><?= csrf_field() ?>
                <div class="form-field">
                    <p class="insurance-in-answer-date">답변 작성일(<?= date('Y-m-d') ?>)</p><textarea class="form-textarea" id="insurance-in-answer-body" name="answer" required placeholder="답변 내용을 입력해 주세요."><?= esc(old('answer', $answer['body'] ?? '')) ?></textarea>
                </div>
                <section class="gray-box">
                    <p><b>*</b> 필수 주의사항</p>
                    <ul class="dash-list">
                        <li>금융소비자보호법에 위반되지 않도록 주의바랍니다. 보험상품 안내, 가입금액 관련 내용은 포함할 수 없습니다.</li>
                        <li>개인정보, 외부 링크, 욕설·비하 및 타인의 권리를 침해하는 내용은 등록할 수 없습니다.</li>
                    </ul>
                </section>
                <section class="insurance-in-agree">
                    <p>모든 작성자는 본인이 작성한 내용에 대해 법적 책임을 갖습니다.</p><label class="c-check"><input type="checkbox" name="agree_notice" value="1" required><span>위 안내 사항을 모두 확인했으며, 이에 동의합니다.</span></label>
                </section>
                <div class="form-actions"><button class="btn btn-primary">답변 <?= $editing ? '수정' : '등록' ?></button></div>
            </form>
        </div>
    </div>
</main>