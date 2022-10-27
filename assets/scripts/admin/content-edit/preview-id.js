window.addEventListener('load', (event) => {
    /**
     * Adds id field to target element
     *
     * The preview target element must have the "data-edit-id-target" attribute
     * The input field must have the "data-edit-id-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-id-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-id-target='" + event.target.dataset.editIdInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.id = event.target.value);
        }
    });
});
