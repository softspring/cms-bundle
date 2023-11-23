import {filterCurrentFilterElements} from "./filter-preview";

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

window.addEventListener('load', (event) => {
    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');

    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.hasAttribute('data-cms-module-locale-filter')) return;

        let widget = event.target;
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
    });

    if (contentEditionLanguageSelector) {
        contentEditionLanguageSelector.addEventListener('change', function (event) {
            localeHideModules(event.target.value);
        });

        localeHideModules(contentEditionLanguageSelector.value);
    }

    window.addEventListener('focusin', function (event) {
        if (!event.target || !event.target.hasAttribute('data-input-lang')) return;

        document.getElementById('contentEditionLanguageSelection').value = event.target.getAttribute('data-input-lang');
        filterCurrentFilterElements();
    });
});

export { localeHideModules };