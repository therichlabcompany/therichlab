<?php
use CodeIgniter\HTTP\ResponseInterface;

public function withdrawAjax(): ResponseInterface
{
    $session = session();

    // =========================
    // 1. 로그인 체크
    // =========================
    if (!$session->get('logged_in')) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => '로그인이 필요합니다.'
        ]);
    }

    $memberUid = $session->get('member_uid');

    // =========================
    // 2. POST 체크 (동의 체크)
    // =========================
    $agree = $this->request->getPost('agree');

    if ($agree !== 'Y') {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => '탈퇴 동의가 필요합니다.'
        ]);
    }

    // =========================
    // 3. 회원 조회
    // =========================
    $memberModel = new \App\Models\MemberModel();

    $member = $memberModel
        ->where('member_uid', $memberUid)
        ->first();

    if (!$member) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => '회원 정보를 찾을 수 없습니다.'
        ]);
    }

    // =========================
    // 4. 탈퇴 처리 (soft delete)
    // =========================
    $update = $memberModel->where('member_uid', $memberUid)->set([
        'status'     => 'LEAVE',
        'deleted_at' => date('Y-m-d H:i:s')
    ])->update();

    if (!$update) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => '탈퇴 처리 실패'
        ]);
    }

    // =========================
    // 5. 세션 제거 (로그아웃)
    // =========================
    $session->destroy();

    return $this->response->setJSON([
        'status' => 'success',
        'message' => '회원 탈퇴가 완료되었습니다.'
    ]);
}