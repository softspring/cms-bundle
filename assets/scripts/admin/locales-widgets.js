(function () {
    if (!window.__sfs_cms_locales_widgets_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_locales_widgets_registered = true;
})();

function _register() {
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.matches('select[data-locale-disables-choice]')) return;
        disableChoiceOnLocaleSelect(event.target);
    });

    document.querySelectorAll('select[data-locale-disables-choice]').forEach(function (select) {
        disableChoiceOnLocaleSelect(select);
    });
}

function disableChoiceOnLocaleSelect(select) {
    const selectedChoice = select.options[select.selectedIndex];
    const choicesWidget = document.getElementById(select.dataset.localeDisablesChoice);

    const allLocaleCheckboxes = choicesWidget.querySelectorAll('input[type="checkbox"]');
    allLocaleCheckboxes.forEach(function (checkbox) {
        checkbox.removeAttribute('disabled');
    });

    const localeCheckbox = choicesWidget.querySelector('input[type="checkbox"][value="'+selectedChoice.value+'"]');
    localeCheckbox.checked = true;
    localeCheckbox.setAttribute('checked', 'checked');
    localeCheckbox.setAttribute('disabled', 'disabled');
}