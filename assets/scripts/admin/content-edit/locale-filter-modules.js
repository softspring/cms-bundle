import {filterCurrentFilterElements} from "./filter-preview";
import {cmsEditListener} from './event-listeners';

export { localeHideModules };

(function () {
    if (!window.__sfs_cms_content_edit_locale_filter_modules_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_locale_filter_modules_registered = true;
})();

function _register() {
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
    document.querySelectorAll('[data-cms-module-locale-filter][value='+locale+']').forEach(function (widget) {
        let localeVisible = widget.checked;
        let modulePreview = widget.closest('.cms-module-edit').querySelector('.module-preview');
        if (localeVisible) {
            modulePreview.classList.remove('cms-module-locale-hidden');
        } else {
            modulePreview.classList.add('cms-module-locale-hidden');
        }
    });
}

