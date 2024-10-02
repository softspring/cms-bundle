/**
 * This feature is under develop. It's still disabled by default.
 *
 * Feature expected behaviour:
 * - if translation default locale input is not empty, and translation other locale is empty, show fallback
 *   content on preview (not in input)
 * - when showing fallback content, input should show as "inherited" or "disabled"
 * - when focus on empty fallback input, it should show as writable
 * - when focus on empty fallback preview element, it should show as writable and hide fallback content
 */

import {cmsEditListener} from './event-listeners';
import {getPreviewElementsFromInput} from './contenteditable';
import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_content_edit_contenteditable_fallback', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    return; // TODO enable fallbacks when fully implemented

    // eslint-disable-next-line no-unreachable
    cmsEditListener('[data-edit-content-input]', 'input', onEditContentInputPropagateFallbacks);
    cmsEditListener('[data-edit-content-input][data-fallback-lang]', 'focusin', onFallbackInputFocus);
    cmsEditListener('[data-edit-content-input][data-fallback-lang]', 'focusout', onFallbackInputBlur);

    // on register process default inputs to set fallback values
    document.querySelectorAll('[data-edit-content-input]:not([data-fallback-lang])').forEach((inputElement) => {
        onEditContentInputPropagateFallbacks(inputElement);
    });
}

function setFallbackInputInherited(inputElement) {
    inputElement.classList.add('bg-light');
}

function unsetFallbackInputInherited(inputElement) {
    inputElement.classList.remove('bg-light');
}

function onFallbackInputFocus(inputElement) {
    unsetFallbackInputInherited(inputElement);
}

function onFallbackInputBlur(inputElement) {
    if (inputElement.value === '') {
        setFallbackInputInherited(inputElement);
    }
}

function onEditContentInputPropagateFallbacks(inputElement) {
    let fallbackInputs = getFallbackEmptyInputsElementsFromInput(inputElement);

    fallbackInputs.forEach((element) => {
        setFallbackInputInherited(element);
        getPreviewElementsFromInput(element).forEach((previewElement) => {
            previewElement.textContent = inputElement.value;
        });
    });
}

function getFallbackEmptyInputsElementsFromInput(inputElement) {
    let moduleForm = inputElement.closest('.cms-module-edit').querySelector('.cms-module-form');
    let currentLang = inputElement.dataset.inputLang;
    return [...moduleForm.querySelectorAll("[data-fallback-lang='" + currentLang + "']")].filter((element) => {
        return element.value === '';
    });
}