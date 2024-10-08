import {cmsEditListener} from './event-listeners';
import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_preview_id', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    cmsEditListener('[data-edit-id-input]', 'input', onEditId);
}

/**
 * Adds id field to target element
 *
 * The preview target element must have the "data-edit-id-target" attribute
 * The input field must have the "data-edit-id-input"
 * Both data attributes must have the same value (as identificator)
 */
function onEditId(inputElement, module, preview/*, form, event*/) {
    let htmlTargetElements = preview.querySelectorAll("[data-edit-id-target='" + inputElement.dataset.editIdInput + "']");
    if (htmlTargetElements.length) {
        htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.id = inputElement.value);
    }
}
