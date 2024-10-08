import {filterCurrentFilterElements} from "./filter-preview";
import {cmsEditListener} from './event-listeners';
import {callForeachSelector, registerFeature} from '../../tools';

registerFeature('admin_content_edit_locale_filter_modules', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    cmsEditListener('[data-cms-module-locale-filter]', 'click', onLocaleFilterClick);

    let contentEditionLanguageSelector = getContentEditionLanguageSelector();
    if (contentEditionLanguageSelector) {
        contentEditionLanguageSelector.addEventListener('change', function (event) {
            localeHideModules(event.target.value);
        });

        localeHideModules(contentEditionLanguageSelector.value);
    }

    // Add listener to input fields to change the global content edition language
    cmsEditListener('[data-input-lang]', 'focusin', onInputLangFocusIn);
}

/**
 * Change the global content edition language when an input field is focused
 */
function onInputLangFocusIn(input) {
    document.getElementById('contentEditionLanguageSelection').value = input.getAttribute('data-input-lang');
    filterCurrentFilterElements();
}

function onLocaleFilterClick(localeSelector) {
    let contentEditionLanguageSelector = getContentEditionLanguageSelector();
    if (!contentEditionLanguageSelector) return;

    let widget = localeSelector;
    let locale = widget.value;
    let currentLocale = contentEditionLanguageSelector.value;

    if (locale !== currentLocale) return;

    let localeVisible = widget.checked;
    let modulePreview = widget.closest('.cms-module-edit').querySelector('.module-preview');
    if (localeVisible) {
        modulePreview.classList.remove('cms-module-locale-hidden');
    } else {
        modulePreview.classList.add('cms-module-locale-hidden');
    }
}

function getContentEditionLanguageSelector() {
    return document.getElementById('contentEditionLanguageSelection');
}

function localeHideModules(locale) {
    callForeachSelector('[data-cms-module-locale-filter][value=' + locale + ']', widgetHideModules);
}

function widgetHideModules(widget) {
    let localeVisible = widget.checked;
    let modulePreview = widget.closest('.cms-module-edit').querySelector('.module-preview');
    if (localeVisible) {
        modulePreview.classList.remove('cms-module-locale-hidden');
    } else {
        modulePreview.classList.add('cms-module-locale-hidden');
    }
}

export {localeHideModules};