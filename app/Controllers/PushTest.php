<?php

namespace App\Controllers;

use App\Libraries\FirebasePush;

class PushTest extends BaseController
{
    public function index()
    {
        $deviceToken = 'e5ly-BQvRa6fCAeq6gTxYO:APA91bFGL2-IlDTwxbntLWN7u906Ki3GKP9liKI26MmhMZLcuKisW84sR8GhQSF7USzpIKTNU3lPgNPswc7svN-bbcObj4RsknigvaV5EVcpOHFhcPY6nWc';

        $push = new FirebasePush();

        $result = $push->send(
            $deviceToken,
            'myfc.co.kr 테스트 알림',
            'myfc.co.kr 보낸 푸시입니다.',
            [
                'type' => 'notice',
                'id'   => 123,
            ]
        );

        return $this->response->setJSON($result);
    }
}