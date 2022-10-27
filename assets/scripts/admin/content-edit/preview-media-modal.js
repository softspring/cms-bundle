window.addEventListener('load', (event) => {
    /**
     * Shows an image preview from image modal type
     *
     * The preview target element must have the "data-media-preview-target" attribute
     * The select option must have the "data-media-preview-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('sfs_media.selected', function (event) {
        if (!event.target || !event.target.hasAttribute('data-media-preview-input')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        var config = JSON.parse(event.target.dataset.mediaTypeConfig);
        var typeConfig = config[event.target.dataset.mediaTypeTypes];

        let htmlTargetElements = moduleForm.querySelectorAll("[data-media-preview-target='" + event.target.dataset.mediaPreviewInput + "']");
        if (htmlTargetElements.length) {
            const previewImage = event.target.dataset.mediaVersionSm;

            // show required preview in every html element
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = previewImage);
        }
    });

    /**
     * Removes an image preview from image modal type
     *
     * The preview target element must have the "data-media-preview-target" attribute
     * The select option must have the "data-media-preview-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('sfs_media.unselected', function (event) {
        if (!event.target || !event.target.hasAttribute('data-media-preview-input')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        var config = JSON.parse(event.target.dataset.mediaTypeConfig);
        var typeConfig = config[event.target.dataset.mediaTypeTypes];

        let htmlTargetElements = moduleForm.querySelectorAll("[data-media-preview-target='" + event.target.dataset.mediaPreviewInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = '');
        }
    });
});