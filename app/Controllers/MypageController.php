<?php

namespace App\Controllers;

class MypageController extends BaseController
{
    public function index(): string
    {
        //return pageView('welcome_message');
        $header_class="search-page results";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/index', $data);
    }

    public function info(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/info', $data);
    }

    public function withdrawalLast(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page flow-result";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/withdrawalLast', $data);
    }

    public function certificate(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page form-page securities-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/certificate', $data);
    }

    public function favoriteFc(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page favorite-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/favoriteFc', $data);
    }

    public function counselList(): string
    {
        //return pageView('welcome_message');
        $header_class="consult-status-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/counselList', $data);
    }

    public function reviewWrite(): string
    {
        //return pageView('welcome_message');
        $header_class="form-page";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewWrite', $data);
    }

    public function reviewWriteLast(): string
    {
        //return pageView('welcome_message');
        $header_class="flow-result";
        $popup_page = [
        ];

        $modal_page = [
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewWriteLast', $data);
    }

    public function reviewList(): string
    {
        //return pageView('welcome_message');
        $header_class="consult-status-page detail-page";
        $popup_page = [
        ];

        $modal_page = [
            "hugi_modal.php"
        ];


        $data = [
            "header_class" => $header_class
            ,"popup_page" => $popup_page
            ,"modal_page" => $modal_page
        ];


        return $this->renderView('mypage/reviewList', $data);
    }


    public function fcinfo(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');
        

        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
        ->where('member_uid', $memberUid)
        ->get()
        ->getRowArray();

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => [],
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcinfo', $data);
    }

    public function updateProfileImage()
    {
        $this->response->setContentType('application/json');

        helper('fileupload_helper');

        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $db = \Config\Database::connect();

        $file = $this->request->getFile('profile_image');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '파일이 없습니다.'
            ]);
        }

        // =========================
        // 1. 업로드 (writable 기준)
        // =========================
        $fileName = upload_file($file, 'uploads/profile');

        if (!$fileName) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '업로드 실패'
            ]);
        }

        $profileTable = $db->table('my_fc_profile');

        $profile = $profileTable
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // =========================
        // 2. DB 저장용 값 (파일명만)
        // =========================
        $saveFile = $fileName;

        // =========================
        // 3. 기존 파일 삭제 (public 기준)
        // =========================
        if ($profile && !empty($profile['profile_image'])) {

            $oldPath = WRITEPATH. 'uploads/profile/' . $profile['profile_image'];

            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // =========================
        // 4. UPSERT
        // =========================
        if ($profile) {

            $profileTable->where('member_uid', $memberUid)
                ->update([
                    'profile_image' => $saveFile,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

        } else {

            $profileTable->insert([
                'member_uid' => $memberUid,
                'profile_image' => $saveFile,
                'step' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '프로필 이미지 업데이트 완료',
            'data' => [
                'profile_image' => $saveFile,
                'url' => base_url('uploads/profile/' . $saveFile)
            ]
        ]);
    }

    public function fcprofile(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');
        

        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
        ->where('member_uid', $memberUid)
        ->get()
        ->getRowArray();

        $modal_page = [
            "fc_time_modal.php",
            "fc_lang_modal.php"
        ];

        // print_r($profile);
        // exit;

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcprofile', $data);
    }

    public function fcactivity(): string
    {
        $session = session();
        helper(['region', 'insurance']);
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');
        

        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
        ->where('member_uid', $memberUid)
        ->get()
        ->getRowArray();

        

        // 활동 정보
        $activity = $db->table('my_fc_profile_activity')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // 활동 아이템 목록
        $activityItems = $db->table('my_fc_profile_activity_item')
            ->where('member_uid', $memberUid)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('item_id', 'ASC')
            ->get()
            ->getResultArray();

        $modal_page = [
            "region_modal.php",
            "insurance_modal.php"
        ];

        // print_r($profile);
        // exit;
        // echo "<br>=========================activity<br>=========================<br>";
        // print_r($activity);
        // echo "<br>=========================activityItems<br>=========================<br>";
        // print_r($activityItems);
        // exit;

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
            "activity"     => $activity,
            "activityItems"=> $activityItems,
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcactivity', $data);
    }

    public function fcstory(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');
        

        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $profile = $db->table('my_fc_profile')
        ->where('member_uid', $memberUid)
        ->get()
        ->getRowArray();

        

        // 활동 정보
        $activity = $db->table('my_fc_profile_activity')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // 활동 아이템 목록
        $activityItems = $db->table('my_fc_profile_activity_item')
            ->where('member_uid', $memberUid)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('item_id', 'ASC')
            ->get()
            ->getResultArray();

        // ==========================
        // Story 정보
        // ==========================
        $story = $db->table('my_fc_profile_story')
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // ==========================
        // Story 이미지
        // ==========================
        $storyImages = $db->table('my_fc_profile_story_image')
            ->where('member_uid', $memberUid)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        $modal_page = [
            
        ];

        $data = [
            "header_class" => "form-page signup-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            "profile" => $profile, // ⭐ 핵심
            "activity"     => $activity,
            "activityItems"=> $activityItems,
             // 추가
            'story'        => $story,
            'storyImages'  => $storyImages,
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcstory', $data);
    }

    public function fcreviewed(): string
    {
        $session = session();
        $memberId = $session->get('member_id');
        $memberUid = $session->get('member_uid');
        

        if (!$memberId) {
            return redirect()->to('/login');
        }

        // 모델 호출 (예: MemberModel)
        $memberModel = new \App\Models\MemberModel();

        $user = $memberModel
            ->where('member_id', $memberId)
            ->first();

        if (!$user) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $review = $db->table('my_fc_reviewed')
        ->where('member_uid', $memberUid)
        ->get()
        ->getRowArray();
        
        $modal_page = [
            
        ];

        $data = [
            "header_class" => "form-page",
            "popup_page" => [],
            "modal_page" => $modal_page,
            "user" => $user, // ⭐ 핵심
            'review' => $review
            
        ];
        $data['mode'] = 'edit';

        return $this->renderView('mypage/fcreviewed', $data);
    }

    public function ajax_save_reviewed()
    {
        $this->response->setContentType('application/json');

        helper('fileupload_helper');

        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'result' => 'fail',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        $db = \Config\Database::connect();

        $table = $db->table('my_fc_reviewed');

        // 기존 데이터
        $review = $table
            ->where('member_uid', $memberUid)
            ->get()
            ->getRowArray();

        // ============================
        // 상태 정책
        // ============================
        $currentStatus = $review['status'] ?? null;

        // WAIT이면 수정 불가 (보안 레벨)
        if ($currentStatus === 'WAIT') {
            return $this->response->setJSON([
                'result' => 'fail',
                'msg' => '승인 대기중에는 수정할 수 없습니다.'
            ]);
        }

        // 입력값
        $saveData = [
            'deliberation_no'      => trim($this->request->getPost('deliberation_no')),
            'approval_start'       => $this->request->getPost('approval_start'),
            'approval_end'         => $this->request->getPost('approval_end'),
            'deliberation_opinion' => trim($this->request->getPost('deliberation_opinion')),
        ];

        // ============================
        // 파일 업로드
        // ============================
        $file = $this->request->getFile('deliberation_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {

            $fileName = upload_file($file, 'uploads/review');

            if (!$fileName) {

                return $this->response->setJSON([
                    'result' => 'fail',
                    'msg' => '파일 업로드에 실패했습니다.'
                ]);

            }

            // 기존 파일 삭제
            if ($review && !empty($review['deliberation_file'])) {

                $oldPath = WRITEPATH . 'uploads/review/' . $review['deliberation_file'];

                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $saveData['deliberation_file'] = $fileName;
        }

        // ============================
        // UPDATE
        // ============================
        if ($review) {

            $saveData['updated_at'] = date('Y-m-d H:i:s');

            // =========================
            // 상태 리셋 규칙
            // =========================
            if (in_array($review['status'], ['APPROVE', 'REJECT'])) {
                $saveData['status'] = 'WAIT';
            }

            $table
                ->where('member_uid', $memberUid)
                ->update($saveData);

        }
        // ============================
        // INSERT
        // ============================
        else {

            $saveData['member_uid'] = $memberUid;
            $saveData['status'] = 'WAIT';
            $saveData['created_at'] = date('Y-m-d H:i:s');

            $table->insert($saveData);

        }

        return $this->response->setJSON([
            'result' => 'success',
            'msg' => '심의필 정보가 저장되었습니다.'
        ]);
    }

    

    

    

    

}
