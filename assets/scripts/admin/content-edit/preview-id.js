import {cmsEditListener} from './event-listeners';

(function () {
    if (!window.__sfs_cms_content_edit_preview_id_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_preview_id_registered = true;
})();

function _register() {
    cmsEditListener('[data-edit-id-input]', 'input', onEditId);
}

/**
 * Adds id field to target element
 *
 * The preview target element must have the "data-edit-id-target" attribute
 * The input field must have the "data-edit-id-input"
 * Both data attributes must have the same value (as identificator)
 */
function onEditId(inputElement, module, preview, form, event) {
    let htmlTargetElements = preview.querySelectorAll("[data-edit-id-target='" + inputElement.dataset.editIdInput + "']");
    if (htmlTargetElements.length) {
        htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.id = inputElement.value);
    }
}
