/**
 * See Softspring\Form\Type\SymfonyRouteType and
 */
window.addEventListener('load', (event) => {
    function selectOptionRouteRequirements(option) {
        let routeParamsRequirements = {};

        for (let attr in option.attributes) {
            const dataName = option.attributes[attr];
            if (!dataName.nodeName || !dataName.nodeName.startsWith('data-route-parameter-')) {
                continue;
            }
            const paramName = dataName.nodeName.substring(21);
            const requirement = dataName.nodeValue;

            routeParamsRequirements[paramName] = requirement;
        }

        return routeParamsRequirements;
    }

    function hasOptionalParams(routeParamsRequirements, routeParams) {
        for (let paramName in routeParams) {
            if (routeParamsRequirements[paramName] === undefined) {
                return true;
            }
        }

        return false;
    }

    function updateRouteParamsField(routeNameSelect, actionType = null) {
        const selectedOption = routeNameSelect.options[routeNameSelect.selectedIndex];
        const routeParamsField = document.getElementById(routeNameSelect.dataset.routeParams);
        const routeParamsLabel = document.querySelector('label[for=' + routeParamsField.id + ']');
        const routeShowParamsLink = document.querySelector('[data-route-show-params=' + routeParamsField.id + ']');
        const routeHideParamsLink = document.querySelector('[data-route-hide-params=' + routeParamsField.id + ']');

        const isInit = selectedOption.dataset.init === undefined;
        selectedOption.dataset.init = true;

        // get route requirements
        const requiredRouteParams = selectOptionRouteRequirements(selectedOption);
        const requiredRouteParamsCount = Object.keys(requiredRouteParams).length;

        // init routeParams with requirements
        let routeParams = requiredRouteParams;

        // fill route params init values
        if (isInit && routeParamsField.value && actionType !== 'change') {
            const initialValues = JSON.parse(routeParamsField.value)

            if (initialValues) {
                [...Object.keys(initialValues)].forEach((paramName) => {
                    routeParams[paramName] = initialValues[paramName];
                });
            }
        }

        if (Object.keys(routeParams).length) {
            routeParamsField.closest('div').showElement();
            routeParamsLabel && routeParamsLabel.closest('div').showElement();
            routeParamsField.value = JSON.stringify(routeParams);
        } else {
            routeParamsField.closest('div').hideElement();
            routeParamsLabel && routeParamsLabel.closest('div').hideElement();
            routeParamsField.value = '{}';
        }

        if(routeShowParamsLink !== null && routeHideParamsLink !== null) {
            if (requiredRouteParamsCount) {
                routeShowParamsLink.hideElement();
                routeHideParamsLink.hideElement();
            } else if (Object.keys(routeParams).length) {
                routeShowParamsLink.hideElement();
                routeHideParamsLink.showElement();
            } else {
                routeShowParamsLink.showElement();
                routeHideParamsLink.hideElement();
            }
        }
    }

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

    document.addEventListener("polymorphic.node.insert.after", function (event) {
        [...event.node().querySelectorAll('[data-route-params]')].forEach((routeParamsField) => {
            updateRouteParamsField(routeParamsField);
        });
    });

    [...document.querySelectorAll('[data-route-params]')].forEach((routeNameField) => {
        if (!routeNameField.value) {
            document.getElementById(routeNameField.dataset.routeParams).closest('div').hideElement();
        }
    });
});
