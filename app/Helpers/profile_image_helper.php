<?php

if (!function_exists('profile_image_url')) {
    /**
     * 저장된 프로필 이미지가 실제로 존재할 때만 공개 URL을 반환한다.
     */
    function profile_image_url(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);
        if (str_contains($path, '..')) {
            return '';
        }

        $relativePath = ltrim($path, '/');
        if (!str_starts_with($relativePath, 'uploads/')) {
            $relativePath = 'uploads/profile/' . $relativePath;
        }

        if (!is_file(FCPATH . $relativePath)) {
            return '';
        }

        return base_url($relativePath);
    }
}
