<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? '관리자 로그인') ?></title>
    <link rel="icon" type="image/png" href="<?= asset_url('assets/images/myfc-symbol.png') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= asset_url('assets/images/myfc-symbol.png') ?>">

    <!-- AdminLTE 4 CSS -->
    <link rel="stylesheet" href="<?= asset_url('assets/adminlte/dist/css/adminlte.min.css') ?>">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #6dd5ed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(10px);
        }

        .login-header {
            padding: 40px 30px 20px;
            text-align: center;
            background: #ffffff;
        }

        .login-header .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 34px;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.35);
        }

        .login-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
        }

        .login-header p {
            margin: 8px 0 0;
            color: #718096;
            font-size: 14px;
        }

        .login-body {
            background: #ffffff;
            padding: 10px 30px 35px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
            border: 1px solid #dfe3e8;
            padding: 0 15px;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
        }

        .btn-login {
            height: 52px;
            border: 0;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            box-shadow: 0 12px 25px rgba(0, 123, 255, 0.30);
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(0, 123, 255, 0.35);
        }

        .alert {
            border-radius: 12px;
            font-size: 14px;
        }

        .login-help-note {
            margin: 18px 0 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.55;
            text-align: center;
            word-break: keep-all;
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.85);
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <div class="icon">A</div>
            <h1>Admin Login</h1>
            <p>관리자 시스템에 로그인하세요.</p>
        </div>

        <div class="login-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger mb-4">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('admin/login') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">아이디</label>
                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="아이디를 입력하세요"
                        value="<?= old('username') ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label">비밀번호</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="비밀번호를 입력하세요"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100">
                    로그인
                </button>
            </form>

            <p class="login-help-note">
                아이디 및 비밀번호 분실 시 고객센터로 문의 주세요<br>
                문의E-mail : help@myfc.co.kr
            </p>
        </div>
    </div>

    <div class="login-footer">
        © <?= date('Y') ?> Admin System
    </div>
</div>

<script src="<?= asset_url('assets/adminlte/dist/js/adminlte.min.js') ?>"></script>
</body>
</html>
