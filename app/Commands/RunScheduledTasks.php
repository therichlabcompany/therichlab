<?php

namespace App\Commands;

use App\Libraries\ScheduledTaskService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class RunScheduledTasks extends BaseCommand
{
    protected $group = 'MyFC';
    protected $name = 'myfc:schedule-run';
    protected $description = '탈퇴 회원 익명화와 예약·즉시 푸시 발송 작업을 실행합니다.';

    protected $usage = 'myfc:schedule-run [--leave-only] [--push-only] [--dry-run]';

    protected $options = [
        '--leave-only' => '탈퇴 회원 익명화만 실행합니다.',
        '--push-only' => '예약·즉시 푸시 발송만 실행합니다.',
        '--dry-run' => '데이터를 변경하거나 푸시를 발송하지 않고 대상 수만 표시합니다.',
    ];

    public function run(array $params)
    {
        $leaveOnly = CLI::getOption('leave-only') !== null;
        $pushOnly = CLI::getOption('push-only') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        if ($leaveOnly && $pushOnly) {
            CLI::error('--leave-only와 --push-only는 함께 사용할 수 없습니다.');
            return EXIT_USER_INPUT;
        }

        $lock = $this->acquireLock();
        if ($lock === null) {
            CLI::write('이미 실행 중인 스케줄러가 있어 이번 실행은 건너뜁니다.', 'yellow');
            return EXIT_SUCCESS;
        }

        try {
            $service = new ScheduledTaskService(Database::connect());
            $result = $service->run(!$pushOnly, !$leaveOnly, $dryRun);

            CLI::write(sprintf(
                '[%s] %s탈퇴 익명화 %d건 / 만료 재설정 토큰 삭제 %d건 / 복구 푸시 %d건 / 처리 푸시 %d건 / 성공 %d건 / 실패 %d건',
                date('Y-m-d H:i:s'),
                $dryRun ? 'DRY-RUN ' : '',
                $result['anonymized_members'],
                $result['expired_reset_tokens'],
                $result['recovered_pushes'],
                $result['processed_pushes'],
                $result['sent_targets'],
                $result['failed_targets']
            ));

            return EXIT_SUCCESS;
        } catch (\Throwable $e) {
            log_message('error', 'MyFC scheduler failed.');
            CLI::error('스케줄러 실행 중 오류가 발생했습니다. 로그를 확인해주세요.');
            return EXIT_ERROR;
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
}
