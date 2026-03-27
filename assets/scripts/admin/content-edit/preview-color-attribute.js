import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_content_edit_preview_color_attribute', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    document.addEventListener('input', onEditColorAttribute);
    document.addEventListener('change', onEditColorAttribute);
}

/**
 * Sets a color field to target element attribute
 *
 * The preview target element must have the "data-edit-color-attribute-{attributeName}-target" attribute
 * The input field must have the "data-edit-color-attribute-{attributeName}-input"
 * Both data attributes must have the same value (as identifier)
 */
function onEditColorAttribute(event) {
    if (!event.target) {
        return;
    }

    let moduleEdit = event.target.closest('.cms-module-edit');
    if (!moduleEdit) {
        return;
    }

    let preview = moduleEdit.querySelector('.module-preview');
    if (!preview) {
        return;
    }

    const kebabize = (str) => str.replace(/[A-Z]+(?![a-z])|[A-Z]/g, ($, ofs) => (ofs ? "-" : "") + $.toLowerCase());
    const escapeAttrValue = (value) => typeof CSS !== 'undefined' && CSS.escape ? CSS.escape(value) : value.replace(/\\/g, '\\\\').replace(/"/g, '\\"');

    Object.keys(event.target.dataset).forEach((dataAttribute) => {
        if (!dataAttribute.startsWith('editColorAttribute') || !dataAttribute.endsWith('Input')) {
            return;
        }

        const targetHashes = event.target.dataset[dataAttribute]
            .split(',')
            .map((hash) => hash.trim())
            .filter(Boolean);

        if (!targetHashes.length) {
            return;
        }

        const targetAttributeName = dataAttribute
            .replace(/^editColorAttribute/, '')
            .replace(/Input$/, '');

        if (!targetAttributeName) {
            return;
        }

        const attributeName = kebabize(targetAttributeName);
        targetHashes.forEach((targetHash) => {
            const htmlTargetElements = preview.querySelectorAll("[data-edit-color-attribute-" + attributeName + "-target=\"" + escapeAttrValue(targetHash) + "\"]");
            if (!htmlTargetElements.length) {
                return;
            }

            htmlTargetElements.forEach((htmlTargetElement) => {
                htmlTargetElement.setAttribute(attributeName, event.target.value);
            });
        });
    });
}
