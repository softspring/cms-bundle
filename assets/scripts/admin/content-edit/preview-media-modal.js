window.addEventListener('load', (event) => {
    /**
     * Shows a media preview from media modal type
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

            let version = null;
            if (config[event.target.dataset.mediaType].image) {
                version = config[event.target.dataset.mediaType].image;
            } else if (config[event.target.dataset.mediaType].video) {
                version = config[event.target.dataset.mediaType].video;
            }

            let previewMedia = null;
            if ('_original' === version) {
                previewMedia = event.target.dataset['mediaVersion-_original'];
            } else {
                previewMedia = event.target.dataset['mediaVersion'+version.charAt(0).toUpperCase() + version.slice(1)];
            }

            // show required preview in every html element
            if (previewMedia) {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = previewMedia);
            } else {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = '');
            }
        }
    });

    /**
     * Removes a media preview from media modal type
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