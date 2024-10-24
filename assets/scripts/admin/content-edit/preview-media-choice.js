import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_content_edit_preview_media_choice', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.hasAttribute('data-media-preview-input')) return;
        onMediaSelectChange(event.target);
    });
}

/**
 * Shows a media preview
 *
 * The preview target element must have the "data-media-preview-target" attribute
 * The select option must have the "data-media-preview-input"
 * Both data attributes must have the same value (as identificator)
 */
function onMediaSelectChange(selectInput) {
    let moduleForm = selectInput.closest('.cms-module-edit').querySelector('.module-preview');

    let htmlTargetElements = moduleForm.querySelectorAll("[data-media-preview-target='" + selectInput.dataset.mediaPreviewInput + "']");
    if (htmlTargetElements.length) {
        if (selectInput.options[selectInput.selectedIndex].dataset.mediaPreviewPicture) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = selectInput.options[selectInput.selectedIndex].dataset.mediaPreviewPicture);
        } else if (selectInput.options[selectInput.selectedIndex].dataset.mediaPreviewImage) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = selectInput.options[selectInput.selectedIndex].dataset.mediaPreviewImage);
        } else if (selectInput.options[selectInput.selectedIndex].dataset.mediaPreviewVideo) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = selectInput.options[selectInput.selectedIndex].dataset.mediaPreviewVideo);
        } else {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = '');
        }
    }
}
