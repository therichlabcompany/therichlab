<!doctype html>
<html lang="ko">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="MyFC 고객 지원 및 문의 안내" />
    <title>고객 지원 | MyFC</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/images/myfc-favicon.svg') ?>" />
    <link rel="stylesheet" href="<?= asset_url('assets/css/reset.css') ?>" />
    <link rel="stylesheet" href="<?= asset_url('assets/css/common.css') ?>" />
    <link rel="stylesheet" href="<?= asset_url('assets/css/customer-support.css') ?>" />
</head>

<body class="customer-support-page">
    <main class="customer-support">
        <a class="customer-support-brand" href="<?= base_url('/') ?>" aria-label="MyFC 홈으로 이동">
            <img src="<?= base_url('assets/images/logo.png') ?>" alt="MyFC" />
        </a>

        <section class="customer-support-content" aria-labelledby="support-title">
            <div class="support-icon" aria-hidden="true">?</div>
            <h1 id="support-title">고객 지원</h1>
            <p class="support-intro">앱 이용 중 궁금한 점이나 문제가 있으면 문의해 주세요.</p>

            <div class="support-card">
                <h2>문의하기</h2>
                <p>오류 신고, 기능 문의, 결제 및 이용 관련 문의는 아래 이메일로 보내 주세요.<br class="support-break" /> 가능한 한 빠르게 확인 후 답변드리겠습니다.</p>
                <a class="support-mail-button" href="mailto:help@myfc.co.kr">이메일 문의</a>
                <a class="support-mail-address" href="mailto:help@myfc.co.kr">help@myfc.co.kr</a>
            </div>
        </section>

        <footer class="customer-support-footer">© <?= date('Y') ?> MyFC. All rights reserved.</footer>
    </main>
</body>

</html>
