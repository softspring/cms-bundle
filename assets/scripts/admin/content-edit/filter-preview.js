import {registerFeature} from '@softspring/cms-bundle/scripts/tools.js';

registerFeature('admin_content_edit_filter_preview', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
    const contentEditionSiteSelector = document.getElementById('contentEditionSiteSelection');
    contentEditionLanguageSelector && contentEditionLanguageSelector.addEventListener('change', filterCurrentFilterElements);
    contentEditionSiteSelector && contentEditionSiteSelector.addEventListener('change', filterCurrentFilterElements);

    filterCurrentFilterElements();
}

function hideElement(htmlElement) {
    htmlElement.style.setProperty('display', 'none');
}

function showElement(htmlElement) {
    htmlElement.style.setProperty('display', '');

    if (htmlElement.dataset.previewUrl && !htmlElement.dataset.previewUrlLoaded && !htmlElement.dataset.previewUrlLoading) {
        htmlElement.dataset.previewUrlLoading = true;
        const previewUrl = htmlElement.dataset.previewUrl;
        fetch(previewUrl)
            .then(response => response.text())
            .then(html => {
                const previewUrlType = htmlElement.dataset.previewUrlType || 'html';
                if (previewUrlType === 'text') {
                    htmlElement.textContent = html;
                } else {
                    htmlElement.innerHTML = html;
                }
                htmlElement.dataset.previewUrlLoaded = true;
                filterCurrentFilterElements();
            })
            .catch(error => {
                htmlElement.dataset.previewUrlError = true;
                console.error('Error loading preview:', error);
            })
            .finally(() => {
                delete htmlElement.dataset.previewUrlLoading;
            });
    }
}

function selectFilterElements(language, site) {
    if (language && site) {
        document.querySelectorAll('[data-lang][data-site]').forEach((el) => hideElement(el));
        document.querySelectorAll('[data-lang=' + language + '][data-site=' + site + ']').forEach((el) => showElement(el));

        document.querySelectorAll('[data-site]:not([data-lang])').forEach((el) => hideElement(el));
        document.querySelectorAll('[data-site=' + site + ']:not([data-lang])').forEach((el) => showElement(el));

        document.querySelectorAll('[data-lang]:not([data-site])').forEach((el) => hideElement(el));
        document.querySelectorAll('[data-lang=' + language + ']:not([data-site])').forEach((el) => showElement(el));
    } else if (language) {
        document.querySelectorAll('[data-lang]').forEach((el) => hideElement(el));
        document.querySelectorAll('[data-lang=' + language + ']').forEach((el) => showElement(el));
    } else if (site) {
        document.querySelectorAll('[data-site]').forEach((el) => hideElement(el));
        document.querySelectorAll('[data-site=' + site + ']').forEach((el) => showElement(el));
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

export {
    getSelectedLanguage,
    getSelectedSite,
    filterCurrentFilterElements,
};
