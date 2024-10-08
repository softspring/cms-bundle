import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_preview_toggle', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    /**
     * Toggles content from input
     *
     * The toggle target element must have the "data-edit-content-toggle-target" attribute
     * The input field must have the "data-edit-content-toggle-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-toggle-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let visible = event.target.type == 'checkbox' ? event.target.checked : event.target.value;

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-content-toggle-target='" + event.target.dataset.editContentToggleInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach(function (htmlTargetElement) {
                if (visible) {
                    htmlTargetElement.classList.remove('d-none');
                } else {
                    htmlTargetElement.classList.add('d-none');
                }
            });
        }
    });
};
