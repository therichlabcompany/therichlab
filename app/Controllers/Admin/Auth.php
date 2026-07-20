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
            $username = trim((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');

            if ($username === '' || $password === '') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '아이디와 비밀번호를 입력해주세요.');
            }

            $adminModel = new AdminUserModel();
            $user = $adminModel
                ->where('username', $username)
                ->first();

            if (! $user || strtoupper(trim((string) ($user['status'] ?? ''))) !== 'Y') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', '아이디 또는 비밀번호가 올바르지 않습니다.');
            }

            $lockedUntil = (string) ($user['login_locked_until'] ?? '');
            if ($lockedUntil !== '' && strtotime($lockedUntil) > time()) {
                $remainingSeconds = strtotime($lockedUntil) - time();
                $remainingMinutes = max(1, (int) ceil($remainingSeconds / 60));

                return redirect()->back()
                    ->withInput()
                    ->with('error', '로그인이 5회 이상 실패하여 차단되었습니다. ' . $remainingMinutes . '분 후 다시 시도해주세요.');
            }

            if (! password_verify($password, $user['password_hash'])) {
                $failedCount = ((int) ($user['failed_login_count'] ?? 0)) + 1;
                $updateData = [
                    'failed_login_count' => $failedCount,
                    'last_failed_login_at' => date('Y-m-d H:i:s'),
                ];

                if ($failedCount >= 5) {
                    $updateData['login_locked_until'] = date('Y-m-d H:i:s', time() + (30 * 60));
                }

                $adminModel->update($user['id'], $updateData);

                if ($failedCount >= 5) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', '비밀번호를 5회 이상 잘못 입력하여 30분간 로그인할 수 없습니다.');
                }

                return redirect()->back()
                    ->withInput()
                    ->with('error', '아이디 또는 비밀번호가 올바르지 않습니다.');
            }

            $adminModel->update($user['id'], [
                'last_login_at' => date('Y-m-d H:i:s'),
                'failed_login_count' => 0,
                'login_locked_until' => null,
                'last_failed_login_at' => null,
            ]);

            session()->set([
                'admin_logged_in' => true,
                'admin_id'        => $user['id'],
                'admin_username'  => $user['username'],
                'admin_name'      => $user['name'],
                'admin_role'      => $user['role'],
            ]);

            return redirect()->to('/admin');
        }

        return view('admin/auth/login', [
            'title' => '관리자 로그인',
        ]);
    }

    /**
     * 관리자 로그아웃
     */
    public function logout()
    {
        session()->remove([
            'admin_logged_in',
            'admin_id',
            'admin_username',
            'admin_name',
            'admin_role',
        ]);

        // session()->destroy();

        return redirect()->to('/admin/login');
    }
}
