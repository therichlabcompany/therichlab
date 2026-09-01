<?php
$story = $story ?? [];
$storyImages = $storyImages ?? [];

$videoPath = !empty($story['story_video'])
    ? "/uploads/story/video/" . $story['story_video']
    : '';

$imagePath = !empty($story['story_image'])
    ? "/uploads/story/main/" . $story['story_image']
    : '';
?>

<div class="file-upload form-field">
    <label class="form-label" for="fc-story-video-display">활동 스토리 영상</label>
    <div>
        <input class="form-input" id="fc-story-video-display" type="text" readonly placeholder="첨부 파일을 선택해 주세요" value="<?= !empty($story['story_video']) ? esc($story['story_video']) : '' ?>" />
        <input id="fc-story-video-file" class="visually-hidden" name="story_video" type="file" accept="video/*" tabindex="-1" />
        <label class="file-upload-file-trigger" for="fc-story-video-file">업로드</label>
    </div>
</div>

<div class="file-upload form-field">
    <label class="form-label" for="fc-story-image-display">활동 스토리영상 대표 이미지</label>
    <div>
        <input class="form-input" id="fc-story-image-display" type="text" readonly placeholder="첨부 파일을 선택해 주세요" value="<?= !empty($story['story_image']) ? esc($story['story_image']) : '' ?>" />
        <input id="fc-story-image-file" class="visually-hidden" name="story_image" type="file" accept="image/*" tabindex="-1" />
        <label class="file-upload-file-trigger" for="fc-story-image-file">파일첨부</label>
    </div>
</div>

<hr />

<div class="story-guide">
    <p>
        <strong>활동 스토리 이미지</strong>
        이미지를 등록할 수 있습니다. (최대 20개 등록)
    </p>

    <ul class="dash-list">
        <li>첨부 가능한 이미지 파일형식은 .jpg .png .gif 입니다.</li>
        <li>무단/부정한 이윤으로 제3자의 권리를 침해하는 이미지는 등록할 수 없습니다.</li>
        <li>활동 스토리 이미지는 FC 상세 페이지 메인에 등록하신 순서대로 노출됩니다.</li>
        <li>첨부하신 이미지에 대한 <em class="story-guide-emphasis">법적 책임은 첨부하신 FC회원님</em>에게 있습니다.</li>
    </ul>
</div>

<div class="fc-story-thumbs" id="fc-story-thumbs">

    <input
        id="fc-story-thumb-file"
        class="visually-hidden"
        name="story_images"
        type="file"
        multiple
        accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" />

    <div
        class="fc-story-thumb-main"
        <?= !empty($storyImages) ? 'style="display:none;"' : '' ?>>

        <img src="../assets/images/ic-story-photo.svg" alt="">

    </div>

    <?php foreach ($storyImages as $img): ?>

        <div
            class="thumb-preview"
            data-id="<?= $img['id'] ?>"
            data-existing="1">

            <img
                src="/uploads/story/images/<?= esc($img['image_path']) ?>">

            <button
                type="button"
                class="thumb-remove">
                ×
            </button>

            <!-- 수정시 유지용 -->
            <input
                type="hidden"
                name="keep_images[]"
                value="<?= $img['id'] ?>">

        </div>

    <?php endforeach; ?>

    <label
        for="fc-story-thumb-file"
        class="fc-story-thumb-add"
        role="button"
        tabindex="0"
        aria-label="이미지 추가">
        +
    </label>

</div>

<style>
    .fc-story-thumbs {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .thumb-preview {
        width: 120px;
        height: 120px;
        position: relative;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .thumb-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .thumb-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 24px;
        height: 24px;
        border: none;
        border-radius: 50%;
        background: rgba(0, 0, 0, .6);
        color: #fff;
        cursor: pointer;
    }
</style>
