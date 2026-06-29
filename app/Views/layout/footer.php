<!-- 푸터 -->
<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-columns">
            <div class="footer-col">
                <p class="footer-logo">
                    <a href="/" class="footer-logo-link"><img src="<?= SITE_IMG_URL ?>images/footer-logo.svg" alt="MyFC" /></a>
                </p>
                <div class="footer-info">
                    <p class="footer-ceo">
                        <span>(주)더리치랩</span>
                        <span>대표이사 박영진</span>
                    </p>
                    <p class="footer-info-split">
                        <span>서울특별시 강남구 테헤란로 507, 8층 131호</span>
                        <span>E-mail : <a href="mailto:myfc.helpdesk@gmail.com">myfc.helpdesk@gmail.com</a></span>
                    </p>
                    <p class="footer-info-split">
                        <span>사업자등록번호 : 762-88-02797</span>
                        <span>통신판매번호 : 제 2025-서울강남-06308호</span>
                    </p>
                    <p class="footer-info-split">
                        <span>이용문의 : 010-9941-2899</span>
                    </p>
                </div>

                <p class="copyright">© 2026 TheRichLab Corp.</p>
            </div>
            <div class="footer-trail">
                <ul class="footer-links">
                    <li><a href="#">회사소개</a></li>
                    <li><a href="#">이용약관</a></li>
                    <li><a href="#">개인정보 처리방침</a></li>
                    <li><a href="#">법적책임</a></li>
                </ul>
                <div class="footer-patent">
                    <p class="footer-patent-mark">
                        <img src="<?= SITE_IMG_URL ?>images/kipo-logo.svg" alt="특허청" />
                        <span>특허청</span>
                    </p>
                    <div class="footer-patent-text">
                        <p>MyFC의 서비스는 여러 건의 특허가 출원되어 있습니다</p>
                        <p>(특허번호 : 10-2026-0062616 / <br class="br-mo" />10-2026-0064554 외 다수)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
</div>
<?php $isReject = ($review['status'] ?? '') === 'REJECT'; ?>

<?php if ($isReject): ?>
<div class="c-modal sm is-open" role="dialog" aria-modal="true">

    <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>

    <div class="c-modal-panel">

        <div class="c-modal-head">
            <h2 class="c-modal-title">승인 거부 사유</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>

        <div class="c-modal-body">

            <p class="c-modal-meta">
                <time>
                    <?= esc($review['updated_at'] ?? '') ?>
                </time>
            </p>

            <textarea class="form-textarea" name="reject_reason" readonly rows="6">
<?= esc($review['reject_reason'] ?? '거부 사유가 없습니다.') ?>
            </textarea>

        </div>

        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" onclick="location.href='/mypage/fcreviewed';">심의필 등록 바로가기</button>
        </div>

    </div>
</div>
<?php endif; ?>


<?php
// 팝업 파일들을 동적으로 include
if (!empty($popup_page) && is_array($popup_page)) {
    foreach ($popup_page as $popup) {
        $file = POPUP_PATH . '/' . $popup;

        // 파일이 존재할 때만 include
        if (is_file($file)) {
            include_once $file;
        }
    }
}

include_once (POPUP_PATH . '/popup_logout.php'); 
?>
</body>

</html>