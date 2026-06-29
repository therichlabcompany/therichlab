<?php

function fc_insurance_label($value)
{
    $map = [
        'all' => '전체',

        'whole_life' => '종신보험',
        'cancer' => '암보험',
        'brain_cardio' => '뇌심장보험',
        'indemnity' => '실비보험',

        'child' => '자녀/태아보험',
        'dementia' => '치매/간병보험',
        'dental' => '치아보험',

        'pension' => '연금/변액보험',
        'business' => '사업자보험',

        'driver' => '운전자보험',
        'car' => '자동차보험',
        'fire' => '화재보험',
    ];

    return $map[$value] ?? $value;
}