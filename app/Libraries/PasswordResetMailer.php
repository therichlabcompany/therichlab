<?php

namespace App\Libraries;

class PasswordResetMailer
{
    public function send(array $member): array
    {
        $memberUid = trim((string) ($member['member_uid'] ?? ''));
        $memberEmail = trim((string) ($member['email'] ?? ''));

        if ($memberUid === '' || !filter_var($memberEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => '회원 이메일 정보를 확인할 수 없습니다.'];
        }

        $db = \Config\Database::connect();
        if (!$db->tableExists('my_fc_password_reset_token')) {
            return ['success' => false, 'message' => '비밀번호 재설정 기능 준비가 필요합니다.'];
        }

        $config = config('Email');
        if (empty($config->fromEmail)) {
            return ['success' => false, 'message' => '메일 발송 설정이 완료되지 않았습니다.'];
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
        $name = htmlspecialchars((string) ($member['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($memberEmail, ENT_QUOTES, 'UTF-8');
        $logoUrl = base_url('assets/images/logo.png');
        $mail = \Config\Services::email();
        $mail->setFrom($config->fromEmail, $config->fromName ?: 'MyFC');
        $mail->setTo($memberEmail);
        $mail->setSubject('[MyFC] 비밀번호 재설정 안내');
        $mail->setMailType('html');
        $mail->setMessage(<<<HTML
<!doctype html>
<html lang="ko"><body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,'Noto Sans KR',sans-serif;color:#172033;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f4f6;"><tr><td align="center" style="padding:40px 16px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#ffffff;border-radius:16px;overflow:hidden;">
<tr><td style="padding:32px 40px 0;"><img src="{$logoUrl}" alt="MyFC" style="display:block;width:auto;height:30px;border:0;"></td></tr>
<tr><td style="padding:32px 40px 80px;"><h1 style="margin:0 0 28px;font-size:28px;line-height:1.35;font-weight:800;color:#111827;">[MyFC] 비밀번호 재설정 안내</h1>
<div style="padding:80px;background:#f3f4f6;border-radius:12px;font-size:16px;line-height:1.8;color:#374151;">
<p style="margin:0 0 24px;">안녕하세요, {$name}님, MyFC입니다.</p>
<p style="margin:0 0 32px;"><strong style="font-weight:700;color:#111827;">{$email}</strong> 계정의 비밀번호를 재설정하시려면,<br>아래 버튼을 클릭해주세요.</p>
<p style="margin:0 0 32px;">문의사항이 있으시면 고객센터로 연락해 주세요.</p>
<p style="margin:0;"><a href="{$resetUrl}" style="display:inline-block;box-sizing:border-box;min-width:190px;padding:15px 24px;border-radius:8px;background:#111827;color:#ffffff;font-size:16px;font-weight:700;line-height:1.4;text-align:center;text-decoration:none;">비밀번호 재설정</a></p>
</div></td></tr>
</table></td></tr></table></body></html>
HTML);

        if (!$mail->send(false)) {
            $db->table('my_fc_password_reset_token')->where('member_uid', $memberUid)->delete();
            return ['success' => false, 'message' => '메일을 발송하지 못했습니다. 잠시 후 다시 시도해주세요.'];
        }

        return ['success' => true, 'message' => '비밀번호 재설정 안내 메일을 발송했습니다.'];
    }
}
