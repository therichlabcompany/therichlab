<?php

namespace App\Controllers;

use App\Models\CounselModel;
use CodeIgniter\Controller;

class CounselController extends BaseController
{

    public function save()
    {
        helper(['text']);

        $session = session();

        // =====================================
        // 로그인 체크
        // =====================================
        if (!$session->get('logged_in')) {

            return $this->response->setJSON([
                'result' => 'fail',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        // =====================================
        // USER만 상담 가능
        // =====================================
        if ($session->get('member_type') != 'USER') {

            return $this->response->setJSON([
                'result' => 'fail',
                'message' => '일반 회원만 상담 신청이 가능합니다.'
            ]);
        }

        $memberUid = $session->get('member_uid');

        // =====================================
        // POST
        // =====================================
        $fcMemberUid    = trim($this->request->getPost('fc_member_uid'));
        $reserveDate    = trim($this->request->getPost('reserve_datetime'));
        $content        = trim($this->request->getPost('content'));

        if (!$fcMemberUid) {

            return $this->response->setJSON([
                'result'=>'fail',
                'message'=>'FC 정보가 없습니다.'
            ]);
        }

        $forbiddenWord = $this->forbiddenWordViolation($content, ['COUNSEL']);
        if ($forbiddenWord !== null) {
            return $this->response->setJSON([
                'result' => 'fail',
                'message' => $this->forbiddenWordErrorMessage('상담 내용', $forbiddenWord),
            ]);
        }

        // =====================================
        // 회원정보 조회
        // =====================================
        $db = \Config\Database::connect();

        $myMember = $db->table('my_fc_member')
            ->where('member_uid',$memberUid)
            ->where('deleted_at',null)
            ->get()
            ->getRowArray();

        if(!$myMember){

            return $this->response->setJSON([
                'result'=>'fail',
                'message'=>'회원정보가 없습니다.'
            ]);
        }

        // =====================================
        // FC 존재 확인
        // =====================================
        $fcMember = $db->table('my_fc_member')
            ->where('member_uid',$fcMemberUid)
            ->where('member_type','FC')
            ->where('deleted_at',null)
            ->get()
            ->getRowArray();

        if(!$fcMember){

            return $this->response->setJSON([
                'result'=>'fail',
                'message'=>'FC 정보를 찾을 수 없습니다.'
            ]);
        }

        // =====================================
        // 상담 UID 생성
        // =====================================
        $counselUid = strtoupper(random_string('alnum',32));

        // =====================================
        // Transaction 시작
        // =====================================
        $db->transBegin();

        try{

            $counselModel = new CounselModel();

            $counselModel->insert([

                'counsel_uid'      => $counselUid,

                'fc_member_uid'    => $fcMemberUid,

                'member_uid'       => $memberUid,

                'name'             => $myMember['name'],

                'email'            => $myMember['email'],

                'phone'            => $myMember['phone'],

                'reserve_datetime' => $reserveDate ?: null,

                'content'          => $content,

                'status'           => 'REQUEST'

            ]);

                        // =====================================
            // 내 증권 선택
            // hidden 값 : 1,2,3
            // =====================================
            $securityIds = trim($this->request->getPost('security_ids'));

            $counselFileModel = new \App\Models\CounselFileModel();
            $memberSecurityModel = new \App\Models\MemberSecurityModel();

            if (!empty($securityIds)) {

                $ids = array_filter(array_map('trim', explode(',', $securityIds)));

                foreach ($ids as $securityId) {

                    $security = $memberSecurityModel
                        ->where('security_id', $securityId)
                        ->where('member_uid', $memberUid)
                        ->first();

                    if (!$security) {
                        continue;
                    }

                    $counselFileModel->insert([

                        'counsel_uid'   => $counselUid,

                        'file_type'     => 'MY_SECURITY',

                        'security_id'   => $security['security_id'],

                        'original_name' => $security['original_name'],

                        'saved_name'    => $security['saved_name'],

                        'file_path'     => $security['file_path'],

                        'file_ext'      => $security['file_ext'],

                        'file_size'     => $security['file_size']

                    ]);

                }

            }

            // =====================================
            // 직접 업로드
            // =====================================
            helper('fileupload_helper');

            $files = $this->request->getFiles();

            if (isset($files['consult_file'])) {

                foreach ($files['consult_file'] as $file) {

                    if (!$file->isValid()) {
                        continue;
                    }

                    $savedName = upload_file($file, 'uploads/counsel');

                    if (!$savedName) {
                        continue;
                    }

                    $counselFileModel->insert([

                        'counsel_uid'   => $counselUid,

                        'file_type'     => 'UPLOAD',

                        'security_id'   => null,

                        'original_name' => $file->getClientName(),

                        'saved_name'    => $savedName,

                        'file_path'     => 'uploads/counsel/' . $savedName,

                        'file_ext'      => strtolower($file->getClientExtension()),

                        'file_size'     => $file->getSize()

                    ]);

                }

            }

            // =====================================
            // Commit
            // =====================================
            if ($db->transStatus() === false) {

                $db->transRollback();

                return $this->response->setJSON([
                    'result' => 'fail',
                    'message' => '저장 중 오류가 발생했습니다.'
                ]);
            }

            $db->transCommit();

            return $this->response->setJSON([
                'result' => 'ok',
                'message' => '상담 신청이 완료되었습니다.',
                'counsel_uid' => $counselUid
            ]);

        } catch (\Throwable $e) {

            $db->transRollback();

            log_message('error', $e->getMessage());

            return $this->response->setJSON([
                'result' => 'fail',
                'message' => '시스템 오류가 발생했습니다.'
            ]);

        }

    }

}
