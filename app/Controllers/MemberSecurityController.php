<?php

namespace App\Controllers;

use App\Models\MemberSecurityModel;

class MemberSecurityController extends BaseController
{
    /**
     * 증권파일 업로드
     */
    public function upload()
    {
        helper('fileupload_helper');

        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'result'  => 'fail',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $files = $this->request->getFiles();

        if (!isset($files['files'])) {
            return $this->response->setJSON([
                'result'  => 'fail',
                'message' => '업로드할 파일이 없습니다.'
            ]);
        }

        $model = new MemberSecurityModel();

        foreach ($files['files'] as $file) {

            if (!$file->isValid()) {
                continue;
            }

            // 업로드
            $savedName = upload_file($file, 'uploads/security');

            if (!$savedName) {
                continue;
            }

            $originalName = $file->getClientName();
            $extension    = strtolower($file->getClientExtension());
            $size         = $file->getSize();

            $model->insert([
                'member_uid'    => $memberUid,
                'original_name' => $originalName,
                'saved_name'    => $savedName,
                'file_path'     => 'uploads/security/' . $savedName,
                'file_ext'      => $extension,
                'file_size'     => $size,
                'sort_order'    => 0
            ]);
        }

        return $this->response->setJSON([
            'result' => 'ok'
        ]);
    }

    /**
     * 증권파일 삭제
     */
    public function delete()
    {
        helper('filesystem');

        $session = session();
        $memberUid = $session->get('member_uid');

        if (!$memberUid) {
            return $this->response->setJSON([
                'result'  => 'fail',
                'message' => '로그인이 필요합니다.'
            ]);
        }

        $securityId = $this->request->getJSON()->security_id ?? 0;

        if (!$securityId) {
            return $this->response->setJSON([
                'result'  => 'fail',
                'message' => '잘못된 요청입니다.'
            ]);
        }

        $model = new MemberSecurityModel();

        $row = $model
            ->where('security_id', $securityId)
            ->where('member_uid', $memberUid)
            ->first();

        if (!$row) {
            return $this->response->setJSON([
                'result'  => 'fail',
                'message' => '파일을 찾을 수 없습니다.'
            ]);
        }

        // 실제 파일 삭제
        $fullPath = FCPATH . $row['file_path'];

        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        // Soft Delete
        $model->delete($securityId);

        return $this->response->setJSON([
            'result' => 'ok'
        ]);
    }

    public function download($securityId)
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return redirect()->to('/login');
        }

        $memberUid = $session->get('member_uid');

        $model = new \App\Models\MemberSecurityModel();

        $file = $model
            ->where('security_id', $securityId)
            ->where('member_uid', $memberUid)
            ->first();

        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $path = FCPATH . $file['file_path'];

        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response->download(
            $path,
            null
        )->setFileName($file['original_name']);
    }
}