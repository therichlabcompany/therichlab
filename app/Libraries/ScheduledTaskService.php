<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class ScheduledTaskService
{
    private const LEAVE_RETENTION_DAYS = 7;
    private const PUSH_STALE_MINUTES = 30;

    public function __construct(private readonly BaseConnection $db)
    {
    }

    /**
     * @return array{anonymized_members:int, expired_reset_tokens:int, recovered_pushes:int, processed_pushes:int, sent_targets:int, failed_targets:int}
     */
    public function run(bool $runLeaveCleanup = true, bool $runPushes = true, bool $dryRun = false): array
    {
        $result = [
            'anonymized_members' => 0,
            'expired_reset_tokens' => 0,
            'recovered_pushes' => 0,
            'processed_pushes' => 0,
            'sent_targets' => 0,
            'failed_targets' => 0,
        ];

        if ($runLeaveCleanup) {
            $result['anonymized_members'] = $this->anonymizeExpiredLeaveMembers($dryRun);
            $result['expired_reset_tokens'] = $this->deleteExpiredPasswordResetTokens($dryRun);
        }

        if ($runPushes) {
            $result['recovered_pushes'] = $this->recoverStalePushes($dryRun);
            $pushResult = $this->sendDuePushes($dryRun);
            $result['processed_pushes'] = $pushResult['processed_pushes'];
            $result['sent_targets'] = $pushResult['sent_targets'];
            $result['failed_targets'] = $pushResult['failed_targets'];
        }

        return $result;
    }

    private function anonymizeExpiredLeaveMembers(bool $dryRun): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::LEAVE_RETENTION_DAYS . ' days'));
        $members = $this->db->table('my_fc_member')
            ->select('member_id, email, phone')
            ->where('status', 'LEAVE')
            ->where('deleted_at <=', $cutoff)
            ->groupStart()
                ->notLike('email', 'withdrawn-', 'after')
                ->orNotLike('phone', 'W', 'after')
            ->groupEnd()
            ->get()
            ->getResultArray();

        if ($dryRun) {
            return count($members);
        }

        $now = date('Y-m-d H:i:s');
        $count = 0;

        foreach ($members as $member) {
            $memberId = (int) ($member['member_id'] ?? 0);
            if ($memberId < 1) {
                continue;
            }

            $emailHash = substr(hash('sha256', (string) ($member['email'] ?? '') . '|' . $memberId), 0, 12);
            $phoneHash = substr(hash('sha256', (string) ($member['phone'] ?? '') . '|' . $memberId), 0, 19);

            $this->db->table('my_fc_member')
                ->where('member_id', $memberId)
                ->where('status', 'LEAVE')
                ->update([
                    'email' => 'withdrawn-' . $memberId . '-' . $emailHash . '@deleted.invalid',
                    'phone' => 'W' . $phoneHash,
                    'phone_verified' => 'N',
                    'name' => '탈퇴회원',
                    'birth' => null,
                    'gender' => null,
                    'nickname' => null,
                    'profile_image' => null,
                    'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                    'login_fail_count' => 0,
                    'password_reset_at' => null,
                    'last_login_at' => null,
                    'agree_marketing' => 0,
                    'join_ip' => null,
                    'admin_memo' => null,
                    'app_token' => null,
                    'fcm_token' => null,
                    'fcm_token_updated_at' => null,
                    'app_platform' => null,
                    'app_token_expire_at' => null,
                    'app_token_updated_at' => null,
                    'updated_at' => $now,
                ]);

            $count += $this->db->affectedRows() > 0 ? 1 : 0;
        }

        return $count;
    }

    private function recoverStalePushes(bool $dryRun): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::PUSH_STALE_MINUTES . ' minutes'));
        $builder = $this->db->table('my_fc_push')
            ->where('status', 'SENDING')
            ->where('updated_at <=', $cutoff)
            ->where('deleted_at', null);

        if ($dryRun) {
            return $builder->countAllResults();
        }

        $stalePushes = $builder->select('push_id, send_type')->get()->getResultArray();
        $count = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($stalePushes as $push) {
            $status = ($push['send_type'] ?? '') === 'RESERVED' ? 'RESERVED' : 'READY';
            $this->db->table('my_fc_push')
                ->where('push_id', (int) $push['push_id'])
                ->where('status', 'SENDING')
                ->update(['status' => $status, 'updated_at' => $now]);
            $count += $this->db->affectedRows() > 0 ? 1 : 0;
        }

        return $count;
    }

    private function deleteExpiredPasswordResetTokens(bool $dryRun): int
    {
        if (!$this->db->tableExists('my_fc_password_reset_token')) {
            return 0;
        }

        $builder = $this->db->table('my_fc_password_reset_token')
            ->where('expires_at <', date('Y-m-d H:i:s'));

        if ($dryRun) {
            return $builder->countAllResults();
        }

        $builder->delete();

        return $this->db->affectedRows();
    }

    /**
     * @return array{processed_pushes:int, sent_targets:int, failed_targets:int}
     */
    private function sendDuePushes(bool $dryRun): array
    {
        $now = date('Y-m-d H:i:s');
        $duePushes = $this->db->table('my_fc_push')
            ->select('push_id')
            ->where('deleted_at', null)
            ->groupStart()
                ->groupStart()
                    ->where('send_type', 'NOW')
                    ->where('status', 'READY')
                ->groupEnd()
                ->orGroupStart()
                    ->where('send_type', 'RESERVED')
                    ->where('status', 'RESERVED')
                    ->where('scheduled_at <=', $now)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('push_id', 'ASC')
            ->get()
            ->getResultArray();

        $result = ['processed_pushes' => 0, 'sent_targets' => 0, 'failed_targets' => 0];

        foreach ($duePushes as $duePush) {
            $pushId = (int) ($duePush['push_id'] ?? 0);
            if ($pushId < 1) {
                continue;
            }

            if ($dryRun) {
                ++$result['processed_pushes'];
                continue;
            }

            $push = $this->claimPush($pushId, $now);
            if ($push === null) {
                continue;
            }

            ++$result['processed_pushes'];

            try {
                $pushResult = $this->sendPush($push);
                $result['sent_targets'] += $pushResult['sent_targets'];
                $result['failed_targets'] += $pushResult['failed_targets'];
            } catch (\Throwable $e) {
                $this->markPushFailed($pushId, $e);
                ++$result['failed_targets'];
            }
        }

        return $result;
    }

    /** @return array<string, mixed>|null */
    private function claimPush(int $pushId, string $now): ?array
    {
        $this->db->transStart();

        $push = $this->db->table('my_fc_push')
            ->where('push_id', $pushId)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$push || !$this->isDuePush($push, $now)) {
            $this->db->transComplete();
            return null;
        }

        $this->db->table('my_fc_push')
            ->where('push_id', $pushId)
            ->where('status', (string) $push['status'])
            ->update(['status' => 'SENDING', 'updated_at' => $now]);

        $claimed = $this->db->affectedRows() === 1;
        $this->db->transComplete();

        return $claimed && $this->db->transStatus() !== false ? $push : null;
    }

    /** @param array<string, mixed> $push */
    private function isDuePush(array $push, string $now): bool
    {
        if (($push['send_type'] ?? '') === 'NOW') {
            return ($push['status'] ?? '') === 'READY';
        }

        return ($push['send_type'] ?? '') === 'RESERVED'
            && ($push['status'] ?? '') === 'RESERVED'
            && !empty($push['scheduled_at'])
            && (string) $push['scheduled_at'] <= $now;
    }

    /**
     * @param array<string, mixed> $push
     * @return array{sent_targets:int, failed_targets:int}
     */
    private function sendPush(array $push): array
    {
        $pushId = (int) $push['push_id'];
        $targets = $this->db->table('my_fc_push_target')
            ->where('push_id', $pushId)
            ->where('send_status', 'WAIT')
            ->orderBy('target_id', 'ASC')
            ->get()
            ->getResultArray();

        $firebase = new FirebasePush();
        $sent = 0;
        $failed = 0;
        $now = date('Y-m-d H:i:s');

        foreach ($targets as $target) {
            $targetId = (int) ($target['target_id'] ?? 0);
            $token = trim((string) ($target['fcm_token'] ?? ''));
            if ($targetId < 1 || $token === '') {
                $this->markTargetFailed($targetId, '발송 토큰이 없습니다.', $now);
                ++$failed;
                continue;
            }

            $response = $firebase->send($token, (string) $push['title'], (string) $push['body'], [
                'type' => 'notice',
                'push_id' => (string) $pushId,
                'click_url' => (string) ($push['click_url'] ?? ''),
                'image_path' => (string) ($push['image_path'] ?? ''),
            ]);

            if (!empty($response['success'])) {
                $this->db->table('my_fc_push_target')
                    ->where('target_id', $targetId)
                    ->where('send_status', 'WAIT')
                    ->update([
                        'send_status' => 'SENT',
                        'error_message' => null,
                        'sent_at' => $now,
                        'updated_at' => $now,
                    ]);
                ++$sent;
                continue;
            }

            $message = $this->pushErrorMessage($response);
            $this->markTargetFailed($targetId, $message, $now);
            $this->clearInvalidMemberToken($target, $message);
            ++$failed;
        }

        $this->finishPush($pushId, $sent, $failed, $now);

        return ['sent_targets' => $sent, 'failed_targets' => $failed];
    }

    private function markTargetFailed(int $targetId, string $message, string $now): void
    {
        if ($targetId < 1) {
            return;
        }

        $this->db->table('my_fc_push_target')
            ->where('target_id', $targetId)
            ->where('send_status', 'WAIT')
            ->update([
                'send_status' => 'FAILED',
                'error_message' => mb_substr($message, 0, 1000),
                'updated_at' => $now,
            ]);
    }

    /** @param array<string, mixed> $target */
    private function clearInvalidMemberToken(array $target, string $message): void
    {
        if (!preg_match('/UNREGISTERED|registration-token-not-registered/i', $message)) {
            return;
        }

        $memberUid = (string) ($target['member_uid'] ?? '');
        $token = (string) ($target['fcm_token'] ?? '');
        if ($memberUid === '' || $token === '') {
            return;
        }

        $this->db->table('my_fc_member')
            ->where('member_uid', $memberUid)
            ->where('fcm_token', $token)
            ->update([
                'fcm_token' => null,
                'fcm_token_updated_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function finishPush(int $pushId, int $sent, int $failed, string $now): void
    {
        $counts = $this->db->table('my_fc_push_target')
            ->select("SUM(send_status = 'SENT') AS success_count, SUM(send_status = 'FAILED') AS fail_count", false)
            ->where('push_id', $pushId)
            ->get()
            ->getRowArray() ?? [];

        $successCount = (int) ($counts['success_count'] ?? $sent);
        $failCount = (int) ($counts['fail_count'] ?? $failed);

        $this->db->table('my_fc_push')
            ->where('push_id', $pushId)
            ->where('status', 'SENDING')
            ->update([
                'status' => $failCount > 0 && $successCount === 0 ? 'FAILED' : 'SENT',
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'sent_at' => $now,
                'updated_at' => $now,
            ]);
    }

    private function markPushFailed(int $pushId, \Throwable $exception): void
    {
        $now = date('Y-m-d H:i:s');
        $message = mb_substr($this->safeExceptionMessage($exception), 0, 1000);

        $this->db->table('my_fc_push')
            ->where('push_id', $pushId)
            ->where('status', 'SENDING')
            ->update([
                'status' => 'FAILED',
                'fail_count' => 1,
                'updated_at' => $now,
            ]);

        $this->db->table('my_fc_push_target')
            ->where('push_id', $pushId)
            ->where('send_status', 'WAIT')
            ->update([
                'send_status' => 'FAILED',
                'error_message' => $message,
                'updated_at' => $now,
            ]);
    }

    /** @param array<string, mixed> $response */
    private function pushErrorMessage(array $response): string
    {
        $message = trim((string) ($response['error'] ?? '푸시 발송에 실패했습니다.'));

        return $this->stripSensitiveText($message);
    }

    private function safeExceptionMessage(\Throwable $exception): string
    {
        return $this->stripSensitiveText($exception->getMessage() ?: '푸시 발송 중 처리 오류가 발생했습니다.');
    }

    private function stripSensitiveText(string $message): string
    {
        $message = preg_replace('/(?:[A-Za-z0-9_-]{20,}:)?APA91[A-Za-z0-9_-]+/', '[redacted-token]', $message) ?? $message;

        return $message !== '' ? $message : '푸시 발송에 실패했습니다.';
    }
}
