window.addEventListener('load', (event) => {
    /**
     * Shows a block preview
     *
     * The preview target element must have the "data-block-preview-target" attribute
     * The select option must have the "data-block-preview-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.hasAttribute('data-block-preview-input')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = moduleForm.querySelectorAll("[data-block-preview-target='" + event.target.dataset.blockPreviewInput + "']");
        let blockPreview = event.target.options[event.target.selectedIndex].dataset.blockPreview;
        [...htmlTargetElements].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = blockPreview === undefined ? '' : blockPreview);
    });
});