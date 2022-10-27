window.addEventListener('load', (event) => {
    /**
     * Sets a background color field to target element
     *
     * The preview target element must have the "data-edit-bgcolor-target" attribute
     * The input field must have the "data-edit-bgcolor-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-bgcolor-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-bgcolor-target='" + event.target.dataset.editBgcolorInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.style.backgroundColor = event.target.value);
        }
    });
});
