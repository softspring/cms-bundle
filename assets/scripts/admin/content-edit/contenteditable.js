/**
 * Content edit contenteditable behaviour
 *
 * Configuration:
 *
 * - data-edit-content-input: the input to update when contenteditable changes
 * - data-edit-content-target: the target to update when input changes
 * - data-edit-content-hide-if-empty: hide the element if empty
 *
 * Important: data-edit-content-input and data-edit-content-target attributes must share the same value to be linked.
 *
 * Extending events:
 * - sfs_cms.content_edit.content_editable.input.change: dispatched when the input changes
 * - sfs_cms.content_edit.content_editable.target.change: dispatched when the target changes
 */

import {getSelectedLanguage} from './filter-preview';

let cssCodeNotValidatedWarningShown;

export {
    contentEditableUpdateElementsFromInput,
    contentEditableUpdateInputsFromElement
};

/**
 * Updates the input/s value from html editable element, also hides the element if empty
 * @param {HTMLElement} previewElement
 */
function contentEditableUpdateInputsFromElement(previewElement) {
    let moduleForm = previewElement.closest('.cms-module-edit').querySelector('.cms-module-form');

    let inputElements = moduleForm.querySelectorAll("[data-edit-content-input='" + previewElement.dataset.editContentTarget + "']");
    if (inputElements.length) {
        inputElements.forEach(function (inputElement) {
            if (previewElement.dataset.editContentEscape) {
                inputElement.value = previewElement.innerText;
            } else {
                inputElement.value = previewElement.innerHTML;
            }
            inputElement.setAttribute('value', previewElement.innerHTML);//Fixed empty value when element is new and is moved
        });
    }

    if (previewElement.dataset.editContentHideIfEmpty) {
        if (previewElement.innerHTML === '') {
            previewElement.style.setProperty('display', 'none');
        } else if (previewElement.matches('[data-lang=' + getSelectedLanguage() + ']')) {
            previewElement.style.setProperty('display', '');
        }
    }
}

/**
 * Updates the preview/s from input element
 * @param {HTMLElement} inputElement
 */
function contentEditableUpdateElementsFromInput(inputElement) {
    let modulePreview = inputElement.closest('.cms-module-edit').querySelector('.module-preview');

    let previewElements = modulePreview.querySelectorAll("[data-edit-content-target='" + inputElement.dataset.editContentInput + "']");
    if (previewElements.length) {
        previewElements.forEach(function (previewElement) {
            let value = inputElement.value;

            if (previewElement.dataset.editContentValidate) {
                switch (previewElement.dataset.editContentValidate) {
                    case 'html':
                        let valueToParse = '<body>' + value + '</body>';
                        valueToParse = valueToParse.replace(new RegExp('\n', 'g'), '');
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(valueToParse, "application/xml");
                        let errorNode = doc.querySelector('parsererror');
                        if (errorNode) {
                            value = errorNode.innerText;
                            previewElement.classList.add('text-error');
                            previewElement.classList.add('border');
                            previewElement.classList.add('border-danger');
                        } else {
                            previewElement.classList.remove('text-error');
                            previewElement.classList.remove('border');
                            previewElement.classList.remove('border-danger');
                        }
                        break;

                    case 'css':
                        if (!cssCodeNotValidatedWarningShown) {
                            console.warn('Sorry, css code is not yet validated');
                            cssCodeNotValidatedWarningShown = true;
                        }
                        break;
                }
            }

            if (previewElement.dataset.editContentEscape) {
                previewElement.innerText = value;
            } else {
                value = value.replace(new RegExp('href', 'g'), 'href-invalidate');
                previewElement.innerHTML = value;
            }

            if (previewElement.dataset.editContentHideIfEmpty) {
                if (previewElement.innerHTML === '') {
                    previewElement.style.setProperty('display', 'none');
                } else if (previewElement.matches('[data-lang=' + getSelectedLanguage() + ']')) {
                    previewElement.style.setProperty('display', '');
                }
            }
        });
    }
}

/**
 * Init content editable behaviour
 * @private
 */
function _init() {
    cssCodeNotValidatedWarningShown = false;

    // dispatch custom events on input event over data-edit-content-input and data-edit-content-target elements
    document.addEventListener('input', function (event) {
        if (event.target && event.target.hasAttribute('data-edit-content-input')) {
            event.preventDefault();
            event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.content_editable.input.change', {bubbles: true}));
        }
        if (event.target && event.target.hasAttribute('data-edit-content-target')) {
            event.preventDefault();
            event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.content_editable.target.change', {bubbles: true}));
        }
    });

    // on content editable input change, update preview, default listener
    document.addEventListener('sfs_cms.content_edit.content_editable.input.change', function (event) {
        event.preventDefault();
        contentEditableUpdateElementsFromInput(event.target);
    });

    // on content editable preview change, update inputs, default listener
    document.addEventListener('sfs_cms.content_edit.content_editable.target.change', function (event) {
        event.preventDefault();
        contentEditableUpdateInputsFromElement(event.target);
    });

    // hide elements with data-edit-content-hide-if-empty attribute if empty
    document.querySelectorAll('[data-edit-content-hide-if-empty]:empty').forEach((htmlElement) =>
        htmlElement.style.setProperty('display', 'none')
    );
}

// init behaviour on window load
window.addEventListener('load', _init);