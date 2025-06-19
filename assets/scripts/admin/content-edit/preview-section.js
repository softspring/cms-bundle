import {cmsEditListener} from './event-listeners';
import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_content_edit_preview_section', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    cmsEditListener('[data-section-preview-input]', 'change', showSectionPreview);
}

/**
 * Shows a section preview
 *
 * The preview target element must have the "data-section-preview-target" attribute
 * The select option must have the "data-section-preview-input"
 * Both data attributes must have the same value (as identificator)
 */
function showSectionPreview(inputElement, module, preview/*, form, event*/) {
    let htmlTargetElements = preview.querySelectorAll("[data-section-preview-target='" + inputElement.dataset.sectionPreviewInput + "']");
    let sectionPreview = inputElement.options[inputElement.selectedIndex].dataset.sectionPreview;
    [...htmlTargetElements].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = sectionPreview === undefined ? '' : sectionPreview);
}