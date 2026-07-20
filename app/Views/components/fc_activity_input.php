<?php
$regionValue = $activity['region'] ?? '';
$insuranceValue = $activity['insurance_types'] ?? '';

// // 팝업 버튼에 표시될 텍스트
// $regionText = str_replace(
//     [
//         'seoul',
//         'gyeonggi',
//         'incheon_bucheon',
//         'gangwon',
//         'daejeon_chungcheong',
//         'gwangju_jeolla',
//         'daegu_gyeongbuk',
//         'busan_ulsan_gyeongnam',
//         'jeju'
//     ],
//     [
//         '서울',
//         '경기',
//         '인천/부천',
//         '강원',
//         '대전/충청',
//         '광주/전라',
//         '대구/경북',
//         '부산/울산/경남',
//         '제주'
//     ],
//     str_replace(',', ', ', $regionValue)
// );

// $insuranceText = str_replace(
//     ['all', 'life', 'nonlife', 'indemnity', 'dental', 'driver', 'fire', 'child'],
//     ['전체', '생명보험', '손해보험', '실손보험', '치아보험', '운전자보험', '화재보험', '어린이보험'],
//     str_replace(',', ', ', $insuranceValue)
// );

$regionText = "";
foreach (explode(',', $regionValue) as $r):
    if($regionText) $regionText.=",";
    $regionText .= fc_region_label(trim($r));
endforeach;  


$insuranceText = "";

foreach (explode(',', $insuranceValue) as $item): 
    if($insuranceText) $insuranceText.=",";
    $insuranceText .= fc_insurance_label(trim($item));
endforeach;  
                                             
                                             


?>
<p class="form-text">모든 항목은 선택 입력입니다. 작성 중인 내용은 중간 저장할 수 있습니다.</p>
<div class="form-field">
    <label class="form-label" for="fc-region-value">본인 활동 지역</label>
    <button type="button" class="directory-select" data-popup-target="#popup-fc-region" data-popup-sync="#fc-region-value">
        <span><?= esc($regionText ?: '지역을 선택해주세요.') ?></span>
    </button>
    <input id="fc-region-value" type="hidden" name="region" value="<?= esc($regionValue) ?>" />
</div>

<div class="form-field">
    <label class="form-label" for="fc-insurance-value">운영 가능 보험 항목</label>
    <button type="button" class="directory-select" data-popup-target="#popup-fc-insurance" data-popup-sync="#fc-insurance-value">
        <span><?= esc($insuranceText ?: '보험 항목을 선택해주세요.') ?></span>
    </button>
    <input id="fc-insurance-value" type="hidden" name="insurance_types" value="<?= esc($insuranceValue) ?>" />
</div>

<div class="form-field">
    <label class="form-label" for="fc-history">한 줄 히어로</label>
    <input
        class="form-input"
        id="fc-history"
        name="history"
        type="text"
        placeholder="(예시) 가족 맞춤 보험, 상해/질병에 대비 컨설팅"
        value="<?= esc($activity['hero_line'] ?? '') ?>" />
</div>

<div class="form-field">
    <label class="form-label" for="fc-intro">자기소개</label>
    <textarea
        class="form-textarea"
        id="fc-intro"
        name="intro"
        rows="4"
        placeholder="(예시) 10년 경력의 베테랑으로, 가족 맞춤형 설계를 믿고 맡길 수 있습니다."><?= esc($activity['intro'] ?? '') ?></textarea>
</div>

<div class="form-field">
    <label class="form-label" for="fc-career">경력사항</label>
    <textarea
        class="form-textarea"
        id="fc-career"
        name="career"
        rows="5"
        placeholder="(예시) 
• 손해보험 FC (2016 ~ 현재, 메인 활동)
• 우수 FC 선정 3회 (2020, 2022, 2023)
• 종합발표자대회 분기별 연속 대상
• 1,000건 이상 고객 맞춤 설계 경험"><?= esc($activity['career'] ?? '') ?></textarea>
</div>
<?php

$proofType = 'file';

if (!empty($activityItems)) {
    $proofType = $activityItems[0]['type'];
}

$fileItems = [];
$linkItems = [];
$textItems = [];

foreach ($activityItems as $item) {

    switch ($item['type']) {

        case 'file':
            $fileItems[] = $item;
            break;

        case 'link':
            $linkItems[] = $item;
            break;

        case 'text':
            $textItems[] = $item;
            break;
    }
}
?>
<div class="file-upload" id="fc-proof-block">
    <input type="hidden" id="proof-delete-items" name="delete_items">
    <fieldset class="proof-mode">
        <legend>이력 및 인증</legend>
        <p>
            인증 관련 파일을 업로드 해주세요. <br />
            생명보험협회의 보험설계사 등록증 또는 손해보험협회 보험모집종사자 등록증을 첨부하시기를 권장드립니다.
        </p>
        <label class="c-radio">
            <input type="radio"
                name="proof_register_mode"
                value="file"
                <?= $proofType == 'file' ? 'checked' : '' ?>>
            첨부파일 등록
        </label>

        <label class="c-radio">
            <input type="radio"
                name="proof_register_mode"
                value="link"
                <?= $proofType == 'link' ? 'checked' : '' ?>>
            링크주소 등록
        </label>

        <label class="c-radio">
            <input type="radio"
                name="proof_register_mode"
                value="text"
                <?= $proofType == 'text' ? 'checked' : '' ?>>
            기타정보 입력
        </label>
        <div class="proof-panels">
            <div class="proof-panel" data-proof-panel="file">
                <div class="proof-sets" data-proof-rows="file">

                    <?php foreach ($fileItems as $row): ?>

                        <div data-proof-row>

                            <input
                                type="hidden"
                                name="proof_item_id"
                                value="<?= $row['item_id'] ?>">

                            <input
                                name="proof_name"
                                type="text"
                                value="<?= esc($row['title']) ?>">

                            <div>

                                <input
                                    type="text"
                                    readonly
                                    value="<?= esc($row['file_path']) ?>">

                                <input
                                    class="visually-hidden"
                                    name="proof_file"
                                    type="file">

                                <button
                                    type="button"
                                    data-file-trigger>
                                    파일찾기
                                </button>

                                <button
                                    type="button"
                                    data-row-remove>
                                    삭제
                                </button>

                            </div>

                            <?php if ($row['file_path']) : ?>

                                <div class="proof-preview">

                                    <img
                                        src="<?= esc(base_url('uploads/activity/' . rawurlencode(basename((string) $row['file_path'])))) ?>"
                                        style="max-width:180px">

                                </div>

                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>
            </div>
            <div class="proof-panel" data-proof-panel="link" hidden>
                <div class="proof-sets" data-proof-rows="link">

                <?php foreach($linkItems as $row): ?>

                <div data-proof-row>

                <input type="hidden"
                    name="proof_item_id"
                    value="<?=$row['item_id']?>">

                <input
                    name="proof_link_name"
                    value="<?=esc($row['title'])?>">

                <input
                    name="proof_link_url"
                    value="<?=esc($row['url'])?>">

                <button
                    type="button"
                    data-row-remove>
                    삭제
                </button>

                </div>

                <?php endforeach;?>

                </div>
            </div>
            <div class="proof-panel" data-proof-panel="text" hidden>
                <div class="proof-sets" data-proof-rows="text">

                <?php foreach($textItems as $row):?>

                <div data-proof-row>

                <input
                    type="hidden"
                    name="proof_item_id"
                    value="<?=$row['item_id']?>">

                <input
                    name="proof_other_name"
                    value="<?=esc($row['title'])?>">

                <input
                    name="proof_other_text"
                    value="<?=esc($row['content'])?>">

                <button
                    type="button"
                    data-row-remove>
                    삭제
                </button>

                </div>

                <?php endforeach;?>

                </div>
            </div>
        </div>
    </fieldset>
    <button type="button" data-proof-add aria-label="이력 및 인증 항목 추가"></button>
</div>

<template data-proof-template="file">
    <div data-proof-row>
        <input name="proof_name" type="text" placeholder="이력명 기재 / 예 : MDRT" />
        <div>
            <input type="text" readonly placeholder="첨부 파일을 선택해 주세요" />
            <input class="visually-hidden" name="proof_file" type="file" tabindex="-1" />
            <button type="button" class="fc-proof-file-trigger" data-file-trigger>파일찾기</button>
            <button type="button" data-row-remove>삭제</button>
        </div>
    </div>
</template>
<template data-proof-template="link">
    <div data-proof-row>
        <input name="proof_link_name" type="text" placeholder="이력명 기재 / 예 : MDRT" />
        <input
            name="proof_link_url"
            type="text"
            inputmode="url"
            autocomplete="url"
            placeholder="링크 주소 입력 / 예 : https://mdrtkorea.org/membership/searchMemberView?mem_id=4862" />
    </div>
</template>
<template data-proof-template="text">
    <div data-proof-row>
        <input name="proof_other_name" type="text" placeholder="이력명 기재 / 예 : MDRT" />
        <input name="proof_other_text" type="text" placeholder="기타 정보 기재" />
    </div>
</template>

<style>
    .proof-preview{
        display:none !important;
    }
</style>
