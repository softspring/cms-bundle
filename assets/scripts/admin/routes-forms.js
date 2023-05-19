var underscored = require("underscore.string/underscored");
var slugify = require("underscore.string/slugify");

window.addEventListener('load', (event) => {

    /* ****************************************************************************************************** *
     * SLUG GENERATION ON CONTENT FORM
     * ****************************************************************************************************** */

    String.prototype.removeAccents = function () {
        return this.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    document.addEventListener('keyup', function (event) {
        if (!event.target.matches('[data-generate-underscore]') && !event.target.matches('[data-generate-slug]')) return;

        // generate underscore
        var element = document.querySelector('[' + event.target.dataset.generateUnderscore + ']');
        if (element && element.value === underscored(event.target.lastValue || '')) {
            element.value = underscored(event.target.value).removeAccents();
        }

        // generate slug
        var element = document.querySelector('[' + event.target.dataset.generateSlug + ']');
        if (element && element.value.replace(/^\/+/, '').replace(/\/+$/, '') === slugify(event.target.lastValue || '')) {
            element.value = slugify(event.target.value).removeAccents();
        }

        event.target.lastValue = event.target.value.removeAccents();
    });

    document.addEventListener('keyup', function (event) {
        if (!event.target.matches('.snake-case')) return;
        event.target.value = underscored(event.target.value);
    });

    document.addEventListener('keyup', function (event) {
        if (!event.target.matches('.sluggize')) return;
        event.target.value = slugify(event.target.value);
    });

    /* ****************************************************************************************************** *
     * FIELDS VISIBILITY DEPENDING ON ROUTE TYPE
     * ****************************************************************************************************** */

    function routeFormFieldsVisiblity() {
        const routeFormTypeSelect = document.getElementById('route_form_type');
        const selectedOption = routeFormTypeSelect.options[routeFormTypeSelect.selectedIndex];

        if (selectedOption.dataset.contentVisible == 'visible') {
            document.getElementById('route_form_content').closest('div').classList.remove('d-none');
        } else {
            document.getElementById('route_form_content').closest('div').classList.add('d-none');
            document.getElementById('route_form_content').value = '';
        }

        if (selectedOption.dataset.redirectUrlVisible == 'visible') {
            document.getElementById('route_form_redirectUrl').closest('div').classList.remove('d-none');
        } else {
            document.getElementById('route_form_redirectUrl').closest('div').classList.add('d-none');
            document.getElementById('route_form_redirectUrl').value = '';
        }

        if (selectedOption.dataset.symfonyRouteVisible == 'visible') {
            document.getElementById('route_form_symfonyRoute').closest('div').classList.remove('d-none');
        } else {
            document.getElementById('route_form_symfonyRoute').closest('div').classList.add('d-none');
            document.getElementById('route_form_symfonyRoute').querySelector('#route_form_symfonyRoute_route_name').value = '';
            document.getElementById('route_form_symfonyRoute').querySelector('#route_form_symfonyRoute_route_params').value = '';
        }

        if (selectedOption.dataset.redirectTypeVisible == 'visible') {
            document.getElementById('route_form_redirectType').closest('div').classList.remove('d-none');
        } else {
            document.getElementById('route_form_redirectType').closest('div').classList.add('d-none');
            document.getElementById('route_form_redirectType').value = '';
        }
    }

    const routeFormType = document.getElementById('route_form_type');
    if (routeFormType) {
        routeFormFieldsVisiblity();
        routeFormType.addEventListener('change', routeFormFieldsVisiblity);
    }

    /* ****************************************************************************************************** *
     * SYMFONY ROUTE PARAMS
     * ****************************************************************************************************** */

    function updateRouteParamsField(routeNameSelect)
    {
        const selectedOption = routeNameSelect.options[routeNameSelect.selectedIndex];
        const routeParamsField = document.getElementById(routeNameSelect.dataset.routeParams);
        const routeParamsLabel = document.querySelector('label[for='+routeParamsField.id+']');

        let routeParams = {};

        for(let attr in selectedOption.attributes) {
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
