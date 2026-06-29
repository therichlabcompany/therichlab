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


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/find', $data);
    }

    public function findResult(): string
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


        return $this->renderView('member/findResult', $data);
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


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
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


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/fcAgree', $data);
    }

    public function fcJoin_step1(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/fcJoin_step1', $data);
    }

    public function fcJoin_step2(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [
            "fc_time_modal.php",
            "fc_lang_modal.php"
        ];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
        ];


        return $this->renderView('member/fcJoin_step2', $data);
    }

    public function fcJoin_step3(): string
    {
        //return pageView('welcome_message');
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
            "profile" => [], // ⭐ 핵심
            "activity"     => [],
            "activityItems" => [],
        ];


        return $this->renderView('member/fcJoin_step3', $data);
    }

    public function fcJoin_step4(): string
    {
        //return pageView('welcome_message');
        $header_class = "form-page signup-page";
        $popup_page = [];

        $modal_page = [];


        $data = [
            "header_class" => $header_class,
            "popup_page" => $popup_page,
            "modal_page" => $modal_page
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

        $exists = $db->table('my_fc_member')
            ->where('email', $email)
            ->where('deleted_at', null)
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'duplicate' => $exists > 0
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

        $exists = $db->table('my_fc_member')
            ->where('phone', $phone)
            ->where('deleted_at', null)
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'duplicate' => $exists > 0
        ]);
    }

    public function register()
    {
        $db = \Config\Database::connect();

        try {

            $db->transBegin();

            // =========================
            // JSON 입력 받기
            // =========================
            $data = $this->request->getJSON(true);

            if (!$data) {
                throw new \Exception('잘못된 요청입니다.');
            }

            // =========================
            // 1. 기본값 정리
            // =========================
            $email = strtolower(trim($data['email'] ?? ''));
            $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');

            $password = $data['password'] ?? '';
            $passwordConfirm = $data['password_confirm'] ?? '';

            $memberType = $data['member_type'] ?? 'USER';

            // =========================
            // 2. 검증
            // =========================
            if (!$email || !$phone || !$password) {
                throw new \Exception('필수값 누락');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('이메일 형식 오류');
            }

            if ($password !== $passwordConfirm) {
                throw new \Exception('비밀번호 불일치');
            }

            // =========================
            // 3. 중복 체크
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

            // =========================
            // 4. INSERT
            // =========================
            $memberData = [
                'member_uid' => $this->generateMemberUid(),

                'member_type' => $memberType,
                'email'       => $email,
                'password'    => password_hash($password, PASSWORD_DEFAULT),
                'phone'       => $phone,

                'name'   => $data['name'] ?? '',
                'birth'  => $data['birth'] ?? '',
                'gender' => $data['gender'] ?? '',

                'phone_verified' => $data['phone_verified'] ?? 'N',

                'agree_age'       => !empty($data['agree_age']) ? 'Y' : 'N',
                'agree_terms'     => !empty($data['agree_terms']) ? 'Y' : 'N',
                'agree_privacy'   => !empty($data['agree_privacy']) ? 'Y' : 'N',
                'agree_marketing' => !empty($data['agree_marketing']) ? 'Y' : 'N',

                'created_at' => date('Y-m-d H:i:s'),
            ];

            $db->table('my_fc_member')->insert($memberData);

            $memberId = $db->insertID();

            // =========================
            // 5. FC 확장 구조 (미래용)
            // =========================
            if ($memberType === 'FC') {
                $db->table('my_fc_fc_profile')->insert([
                    'member_id' => $memberId,
                    'status'    => 'PENDING',
                    'created_at' => date('Y-m-d H:i:s')
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

    private function generateMemberUid()
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijklmnopqrstuvwxyz';

        $uid = '';
        for ($i = 0; $i < 20; $i++) {
            $uid .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $uid;
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
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '존재하지 않는 계정입니다'
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
            'member_id' => $user['member_id']
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

            $db->transBegin();

            helper('fileupload_helper');

            // =========================
            // 1. 기존 데이터 확인
            // =========================
            $profile = $db->table('my_fc_profile')
                ->where('member_uid', $memberUid)
                ->get()
                ->getRowArray();

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
                'company'      => $this->request->getPost('company'),
                'company_sub'  => $this->request->getPost('company_sub'),
                'ga'           => $this->request->getPost('ga'),
                'position'     => $this->request->getPost('position'),
                'license_date' => $this->request->getPost('license_date'),
                'license_no'   => $this->request->getPost('license_no'),
                'time_from'    => $this->request->getPost('time_from'),
                'time_to'      => $this->request->getPost('time_to'),
                'language'     => $this->request->getPost('language'),
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
            $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->update([
                    'fc_step' => 2,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            // =========================
            // 7. session sync
            // =========================
            $session->set([
                'fc_step' => 2
            ]);

            $db->transCommit();

            return $this->response->setJSON([
                'status' => 'success'
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

            $db->transBegin();

            // ===========================
            // Activity 저장
            // ===========================

            $activityData = [

                'member_uid'      => $memberUid,

                'region'          => $this->request->getPost('region'),

                'insurance_types' => $this->request->getPost('insurance_types'),

                'hero_line'       => $this->request->getPost('history'),

                'intro'           => $this->request->getPost('intro'),

                'career'          => $this->request->getPost('career'),

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

                    'content'    => $item['content'] ?? null,

                    'url'        => $item['url'] ?? null,

                    'sort_order' => $i,

                    'is_visible' => 1,

                ];

                // 제목 없으면 skip
                if ($data['title'] == '') {
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

            $db->table('my_fc_member')
                ->where('member_uid', $memberUid)
                ->update([

                    'fc_step'    => 3,

                    'updated_at' => date('Y-m-d H:i:s')

                ]);

            // ===========================
            // Commit
            // ===========================

            if ($db->transStatus() === false) {

                throw new \Exception('DB 저장 중 오류가 발생했습니다.');
            }

            $db->transCommit();

            return $this->response->setJSON([

                'status' => 'success',

                'message' => '저장되었습니다.'

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

            //---------------------------------------------------
            // 삭제 이미지 찾기
            //---------------------------------------------------

            $oldImages=$db->table('my_fc_profile_story_image')
                ->where('member_uid',$memberUid)
                ->get()
                ->getResultArray();

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
            // 기존 이미지 순서
            //---------------------------------------------------

            foreach($keepImages as $sort=>$id){

                $db->table('my_fc_profile_story_image')
                    ->where('id',$id)
                    ->update([

                        'sort_order'=>$sort

                    ]);

            }

            //---------------------------------------------------
            // 신규 이미지
            //---------------------------------------------------

            $files=$this->request->getFiles();

            $sort=count($keepImages);

            if(isset($files['story_images'])){

                foreach($files['story_images'] as $file){

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

            $db->table('my_fc_member')
                ->where('member_uid',$memberUid)
                ->update([

                    'fc_step'=>4,

                    'updated_at'=>date('Y-m-d H:i:s')

                ]);

            $db->transCommit();

            return $this->response->setJSON([

                'status'=>'success'

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

        if (!$memberId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $data = $this->request->getJSON(true);

        $phone = trim($data['phone'] ?? '');
        $agreeMarketing = (int)($data['agree_marketing'] ?? 0);

        if (!$phone) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => '휴대폰 번호를 입력해주세요.'
            ]);
        }

        $db = \Config\Database::connect();

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
            ->update([
                'phone' => $phone,
                'agree_marketing' => $agreeMarketing,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '회원정보가 수정되었습니다.'
        ]);
    }
}
