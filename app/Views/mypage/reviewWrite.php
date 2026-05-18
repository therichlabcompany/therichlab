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

        <form class="form-box" action="MFC004_L01_04_02.html" method="post">
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