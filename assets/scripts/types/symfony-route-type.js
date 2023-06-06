/**
 * See Softspring\Form\Type\SymfonyRouteType and
 */

window.addEventListener('load', (event) => {
    function updateRouteParamsField(routeNameSelect) {
        const selectedOption = routeNameSelect.options[routeNameSelect.selectedIndex];
        const routeParamsField = document.getElementById(routeNameSelect.dataset.routeParams);
        const routeParamsLabel = document.querySelector('label[for=' + routeParamsField.id + ']');

        let routeParams = {};

        for (let attr in selectedOption.attributes) {
            const dataName = selectedOption.attributes[attr];
            if (!dataName.nodeName || !dataName.nodeName.startsWith('data-route-parameter-')) {
                continue;
            }
            const paramName = dataName.nodeName.substring(21);
            const requirement = dataName.nodeValue;

            routeParams[paramName] = requirement;
        }

        if (Object.keys(routeParams).length) {
            routeParamsField.closest('div').classList.remove('d-none');
            routeParamsLabel && routeParamsLabel.closest('div').classList.remove('d-none');
            routeParamsField.value = JSON.stringify(routeParams);
        } else {
            routeParamsField.closest('div').classList.add('d-none');
            routeParamsLabel && routeParamsLabel.closest('div').classList.add('d-none');
            routeParamsField.value = '';
        }
    }

    document.addEventListener('change', function (event) {
        if (!event.target.matches('[data-route-params]')) return;
        updateRouteParamsField(event.target);
    });

    [...document.querySelectorAll('[data-route-params]')].forEach((routeParamsField) => {
        updateRouteParamsField(routeParamsField);
    });

    document.addEventListener("polymorphic.node.insert.after", function (event) {
        [...event.node().querySelectorAll('[data-route-params]')].forEach((routeParamsField) => {
            updateRouteParamsField(routeParamsField);
        });
    });

    [...document.querySelectorAll('[data-route-params]')].forEach((routeNameField) => {
        if (!routeNameField.value) {
            document.getElementById(routeNameField.dataset.routeParams).closest('div').classList.add('d-none');
        }
    });
});
