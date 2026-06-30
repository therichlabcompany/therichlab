<?php
$profileImage = !empty($counsel['profile_image'])
    ? '/uploads/profile/' . $counsel['profile_image']
    : SITE_IMG_URL . 'images/temp/@profile-w.png';

$companyLine = [];

if (!empty($counsel['ga'])) {
    $companyLine[] = $counsel['ga'];
} else {
    if (!empty($counsel['company'])) $companyLine[] = $counsel['company'];
    if (!empty($counsel['company_sub'])) $companyLine[] = $counsel['company_sub'];
}

$regionText = '';

if (!empty($counsel['region'])) {
    $regionText = implode(' ', array_map(
        fn($r) => fc_region_label(trim($r)),
        explode(',', $counsel['region'])
    ));
}
?>
<main>
    <section class="page-inner-narrow">
        <h1 class="page-main-title">후기 남기기</h1>
        <p class="page-main-lead">
            FC와의 상담은 어떠셨나요?<br />
            여러분의 후기가 다른 고객에게 큰 도움이 됩니다.<br />
            단, 비속어/개인정보가 포함된 내용은<br class="br-mo" />
            노출이 제한될 수 있습니다.
        </p>

        <article class="consult-request-fc">
            <img src="<?= esc($profileImage) ?>" alt="FC 프로필 사진" />

            <div>
                <h2><?= esc($counsel['fc_name']) ?></h2>

                <p>
                    <span class="c-rate">
                        <span class="c-rate-star">★</span>
                        <?= number_format($counsel['rating'] ?? 5.0, 1) ?>
                    </span>
                    <span class="c-rate-count">(<?= number_format($counsel['review_count'] ?? 0) ?>)</span>
                </p>

                <p>
                    <?php if ($regionText): ?>
                        <?= esc($regionText) ?>
                    <?php endif; ?>
                </p>

                <p><?= esc($counsel['hero_line']) ?></p>
            </div>
        </article>

        <form class="form-box" id="reviewForm">
            <input type="hidden" name="counsel_uid" value="<?= esc($counsel['counsel_uid']) ?>">
            <input type="hidden" name="fc_member_uid" value="<?= esc($counsel['fc_member_uid']) ?>">
            <div class="review-rate">
                <p class="form-label">상담은 전반적으로 어떠셨나요? <b>*</b></p>
                <div class="review-rate-stars star-half" role="radiogroup">
                    <div class="star-unit">
                        <label class="star-hit star-hit-l"><input type="radio" name="rating" value="0.5" /><span class="visually-hidden">0.5점</span></label>
                        <label class="star-hit star-hit-r"><input type="radio" name="rating" value="1" /><span class="visually-hidden">1점</span></label>
                        <span class="star-bg" aria-hidden="true">★</span>
                        <span class="star-fill" aria-hidden="true">★</span>
                    </div>
                    <div class="star-unit">
                        <label class="star-hit star-hit-l"><input type="radio" name="rating" value="1.5" /><span class="visually-hidden">1.5점</span></label>
                        <label class="star-hit star-hit-r"><input type="radio" name="rating" value="2" /><span class="visually-hidden">2점</span></label>
                        <span class="star-bg" aria-hidden="true">★</span>
                        <span class="star-fill" aria-hidden="true">★</span>
                    </div>
                    <div class="star-unit">
                        <label class="star-hit star-hit-l"><input type="radio" name="rating" value="2.5" /><span class="visually-hidden">2.5점</span></label>
                        <label class="star-hit star-hit-r"><input type="radio" name="rating" value="3" /><span class="visually-hidden">3점</span></label>
                        <span class="star-bg" aria-hidden="true">★</span>
                        <span class="star-fill" aria-hidden="true">★</span>
                    </div>
                    <div class="star-unit">
                        <label class="star-hit star-hit-l"><input type="radio" name="rating" value="3.5" /><span class="visually-hidden">3.5점</span></label>
                        <label class="star-hit star-hit-r"><input type="radio" name="rating" value="4" /><span class="visually-hidden">4점</span></label>
                        <span class="star-bg" aria-hidden="true">★</span>
                        <span class="star-fill" aria-hidden="true">★</span>
                    </div>
                    <div class="star-unit">
                        <label class="star-hit star-hit-l"><input type="radio" name="rating" value="4.5" /><span class="visually-hidden">4.5점</span></label>
                        <label class="star-hit star-hit-r"><input type="radio" name="rating" value="5" /><span class="visually-hidden">5점</span></label>
                        <span class="star-bg" aria-hidden="true">★</span>
                        <span class="star-fill" aria-hidden="true">★</span>
                    </div>
                </div>
            </div>
            <div class="form-field">
                <label class="form-label" for="compose-title">제목 <b>*</b></label>
                <p class="form-text">한줄로 요약해 주세요.</p>
                <input class="form-input" id="compose-title" name="title" type="text" placeholder="예: 꼼꼼한 상담으로 신뢰가 갔어요" />
            </div>

            <div class="form-field">
                <label class="form-label" for="compose-body">내용 <b>*</b></label>
                <p class="form-text">상담 경험을 자세히 작성해주세요.</p>
                <textarea
                    class="form-textarea"
                    id="compose-body"
                    name="body"
                    placeholder="예: 보험 용어가 어려웠는데 쉽게 설명해주셔서 좋았어요. 필요한 보장만 추천해주셔서 신뢰가 갔습니다."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit">작성 완료</button>
            </div>
        </form>
    </section>
</main>

<script>
document.getElementById('reviewForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const btn = form.querySelector('button[type="submit"]');

    const rating = form.querySelector('input[name="rating"]:checked');
    const title = form.querySelector('input[name="title"]');
    const body = form.querySelector('textarea[name="body"]');

    // ===========================
    // 🚨 필수값 체크 (프론트)
    // ===========================

    if (!rating) {
        alert('평점을 선택해주세요.');
        return;
    }

    if (!title.value.trim()) {
        alert('제목을 입력해주세요.');
        title.focus();
        return;
    }

    if (title.value.trim().length < 2) {
        alert('제목은 2자 이상 입력해주세요.');
        title.focus();
        return;
    }

    if (!body.value.trim()) {
        alert('내용을 입력해주세요.');
        body.focus();
        return;
    }

    if (body.value.trim().length < 10) {
        alert('내용은 10자 이상 입력해주세요.');
        body.focus();
        return;
    }

    // ===========================
    // 🚨 중복 클릭 방지
    // ===========================
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerText = '전송 중...';

    const formData = new FormData(form);

    fetch('/mypage/counselReviewSubmitAjax/<?= $counsel['counsel_uid'] ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {

        if (res.result === 'success') {
            window.location.href = '/mypage/counselReviewLast';
            return;
        }

        alert(res.message);

        // 실패 시 버튼 복구
        btn.disabled = false;
        btn.innerText = '작성 완료';
    })
    .catch(() => {
        alert('서버 오류가 발생했습니다.');

        // 에러 시 버튼 복구
        btn.disabled = false;
        btn.innerText = '작성 완료';
    });
});
</script>