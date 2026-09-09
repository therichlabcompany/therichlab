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
                'jpg','jpeg','png','webp','gif','bmp','heic','heif','tif','tiff',

                // document
            'pdf','doc','docx','ppt','pptx','xls','xlsx','hwp','hwpx','txt','rtf','odt','ods','csv',

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
        // 브라우저가 전달한 MIME과 PHP가 임시 업로드 파일에서 감지한 MIME을
        // 함께 확인한다. HWP는 브라우저별 client MIME이 달라질 수 있다.
        $clientMime = $file->getClientMimeType();
        $detectedMime = $file->getMimeType();

        $allowedMime = [
            // images
            'image/jpeg','image/png','image/webp','image/gif','image/bmp',
            'image/heic','image/heif','image/tiff',

            // pdf/doc/ppt/xls
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

            // HWP. Depending on the browser/server, HWP can be reported as
            // one of the vendor MIME types or as a generic binary file.
            'application/x-hwp',
            'application/haansoft-hwp',
            'application/vnd.hancom.hwp',
            'application/x-hwpx',
            'application/vnd.hancom.hwpx',
            'application/haansoft-hwpx',

            // Plain text documents may be reported differently by browsers.
            'text/plain',
            'text/csv',
            'application/rtf',
            'text/rtf',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',

            // video
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo'
        ];

        $genericMimeAllowedExtensions = [
            'hwp', 'hwpx', 'txt', 'rtf', 'odt', 'ods', 'csv',
            'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx',
            'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'heic', 'heif', 'tif', 'tiff',
        ];
        $mimeCandidates = array_values(array_unique(array_filter([$clientMime, $detectedMime])));
        $isKnownFileWithGenericMime = in_array($ext, $genericMimeAllowedExtensions, true)
            && in_array('application/octet-stream', $mimeCandidates, true);
        $isHwpContainerMime = in_array($ext, ['hwp', 'hwpx'], true)
            && in_array('application/zip', $mimeCandidates, true);

        $hasAllowedMime = (bool) array_intersect($mimeCandidates, $allowedMime);

        if (!$hasAllowedMime && !$isKnownFileWithGenericMime && !$isHwpContainerMime) {
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
