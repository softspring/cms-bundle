import {addTargetEventListener, registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_routes_forms', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    addTargetEventListener('[data-generate-underscore]', 'keyup', fillUnderscore);
    addTargetEventListener('[data-generate-slug]', 'keyup', fillSlug);
    addTargetEventListener('.snake-case', 'keyup', underscoreInput);
    addTargetEventListener('.snake-case', 'focusout', underscoreInput);
    addTargetEventListener('.sluggize', 'keyup', slugInput);
    addTargetEventListener('.sluggize', 'focusout', slugInput);
}

function fillUnderscore(sourceElement) {
    const htmlTargetElements = document.querySelectorAll('[' + sourceElement.dataset.generateUnderscore + ']');
    const cleanValue = underscore(sourceElement.value);

    [...htmlTargetElements].forEach(function (htmlTargetElement) {
        if (sourceElement.lastUnderscoreValue === undefined || htmlTargetElement.value === sourceElement.lastUnderscoreValue) {
            htmlTargetElement.value = cleanValue;
        }
    });

    sourceElement.lastUnderscoreValue = cleanValue;
}

function underscoreInput(input, event) {
    input.value = underscore(input.value, event.type === 'keyup');
}

function fillSlug(sourceElement) {
    const htmlTargetElements = document.querySelectorAll('[' + sourceElement.dataset.generateSlug + ']');
    const cleanValue = slug(sourceElement.value);

    [...htmlTargetElements].forEach(function (htmlTargetElement) {
        if (sourceElement.lastSlugValue === undefined || htmlTargetElement.value === sourceElement.lastSlugValue) {
            htmlTargetElement.value = cleanValue;
        }
    });

    sourceElement.lastSlugValue = cleanValue;
}

function slugInput(input, event) {
    input.value = slug(input.value, event.type === 'keyup');
}

function underscore(value, allowLastUnderscore = false) {
    if (!allowLastUnderscore) {
        value = value.replace(/_+$/g, ''); // remove last underscores
    }
    return value.replace(/[\s\-]+/g, '_') // convert spaces to underscores
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // remove accents
        .replace(/[^a-zA-Z0-9_]/g, '') // remove special chars
        .replace(/_+/g, '_') // remove double underscores
        .toLowerCase(); // makes lowercase
}

function slug(value, allowLastDash = false) {
    if (!allowLastDash) {
        value = value.replace(/-+$/g, ''); // remove last dashes
    }
    return value.replace(/[\s_]+/g, '-') // convert spaces to dashes
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // remove accents
        .replace(/[^a-zA-Z0-9\-]/g, '') // remove special chars
        .replace(/-+/g, '-') // remove double dashes
        .toLowerCase(); // makes lowercase
}
