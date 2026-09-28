import {addTargetEventListener, registerFeature} from '@softspring/cms-bundle/scripts/tools.js';

registerFeature('admin_content_forms', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    addTargetEventListener('[data-locale-add-path]', 'change', addLocalePath);
}

function addLocalePath(localeCheckbox) {
    if (!localeCheckbox.checked || !localeCheckbox.dataset.localeAddPath) {
        return;
    }
    // find route paths container
    const routePathsContainer = document.querySelector('#' + localeCheckbox.dataset.localeAddPath);
    if (!routePathsContainer) {
        return;
    }

    const localePaths = routePathsContainer.querySelectorAll('[data-collection=node] [data-route-form=path-locale]');
    let localePathExists = false;
    [...localePaths].forEach(function (localePath) {
        if (localePath.value === localeCheckbox.value) {
            localePathExists = true;
        }
    });

    if (localePathExists) {
        return;
    }

    const addButton = routePathsContainer.querySelector('[data-collection-action=add]');
    if (!addButton) {
        return;
    }
    addButton.click();

    // set locale value to last added row
    const newLocalePaths = routePathsContainer.querySelectorAll('[data-collection=node] [data-route-form=path-locale]');
    const lastLocale = newLocalePaths.item(newLocalePaths.length - 1);
    lastLocale.value = localeCheckbox.value;
    lastLocale.dispatchEvent(new Event('change', {bubbles: true}));
}
