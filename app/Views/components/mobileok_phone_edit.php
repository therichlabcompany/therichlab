<?php if (!empty($mobileOkJsUrl) && !empty($mobileOkEnabled)): ?>
<script src="<?= esc($mobileOkJsUrl) ?>"></script>
<?php endif; ?>

<script>
(() => {
    const enabled = <?= json_encode((bool) ($mobileOkEnabled ?? false)) ?>;
    const requestUrl = <?= json_encode($mobileOkRequestUrl ?? '') ?>;
    const resultUrl = <?= json_encode($mobileOkResultUrl ?? '') ?>;
    const phoneInput = document.getElementById('phone');
    const phoneButton = document.getElementById('btnPhoneCheck');
    let phoneVerified = false;

    const digitsOnly = (value) => String(value || '').replace(/\D/g, '');
    const setButtonLabel = (label) => {
        if (phoneButton) {
            phoneButton.textContent = label;
        }
    };

    window.mypagePhoneAuthResult = function (result) {
        let payload = result;

        if (typeof result === 'string') {
            try {
                payload = JSON.parse(result);
            } catch (error) {
                payload = { resultMsg: result };
            }
        }

        if (!payload || payload.status === 'error' || (payload.resultCode && payload.resultCode !== '2000')) {
            alert(payload?.message || payload?.resultMsg || '휴대폰 인증에 실패했습니다.');
            return;
        }

        fetch(resultUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payload })
        })
            .then((response) => response.json())
            .then((response) => {
                if (response.status !== 'success') {
                    alert(response.message || '휴대폰 인증 처리 중 오류가 발생했습니다.');
                    return;
                }

                const phone = digitsOnly(response.userPhone ?? response.phone);
                if (!phone) {
                    alert('인증된 휴대폰 번호를 확인할 수 없습니다.');
                    return;
                }

                phoneInput.value = phone;
                phoneInput.readOnly = true;
                phoneVerified = true;
                setButtonLabel('인증완료');
            })
            .catch(() => alert('서버 통신 실패'));
    };

    phoneButton?.addEventListener('click', () => {
        if (!enabled || !window.MOBILEOK || typeof window.MOBILEOK.process !== 'function' || !requestUrl) {
            alert('휴대폰 본인인증 설정이 완료되지 않았습니다.');
            return;
        }

        // 인증 결과의 휴대폰 번호를 사용하므로, 기존 번호를 먼저 수정할 필요가 없습니다.
        phoneVerified = false;
        window.MOBILEOK.process(requestUrl, 'WB', 'mypagePhoneAuthResult');
    });

    phoneInput?.addEventListener('input', () => {
        phoneVerified = false;
        setButtonLabel('인증');
    });
})();
</script>
