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

        let modulePreview = event.target.closest('.cms-module-edit').querySelector(':scope > .module-preview');
        let moduleForm = event.target.closest('.cms-module-edit').querySelector(':scope > .cms-module-form');

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-class-target='" + event.target.dataset.editClassInput + "']");
        let htmlInputsElements = moduleForm.querySelectorAll("[data-edit-class-input='" + event.target.dataset.editClassInput + "']");

        let classesElement = '';

        // Combine all option to class attribute
        [...htmlInputsElements].forEach(function (htmlInputsElement) {
            classesElement = htmlInputsElement.value + ' ' + classesElement;
        });

        [...htmlTargetElements].forEach(function (htmlTargetElement) {
            htmlTargetElement.className = htmlTargetElement.dataset.editClassDefault + ' ' + classesElement;
        });
    });
});
