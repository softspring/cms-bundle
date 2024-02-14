// init behaviour on window load
window.addEventListener('load', _init);

function _init() {
    document.addEventListener('change', function (event) {
        if (
            !event.target.matches('[data-field-route-name]') &&
            !event.target.matches('[data-field-route-params]') &&
            !event.target.matches('[data-field-anchor]') &&
            !event.target.matches('[data-field-url]')
        ) return;

        updateLinkField(event.target);
    });

    document.addEventListener('change', function (event) {
        if (!event.target.matches('[data-field-custom-target]')) return;

        updateCustomTargetField(event.target);
    });

    document.addEventListener("polymorphic.node.insert.after", function (event) {
        updateAllLinkTypes();
    });

    // on page load
    updateAllLinkTypes();
}

function cmsLinkTypeHideElement(field, label) {
    field.closest('div').classList.add('d-none');
    label && label.closest('div').classList.add('d-none');
}

function cmsLinkTypeShowElement(field, label) {
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

    selectedOption.value == 'route' ? cmsLinkTypeShowElement(fieldRouteNameField, fieldRouteNameLabel) : cmsLinkTypeHideElement(fieldRouteNameField, fieldRouteNameLabel);
    selectedOption.value == 'route' ? fieldRouteParamsField.value && cmsLinkTypeShowElement(fieldRouteParamsField, fieldRouteParamsLabel) : cmsLinkTypeHideElement(fieldRouteParamsField, fieldRouteParamsLabel);
    selectedOption.value == 'anchor' ? cmsLinkTypeShowElement(fieldAnchorField, fieldAnchorLabel) : cmsLinkTypeHideElement(fieldAnchorField, fieldAnchorLabel);
    selectedOption.value == 'url' ? cmsLinkTypeShowElement(fieldUrlField, fieldUrlLabel) : cmsLinkTypeHideElement(fieldUrlField, fieldUrlLabel);
}

function updateCustomTargetField(targetSelect) {
    const selectedOption = targetSelect.options[targetSelect.selectedIndex];

    const customTargetField = document.getElementById(targetSelect.dataset.fieldCustomTarget);
    const customTargetLabel = document.querySelector('label[for=' + customTargetField.id + ']');

    selectedOption.value == 'custom' ? cmsLinkTypeShowElement(customTargetField, customTargetLabel) : cmsLinkTypeHideElement(customTargetField, customTargetLabel);
}

function updateAllLinkTypes() {
    [...document.querySelectorAll('[data-field-route-name],[data-field-route-params],[data-field-anchor],[data-field-url]')].forEach((linkTypeField) => {
        updateLinkField(linkTypeField)
    });

    [...document.querySelectorAll('[data-field-custom-target]')].forEach((customTargetField) => {
        updateCustomTargetField(customTargetField)
    });
}

export {
    cmsLinkTypeHideElement,
    cmsLinkTypeShowElement,
};
