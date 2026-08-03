<?php

namespace App\Controllers;

use App\Libraries\ScheduledTaskService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class CronController extends BaseController
{
    public function scheduledTasks(): ResponseInterface
    {
        $secret = trim((string) (getenv('MYFC_CRON_SECRET') ?: ''));
        $providedSecret = trim($this->request->getHeaderLine('X-MyFC-Cron-Key'));

        if ($secret === '' || $providedSecret === '' || !hash_equals($secret, $providedSecret)) {
            log_message('warning', 'Blocked unauthorized scheduler URL request.');
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => '접근 권한이 없습니다.']);
        }

        $lock = $this->acquireLock();
        if ($lock === null) {
            return $this->response->setStatusCode(202)->setJSON([
                'status' => 'skipped',
                'message' => '이미 실행 중인 스케줄러가 있습니다.',
            ]);
        }

        try {
            set_time_limit(0);
            $result = (new ScheduledTaskService(Database::connect()))->run();

            return $this->response->setJSON([
                'status' => 'success',
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Scheduler URL request failed [{type}]: {message}', [
                'type' => get_class($e),
                'message' => $this->safeErrorMessage($e->getMessage()),
            ]);
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => '스케줄러 실행에 실패했습니다.',
            ]);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @return resource|null */
    private function acquireLock()
    {
        $directory = WRITEPATH . 'cache';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return null;
        }

        $lock = fopen($directory . DIRECTORY_SEPARATOR . 'myfc-schedule.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            return null;
        }

        return $lock;
    }

    private function safeErrorMessage(string $message): string
    {
        $message = preg_replace('/(?:[A-Za-z0-9_-]{20,}:)?APA91[A-Za-z0-9_-]+/', '[redacted-token]', $message) ?? $message;
        $message = preg_replace('/(password\s*[=:]\s*)[^\s,;]+/i', '$1[redacted]', $message) ?? $message;

        return mb_substr($message !== '' ? $message : 'Unknown scheduler error.', 0, 1000);
    }
}
