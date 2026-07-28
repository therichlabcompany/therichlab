<?php

namespace App\Controllers;

use App\Libraries\FirebasePush;

class PushTest extends BaseController
{
    public function index()
    {
        $deviceToken = 'fp5AZ8kGx0Jdhu83hatwGB:APA91bFv8x27b-40WqzH__rPX2h_Y2vCF5hzZ6mGxP7W_h91tS3gwkwt8CHVy5gHfW42HI7nC0vAGVWYOwNJKHlXWFcEI3U7N2EU2DPfrI23tVgx71hxIoM';

        $push = new FirebasePush();

        $result = $push->send(
            $deviceToken,
            'myfc.co.kr 테스트 알림 - ios',
            'myfc.co.kr 보낸 푸시입니다.-ios',
            [
                'type' => 'notice',
                'id'   => 123,
            ]
        );

        return $this->response->setJSON($result);
    }
}