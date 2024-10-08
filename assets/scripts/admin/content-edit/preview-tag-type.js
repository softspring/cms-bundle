import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_preview_tag_type', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-tag-type-input')) return;
        event.preventDefault();

        let preview = event.target.closest('.cms-module-edit').querySelector('.module-preview');
        let htmlTargetElements = preview.querySelectorAll("[data-edit-tag-type-target='" + event.target.dataset.editTagTypeInput + "']");

        if (htmlTargetElements.length) {
            htmlTargetElements.forEach(function (targetElement) {
                targetElement.outerHTML = targetElement.outerHTML.trim()
                    .replace('<' + targetElement.nodeName.toLowerCase() + ' ', '<' + event.target.value + ' ')
                    .replace('</' + targetElement.nodeName.toLowerCase() + '>', '</' + event.target.value + '>');
            });
        }
    });
}