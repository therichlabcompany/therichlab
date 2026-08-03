<?php

namespace App\Controllers;

class MemberController extends BaseController
{
    public function login(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/login', $data);
    }

    public function find(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page account-find-page";
        $popup_page = [];

        $modal_page = [];


        $mobileOk = service('mobileOk');
        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            'mobileOkEnabled' => $mobileOk->isConfigured(),
            'mobileOkJsUrl' => $mobileOk->requestJsUrl(),
            'mobileOkRequestUrl' => base_url('member/phone-auth/request'),
            'mobileOkResultUrl' => $mobileOk->returnUrl(),
        ];


        return $this->renderView('member/find', $data);
    }

    public function findResult(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $session = session();
        $email = (string) $session->get('account_find_email');
        $expiresAt = (int) $session->get('account_find_expires_at');
        if ($email === '' || $expiresAt < time()) {
            $session->remove(['account_find_email', 'account_find_expires_at']);
            return redirect()->to('/member/find')->with('error', '휴대폰 본인인증 후 계정을 확인해주세요.');
        }

        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            'email' => $email,
        ];


        return $this->renderView('member/findResult', $data);
    }

    public function accountFindAuthStart()
    {
        session()->set('phone_auth_purpose_pending', 'account_find');
        return $this->response->setJSON(['status' => 'success']);
    }

    public function accountFindResult()
    {
        $session = session();
        $phone = preg_replace('/\D/', '', (string) $session->get('phone_auth_phone'));
        $verifiedAt = strtotime((string) $session->get('phone_auth_verified_at'));
        if (!(bool) $session->get('phone_auth_verified') || $phone === '' || !$verifiedAt || $verifiedAt < time() - 600) {
            return $this->response->setJSON(['status' => 'error', 'message' => '휴대폰 본인인증을 먼저 완료해주세요.']);
        }

        $member = \Config\Database::connect()->table('my_fc_member')->select('email')
            ->where('phone', $phone)->where('status', 'ACTIVE')->where('deleted_at IS NULL', null, false)
            ->orderBy('member_id', 'DESC')->get()->getRowArray();
        if (!$member) {
            return $this->response->setJSON(['status' => 'error', 'message' => '인증한 휴대폰 번호로 가입된 계정을 찾을 수 없습니다.']);
        }

        $session->set(['account_find_email' => $member['email'], 'account_find_expires_at' => time() + 600]);
        $session->remove('phone_auth_purpose');
        return $this->response->setJSON(['status' => 'success', 'redirect' => base_url('member/findResult')]);
    }

    public function passEmail(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page password-reset-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/passEmail', $data);
    }

    public function passReset(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page password-reset-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/passReset', $data);
    }

    public function passResult(): string
    {
        //return pageView('welcome_message');
        $header_class = "password-reset-page flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/passResult', $data);
    }

    public function passwordResetRequest()
    {
        $session = session();
        $memberUid = (string) $session->get('member_uid');
        if (!$session->get('logged_in') || $memberUid === '') {
            return redirect()->to('/member/login')->with('error', '로그인 후 이용해주세요.');
        }

        $member = \Config\Database::connect()->table('my_fc_member')->select('email, member_type')
            ->where('member_uid', $memberUid)->whereIn('member_type', ['USER', 'FC'])->where('status', 'ACTIVE')
            ->where('deleted_at IS NULL', null, false)->get()->getRowArray();
        if (!$member) return redirect()->to('/member/login')->with('error', '사용할 수 없는 회원입니다. 다시 로그인해주세요.');

        return $this->renderView('member/password_reset_request', [
            'header_class' => 'form-page password-reset-page',
            'email' => (string) $member['email'],
        ]);
    }

    public function sendPasswordResetMail()
    {
        $session = session();
        $memberUid = (string) $session->get('member_uid');
        $emailAddress = trim((string) $this->request->getPost('email'));
        if (!$session->get('logged_in') || $memberUid === '') {
            return redirect()->to('/member/login')->with('error', '로그인이 필요합니다.');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('my_fc_password_reset_token')) {
            return redirect()->back()->withInput()->with('error', '비밀번호 재설정 기능 준비가 필요합니다. 관리자에게 문의해주세요.');
        }
        $member = $db->table('my_fc_member')->where('member_uid', $memberUid)
            ->whereIn('member_type', ['USER', 'FC'])->where('status', 'ACTIVE')
            ->where('deleted_at IS NULL', null, false)->get()->getRowArray();
        if (!$member || !hash_equals((string) $member['email'], $emailAddress)) {
            return redirect()->back()->withInput()->with('error', '현재 로그인한 회원의 이메일 주소를 정확히 입력해주세요.');
        }

        $config = config('Email');
        if (empty($config->fromEmail)) {
            return redirect()->back()->withInput()->with('error', '메일 발송 설정이 완료되지 않았습니다. 관리자에게 문의해주세요.');
        }

        $token = bin2hex(random_bytes(32));
        $db->table('my_fc_password_reset_token')->where('member_uid', $memberUid)->delete();
        $db->table('my_fc_password_reset_token')->insert([
            'member_uid' => $memberUid,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $resetUrl = base_url('member/password-reset?token=' . rawurlencode($token));
        $mail = \Config\Services::email();
        $mail->setFrom($config->fromEmail, $config->fromName ?: 'MyFC');
        $mail->setTo($member['email']);
        $mail->setSubject('[MyFC] 비밀번호 재설정 안내');
        $mail->setMailType('html');
        $memberName = htmlspecialchars((string) ($member['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $memberEmail = htmlspecialchars((string) $member['email'], ENT_QUOTES, 'UTF-8');
        $logoUrl = base_url('assets/images/logo.png');
        $mail->setMessage(<<<HTML
<!doctype html>
<html lang="ko"><body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,'Noto Sans KR',sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f4f6;"><tr><td align="center" style="padding:40px 16px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#ffffff;border-radius:16px;overflow:hidden;">
<tr><td style="padding:32px 40px 0;"><img src="{$logoUrl}" alt="MyFC" style="display:block;width:auto;height:30px;border:0;"></td></tr>
<tr><td style="padding:32px 40px 80px;"><h1 style="margin:0 0 28px;font-size:28px;line-height:1.35;font-weight:800;color:#111827;">[MyFC] 비밀번호 재설정 안내</h1>
<div style="padding:80px;background:#f3f4f6;border-radius:12px;font-size:16px;line-height:1.8;color:#374151;">
<p style="margin:0 0 24px;">안녕하세요, {$memberName}님, MyFC입니다.</p>
<p style="margin:0 0 32px;"><strong style="font-weight:700;color:#111827;">{$memberEmail}</strong> 계정의 비밀번호를 재설정하시려면,<br>아래 버튼을 클릭해주세요.</p>
<p style="margin:0 0 32px;">문의사항이 있으시면 고객센터로 연락해 주세요.</p>
<p style="margin:0;"><a href="{$resetUrl}" style="display:inline-block;box-sizing:border-box;min-width:190px;padding:15px 24px;border-radius:8px;background:#111827;color:#ffffff;font-size:16px;font-weight:700;line-height:1.4;text-align:center;text-decoration:none;">비밀번호 재설정</a></p>
</div></td></tr>
</table></td></tr></table></body></html>
HTML);
        if (!$mail->send(false)) {
            $db->table('my_fc_password_reset_token')->where('member_uid', $memberUid)->delete();
            return redirect()->back()->withInput()->with('error', '메일을 발송하지 못했습니다. 잠시 후 다시 시도해주세요.');
        }

        return redirect()->to('/mypage/password-reset')->with('message', '비밀번호 재설정 안내 메일을 발송했습니다. 이메일을 확인한 후 비밀번호를 재설정해주세요.');
    }

    public function passwordResetGuestRequest(): string
    {
        return $this->renderView('member/password_reset_request', [
            'header_class' => 'form-page password-reset-page',
            'guestMode' => true,
            'resetRequestAction' => base_url('member/password-reset-request'),
        ]);
    }

    public function sendGuestPasswordResetMail()
    {
        $emailAddress = trim((string) $this->request->getPost('email'));
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', '올바른 이메일 주소를 입력해주세요.');
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('my_fc_password_reset_token')) {
            return redirect()->back()->withInput()->with('error', '비밀번호 재설정 기능 준비가 필요합니다. 관리자에게 문의해주세요.');
        }
        $member = $db->table('my_fc_member')->where('email', $emailAddress)
            ->whereIn('member_type', ['USER', 'FC'])->where('status', 'ACTIVE')
            ->where('deleted_at IS NULL', null, false)->get()->getRowArray();
        $config = config('Email');
        if (empty($config->fromEmail)) {
            return redirect()->back()->withInput()->with('error', '메일 발송 설정이 완료되지 않았습니다. 관리자에게 문의해주세요.');
        }

        if ($member) {
            $token = bin2hex(random_bytes(32));
            $db->table('my_fc_password_reset_token')->where('member_uid', $member['member_uid'])->delete();
            $db->table('my_fc_password_reset_token')->insert([
                'member_uid' => $member['member_uid'], 'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600), 'created_at' => date('Y-m-d H:i:s'),
            ]);
            $resetUrl = base_url('member/password-reset?token=' . rawurlencode($token));
            $mail = \Config\Services::email();
            $mail->setFrom($config->fromEmail, $config->fromName ?: 'MyFC');
            $mail->setTo($member['email']);
            $mail->setSubject('[MyFC] 비밀번호 재설정 안내');
            $mail->setMailType('html');
            $memberName = htmlspecialchars((string) ($member['name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $memberEmail = htmlspecialchars((string) $member['email'], ENT_QUOTES, 'UTF-8');
            $mail->setMessage('<div style="max-width:680px;margin:0 auto;padding:40px;font-family:Arial,\'Noto Sans KR\',sans-serif;color:#172033"><img src="' . base_url('assets/images/logo.png') . '" alt="MyFC" style="height:30px"><h1 style="font-size:28px">[MyFC] 비밀번호 재설정 안내</h1><div style="padding:80px;background:#f3f4f6;border-radius:12px;font-size:16px;line-height:1.8"><p>안녕하세요, ' . $memberName . '님, MyFC입니다.</p><p><strong>' . $memberEmail . '</strong> 계정의 비밀번호를 재설정하시려면,<br>아래 버튼을 클릭해주세요.</p><p>문의사항이 있으시면 고객센터로 연락해 주세요.</p><a href="' . $resetUrl . '" style="display:inline-block;padding:15px 24px;background:#111827;color:#fff;border-radius:8px;font-weight:700;text-decoration:none">비밀번호 재설정</a></div></div>');
            if (!$mail->send(false)) {
                $db->table('my_fc_password_reset_token')->where('member_uid', $member['member_uid'])->delete();
                return redirect()->back()->withInput()->with('error', '메일을 발송하지 못했습니다. 잠시 후 다시 시도해주세요.');
            }
        }

        return redirect()->to('/member/password-reset-request')->with('message', '입력한 이메일을 확인해주세요. 비밀번호 재설정 안내 메일을 발송했습니다.');
    }

    public function passwordReset()
    {
        $token = trim((string) $this->request->getGet('token'));
        if (!$this->passwordResetToken($token)) {
            return redirect()->to('/member/login')->with('error', '유효하지 않거나 만료된 비밀번호 재설정 링크입니다.');
        }
        return $this->renderView('member/password_reset', ['header_class' => 'form-page password-reset-page', 'token' => $token]);
    }

    public function updatePasswordReset()
    {
        $token = trim((string) $this->request->getPost('token'));
        $reset = $this->passwordResetToken($token);
        $password = (string) $this->request->getPost('password');
        $confirm = (string) $this->request->getPost('password_confirm');
        if (!$reset || !$this->isValidSignupPassword($password) || !hash_equals($password, $confirm)) {
            return redirect()->back()->withInput()->with('error', '비밀번호 규칙을 확인하고 동일하게 입력해주세요.');
        }
        $db = \Config\Database::connect();
        $db->transStart();
        $db->table('my_fc_member')->where('member_uid', $reset['member_uid'])
            ->whereIn('member_type', ['USER', 'FC'])->where('status', 'ACTIVE')->where('deleted_at IS NULL', null, false)->update([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'password_reset_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('my_fc_password_reset_token')->where('token_id', $reset['token_id'])->delete();
        $db->transComplete();
        return redirect()->to('/member/password-reset/complete');
    }

    public function passwordResetComplete(): string
    {
        return $this->renderView('member/password_reset_result', ['header_class' => 'password-reset-page flow-result']);
    }

    private function passwordResetToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) return null;
        $db = \Config\Database::connect();
        if (!$db->tableExists('my_fc_password_reset_token')) return null;
        return $db->table('my_fc_password_reset_token')
            ->where('token_hash', hash('sha256', $token))->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRowArray() ?: null;
    }

    public function join(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page";
        $popup_page = [];

        $modal_page = [
            "agree_modal.php",
            "privacy_modal.php",
            "marketing_modal.php"
        ];


        $agreements = $this->signupAgreementDocuments();
        $mobileOk = service('mobileOk');
        $isApp = $this->isAppWebView();
        $mobileOkRequestUrl = base_url('member/phone-auth/request');
        if ($isApp) {
            // WebView는 표준창 popup/callback 대신 같은 WebView에서 돌아오는 redirect를 사용한다.
            $mobileOkRequestUrl .= '?return_mode=redirect&redirect_to=member/join';
        }
        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "agreementTerms" => $agreements['TERMS'],
            "agreementPrivacy" => $agreements['PRIVACY'],
            "mobileOkEnabled" => $mobileOk->isConfigured(),
            "mobileOkJsUrl" => $mobileOk->requestJsUrl(),
            "mobileOkRequestUrl" => $mobileOkRequestUrl,
            "mobileOkResultUrl" => $mobileOk->returnUrl(),
            "mobileOkUseRedirect" => $isApp,
            "mobileOkAuthResult" => $isApp && (bool) session()->get('phone_auth_verified')
                ? [
                    'phone' => (string) session()->get('phone_auth_phone'),
                    'name' => (string) session()->get('phone_auth_name'),
                    'birth' => (string) session()->get('phone_auth_birth'),
                    'gender' => (string) session()->get('phone_auth_gender'),
                ]
                : null,
        ];


        return $this->renderView('member/join', $data);
    }

    public function joinComplete(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/joinComplete', $data);
    }

    public function fcAgree(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [
            "agree_modal.php",
            "privacy_modal.php",
            "marketing_modal.php"
        ];


        $agreements = $this->signupAgreementDocuments();
        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "agreementTerms" => $agreements['TERMS'],
            "agreementPrivacy" => $agreements['PRIVACY'],
        ];


        return $this->renderView('member/fcAgree', $data);
    }

    private function signupAgreementDocuments(): array
    {
        $documents = [
            'TERMS' => ['title' => '이용약관', 'content' => ''],
            'PRIVACY' => ['title' => '개인정보 수집 및 이용', 'content' => ''],
        ];

        $rows = \Config\Database::connect()->table('my_fc_terms')
            ->whereIn('term_type', array_keys($documents))
            ->where('display_status', 'Y')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->orderBy('term_id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $type = (string) ($row['term_type'] ?? '');
            if (isset($documents[$type]) && $documents[$type]['content'] === '') {
                $documents[$type] = [
                    'title' => (string) ($row['title'] ?? $documents[$type]['title']),
                    'content' => (string) ($row['content'] ?? ''),
                ];
            }
        }

        return $documents;
    }

    public function fcJoin_step1(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [];


        $mobileOk = service('mobileOk');
        $isApp = $this->isAppWebView();
        $mobileOkRequestUrl = base_url('member/phone-auth/request');
        if ($isApp) {
            $mobileOkRequestUrl .= '?return_mode=redirect&redirect_to=member/fcJoin1';
        }
        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "mobileOkEnabled" => $mobileOk->isConfigured(),
            "mobileOkJsUrl" => $mobileOk->requestJsUrl(),
            "mobileOkRequestUrl" => $mobileOkRequestUrl,
            "mobileOkResultUrl" => $mobileOk->returnUrl(),
            "mobileOkUseRedirect" => $isApp,
            "mobileOkAuthResult" => $isApp && (bool) session()->get('phone_auth_verified')
                ? [
                    'phone' => (string) session()->get('phone_auth_phone'),
                    'name' => (string) session()->get('phone_auth_name'),
                    'birth' => (string) session()->get('phone_auth_birth'),
                    'gender' => (string) session()->get('phone_auth_gender'),
                ]
                : null,
            "mode" => "create",
        ];


        return $this->renderView('member/fcJoin_step1', $data);
    }

    public function fcJoin_step2(): string
    {
        //return pageView('welcome_message');
        $db = \Config\Database::connect();
        $memberUid = session()->get('member_uid');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [
            "fc_time_modal.php",
            "fc_lang_modal.php"
        ];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "profile" => $memberUid
                ? ($db->table('my_fc_profile')->where('member_uid', $memberUid)->get()->getRowArray() ?? [])
                : [],
        ];


        return $this->renderView('member/fcJoin_step2', $data);
    }

    public function fcJoin_step3(): string
    {
        //return pageView('welcome_message');
        helper(['region', 'insurance']);
        $db = \Config\Database::connect();
        $memberUid = session()->get('member_uid');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [
            "region_modal.php",
            "insurance_modal.php"
        ];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "activity" => $memberUid
                ? ($db->table('my_fc_profile_activity')->where('member_uid', $memberUid)->get()->getRowArray() ?? [])
                : [],
            "activityItems" => $memberUid
                ? $db->table('my_fc_profile_activity_item')
                    ->where('member_uid', $memberUid)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('item_id', 'ASC')
                    ->get()
                    ->getResultArray()
                : [],
        ];


        return $this->renderView('member/fcJoin_step3', $data);
    }

    public function fcJoin_step4(): string
    {
        //return pageView('welcome_message');
        $db = \Config\Database::connect();
        $memberUid = session()->get('member_uid');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page,
            "story" => $memberUid
                ? ($db->table('my_fc_profile_story')->where('member_uid', $memberUid)->get()->getRowArray() ?? [])
                : [],
            "storyImages" => $memberUid
                ? $db->table('my_fc_profile_story_image')
                    ->where('member_uid', $memberUid)
                    ->orderBy('sort_order', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray()
                : [],
        ];


        return $this->renderView('member/fcJoin_step4', $data);
    }

    public function fcComplete(): string
    {
        //return pageView('welcome_message');
        $header_class = "flow-result";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/fcComplete', $data);
    }

    public function checkEmail()
    {
        $email = $this->request->getJSON(true)['email'] ?? null;

        if (!$email) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'email required'
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'invalid email format'
            ]);
        }

        $db = \Config\Database::connect();

        $rejoinCutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
        $recentLeave = $db->table('my_fc_member')
            ->select('deleted_at')
            ->where('email', $email)
            ->where('status', 'LEAVE')
            ->where('deleted_at >', $rejoinCutoff)
            ->orderBy('deleted_at', 'DESC')
            ->get()
            ->getRowArray();
        $exists = $db->table('my_fc_member')
            ->where('email', $email)
            ->groupStart()
                ->where('deleted_at', null)
                ->orGroupStart()
                    ->where('status', 'LEAVE')
                    ->where('deleted_at >', $rejoinCutoff)
                ->groupEnd()
            ->groupEnd()
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'duplicate' => $exists > 0,
            'message' => $recentLeave ? $this->rejoinRestrictionMessage($recentLeave['deleted_at'] ?? null) : '',
        ]);
    }


    public function checkPhone()
    {
        $data = $this->request->getJSON(true);
        $phone = $data['phone'] ?? '';

        if (!$phone) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'phone required'
            ]);
        }

        // 숫자만 정리
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 10) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'invalid phone format'
            ]);
        }

        $db = \Config\Database::connect();

        $rejoinCutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
        $recentLeave = $db->table('my_fc_member')
            ->select('deleted_at')
            ->where('phone', $phone)
            ->where('status', 'LEAVE')
            ->where('deleted_at >', $rejoinCutoff)
            ->orderBy('deleted_at', 'DESC')
            ->get()
            ->getRowArray();
        $exists = $db->table('my_fc_member')
            ->where('phone', $phone)
            ->groupStart()
                ->where('deleted_at', null)
                ->orGroupStart()
                    ->where('status', 'LEAVE')
                    ->where('deleted_at >', $rejoinCutoff)
                ->groupEnd()
            ->groupEnd()
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'duplicate' => $exists > 0,
            'message' => $recentLeave ? $this->rejoinRestrictionMessage($recentLeave['deleted_at'] ?? null) : '',
        ]);
    }

    public function register()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            $db->transBegin();

            $data = $this->request->getJSON(true);

            if (!$data) {
                throw new \Exception('잘못된 요청입니다.');
            }

            $email = strtolower(trim($data['email'] ?? ''));
            $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
            $password = $data['password'] ?? '';
            $passwordConfirm = $data['password_confirm'] ?? '';
            $memberType = strtoupper(trim($data['member_type'] ?? 'USER'));
            $name = trim($data['name'] ?? '');
            $birth = preg_replace('/[^0-9]/', '', $data['birth'] ?? '');
            $gender = strtoupper(trim($data['gender'] ?? ''));
            $phoneVerified = 'N';
            $authPhone = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_phone'));
            $authName = trim((string) $session->get('phone_auth_name'));
            $authBirth = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_birth'));
            $authGender = strtoupper(trim((string) $session->get('phone_auth_gender')));
            $authVerified = (bool) $session->get('phone_auth_verified');

            // =========================
            // 2. 검증
            // =========================
            if (!$email || !$phone || !$password || !$passwordConfirm) {
                throw new \Exception('필수값 누락');
            }

            if (!in_array($memberType, ['USER', 'FC'], true)) {
                throw new \Exception('회원 유형이 올바르지 않습니다.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('이메일 형식 오류');
            }

            if (strlen($phone) < 10 || strlen($phone) > 11) {
                throw new \Exception('휴대폰 번호를 확인해주세요.');
            }

            if ($password !== $passwordConfirm) {
                throw new \Exception('비밀번호 불일치');
            }

            if (!$this->isValidSignupPassword($password)) {
                throw new \Exception('비밀번호는 8자~20자이며 영문 대문자, 영문 소문자, 숫자, 특수문자를 각각 1개 이상 포함해야 합니다.');
            }

            if ($authVerified) {
                if ($authPhone === '' || $authPhone !== $phone) {
                    throw new \Exception('인증된 휴대폰 번호와 입력값이 일치하지 않습니다.');
                }

                $phoneVerified = 'Y';
            } elseif (in_array($memberType, ['USER', 'FC'], true)) {
                throw new \Exception('휴대폰 인증을 먼저 완료해주세요.');
            }

            if ($memberType === 'USER') {
                if (!$authVerified) {
                    throw new \Exception('휴대폰 인증을 먼저 완료해주세요.');
                }

                $name = $authName;
                $birth = $authBirth;
                $gender = $authGender;

                if ($name === '' || !preg_match('/^\d{8}$/', $birth) || !in_array($gender, ['M', 'F'], true)) {
                    throw new \Exception('휴대폰 인증 정보를 확인해주세요.');
                }

                if (
                    empty($data['agree_age']) ||
                    empty($data['agree_terms']) ||
                    empty($data['agree_privacy'])
                ) {
                    throw new \Exception('필수 약관에 동의해주세요.');
                }
            } elseif ($memberType === 'FC') {
                // FC도 이름을 직접 입력받지 않고, 휴대폰 본인인증 결과만 사용한다.
                $name = $authName;
                $birth = $authBirth;
                $gender = $authGender;

                if ($name === '' || !preg_match('/^\d{8}$/', $birth) || !in_array($gender, ['M', 'F'], true)) {
                    throw new \Exception('휴대폰 인증 정보를 확인해주세요.');
                }
            }

            // =========================
            // 3. 탈퇴 후 7일 재가입 제한 및 만료 식별값 해제
            // =========================
            $rejoinCutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
            $recentLeave = $db->table('my_fc_member')
                ->select('member_id, deleted_at')
                ->where('status', 'LEAVE')
                ->where('deleted_at >', $rejoinCutoff)
                ->groupStart()
                    ->where('email', $email)
                    ->orWhere('phone', $phone)
                ->groupEnd()
                ->orderBy('deleted_at', 'DESC')
                ->get()
                ->getRowArray();

            if ($recentLeave) {
                $availableAt = !empty($recentLeave['deleted_at'])
                    ? date('Y-m-d H:i', strtotime((string) $recentLeave['deleted_at'] . ' +7 days'))
                    : null;
                throw new \Exception($availableAt
                    ? '탈퇴한 계정은 7일 후(' . $availableAt . ')에 같은 이메일 또는 휴대폰 번호로 재가입할 수 있습니다.'
                    : '탈퇴한 계정은 7일 후에 같은 이메일 또는 휴대폰 번호로 재가입할 수 있습니다.');
            }

            // 이메일·휴대폰은 단일 고유 인덱스다. 제한 기간이 지난 탈퇴 행의 값은
            // 재가입 요청 시에만 폐기값으로 전환해 새 계정이 같은 값을 사용할 수 있게 한다.
            $expiredLeaves = $db->table('my_fc_member')
                ->select('member_id, email, phone')
                ->where('status', 'LEAVE')
                ->where('deleted_at <=', $rejoinCutoff)
                ->groupStart()
                    ->where('email', $email)
                    ->orWhere('phone', $phone)
                ->groupEnd()
                ->get()
                ->getResultArray();

            foreach ($expiredLeaves as $expiredLeave) {
                $memberId = (int) ($expiredLeave['member_id'] ?? 0);
                if ($memberId < 1) {
                    continue;
                }

                $db->table('my_fc_member')
                    ->where('member_id', $memberId)
                    ->update([
                        'email' => 'withdrawn-' . $memberId . '-' . substr(hash('sha256', (string) ($expiredLeave['email'] ?? '') . '|' . $memberId), 0, 12) . '@deleted.invalid',
                        'phone' => 'W' . substr(hash('sha256', (string) ($expiredLeave['phone'] ?? '') . '|' . $memberId), 0, 19),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }

            // =========================
            // 4. 중복 체크
            // =========================
            $exists = $db->table('my_fc_member')
                ->groupStart()
                ->where('email', $email)
                ->orWhere('phone', $phone)
                ->groupEnd()
                ->countAllResults();

            if ($exists > 0) {
                throw new \Exception('이미 가입된 정보입니다.');
            }

            $now = date('Y-m-d H:i:s');

            // =========================
            // 4. INSERT
            // =========================
            $memberData = [
                'member_uid' => $this->generateMemberUid(),
                'member_type' => $memberType,
                'email'       => $email,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'phone'       => $phone,
                'name'        => $name,
                'birth'       => $birth ?: null,
                'gender'      => $gender ?: null,
                'phone_verified' => $phoneVerified,
                'agree_age'       => !empty($data['agree_age']) ? 1 : 0,
                'agree_terms'     => !empty($data['agree_terms']) ? 1 : 0,
                'agree_privacy'   => !empty($data['agree_privacy']) ? 1 : 0,
                'agree_marketing' => !empty($data['agree_marketing']) ? 1 : 0,
                'join_ip' => $this->request->getIPAddress(),
                'fc_step' => $memberType === 'FC' ? 1 : 0,
                'created_at' => $now,
            ];

            $db->table('my_fc_member')->insert($memberData);

            $memberId = $db->insertID();

            // =========================
            // 2. user check
            // =========================
            $user = $db->table('my_fc_member')
                ->where('email', $email)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();


            $session = session();
            // =========================
            // 4. session 저장
            // =========================
            $sessionData = [
                'member_id'  => $user['member_id'],
                'member_uid' => $user['member_uid'],
                'email'      => $user['email'],
                'name'       => $user['name'],
                'member_type' => $user['member_type'],
                'logged_in'  => true,
            ];

            // 🔥 FC 추가 상태 관리
            if ($user['member_type'] === 'FC') {

                // FC 가입 단계 (예: 프로필 미완료 상태)
                $sessionData['fc_step'] = $user['fc_step'] ?? 99;
                $sessionData['fc_onboarding'] = true;
            }

            $session->set($sessionData);
            $session->remove([
                'phone_auth_verified',
                'phone_auth_phone',
                'phone_auth_name',
                'phone_auth_birth',
                'phone_auth_gender',
                'phone_auth_verified_at',
                'phone_auth_tx_id',
                'phone_auth_return_mode',
                'phone_auth_redirect_to',
            ]);

            $db->transCommit();

            return $this->response->setJSON([
                'status' => 'success',
                'member_id' => $memberId
            ]);
        } catch (\Throwable $e) {

            $db->transRollback();

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function phoneAuthRequest()
    {
        $mobileOk = service('mobileOk');
        $session = session();
        $request = $this->request;

        if (!$mobileOk->isConfigured()) {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('request', 'missing-configuration', [
                'message' => '본인인증 설정이 누락되었습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'keyPath' => $mobileOk->keyPath(),
                'keyExists' => is_file($mobileOk->keyPath()) ? 'Y' : 'N',
                'keyReadable' => is_readable($mobileOk->keyPath()) ? 'Y' : 'N',
                'enabled' => $mobileOk->isEnabled() ? 'Y' : 'N',
                'missingConfiguration' => $mobileOk->missingConfiguration(),
                'requestMethod' => $request->getMethod(),
                'requestUri' => (string) current_url(true),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'baseUrl' => base_url('/'),
                'requestJsUrl' => $mobileOk->requestJsUrl(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'returnUrl' => $mobileOk->returnUrl(),
            ]));

            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'error',
                'message' => implode(' ', $mobileOk->missingConfiguration()),
            ]);
        }

        if (!$mobileOk->sdkAvailable()) {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('request', 'sdk-missing', [
                'message' => 'MobileOK SDK 파일 또는 composer autoload를 찾을 수 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'keyPath' => $mobileOk->keyPath(),
                'keyExists' => is_file($mobileOk->keyPath()) ? 'Y' : 'N',
                'keyReadable' => is_readable($mobileOk->keyPath()) ? 'Y' : 'N',
                'sdkAutoloadPath' => $mobileOk->sdkAutoloadPath(),
                'sdkAutoloadExists' => is_file($mobileOk->sdkAutoloadPath()) ? 'Y' : 'N',
                'sdkManagerPath' => $mobileOk->sdkManagerPath(),
                'sdkManagerExists' => is_file($mobileOk->sdkManagerPath()) ? 'Y' : 'N',
                'requestMethod' => $request->getMethod(),
                'requestUri' => (string) current_url(true),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'baseUrl' => base_url('/'),
                'requestJsUrl' => $mobileOk->requestJsUrl(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'returnUrl' => $mobileOk->returnUrl(),
            ]));

            return $this->response->setStatusCode(503)->setJSON([
                'status' => 'error',
                'message' => 'MobileOK SDK 파일 또는 composer autoload를 찾을 수 없습니다.',
            ]);
        }

        try {
            $clientTxId = $mobileOk->makeClientTxId();
            $purpose = $session->get('phone_auth_purpose_pending') === 'account_find' ? 'account_find' : null;
            $returnMode = $request->getGet('return_mode') === 'redirect' ? 'redirect' : 'callback';
            $redirectTo = (string) $request->getGet('redirect_to');
            $allowedRedirectTargets = ['member/join', 'member/fcJoin1', 'mypage/info', 'mypage/fcinfo'];
            if (!in_array($redirectTo, $allowedRedirectTargets, true)) {
                $redirectTo = 'member/join';
            }
            $session->remove(['phone_auth_purpose_pending', 'phone_auth_purpose']);
            $session->set([
                'phone_auth_tx_id' => $clientTxId,
                'phone_auth_requested_at' => date('Y-m-d H:i:s'),
                'phone_auth_verified' => false,
                'phone_auth_phone' => null,
                'phone_auth_name' => null,
                'phone_auth_birth' => null,
                'phone_auth_gender' => null,
                'phone_auth_issue_date' => null,
                'phone_auth_result_code' => null,
                'phone_auth_purpose' => $purpose,
                'phone_auth_return_mode' => $returnMode,
                'phone_auth_redirect_to' => $redirectTo,
            ]);

            $manager = $mobileOk->createSdkManager();
            $payload = $mobileOk->makeRequestPayload(
                $manager,
                $clientTxId,
                $mobileOk->returnUrl()
            );

            // MobileOK 표준창 스크립트는 이 응답을 JSON.parse()한 객체로 팝업에 전달한다.
            // CI의 JSON 응답 처리를 사용해 Content-Type과 본문 형식을 일관되게 유지한다.
            return $this->response
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->setJSON($payload);
        } catch (\Throwable $e) {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('request', 'exception', [
                'message' => $e->getMessage(),
                'exceptionClass' => get_class($e),
                'mode' => $mobileOk->mode(),
                'enabled' => $mobileOk->isEnabled() ? 'Y' : 'N',
                'serviceId' => $mobileOk->serviceId(),
                'clientPrefix' => $mobileOk->clientPrefix(),
                'usageCode' => $mobileOk->usageCode(),
                'serviceType' => $mobileOk->serviceType(),
                'retTransferType' => $mobileOk->retTransferType(),
                'requestMode' => $mobileOk->requestMode(),
                'keyPath' => $mobileOk->keyPath(),
                'keyExists' => is_file($mobileOk->keyPath()) ? 'Y' : 'N',
                'keyReadable' => is_readable($mobileOk->keyPath()) ? 'Y' : 'N',
                'sdkAutoloadPath' => $mobileOk->sdkAutoloadPath(),
                'sdkAutoloadExists' => is_file($mobileOk->sdkAutoloadPath()) ? 'Y' : 'N',
                'sdkManagerPath' => $mobileOk->sdkManagerPath(),
                'sdkManagerExists' => is_file($mobileOk->sdkManagerPath()) ? 'Y' : 'N',
                'requestMethod' => $request->getMethod(),
                'requestUri' => (string) current_url(true),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'baseUrl' => base_url('/'),
                'requestJsUrl' => $mobileOk->requestJsUrl(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'returnUrl' => $mobileOk->returnUrl(),
                'clientTxId' => $clientTxId ?? '',
            ]));

            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '휴대폰 본인인증 요청을 생성하지 못했습니다.',
            ]);
        }
    }

    public function phoneAuthResult()
    {
        $mobileOk = service('mobileOk');
        $session = session();
        $db = \Config\Database::connect();
        $request = $this->request;

        $payload = [];
        $contentType = strtolower($request->getHeaderLine('Content-Type'));
        $isRedirectReturn = $session->get('phone_auth_return_mode') === 'redirect';

        if (str_contains($contentType, 'application/json')) {
            try {
                $payload = $request->getJSON(true) ?? [];
            } catch (\Throwable $e) {
                $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'invalid-json', [
                    'message' => $e->getMessage(),
                    'contentType' => $contentType,
                    'requestMethod' => $request->getMethod(),
                    'requestUri' => (string) current_url(true),
                ]));
            }
        } else {
            $payload = $request->getPost();
        }

        if (isset($payload['data']) && is_string($payload['data'])) {
            try {
                $tokenPayload = json_decode(urldecode($payload['data']), true, 512, JSON_THROW_ON_ERROR);
                $payload = $mobileOk->resolveResultToken((string) ($tokenPayload['encryptMOKKeyToken'] ?? ''));
            } catch (\Throwable $e) {
                $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'token-processing-failed', [
                    'message' => $e->getMessage(),
                    'exceptionClass' => get_class($e),
                    'mode' => $mobileOk->mode(),
                    'serviceId' => $mobileOk->serviceId(),
                    'contentType' => $contentType,
                    'requestMethod' => $request->getMethod(),
                    'requestUri' => (string) current_url(true),
                ]));

                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => '휴대폰 본인인증 결과를 처리하지 못했습니다.',
                ]);
            }
        }

        // MobileOK 콜백이 이미 처리된 오류 응답을 다시 전달할 수 있다.
        // 이 경우 인증 데이터로 해석하지 않고 원래 오류를 그대로 반환한다.
        if (($payload['status'] ?? '') === 'error') {
            return $this->response->setJSON($payload);
        }

        if (!$payload) {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'payload-missing', [
                'message' => '본인인증 결과가 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => (string) $session->get('phone_auth_tx_id'),
                'sessionRequestedAt' => (string) $session->get('phone_auth_requested_at'),
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 결과가 없습니다.',
            ]);
        }

        $resultData = $payload['payload'] ?? $payload;
        $resultCode = (string) ($resultData['resultCode'] ?? '');
        $resultMsg = (string) ($resultData['resultMsg'] ?? '');
        $sessionTxId = (string) $session->get('phone_auth_tx_id');
        $resultTxId = (string) ($resultData['clientTxId'] ?? '');
        $issueDate = trim((string) ($resultData['issueDate'] ?? ''));

        if ($resultCode !== '' && $resultCode !== '2000') {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'result-code-failed', [
                'message' => $resultMsg !== '' ? $resultMsg : '본인인증에 실패했습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $resultMsg !== '' ? $resultMsg : '본인인증에 실패했습니다.',
            ]);
        }

        if ($sessionTxId !== '' && $resultTxId !== '' && $sessionTxId !== $resultTxId) {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'txid-mismatch', [
                'message' => '본인인증 거래 정보가 일치하지 않습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 거래 정보가 일치하지 않습니다.',
            ]);
        }

        if ($issueDate !== '' && $mobileOk->isExpired($issueDate)) {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'token-expired', [
                'message' => '본인인증 유효 시간이 만료되었습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 유효 시간이 만료되었습니다.',
            ]);
        }

        $phone = $mobileOk->normalizePhone((string) ($resultData['userPhone'] ?? $resultData['phone'] ?? ''));
        $name = trim((string) ($resultData['userName'] ?? $resultData['name'] ?? ''));
        $birth = preg_replace('/[^0-9]/', '', (string) ($resultData['userBirthday'] ?? $resultData['birth'] ?? ''));
        $genderRaw = strtoupper(trim((string) ($resultData['userGender'] ?? $resultData['gender'] ?? '')));
        $gender = in_array($genderRaw, ['1', 'M'], true)
            ? 'M'
            : (in_array($genderRaw, ['2', 'F'], true) ? 'F' : '');
        $ci = trim((string) ($resultData['ci'] ?? ''));
        $di = trim((string) ($resultData['di'] ?? ''));
        $siteId = trim((string) ($resultData['siteID'] ?? ''));
        $providerId = trim((string) ($resultData['providerId'] ?? ''));
        $serviceType = trim((string) ($resultData['serviceType'] ?? ''));
        $reqAuthType = trim((string) ($resultData['reqAuthType'] ?? ''));
        $reqDate = trim((string) ($resultData['reqDate'] ?? ''));
        $issuer = trim((string) ($resultData['issuer'] ?? ''));
        $nation = trim((string) ($resultData['userNation'] ?? ''));

        if ($gender === '1') {
            $gender = 'M';
        } elseif ($gender === '2') {
            $gender = 'F';
        }

        if ($phone === '') {
            $phoneFieldSummary = [
                'userPhone' => isset($resultData['userPhone']) ? strlen((string) $resultData['userPhone']) : 0,
                'phone' => isset($resultData['phone']) ? strlen((string) $resultData['phone']) : 0,
                'mobile' => isset($resultData['mobile']) ? strlen((string) $resultData['mobile']) : 0,
                'mobileNo' => isset($resultData['mobileNo']) ? strlen((string) $resultData['mobileNo']) : 0,
            ];

            log_message('error', '[MobileOK] 인증 결과에 휴대폰 번호가 없습니다. payloadKeys: {payloadKeys}, phoneFieldLengths: {phoneFieldLengths}', [
                'payloadKeys' => implode(', ', array_keys($resultData)),
                'phoneFieldLengths' => json_encode($phoneFieldSummary),
            ]);

            $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'phone-missing', [
                'message' => '휴대폰 번호를 확인할 수 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'phoneFieldLengths' => $phoneFieldSummary,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 확인할 수 없습니다.',
            ]);
        }

        if ($name === '' && $siteId === '') {
            $this->writeMobileOkLog($this->formatMobileOkFailureLog('result', 'identity-missing', [
                'message' => '본인인증 결과를 확인할 수 없습니다.',
                'mode' => $mobileOk->mode(),
                'serviceId' => $mobileOk->serviceId(),
                'resultRequestUrl' => $mobileOk->resultRequestUrl(),
                'requestUri' => (string) current_url(true),
                'requestMethod' => $request->getMethod(),
                'host' => (string) ($request->getServer('HTTP_HOST') ?? ''),
                'scheme' => (string) (function_exists('is_https') && is_https() ? 'https' : 'http'),
                'referer' => (string) ($request->getServer('HTTP_REFERER') ?? ''),
                'userAgent' => (string) ($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : ''),
                'clientIp' => (string) $request->getIPAddress(),
                'sessionTxId' => $sessionTxId,
                'resultTxId' => $resultTxId,
                'issueDate' => $issueDate,
                'payloadKeys' => array_keys($resultData),
                'resultData' => $resultData,
            ]));

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '본인인증 결과를 확인할 수 없습니다.',
            ]);
        }

        $isAccountFind = $session->get('phone_auth_purpose') === 'account_find';
        $rejoinCutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
        $duplicateQuery = $db->table('my_fc_member')
            ->where('phone', $phone)
            ->groupStart()
                ->where('deleted_at', null)
                ->orGroupStart()
                    ->where('status', 'LEAVE')
                    ->where('deleted_at >', $rejoinCutoff)
                ->groupEnd()
            ->groupEnd();

        $currentMemberId = (int) $session->get('member_id');
        if ($currentMemberId > 0) {
            $duplicateQuery->where('member_id !=', $currentMemberId);
        }

        $exists = $duplicateQuery->countAllResults();

        if (!$isAccountFind && $exists > 0) {
            $recentLeave = $db->table('my_fc_member')
                ->select('deleted_at')
                ->where('phone', $phone)
                ->where('status', 'LEAVE')
                ->where('deleted_at >', $rejoinCutoff)
                ->orderBy('deleted_at', 'DESC')
                ->get()
                ->getRowArray();

            return $this->response->setJSON([
                'status' => 'error',
                'duplicate' => true,
                'message' => $recentLeave
                    ? $this->rejoinRestrictionMessage($recentLeave['deleted_at'] ?? null)
                    : '이미 사용 중인 휴대폰 번호입니다.',
            ]);
        }

        $session->set([
            'phone_auth_verified' => true,
            'phone_auth_result_code' => $resultCode,
            'phone_auth_result_msg' => $resultMsg,
            'phone_auth_phone' => $phone,
            'phone_auth_name' => $name,
            'phone_auth_birth' => $birth,
            'phone_auth_gender' => $gender,
            'phone_auth_ci' => $ci,
            'phone_auth_di' => $di,
            'phone_auth_site_id' => $siteId,
            'phone_auth_provider_id' => $providerId,
            'phone_auth_service_type' => $serviceType,
            'phone_auth_req_auth_type' => $reqAuthType,
            'phone_auth_req_date' => $reqDate,
            'phone_auth_issuer' => $issuer,
            'phone_auth_nation' => $nation,
            'phone_auth_issue_date' => $issueDate,
            'phone_auth_verified_at' => date('Y-m-d H:i:s'),
            'phone_auth_tx_id' => $resultTxId !== '' ? $resultTxId : $sessionTxId,
        ]);

        if ($isRedirectReturn) {
            // WebView redirect 방식은 콜백 함수를 호출하지 않으므로 가입 화면을 다시 열어
            // 세션에 저장한 인증 결과를 화면에 반영한다.
            return redirect()->to('/' . ltrim((string) $session->get('phone_auth_redirect_to'), '/'));
        }

        return $this->response->setJSON([
            'status' => 'success',
            'phone' => $phone,
            'name' => $name,
            'birth' => $birth,
            'gender' => $gender,
            'resultCode' => $resultCode,
            'resultMsg' => $resultMsg,
            'issueDate' => $issueDate,
            'phone_verified' => 'Y',
        ]);
    }

    /**
     * 업체 전달용 실패 로그를 만든다.
     *
     * @param array<string, mixed> $context
     */
    private function formatMobileOkFailureLog(string $stage, string $reason, array $context): string
    {
        $lines = [
            '[MobileOK Failure]',
            'stage: ' . $stage,
            'reason: ' . $reason,
        ];

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $lines[] = $key . ': ' . json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                continue;
            }

            $lines[] = $key . ': ' . (string) $value;
        }

        return implode("\n", $lines);
    }

    private function writeMobileOkLog(string $message): void
    {
        $path = $this->mobileOkLogPath();
        $entry = '[' . date('Y-m-d H:i:s') . "]\n" . $message . "\n\n";

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        file_put_contents($path, $entry, FILE_APPEND | LOCK_EX);
    }

    private function mobileOkLogPath(): string
    {
        return WRITEPATH . 'logs/mobileok-' . date('Y-m-d') . '.log';
    }

    private function generateMemberUid()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijklmnopqrstuvwxyz';
        $db = \Config\Database::connect();

        do {
            $uid = '';
            for ($i = 0; $i < 20; $i++) {
                $uid .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $exists = $db->table('my_fc_member')
                ->where('member_uid', $uid)
                ->countAllResults();
        } while ($exists > 0);

        return $uid;
    }

    /**
     * 비밀번호는 영문 대문자·소문자, 숫자, 특수문자를 각각 1개 이상 포함해야 한다.
     */
    private function isValidSignupPassword(string $password): bool
    {
        if (strlen($password) < 8 || strlen($password) > 20 || preg_match('/\s/', $password)) {
            return false;
        }

        return preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/\d/', $password)
            && preg_match('/[^A-Za-z0-9\s]/', $password);
    }

    private function rejoinRestrictionMessage(?string $deletedAt): string
    {
        if ($deletedAt) {
            $availableAt = date('Y-m-d H:i', strtotime($deletedAt . ' +7 days'));
            return '기존 탈퇴 회원은 탈퇴 후 7일 후 가입 가능합니다. 재가입 가능 시점: ' . $availableAt;
        }

        return '기존 탈퇴 회원은 탈퇴 후 7일 후 가입 가능합니다.';
    }

    public function loginProc()
    {
        $this->response->setContentType('application/json');

        $db = \Config\Database::connect();

        $data = $this->request->getJSON(true);

        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $remember = $data['remember'] ?? 0;

        // =========================
        // 1. validation
        // =========================
        if (!$email || !$password) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '필수값 누락'
            ]);
        }

        // =========================
        // 2. user check
        // =========================
        $user = $db->table('my_fc_member')
            ->where('email', $email)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$user) {
            $rejoinCutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
            $recentLeave = $db->table('my_fc_member')
                ->select('deleted_at')
                ->where('email', $email)
                ->where('status', 'LEAVE')
                ->where('deleted_at >', $rejoinCutoff)
                ->orderBy('deleted_at', 'DESC')
                ->get()
                ->getRowArray();

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $recentLeave
                    ? $this->rejoinRestrictionMessage($recentLeave['deleted_at'] ?? null)
                    : '존재하지 않는 계정입니다'
            ]);
        }

        // =========================
        // 3. password check
        // =========================
        if (!password_verify($password, $user['password'])) {

            return $this->response->setJSON([
                'status' => 'error',
                'message' => '비밀번호가 일치하지 않습니다'
            ]);
        }

        $session = session();

        // FC 가입 단계 1은 기본정보만 등록된 프로필 미등록 상태다.
        // 로그인 처리 중 별도 프로필 테이블을 조회하지 않아 로그인 실패와 분리한다.
        $showProfileRegistrationNotice = $user['member_type'] === 'FC'
            && (int) ($user['fc_step'] ?? 0) === 1;

        // =========================
        // 4. session 저장
        // =========================
        $sessionData = [
            'member_id'  => $user['member_id'],
            'member_uid' => $user['member_uid'],
            'email'      => $user['email'],
            'name'       => $user['name'],
            'member_type' => $user['member_type'],
            'logged_in'  => true,
        ];

        // 🔥 FC 추가 상태 관리
        if ($user['member_type'] === 'FC') {

            // FC 가입 단계 (예: 프로필 미완료 상태)
            $sessionData['fc_step'] = $user['fc_step'] ?? 99;
            $sessionData['fc_onboarding'] = true;
        }

        $session->set($sessionData);

        // =========================
        // 5. 로그인 로그 업데이트
        // =========================
        $db->table('my_fc_member')
            ->where('member_id', $user['member_id'])
            ->update([
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);

        // =========================
        // 6. 성공 응답
        // =========================
        return $this->response->setJSON([
            'status' => 'success',
            'member_id' => $user['member_id'],
            'show_profile_registration_notice' => $showProfileRegistrationNotice,
        ]);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/');
    }

    public function fcProfileUpdate()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            $memberUid = $session->get('member_uid');

            if (!$memberUid) {
                throw new \Exception('로그인이 필요합니다.');
            }

            $member = $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->where('member_type', 'FC')
                ->where('status', 'ACTIVE')
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$member) {
                throw new \Exception('FC 회원만 등록할 수 있습니다.');
            }

            $db->transBegin();

            helper('fileupload_helper');

            $company = trim((string) $this->request->getPost('company'));
            $companySub = trim((string) $this->request->getPost('company_sub'));
            $ga = trim((string) $this->request->getPost('ga'));
            $position = trim((string) $this->request->getPost('position'));
            $licenseDate = trim((string) $this->request->getPost('license_date'));
            $licenseNo = preg_replace('/[^0-9]/', '', (string) $this->request->getPost('license_no'));
            $timeFrom = $this->request->getPost('time_from');
            $timeTo = $this->request->getPost('time_to');
            $language = fc_language_normalize((string) $this->request->getPost('language'));

            if ($company === '' && $ga === '') {
                throw new \Exception('소속 원수사 또는 소속 GA 중 하나는 반드시 입력해주세요.');
            }

            if ($ga !== '' && $companySub !== '') {
                throw new \Exception('소속 GA를 입력한 경우 추가 소속 보험사는 입력할 수 없습니다.');
            }

            if ($position === '' || $licenseDate === '' || $licenseNo === '' || $language === '') {
                throw new \Exception('프로필 필수값을 입력해주세요.');
            }

            if (!is_numeric($timeFrom) || !is_numeric($timeTo)) {
                throw new \Exception('상담 가능 시간을 선택해주세요.');
            }

            $timeFrom = (int) $timeFrom;
            $timeTo = (int) $timeTo;

            if ($timeFrom < 0 || $timeFrom > 23 || $timeTo < 0 || $timeTo > 23) {
                throw new \Exception('상담 가능 시간 값이 올바르지 않습니다.');
            }

            // =========================
            // 1. 기존 데이터 확인
            // =========================
            $profile = $db->table('my_fc_profile')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();
            $oldProfileImage = basename((string) ($profile['profile_image'] ?? ''));

            // =========================
            // 2. 파일 업로드 처리 (있을 때만)
            // =========================
            $file = $this->request->getFile('profile_image');
            $fileName = null;

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $fileName = upload_file($file, 'uploads/profile');
            }

            // =========================
            // 3. 공통 데이터 구성
            // =========================
            $data = [
                'member_uid'   => $memberUid,
                'company'      => $company !== '' ? $company : null,
                'company_sub'  => $companySub !== '' ? $companySub : null,
                'ga'           => $ga !== '' ? $ga : null,
                'position'     => $position,
                'license_date' => $licenseDate,
                'license_no'   => $licenseNo,
                'time_from'    => $timeFrom,
                'time_to'      => $timeTo,
                'language'     => $language,
                'updated_at'   => date('Y-m-d H:i:s'),
            ];

            // =========================
            // 4. 이미지 조건부 업데이트 핵심
            // =========================
            if ($fileName) {
                $data['profile_image'] = $fileName;
            }

            // =========================
            // 5. INSERT / UPDATE 분기
            // =========================
            if ($profile) {

                // UPDATE
                $db->table('my_fc_profile')
                    ->where('member_uid', $memberUid)
                    ->update($data);
            } else {

                // INSERT
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['step'] = 2;

                $db->table('my_fc_profile')->insert($data);
            }

            // =========================
            // 6. member fc_step 업데이트
            // =========================
            $memberUpdate = [
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (($member['fc_review_status'] ?? '') !== 'APPROVE') {
                $memberUpdate['fc_step'] = 2;
                $memberUpdate['fc_review_status'] = 'WAIT';
            }

            $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->update($memberUpdate);

            // =========================
            // 7. session sync
            // =========================
            $session->set([
                'fc_step' => 2
            ]);

            $db->transCommit();

            // 새 이미지 저장이 완료된 뒤에만 이전 파일을 정리한다.
            if ($fileName && $oldProfileImage !== '' && $oldProfileImage !== $fileName) {
                $oldProfilePath = WRITEPATH . 'uploads/profile/' . $oldProfileImage;
                if (is_file($oldProfilePath)) {
                    @unlink($oldProfilePath);
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'profile_image_url' => $fileName
                        ? profile_image_url($fileName)
                        : profile_image_url($profile['profile_image'] ?? ''),
                ],
            ]);
        } catch (\Throwable $e) {

            $db->transRollback();

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


    public function fcActivitySave()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            helper('fileupload_helper');

            $memberUid = $session->get('member_uid');

            if (!$memberUid) {
                throw new \Exception('로그인이 필요합니다.');
            }

            $member = $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->where('member_type', 'FC')
                ->where('status', 'ACTIVE')
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$member) {
                throw new \Exception('FC 회원만 등록할 수 있습니다.');
            }

            $db->transBegin();

            $region = trim((string) $this->request->getPost('region'));
            $insuranceTypes = trim((string) $this->request->getPost('insurance_types'));
            $heroLine = trim((string) $this->request->getPost('history'));
            $intro = trim((string) $this->request->getPost('intro'));
            $career = trim((string) $this->request->getPost('career'));

            // ===========================
            // Activity 저장
            // ===========================

            $activityData = [

                'member_uid'      => $memberUid,

                'region'          => $region !== '' ? $region : null,

                'insurance_types' => $insuranceTypes !== '' ? $insuranceTypes : null,

                'hero_line'       => $heroLine !== '' ? $heroLine : null,

                'intro'           => $intro !== '' ? $intro : null,

                'career'          => $career !== '' ? $career : null,

                'updated_at'      => date('Y-m-d H:i:s'),

            ];

            $exists = $db->table('my_fc_profile_activity')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();

            if ($exists) {

                $db->table('my_fc_profile_activity')
                    ->where('member_uid', $memberUid)
                    ->update($activityData);
            } else {

                $activityData['created_at'] = date('Y-m-d H:i:s');

                $db->table('my_fc_profile_activity')
                    ->insert($activityData);
            }

            // ===========================
            // 삭제 처리
            // ===========================

            $deleteItems = $this->request->getPost('delete_items');

            if (!empty($deleteItems)) {

                if (!is_array($deleteItems)) {
                    $deleteItems = explode(',', $deleteItems);
                }

                $deleteItems = array_filter($deleteItems);

                if (!empty($deleteItems)) {

                    $deleteRows = $db->table('my_fc_profile_activity_item')
                        ->where('member_uid', $memberUid)
                        ->whereIn('item_id', $deleteItems)
                        ->get()
                        ->getResultArray();

                    foreach ($deleteRows as $row) {

                        if (!empty($row['file_path'])) {

                            $path = ROOTPATH . 'public/uploads/activity/' . $row['file_path'];

                            if (is_file($path)) {
                                @unlink($path);
                            }
                        }
                    }

                    $db->table('my_fc_profile_activity_item')
                        ->where('member_uid', $memberUid)
                        ->whereIn('item_id', $deleteItems)
                        ->delete();
                }
            }

            // ===========================
            // items
            // ===========================

            $items = $this->request->getVar('items');

            if (!is_array($items)) {
                $items = [];
            }

            $keepIds = [];

            // ===========================
            // items 저장
            // ===========================

            foreach ($items as $i => $item) {

                $type = $item['type'] ?? 'text';

                $data = [

                    'member_uid' => $memberUid,

                    'category'   => 'activity',

                    'type'       => $type,

                    'title'      => trim($item['title'] ?? ''),

                    'content'    => trim($item['content'] ?? '') ?: null,

                    'url'        => trim($item['url'] ?? '') ?: null,

                    'sort_order' => $i,

                    'is_visible' => 1,

                ];

                // 제목 없으면 skip
                if ($data['title'] == '') {
                    continue;
                }

                if (!in_array($type, ['file', 'link', 'text'], true)) {
                    continue;
                }

                $itemId = $item['item_id'] ?? null;

                // ==========================
                // FILE 업로드
                // ==========================

                if ($type == 'file') {

                    $file = $this->request->getFile("items.$i.file");

                    if ($file && $file->isValid() && !$file->hasMoved()) {

                        // 기존파일 삭제
                        if (!empty($itemId)) {

                            $old = $db->table('my_fc_profile_activity_item')
                                ->where('item_id', $itemId)
                                ->where('member_uid', $memberUid)
                                ->get()
                                ->getRowArray();

                            if (!empty($old['file_path'])) {

                                $oldPath = ROOTPATH .
                                    'public/uploads/activity/' .
                                    $old['file_path'];

                                if (is_file($oldPath)) {
                                    @unlink($oldPath);
                                }
                            }
                        }

                        // 새파일 저장
                        $data['file_path'] = upload_file(
                            $file,
                            'uploads/activity'
                        );
                    } else {

                        // 수정 시 기존파일 유지
                        if (!empty($itemId)) {

                            $old = $db->table('my_fc_profile_activity_item')
                                ->where('item_id', $itemId)
                                ->where('member_uid', $memberUid)
                                ->get()
                                ->getRowArray();

                            if (!empty($old['file_path'])) {

                                $data['file_path'] = $old['file_path'];
                            }
                        }
                    }

                    if (empty($data['file_path'])) {
                        continue;
                    }
                }

                if ($type === 'link' && empty($data['url'])) {
                    continue;
                }

                if ($type === 'text' && empty($data['content'])) {
                    continue;
                }

                // ==========================
                // UPDATE
                // ==========================

                if (!empty($itemId)) {

                    $db->table('my_fc_profile_activity_item')
                        ->where('item_id', $itemId)
                        ->where('member_uid', $memberUid)
                        ->update($data);

                    $keepIds[] = $itemId;
                }

                // ==========================
                // INSERT
                // ==========================

                else {

                    $data['created_at'] = date('Y-m-d H:i:s');

                    $db->table('my_fc_profile_activity_item')
                        ->insert($data);

                    $keepIds[] = $db->insertID();
                }
            }

            // ===========================
            // 회원 단계 업데이트
            // ===========================

            $memberUpdate = [
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // 승인된 FC가 활동정보를 보완해도 공개 승인 상태를 해제하지 않는다.
            // 가입 진행 중이거나 미승인인 경우에만 기존 심사 대기 흐름을 적용한다.
            if (($member['fc_review_status'] ?? '') !== 'APPROVE') {
                $memberUpdate['fc_step'] = 3;
                $memberUpdate['fc_review_status'] = 'WAIT';
            }

            $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->update($memberUpdate);

            // ===========================
            // Commit
            // ===========================

            if ($db->transStatus() === false) {

                throw new \Exception('DB 저장 중 오류가 발생했습니다.');
            }

            $db->transCommit();

            return $this->response->setJSON([

                'status' => 'success',

                'message' => '저장되었습니다.',

            ]);
        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->__toString());

            return $this->response->setJSON([

                'status' => 'error',

                'message' => $e->getMessage()

            ]);
        }
    }


    public function fcStorySave()
    {
        $db = \Config\Database::connect();
        $session = session();

        try {

            helper('fileupload_helper');
            helper('filesystem');

            $memberUid = $session->get('member_uid');

            if (!$memberUid) {
                throw new \Exception('로그인이 필요합니다.');
            }

            $member = $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->where('member_type', 'FC')
                ->where('status', 'ACTIVE')
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$member) {
                throw new \Exception('FC 회원만 등록할 수 있습니다.');
            }

            $db->transBegin();

            //---------------------------------------------------
            // Story 조회
            //---------------------------------------------------

            $story = $db->table('my_fc_profile_story')
                ->where('member_uid',$memberUid)
                ->get()
                ->getRowArray();

            $storyData=[];

            //---------------------------------------------------
            // 대표 영상
            //---------------------------------------------------

            $video=$this->request->getFile('story_video');

            if($video && $video->isValid() && !$video->hasMoved()){

                if(!empty($story['story_video'])){

                    @unlink(ROOTPATH.'public/uploads/story/video/'.$story['story_video']);

                }

                $storyData['story_video']=upload_file(
                    $video,
                    'uploads/story/video'
                );

            }

            //---------------------------------------------------
            // 대표 이미지
            //---------------------------------------------------

            $image=$this->request->getFile('story_image');

            if($image && $image->isValid() && !$image->hasMoved()){

                if(!empty($story['story_image'])){

                    @unlink(ROOTPATH.'public/uploads/story/main/'.$story['story_image']);

                }

                $storyData['story_image']=upload_file(
                    $image,
                    'uploads/story/main'
                );

            }

            //---------------------------------------------------
            // Story 저장
            //---------------------------------------------------

            if($story){

                if(!empty($storyData)){

                    $storyData['updated_at']=date('Y-m-d H:i:s');

                    $db->table('my_fc_profile_story')
                        ->where('member_uid',$memberUid)
                        ->update($storyData);

                }

            }else{

                $storyData['member_uid']=$memberUid;
                $storyData['created_at']=date('Y-m-d H:i:s');

                $db->table('my_fc_profile_story')
                    ->insert($storyData);

            }

            //---------------------------------------------------
            // 기존 이미지
            //---------------------------------------------------

            $keepImages=$this->request->getPost('keep_images');

            if(!is_array($keepImages)){
                $keepImages=[];
            }

            $imageOrder=$this->request->getPost('story_image_order');

            if(!is_array($imageOrder)){
                $imageOrder=[];
            }

            $files=$this->request->getFiles();
            $newImageFiles=[];

            if(isset($files['story_images'])){
                $newImageFiles=is_array($files['story_images'])
                    ? $files['story_images']
                    : [$files['story_images']];
            }

            $validNewImageCount=0;

            foreach($newImageFiles as $file){
                if($file && $file->isValid()){
                    $validNewImageCount++;
                }
            }

            if(count($keepImages) + $validNewImageCount < 1){
                throw new \Exception('스토리 이미지를 최소 1개 이상 등록해주세요.');
            }

            $oldImages=$db->table('my_fc_profile_story_image')
                ->where('member_uid',$memberUid)
                ->get()
                ->getResultArray();

            $keepImages=array_values(array_unique(array_filter($keepImages)));
            $oldImageIds=array_column($oldImages,'id');

            if(empty($imageOrder)){
                foreach($keepImages as $id){
                    $imageOrder[]='existing:'.$id;
                }

                for($i=0;$i<$validNewImageCount;$i++){
                    $imageOrder[]='new';
                }
            }

            //---------------------------------------------------
            // 삭제 이미지 찾기
            //---------------------------------------------------

            foreach($oldImages as $img){

                if(!in_array($img['id'],$keepImages)){

                    @unlink(
                        ROOTPATH.'public/uploads/story/images/'.$img['image_path']
                    );

                    $db->table('my_fc_profile_story_image')
                        ->where('id',$img['id'])
                        ->delete();

                }

            }

            //---------------------------------------------------
            // 이미지 순서 및 신규 이미지
            //---------------------------------------------------

            $sort=0;
            $newFileIndex=0;

            foreach($imageOrder as $token){
                if(strpos($token,'existing:')===0){
                    $id=(int) substr($token,9);

                    if($id>0 && in_array($id,$oldImageIds)){
                        $db->table('my_fc_profile_story_image')
                            ->where('id',$id)
                            ->where('member_uid',$memberUid)
                            ->update([
                                'sort_order'=>$sort++
                            ]);
                    }

                    continue;
                }

                if($token==='new' && isset($newImageFiles[$newFileIndex])){
                    $file=$newImageFiles[$newFileIndex++];

                    if(!$file->isValid()) continue;

                    $path=upload_file(
                        $file,
                        'uploads/story/images'
                    );

                    $db->table('my_fc_profile_story_image')->insert([
                        'member_uid'=>$memberUid,
                        'image_path'=>$path,
                        'sort_order'=>$sort++,
                        'created_at'=>date('Y-m-d H:i:s')
                    ]);
                }
            }

            //---------------------------------------------------
            // Step
            //---------------------------------------------------

            $memberUpdate = [
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (($member['fc_review_status'] ?? '') !== 'APPROVE') {
                $memberUpdate['fc_step'] = 4;
                $memberUpdate['fc_review_status'] = 'WAIT';
            }

            $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->update($memberUpdate);

            $session->set([
                'fc_step'=>4,
                'fc_onboarding'=>true,
            ]);

            // 프로필·활동·스토리 입력이 모두 완료됐지만 심의필 번호가 없을 때만 안내한다.
            $hasProfile = $db->table('my_fc_profile')->where('member_uid', $memberUid)->countAllResults() > 0;
            $hasActivity = $db->table('my_fc_profile_activity')->where('member_uid', $memberUid)->countAllResults() > 0;
            $hasStoryImage = $db->table('my_fc_profile_story_image')->where('member_uid', $memberUid)->countAllResults() > 0;
            $review = $db->table('my_fc_reviewed')->select('deliberation_no')->where('member_uid', $memberUid)->get()->getRowArray();
            $showDeliberationRegistrationNotice = $hasProfile && $hasActivity && $hasStoryImage
                && trim((string) ($review['deliberation_no'] ?? '')) === '';

            $db->transCommit();

            return $this->response->setJSON([

                'status'=>'success',
                'redirect_url' => base_url('fc/view') . '?uid=' . rawurlencode((string) $memberUid),
                'show_deliberation_registration_notice' => $showDeliberationRegistrationNotice,

            ]);

        }catch(\Throwable $e){

            $db->transRollback();

            return $this->response->setJSON([

                'status'=>'error',

                'message'=>$e->getMessage()

            ]);

        }

    }

    public function updateBasicInfo()
    {
        $this->response->setContentType('application/json');

        $session = session();

        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');

        if (!$memberId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $data = $this->request->getJSON(true);

        $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
        $agreeMarketing = (int)($data['agree_marketing'] ?? 0);

        if (!$phone) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 입력해주세요.'
            ]);
        }

        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 확인해주세요.'
            ]);
        }

        $db = \Config\Database::connect();

        $member = $db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_uid', $memberUid)
            ->where('member_type', 'FC')
            ->where('status', 'ACTIVE')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$member) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '활성 FC 회원만 수정할 수 있습니다.'
            ]);
        }

        $currentPhone = preg_replace('/[^0-9]/', '', (string) ($member['phone'] ?? ''));
        $phoneChanged = $phone !== $currentPhone;
        if ($phoneChanged) {
            $authPhone = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_phone'));
            if (!(bool) $session->get('phone_auth_verified') || $authPhone !== $phone) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => '변경할 휴대폰 번호를 본인인증해주세요.'
                ]);
            }
        }

        // 이름은 요청값이 아니라 기존 값 또는 휴대폰 본인인증 결과만 사용한다.
        $name = (string) ($member['name'] ?? '');
        $birth = (string) ($member['birth'] ?? '');
        $gender = (string) ($member['gender'] ?? '');
        $authPhone = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_phone'));
        if ((bool) $session->get('phone_auth_verified') && $authPhone === $phone) {
            $authName = trim((string) $session->get('phone_auth_name'));
            if ($authName !== '') {
                $name = $authName;
            }
            $authBirth = preg_replace('/[^0-9]/', '', (string) $session->get('phone_auth_birth'));
            $authGender = strtoupper(trim((string) $session->get('phone_auth_gender')));
            if (preg_match('/^\d{8}$/', $authBirth)) $birth = $authBirth;
            if (in_array($authGender, ['M', 'F'], true)) $gender = $authGender;
        }

        // 휴대폰 중복 체크
        $exists = $db->table('my_fc_member')
            ->where('phone', $phone)
            ->where('member_id !=', $memberId)
            ->where('deleted_at', null)
            ->countAllResults();

        if ($exists > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '이미 사용중인 휴대폰 번호입니다.'
            ]);
        }

        $db->table('my_fc_member')
            ->where('member_id', $memberId)
            ->where('member_type', 'FC')
            ->where('status', 'ACTIVE')
            ->where('deleted_at', null)
            ->update([
                'name' => $name,
                'birth' => $birth,
                'gender' => $gender,
                'phone' => $phone,
                'phone_verified' => $phoneChanged ? 'Y' : ($member['phone_verified'] ?? 'N'),
                'agree_marketing' => $agreeMarketing,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '회원정보가 수정되었습니다.'
        ]);
    }
    public function appLogin()
    {

        $request = service('request');


        $appToken =
            $request->getPost('app_token');


        $fcmToken =
            $request->getPost('fcm_token');



        if(empty($appToken)){

            return $this->response->setJSON([
                'result'=>false,
                'message'=>'TOKEN_EMPTY'
            ]);

        }



        $db = \Config\Database::connect();



        // 회원 조회
        $member =
            $db->table('my_fc_member')
            ->where(
                'app_token',
                $appToken
            )
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();



        if(!$member){


            return $this->response->setJSON([

                'result'=>false,

                'message'=>'MEMBER_NOT_FOUND'

            ]);

        }



        // =========================
        // FCM 토큰 업데이트
        // =========================

        $db->table('my_fc_member')
            ->where(
                'member_id',
                $member['member_id']
            )
            ->update([

                'fcm_token'=>$fcmToken,

                'fcm_token_updated_at'=>date(
                    'Y-m-d H:i:s'
                )

            ]);




        // =========================
        // 세션 생성
        // =========================

        $session = session();


        $session->set([

            'member_id'=>
                $member['member_id'],


            'member_uid'=>
                $member['member_uid'],


            'email'=>
                $member['email'],


            'name'=>
                $member['name'],


            'member_type'=>
                $member['member_type'],


            'logged_in'=>true

        ]);




        return $this->response->setJSON([

            'result'=>true,

            'member'=>[

                'member_uid'=>
                    $member['member_uid'],

                'name'=>
                    $member['name']

            ]

        ]);

    }
}
