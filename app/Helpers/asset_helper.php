<?php

if (!function_exists('asset_url')) {
    /**
     * 파일 내용 기반 버전을 쿼리 문자열로 붙여 정적 자산의 브라우저 캐시를 무효화한다.
     */
    function asset_url(string $path): string
    {
        $path = ltrim($path, '/');
        $filePath = FCPATH . $path;
        $version = '1';
        if (is_file($filePath)) {
            $modifiedAt = (string) filemtime($filePath);
            $contentHash = hash_file('sha256', $filePath);
            $version = $modifiedAt . '-' . substr((string) $contentHash, 0, 12);
        }

        return base_url($path) . '?v=' . rawurlencode($version);
    }
}
