<?php

namespace App\Controllers;

use App\Models\FcBookmarkModel;
use CodeIgniter\HTTP\ResponseInterface;

class FcBookmarkController extends BaseController
{
    protected $bookmarkModel;

    public function __construct()
    {
        $this->bookmarkModel = new FcBookmarkModel();
    }

    /**
     * 관심 FC 토글
     */
    public function toggle(): ResponseInterface
    {
        $session = session();

        $memberUid = $session->get('member_uid');
        $fcMemberUid = $this->request->getPost('fc_member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '로그인이 필요합니다.'
            ]);
        }

        if (!$fcMemberUid) {
            return $this->response->setJSON([
                'result' => 'error',
                'msg' => '잘못된 요청입니다.'
            ]);
        }

        // 기존 존재 여부 확인
        $exists = $this->bookmarkModel
            ->where('member_uid', $memberUid)
            ->where('fc_member_uid', $fcMemberUid)
            ->first();

        if ($exists) {

            // 삭제
            $this->bookmarkModel
                ->where('member_uid', $memberUid)
                ->where('fc_member_uid', $fcMemberUid)
                ->delete();

            return $this->response->setJSON([
                'result' => 'removed',
                'msg' => '관심 FC에서 제거되었습니다.'
            ]);
        }

        // 추가
        $this->bookmarkModel->insert([
            'member_uid'     => $memberUid,
            'fc_member_uid'  => $fcMemberUid
        ]);

        return $this->response->setJSON([
            'result' => 'added',
            'msg' => '관심 FC에 추가되었습니다.'
        ]);
    }


    /**
     * 상태 체크
     */
    public function check()
    {
        $session = session();

        $memberUid = $session->get('member_uid');
        $fcMemberUid = $this->request->getGet('fc_member_uid');

        if (!$memberUid || !$fcMemberUid) {
            return $this->response->setJSON([
                'bookmarked' => false
            ]);
        }

        $exists = $this->bookmarkModel
            ->where('member_uid', $memberUid)
            ->where('fc_member_uid', $fcMemberUid)
            ->first();

        return $this->response->setJSON([
            'bookmarked' => $exists ? true : false
        ]);
    }
}