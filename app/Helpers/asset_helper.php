<?php

if (!function_exists('asset_url')) {
    /**
     * 수정 시각을 쿼리 문자열로 붙여 정적 자산의 브라우저 캐시를 무효화한다.
     */
    function asset_url(string $path): string
    {
        $path = ltrim($path, '/');
        $filePath = FCPATH . $path;
        $version = is_file($filePath) ? (string) filemtime($filePath) : '1';

        return base_url($path) . '?v=' . rawurlencode($version);
    }
}
