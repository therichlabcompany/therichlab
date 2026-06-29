<!-- GNB「회원가입」진입 화면 — 중앙 팝업만 노출 -->
<div class="c-modal signup-type " role="dialog" aria-modal="true" id = "join_select"><!-- is-open -->
    <button type="button" class="c-modal-backdrop" data-popup-close aria-label="닫기"></button>
    <div class="c-modal-panel">
        <div class="c-modal-head">
            <h2 class="c-modal-title">회원가입 선택</h2>
            <button type="button" class="c-modal-close" data-popup-close aria-label="닫기"></button>
        </div>
        <div class="c-modal-body">
            <div class="signup-type-options">
                <button type="button" class="signup-type-card individual" onclick="location.href='/member/join';">
                    <span aria-hidden="true">
                        <img src="../assets/images/ic-signup-individual.svg" alt="" />
                    </span>
                    <strong>개인회원 가입</strong>
                    <p>개인회원으로 가입하시고 원하는 설계사를 통해 보험상담하세요.</p>
                </button>
                <button type="button" class="signup-type-card fc" onclick="location.href='/member/fcAgree';">
                    <span aria-hidden="true">
                        <img src="../assets/images/ic-signup-fc.svg" alt="" />
                    </span>
                    <strong>FC회원 가입</strong>
                    <p>FC회원으로 가입하시고 프로필도 홍보하고 상담신청 받아보세요.</p>
                </button>
            </div>
        </div>
        <div class="c-modal-foot">
            <button type="button" class="btn btn-line" data-popup-close>확인</button>
        </div>
    </div>
</div>