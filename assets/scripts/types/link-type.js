window.addEventListener('load', (event) => {

    function hideElement(field, label) {
        field.closest('div').classList.add('d-none');
        label && label.closest('div').classList.add('d-none');
    }

    function showElement(field, label) {
        field.closest('div').classList.remove('d-none');
        label && label.closest('div').classList.remove('d-none');
    }

    function updateLinkField(linkTypeSelect) {
        const selectedOption = linkTypeSelect.options[linkTypeSelect.selectedIndex];

        const fieldRouteNameField = document.getElementById(linkTypeSelect.dataset.fieldRouteName);
        const fieldRouteNameLabel = document.querySelector('label[for=' + fieldRouteNameField.id + ']');

        const fieldRouteParamsField = document.getElementById(fieldRouteNameField.dataset.routeParams);
        const fieldRouteParamsLabel = document.querySelector('label[for=' + fieldRouteParamsField.id + ']');

        const fieldAnchorField = document.getElementById(linkTypeSelect.dataset.fieldAnchor);
        const fieldAnchorLabel = document.querySelector('label[for=' + fieldAnchorField.id + ']');

        const fieldUrlField = document.getElementById(linkTypeSelect.dataset.fieldUrl);
        const fieldUrlLabel = document.querySelector('label[for=' + fieldUrlField.id + ']');

        selectedOption.value == 'route' ? showElement(fieldRouteNameField, fieldRouteNameLabel) : hideElement(fieldRouteNameField, fieldRouteNameLabel);
        selectedOption.value == 'route' ? fieldRouteParamsField.value && showElement(fieldRouteParamsField, fieldRouteParamsLabel) : hideElement(fieldRouteParamsField, fieldRouteParamsLabel);
        selectedOption.value == 'anchor' ? showElement(fieldAnchorField, fieldAnchorLabel) : hideElement(fieldAnchorField, fieldAnchorLabel);
        selectedOption.value == 'url' ? showElement(fieldUrlField, fieldUrlLabel) : hideElement(fieldUrlField, fieldUrlLabel);
    }

    document.addEventListener('change', function (event) {
        if (
            !event.target.matches('[data-field-route-name]') &&
            !event.target.matches('[data-field-route-params]') &&
            !event.target.matches('[data-field-anchor]') &&
            !event.target.matches('[data-field-url]')
        ) return;

        updateLinkField(event.target);
    });


    function updateCustomTargetField(targetSelect) {
        const selectedOption = targetSelect.options[targetSelect.selectedIndex];

        const customTargetField = document.getElementById(targetSelect.dataset.fieldCustomTarget);
        const customTargetLabel = document.querySelector('label[for=' + customTargetField.id + ']');

        selectedOption.value == 'custom' ? showElement(customTargetField, customTargetLabel) : hideElement(customTargetField, customTargetLabel);
    }

    document.addEventListener('change', function (event) {
        if (!event.target.matches('[data-field-custom-target]')) return;

        updateCustomTargetField(event.target);
    });

    function updateAllLinkTypes() {
        [...document.querySelectorAll('[data-field-route-name],[data-field-route-params],[data-field-anchor],[data-field-url]')].forEach((linkTypeField) => {
            updateLinkField(linkTypeField)
        });

        [...document.querySelectorAll('[data-field-custom-target]')].forEach((customTargetField) => {
            updateCustomTargetField(customTargetField)
        });
    }

    document.addEventListener("polymorphic.node.insert.after", function (event) {
        updateAllLinkTypes();
    });

    // on page load
    updateAllLinkTypes();
});
