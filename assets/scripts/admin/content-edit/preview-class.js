import {cmsEditListener} from './event-listeners';

(function () {
    if (!window.__sfs_cms_content_edit_preview_class_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_preview_class_registered = true;
})();

function _register() {
    cmsEditListener('[data-edit-class-input]', 'input', onEditClass);
}

/**
 * Adds class field to target element
 *
 * The preview target element must have the "data-edit-class-target" attribute
 * The input field must have the "data-edit-class-input"
 * Both data attributes must have the same value (as identificator)
 *
 * If the preview target element has some default classes, those classes should be set on a "data-edit-class-default" attribute to not to be lost during preview
 */
function onEditClass(inputElement, module, preview, form, event) {
    let htmlTargetElements = preview.querySelectorAll("[data-edit-class-target='" + inputElement.dataset.editClassInput + "']");
    let htmlInputsElements = form.querySelectorAll("[data-edit-class-input='" + inputElement.dataset.editClassInput + "']");

    let classesElement = '';

    // Combine all option to class attribute
    [...htmlInputsElements].forEach(function (htmlInputsElement) {
        classesElement = htmlInputsElement.value + ' ' + classesElement;
    });

    [...htmlTargetElements].forEach(function (htmlTargetElement) {
        htmlTargetElement.className = htmlTargetElement.dataset.editClassDefault + ' ' + classesElement;
    });
};