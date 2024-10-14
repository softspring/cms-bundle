import {cmsEditListener} from './event-listeners';
import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_preview_background_color', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    cmsEditListener('[data-edit-bgcolor-input]', 'input', onEditBackgroundColor);
}

/**
 * Sets a background color field to target element
 *
 * The preview target element must have the "data-edit-bgcolor-target" attribute
 * The input field must have the "data-edit-bgcolor-input"
 * Both data attributes must have the same value (as identificator)
 */
function onEditBackgroundColor(inputElement, module, preview/*, form, event*/) {
    let htmlTargetElements = preview.querySelectorAll("[data-edit-bgcolor-target='" + inputElement.dataset.editBgcolorInput + "']");
    if (htmlTargetElements.length) {
        htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.style.backgroundColor = inputElement.value);
    }
}