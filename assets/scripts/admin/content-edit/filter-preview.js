import {cmsEditListener} from './event-listeners';

export {
    getSelectedLanguage,
    getSelectedSite,
    filterCurrentFilterElements,
};

(function () {
    if (!window.__sfs_cms_content_edit_filter_preview_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_filter_preview_registered = true;
})();

function _register() {
    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
    const contentEditionSiteSelector = document.getElementById('contentEditionSiteSelection');
    contentEditionLanguageSelector && contentEditionLanguageSelector.addEventListener('change', filterCurrentFilterElements);
    contentEditionSiteSelector && contentEditionSiteSelector.addEventListener('change', filterCurrentFilterElements);

    filterCurrentFilterElements();
}

function selectFilterElements(language, site) {
    if (language && site) {
        document.querySelectorAll('[data-lang][data-site]').forEach((el) => el.style.setProperty('display', 'none'));
        document.querySelectorAll('[data-lang='+language+'][data-site='+site+']').forEach((el) => el.style.setProperty('display', ''));

        document.querySelectorAll('[data-site]:not([data-lang])').forEach((el) => el.style.setProperty('display', 'none'));
        document.querySelectorAll('[data-site='+site+']:not([data-lang])').forEach((el) => el.style.setProperty('display', ''));

        document.querySelectorAll('[data-lang]:not([data-site])').forEach((el) => el.style.setProperty('display', 'none'));
        document.querySelectorAll('[data-lang='+language+']:not([data-site])').forEach((el) => el.style.setProperty('display', ''));
    } else if (language) {
        document.querySelectorAll('[data-lang]').forEach((el) => el.style.setProperty('display', 'none'));
        document.querySelectorAll('[data-lang='+language+']').forEach((el) => el.style.setProperty('display', ''));
    } else if (site) {
        document.querySelectorAll('[data-site]').forEach((el) => el.style.setProperty('display', 'none'));
        document.querySelectorAll('[data-site='+site+']').forEach((el) => el.style.setProperty('display', ''));
    }

    document.querySelectorAll('[data-edit-content-hide-if-empty]:empty').forEach((htmlElement) => htmlElement.style.setProperty('display', 'none'));
}

function getSelectedLanguage() {
    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
    if (contentEditionLanguageSelector == null || !contentEditionLanguageSelector.length) return null;
    return contentEditionLanguageSelector.value;
}

function getSelectedSite() {
    const contentEditionSiteSelector = document.getElementById('contentEditionSiteSelection');
    if (contentEditionSiteSelector == null || !contentEditionSiteSelector.length) return null;
    return contentEditionSiteSelector.value;
}

function filterCurrentFilterElements() {
    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
    const contentEditionSiteSelector = document.getElementById('contentEditionSiteSelection');
    if (!contentEditionLanguageSelector && !contentEditionSiteSelector) return;

    selectFilterElements(getSelectedLanguage(), getSelectedSite());
}
