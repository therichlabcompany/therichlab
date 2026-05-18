<main>
    <div class="page-inner-narrow">
        <h1 class="page-main-title">회원 탈퇴 시 유의사항</h1>

        <form class="form-box" method="post">
            <section class="dash-notice">
                <h2>탈퇴 전 꼭 확인하세요</h2>
                <ul class="dash-list">
                    <li>탈퇴 시 MyFC 계정을 통해 등록한 모든 프로필, 리뷰, 활동 이력이 영구적으로 삭제되며 복구가 불가능합니다.</li>
                    <li>탈퇴 즉시, 진행 중인 승인 검토나 노출 대기 상태의 FC 등록 신청은 자동으로 취소됩니다.</li>
                    <li>삭제된 정보는 고객에게 노출될 수 없으며, 향후 재가입 시 새로 등록 절차를 진행해야 합니다.</li>
                </ul>
            </section>

            <section class="dash-notice">
                <h2>데이터 백업 안내</h2>
                <ul class="dash-list">
                    <li>필요 시, 탈퇴 이전에 프로필 이미지, 소개글, 후기 등 개인 보관이 필요한 데이터를 미리 저장해 주세요.</li>
                    <li>MyFC는 회원 탈퇴 이후 개인 데이터의 복구를 지원하지 않습니다.</li>
                </ul>
            </section>

            <section class="dash-notice">
                <h2>등록 정보 관리</h2>
                <ul class="dash-list">
                    <li>카카오 계정과 연동되지 않은 외부 링크, SNS, 영상 등은 자동 삭제되지 않을 수 있습니다.</li>
                    <li>탈퇴 전 직접 삭제를 권장드리며, 삭제되지 않은 콘텐츠는 추후 노출 제한 처리될 수 있습니다.</li>
                </ul>
            </section>

            <div class="form-field">
                <label class="form-label" for="withdraw-email">탈퇴하려는 계정</label>
                <input class="form-input" id="withdraw-email" name="email" type="email" value="username@gmail.com" readonly />
            </div>

            <div class="form-field">
                <label class="form-label" for="withdraw-consult">상담 현황</label>
                <input class="form-input" id="withdraw-consult" name="consult_count" type="text" value="0 건" readonly />
            </div>

            <div>
                <label class="c-check">
                    <input type="checkbox" name="agree_withdraw" />
                    <span>회원 탈퇴를 진행하면 MyFC에 저장된 모든 정보를 삭제하는 데 동의합니다.</span>
                </label>
            </div>

            <div class="form-actions">
                <button type="button">회원 탈퇴 취소</button>
                <button type="submit" disabled>회원 탈퇴</button>
            </div>
        </form>
    </div>
</main>