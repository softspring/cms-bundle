import {addTargetEventListener, callForeachSelector, registerFeature} from '../tools';

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

    const localeCheckbox = choicesWidget.querySelector('input[type="checkbox"][value="' + selectedChoice.value + '"]');
    localeCheckbox.checked = true;
    localeCheckbox.setAttribute('checked', 'checked');
    localeCheckbox.setAttribute('disabled', 'disabled');
}