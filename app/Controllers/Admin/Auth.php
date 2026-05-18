<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminUserModel;

class Auth extends BaseController
{
    /**
     * 관리자 로그인
     */
    public function login()
    {

        
        // 이미 로그인 되어 있으면 대시보드로 이동
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin');
        }

        // POST 요청일 경우 로그인 처리
        if ($this->request->is('post')) {

            // 입력값 받기
            $username = trim((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');

            // 기본 검증
            if ($username === '' || $password === '') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '아이디와 비밀번호를 입력해주세요.');
            }

            // 관리자 정보 조회
            $adminModel = new AdminUserModel();

            $user = $adminModel
                ->where('username', $username)
                ->where('status', 'Y')
                ->first();
            // print_r($user);
            // exit;
            // 계정이 없거나 비밀번호 불일치
            if (! $user || ! password_verify($password, $user['password_hash'])) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '아이디 또는 비밀번호가 올바르지 않습니다.');
            }

            // 마지막 로그인 시간 업데이트
            $adminModel->update($user['id'], [
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);

            // 세션 저장
            session()->set([
                'admin_logged_in' => true,
                'admin_id'        => $user['id'],
                'admin_username'  => $user['username'],
                'admin_name'      => $user['name'],
                'admin_role'      => $user['role'],
            ]);

            // 관리자 메인으로 이동
            return redirect()->to('/admin');
        }

        // GET 요청 시 로그인 화면 출력
        return view('admin/auth/login', [
            'title' => '관리자 로그인',
        ]);
    }

    /**
     * 관리자 로그아웃
     */
    public function logout()
    {
        // 관리자 세션 제거
        session()->remove([
            'admin_logged_in',
            'admin_id',
            'admin_username',
            'admin_name',
            'admin_role',
        ]);

        // 전체 세션 초기화가 필요하면 아래 사용
        // session()->destroy();

        // 로그인 페이지로 이동
        return redirect()->to('/admin/login');
    }
}