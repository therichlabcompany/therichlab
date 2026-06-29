<?php

if (!function_exists('upload_file')) {

    function upload_file($file, $path = 'uploads', $allowed = [])
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // =========================
        // 확장자 소문자 통일
        // =========================
        $ext = strtolower($file->getClientExtension());

        // =========================
        // 기본 허용 확장자 (전체 지원)
        // =========================
        if (empty($allowed)) {
            $allowed = [
                // image
                'jpg','jpeg','png','webp','gif',

                // document
                'pdf','doc','docx','ppt','pptx','xls','xlsx','hwp','txt',

                // video
                'mp4','mov','avi','wmv','mkv',

                // archive
                'zip','rar'
            ];
        }

        // =========================
        // 위험 파일 차단 (중요)
        // =========================
        $blocked = ['php','phtml','html','js','sh','exe','bat'];

        if (in_array($ext, $blocked)) {
            throw new \Exception('보안상 허용되지 않는 파일입니다.');
        }

        // =========================
        // 허용 체크
        // =========================
        if (!in_array($ext, $allowed)) {
            throw new \Exception('허용되지 않은 파일 형식입니다.');
        }

        // =========================
        // MIME 체크 (추가 보안)
        // =========================
        $mime = $file->getClientMimeType();

        $allowedMime = [
            // images
            'image/jpeg','image/png','image/webp','image/gif',

            // pdf/doc/ppt/xls
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            // video
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo'
        ];

        if (!in_array($mime, $allowedMime)) {
            throw new \Exception('파일 타입이 올바르지 않습니다.');
        }

        // =========================
        // 저장 경로
        // =========================
        $targetPath = WRITEPATH . rtrim($path, '/');

        if (!is_dir($targetPath)) {
            if (!mkdir($targetPath, 0777, true) && !is_dir($targetPath)) {
                throw new \Exception('디렉토리 생성 실패');
            }
        }

        // =========================
        // 파일명 생성
        // =========================
        $newName = $file->getRandomName();

        // =========================
        // 이동
        // =========================
        $file->move($targetPath, $newName);

        //return $path . '/' . $newName;
        return $newName;
    }
}