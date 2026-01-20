import {cmsEditListener} from './event-listeners';
import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_content_edit_translations_json', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    cmsEditListener('[data-translation-json-field]', 'input', onEditTranslationInput);
    cmsEditListener('[data-edit-content-target]', 'input', onEditTranslationContent);
}

/**
 * On translation input change, update json field
 */
function onEditTranslationInput(inputElement, module, preview, form/*, event*/) {
    updateTranslationJson(inputElement);
};

/**
 * On translation input change, update json field
 */
function onEditTranslationContent(editableContent, module, preview, form/*, event*/) {
    let inputElement = form.querySelector('[data-edit-content-input="' + editableContent.dataset.editContentTarget + '"]');
    if (!inputElement.dataset.translationJsonField) {
        return;
    }

    updateTranslationJson(inputElement);
};


function updateTranslationJson(inputElement) {
    let jsonField = document.getElementById(inputElement.dataset.translationJsonField);
    let jsonValue = JSON.parse(jsonField.value);
    jsonValue[inputElement.dataset.inputLang] = inputElement.value;
    jsonField.value = JSON.stringify(jsonValue);
}