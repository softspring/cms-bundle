function selectTranslatableElementsLanguage(language) {
    document.querySelectorAll('[data-lang]').forEach((el) => el.style.setProperty('display', 'none'));
    document.querySelectorAll('[data-lang='+language+']').forEach((el) => el.style.setProperty('display', ''));
    document.querySelectorAll('[data-edit-content-hide-if-empty]:empty').forEach((htmlElement) => htmlElement.style.setProperty('display', 'none'));
}

function getSelectedLanguage() {
    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
    if (contentEditionLanguageSelector == null || !contentEditionLanguageSelector.length) return;
    return contentEditionLanguageSelector.value;
}

function filterCurrentTranslatableElementsLanguage() {
    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
    if (contentEditionLanguageSelector == null || !contentEditionLanguageSelector.length) return;
    contentEditionLanguageSelector.addEventListener('change', function (event) {
        selectTranslatableElementsLanguage(event.target.value);
    });

    selectTranslatableElementsLanguage(contentEditionLanguageSelector.value);
}

window.addEventListener('load', (event) => {
    // onload, do the filtering
    filterCurrentTranslatableElementsLanguage();
});
