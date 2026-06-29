<?php

function fc_region_label($value)
{
    $map = [
        '' => '전체',
        'all' => '전체',

        'seoul' => '서울',
        'gyeonggi' => '경기',

        'incheon_bucheon' => '인천/부천',
        'seoul_incheon_gyeonggi' => '수도권',

        'busan_ulsan_gyeongnam' => '부산/울산/경남',
        'daegu_gyeongbuk' => '대구/경북',
        'daejeon_sejong_chungnam' => '대전/세종/충남',
        'cheongju_chungbuk' => '충북',

        'gwangju_jeonnam' => '광주/전남',
        'jeonju_jeonbuk' => '전북',

        'chuncheon_gangwon' => '강원',
        'jeju' => '제주',
    ];

    return $map[$value] ?? $value;
}