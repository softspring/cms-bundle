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

        let previewMedia = null;

        // if data-media-version-type-field do preview in select version
        if (event.target.dataset.mediaVersionTypeField === undefined) {
            let version = null;
            if (config[event.target.dataset.mediaType].image) {
                version = config[event.target.dataset.mediaType].image[0];

                if ('_original' === version) {
                    previewMedia = event.target.dataset['mediaImage-_original'];
                } else {
                    previewMedia = event.target.dataset['mediaImage-'+version.charAt(0).toUpperCase() + version.slice(1)];
                }
            } else if (config[event.target.dataset.mediaType].video) {
                version = config[event.target.dataset.mediaType].video[0];
            } else if (config[event.target.dataset.mediaType].picture) {
                version = config[event.target.dataset.mediaType].picture[0];
            } else if (config[event.target.dataset.mediaType].videoSet) {
                version = config[event.target.dataset.mediaType].videoSet[0];
            }

            // show required preview in every html element
            [...moduleForm.querySelectorAll("[data-media-preview-target='" + event.target.dataset.mediaPreviewInput + "']")].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = previewMedia ? previewMedia : '');
        }
    });

    /**
     * Shows a media version preview from media modal type
     *
     * The preview target element must have the "data-media-preview-target" attribute
     * The select option must have the "data-media-preview-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('sfs_media.select_version', function (event) {
        if (!event.target) return;

        const mediaVersionInput = event.target;
        const moduleForm = mediaVersionInput.closest('.cms-module-edit').querySelector('.module-preview');
        const mediaInput = document.getElementById(mediaVersionInput.dataset.mediaTypeField);

        var config = JSON.parse(mediaInput.dataset.mediaTypeConfig);
        var typeConfig = config[mediaInput.dataset.mediaTypeTypes];

        let previewMedia = null;

        let [versionType, versionName] = mediaVersionInput.value.split('#');
        versionName = '_' === versionName.charAt(0) ? '-'+versionName : versionName.charAt(0).toUpperCase() + versionName.slice(1);

        switch (versionType) {
            case 'image':
                previewMedia = mediaInput.dataset['mediaImage'+versionName];
                break;

            case 'video':
                previewMedia = mediaInput.dataset['mediaVideo'+versionName];
                break;

            case 'picture':
                previewMedia = mediaInput.dataset['mediaPicture'+versionName];
                break;

            case 'videoSet':
                previewMedia = mediaInput.dataset['mediaVideoSet'+versionName];
                break;
        }

        // show required preview in every html element
        [...moduleForm.querySelectorAll("[data-media-preview-target='" + mediaInput.dataset.mediaPreviewInput + "']")].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = previewMedia ? previewMedia : '');
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

        [...moduleForm.querySelectorAll("[data-media-preview-target='" + event.target.dataset.mediaPreviewInput + "']")].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = '');
    });
});
