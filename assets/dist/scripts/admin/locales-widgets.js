import {addTargetEventListener, callForeachSelector, registerFeature} from '../tools.js';

registerFeature('admin_locales_widgets', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    addTargetEventListener('select[data-locale-disables-choice]', 'change', disableChoiceOnLocaleSelect);
    callForeachSelector('select[data-locale-disables-choice]', disableChoiceOnLocaleSelect);
}

function disableChoiceOnLocaleSelect(select) {
    const selectedChoice = select.options[select.selectedIndex];
    const choicesWidget = document.getElementById(select.dataset.localeDisablesChoice);

    callForeachSelector('input[type="checkbox"]', (checkbox) => checkbox.removeAttribute('disabled'));
    choicesWidget.querySelectorAll('input[type="hidden"][data-default-locale-choice]').forEach((input) => input.remove());

    const localeCheckbox = choicesWidget.querySelector('input[type="checkbox"][value="' + selectedChoice.value + '"]');
    localeCheckbox.checked = true;
    localeCheckbox.setAttribute('checked', 'checked');
    localeCheckbox.setAttribute('disabled', 'disabled');

    const localeHiddenInput = document.createElement('input');
    localeHiddenInput.type = 'hidden';
    localeHiddenInput.name = localeCheckbox.name;
    localeHiddenInput.value = localeCheckbox.value;
    localeHiddenInput.dataset.defaultLocaleChoice = 'true';
    choicesWidget.append(localeHiddenInput);
}
