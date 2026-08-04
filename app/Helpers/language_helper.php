<?php

if (!function_exists('fc_language_options')) {
    function fc_language_options(): array
    {
        return [
            ['value' => 'sign', 'label' => '수어'],
            ['value' => 'vi', 'label' => '베트남'],
            ['value' => 'zh', 'label' => '중국'],
            ['value' => 'en', 'label' => '영어'],
            ['value' => 'th', 'label' => '태국'],
            ['value' => 'ja', 'label' => '일본'],
            ['value' => 'fil', 'label' => '필리핀'],
            ['value' => 'km', 'label' => '캄보디아'],
        ];
    }
}

if (!function_exists('fc_language_normalize')) {
    function fc_language_normalize(?string $languages): string
    {
        $aliases = [
            'sign' => 'sign', '수어' => 'sign',
            'vi' => 'vi', 'vn' => 'vi', '베트남' => 'vi', '베트남어' => 'vi',
            'zh' => 'zh', 'cn' => 'zh', '중국' => 'zh', '중국어' => 'zh',
            'en' => 'en', '영어' => 'en',
            'th' => 'th', '태국' => 'th', '태국어' => 'th',
            'ja' => 'ja', 'jp' => 'ja', '일본' => 'ja', '일본어' => 'ja',
            'fil' => 'fil', 'ph' => 'fil', '필리핀' => 'fil', '필리핀어' => 'fil',
            'km' => 'km', 'kh' => 'km', '캄보디아' => 'km', '캄보디아어' => 'km',
        ];
        $normalized = [];
        foreach (explode(',', (string) $languages) as $language) {
            $language = trim($language);
            $key = strtolower($language);
            $code = $aliases[$key] ?? $aliases[$language] ?? null;
            if ($code !== null && !in_array($code, $normalized, true)) {
                $normalized[] = $code;
            }
        }

        return implode(',', $normalized);
    }
}

if (!function_exists('fc_language_label')) {
    function fc_language_label(string $code): string
    {
        foreach (fc_language_options() as $option) {
            if ($option['value'] === $code) {
                return $option['label'];
            }
        }
        return $code;
    }
}

if (!function_exists('fc_language_labels')) {
    function fc_language_labels(?string $languages): string
    {
        $normalized = fc_language_normalize($languages);
        return $normalized === '' ? '' : implode(', ', array_map('fc_language_label', explode(',', $normalized)));
    }
}

if (!function_exists('fc_language_search_codes')) {
    function fc_language_search_codes(string $keyword): array
    {
        $normalized = fc_language_normalize($keyword);
        return $normalized === '' ? [] : explode(',', $normalized);
    }
}
