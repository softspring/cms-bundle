window.addEventListener('load', (event) => {
    /**
     * Adds class field to target element
     *
     * The preview target element must have the "data-edit-class-target" attribute
     * The input field must have the "data-edit-class-input"
     * Both data attributes must have the same value (as identificator)
     *
     * If the preview target element has some default classes, those classes should be set on a "data-edit-class-default" attribute to not to be lost during preview
     */
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-class-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-class-target='" + event.target.dataset.editClassInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.className = htmlTargetElement.dataset.editClassDefault + ' ' + event.target.value);
        }
    });
});
