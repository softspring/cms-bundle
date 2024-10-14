import {cmsEditListener} from './event-listeners';
import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_preview_block', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    cmsEditListener('[data-block-preview-input]', 'change', showBlockPreview);
}

/**
 * Shows a block preview
 *
 * The preview target element must have the "data-block-preview-target" attribute
 * The select option must have the "data-block-preview-input"
 * Both data attributes must have the same value (as identificator)
 */
function showBlockPreview(inputElement, module, preview/*, form, event*/) {
    let htmlTargetElements = preview.querySelectorAll("[data-block-preview-target='" + inputElement.dataset.blockPreviewInput + "']");
    let blockPreview = inputElement.options[inputElement.selectedIndex].dataset.blockPreview;
    [...htmlTargetElements].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = blockPreview === undefined ? '' : blockPreview);
};