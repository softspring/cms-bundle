import {cmsEditListener} from './event-listeners';

(function () {
    if (!window.__sfs_cms_content_edit_preview_block_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_preview_block_registered = true;
})();

function _register() {
    cmsEditListener('[data-block-preview-input]', 'change', showBlockPreview);
}

/**
 * Shows a block preview
 *
 * The preview target element must have the "data-block-preview-target" attribute
 * The select option must have the "data-block-preview-input"
 * Both data attributes must have the same value (as identificator)
 */
function showBlockPreview (inputElement, module, preview, form, event) {
    let htmlTargetElements = preview.querySelectorAll("[data-block-preview-target='" + inputElement.dataset.blockPreviewInput + "']");
    let blockPreview = inputElement.options[inputElement.selectedIndex].dataset.blockPreview;
    [...htmlTargetElements].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = blockPreview === undefined ? '' : blockPreview);
};