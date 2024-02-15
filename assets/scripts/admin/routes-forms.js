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

    function routeFormFieldsVisiblityBySelect() {
        const routeFormTypeSelect = document.getElementById('route_type');
        const selectedOption = routeFormTypeSelect.options[routeFormTypeSelect.selectedIndex];
        routeFormFieldsVisiblity(selectedOption);
    }
    function routeFormFieldsVisiblityByRadio() {
        const selectedOption = document.querySelector('input[data-route-form-radio]:checked');
        routeFormFieldsVisiblity(selectedOption);
    }
    function routeFormFieldsVisiblity(selectedOption) {
        if(document.querySelector('[data-route-form-content]') !== null) {
            if (selectedOption.dataset.contentVisible === 'visible') {
                document.querySelector('[data-route-form-content]').closest('[data-route-field-container]').classList.remove('d-none');
            } else {
                document.querySelector('[data-route-form-content]').closest('[data-route-field-container]').classList.add('d-none');
                document.querySelector('[data-route-form-content]').value = '';
            }
        }

        if(document.querySelector('[data-route-form-redirect-url]') !== null) {
            if (selectedOption.dataset.redirectUrlVisible === 'visible') {
                document.querySelector('[data-route-form-redirect-url]').closest('[data-route-field-container]').classList.remove('d-none');
            } else {
                document.querySelector('[data-route-form-redirect-url]').closest('[data-route-field-container]').classList.add('d-none');
                document.querySelector('[data-route-form-redirect-url]').value = '';
            }
        }

        if(document.getElementById('route_form_symfonyRoute') !== null) {
            if (selectedOption.dataset.symfonyRouteVisible === 'visible') {
                document.getElementById('route_form_symfonyRoute').closest('[data-route-field-container]').classList.remove('d-none');
            } else {
                document.getElementById('route_form_symfonyRoute').closest('[data-route-field-container]').classList.add('d-none');
                document.getElementById('route_form_symfonyRoute').querySelector('#route_form_symfonyRoute_route_name').value = '';
                document.getElementById('route_form_symfonyRoute').querySelector('#route_form_symfonyRoute_route_params').value = '';
            }
        }

        if(document.querySelector('[data-route-form-redirect-type]') !== null) {

            if (selectedOption.dataset.redirectTypeVisible === 'visible') {
                document.querySelector('[data-route-form-redirect-type]').closest('[data-route-field-container]').classList.remove('d-none');
            } else {
                document.querySelector('[data-route-form-redirect-type]').closest('[data-route-field-container]').classList.add('d-none');
                document.querySelector('[data-route-form-redirect-type]').value = '';
            }
        }
    }

    const routeFormType = document.getElementById('route_type');
    if (routeFormType) {
        routeFormFieldsVisiblityBySelect();
        routeFormType.addEventListener('change', routeFormFieldsVisiblityBySelect);
    } else {
        const routeFormTypeRadio = document.querySelectorAll('[data-route-form-radio]');
        if (routeFormTypeRadio) {
            routeFormFieldsVisiblityByRadio();
            routeFormTypeRadio.forEach((routeTypeRadio) =>
                routeTypeRadio.addEventListener('change', routeFormFieldsVisiblityByRadio)
            );
        }
    }
});
