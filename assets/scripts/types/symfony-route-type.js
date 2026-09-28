/**
 * See Softspring\Form\Type\SymfonyRouteType and
 */
import {registerFeature} from '@softspring/cms-bundle/scripts/tools.js';

registerFeature('types_symfony_route_type', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    document.addEventListener('click', function (event) {
        if (!event.target.matches('[data-route-show-params]') && !event.target.matches('[data-route-hide-params]')) return;

        // hide this button
        event.target.hideElement();

        if (event.target.matches('[data-route-show-params]')) {
            // get the params field
            const paramsFieldId = event.target.dataset.routeShowParams;
            const routeParamsField = document.getElementById(paramsFieldId);
            const routeParamsLabel = document.querySelector('label[for=' + paramsFieldId + ']');

            // show the hide button
            const routeHideParamsLink = document.querySelector('[data-route-hide-params=' + paramsFieldId + ']');
            routeHideParamsLink.showElement();
            routeParamsField.closest('div').showElement();
            routeParamsLabel.closest('div').showElement();
        } else {
            // get the params field
            const paramsFieldId = event.target.dataset.routeHideParams;
            const routeParamsField = document.getElementById(paramsFieldId);
            const routeParamsLabel = document.querySelector('label[for=' + paramsFieldId + ']');

            // show the show button
            const routeShowParamsLink = document.querySelector('[data-route-show-params=' + paramsFieldId + ']');
            routeShowParamsLink.showElement();
            routeParamsField.value = '{}';
            routeParamsField.closest('div').hideElement();
            routeParamsLabel.closest('div').hideElement();
        }
    });

    document.addEventListener('change', function (event) {
        if (!event.target.matches('[data-route-params]')) return;
        updateRouteParamsField(event.target, 'change');
    });

    [...document.querySelectorAll('[data-route-params]')].forEach((routeParamsField) => {
        updateRouteParamsField(routeParamsField, 'init');
    });

    document.addEventListener("collection.node.insert.after", function (event) {
        [...event.node().querySelectorAll('[data-route-params]')].forEach((routeParamsField) => {
            updateRouteParamsField(routeParamsField);
        });
    });

    [...document.querySelectorAll('[data-route-params]')].forEach((routeNameField) => {
        if (!routeNameField.value) {
            document.getElementById(routeNameField.dataset.routeParams).closest('div').hideElement();
        }
    });
}

function updateRouteParamsField(routeNameSelect, actionType = null) {
    const selectedOption = routeNameSelect.options[routeNameSelect.selectedIndex];
    const routeParamsField = document.getElementById(routeNameSelect.dataset.routeParams);
    const routeParamsLabel = document.querySelector('label[for=' + routeParamsField.id + ']');
    const routeShowParamsLink = document.querySelector('[data-route-show-params=' + routeParamsField.id + ']');
    const routeHideParamsLink = document.querySelector('[data-route-hide-params=' + routeParamsField.id + ']');

    let showField, showShowLink, showHideLink;

    if (actionType !== 'init') {
        if (selectedOption.dataset.routeParameter) {
            routeParamsField.value = selectedOption.dataset.routeParameter;
        } else {
            routeParamsField.value = '{}';
        }
    }

    showField = !!(routeParamsField.value && routeParamsField.value !== '{}');
    showShowLink = !showField && !selectedOption.dataset.routeParameter;
    showHideLink = showField && !selectedOption.dataset.routeParameter;

    if (showField) {
        routeParamsField.closest('div').showElement();
        routeParamsLabel && routeParamsLabel.closest('div').showElement();
    } else {
        routeParamsField.closest('div').hideElement();
        routeParamsLabel && routeParamsLabel.closest('div').hideElement();
    }

    if (showShowLink) {
        routeShowParamsLink && routeShowParamsLink.showElement();
    } else {
        routeShowParamsLink && routeShowParamsLink.hideElement();
    }

    if (showHideLink) {
        routeHideParamsLink && routeHideParamsLink.showElement();
    } else {
        routeHideParamsLink && routeHideParamsLink.hideElement();
    }
}
