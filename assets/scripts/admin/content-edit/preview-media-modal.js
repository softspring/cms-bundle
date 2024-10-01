import {cmsEditListener} from './event-listeners';

(function () {
    if (!window.__sfs_cms_content_edit_preview_media_modal_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_preview_media_modal_registered = true;
})();

function _register() {
    document.addEventListener('sfs_media.selected', function (event) {
        if (!event.target || !event.target.hasAttribute('data-media-preview-input')) return;
        onModalMediaSelectShowPreview(event.target);
    });

    document.addEventListener('sfs_media.select_version', function (event) {
        if (!event.target) return;
        onModalMediaVersionSelectShowPreview(event.target);
    });

    document.addEventListener('sfs_media.unselected', function (event) {
        if (!event.target || !event.target.hasAttribute('data-media-preview-input')) return;
        onModalMediaUnselectRemovePreview(event.target);
    });

    document.querySelectorAll('[data-media-preview-target]').forEach((htmlTargetElement) => {
        showMediaPlaceholder(htmlTargetElement);
    });

    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.hasAttribute('data-media-placeholder')) return;

        const targetInput = document.querySelector('[data-media-preview-input="'+event.target.closest('[data-media-preview-target]').dataset.mediaPreviewTarget+'"]');
        const mediaWidget = targetInput.closest('.media-widget');
        const openModalButton = mediaWidget.querySelector('[data-bs-toggle="modal"]');
        openModalButton.click();
    });
}

/**
 * Shows a media preview from media modal type
 *
 * The preview target element must have the "data-media-preview-target" attribute
 * The select option must have the "data-media-preview-input"
 * Both data attributes must have the same value (as identificator)
 */
function onModalMediaSelectShowPreview(selectedMedia) {
    let moduleForm = selectedMedia.closest('.cms-module-edit').querySelector('.module-preview');

    var config = JSON.parse(selectedMedia.dataset.mediaTypeConfig);
    var typeConfig = config[selectedMedia.dataset.mediaTypeTypes];

    let previewMedia = null;

    // if data-media-version-type-field do preview in select version
    if (selectedMedia.dataset.mediaVersionTypeField === undefined) {
        let version = null;
        if (config[selectedMedia.dataset.mediaType].image) {
            version = config[selectedMedia.dataset.mediaType].image[0];

            if ('_original' === version) {
                previewMedia = selectedMedia.dataset['mediaImage-_original'];
            } else {
                previewMedia = selectedMedia.dataset['mediaImage-'+version.charAt(0).toUpperCase() + version.slice(1)];
            }
        } else if (config[selectedMedia.dataset.mediaType].video) {
            version = config[selectedMedia.dataset.mediaType].video[0];
        } else if (config[selectedMedia.dataset.mediaType].picture) {
            version = config[selectedMedia.dataset.mediaType].picture[0];
        } else if (config[selectedMedia.dataset.mediaType].videoSet) {
            version = config[selectedMedia.dataset.mediaType].videoSet[0];
        }

        // show required preview in every html element
        [...moduleForm.querySelectorAll("[data-media-preview-target='" + selectedMedia.dataset.mediaPreviewInput + "']")].forEach((htmlTargetElement) => htmlTargetElement.innerHTML = previewMedia ? previewMedia : '');
    }
}

/**
 * Shows a media version preview from media modal type
 *
 * The preview target element must have the "data-media-preview-target" attribute
 * The select option must have the "data-media-preview-input"
 * Both data attributes must have the same value (as identificator)
 */
function onModalMediaVersionSelectShowPreview(mediaVersionInput) {
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
}

/**
 * Removes a media preview from media modal type
 *
 * The preview target element must have the "data-media-preview-target" attribute
 * The select option must have the "data-media-preview-input"
 * Both data attributes must have the same value (as identificator)
 */
function onModalMediaUnselectRemovePreview(unselectedMedia) {
    let moduleForm = unselectedMedia.closest('.cms-module-edit').querySelector('.module-preview');

    var config = JSON.parse(unselectedMedia.dataset.mediaTypeConfig);
    var typeConfig = config[unselectedMedia.dataset.mediaTypeTypes];

    [...moduleForm.querySelectorAll("[data-media-preview-target='" + unselectedMedia.dataset.mediaPreviewInput + "']")].forEach((htmlTargetElement) => showMediaPlaceholder(htmlTargetElement));
}

function showMediaPlaceholder(htmlTargetElement) {
    if (!htmlTargetElement.matches('[data-media-preview-placeholder]') || htmlTargetElement.innerHTML.trim() !== '') return;

    htmlTargetElement.innerHTML = '<img class="img-fluid" data-media-placeholder src="'+htmlTargetElement.dataset.mediaPreviewPlaceholder+'">';
}
