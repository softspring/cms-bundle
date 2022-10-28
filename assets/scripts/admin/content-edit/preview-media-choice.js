window.addEventListener('load', (event) => {
    /**
     * Shows a media preview
     *
     * The preview target element must have the "data-media-preview-target" attribute
     * The select option must have the "data-media-preview-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.hasAttribute('data-media-preview-input')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = moduleForm.querySelectorAll("[data-media-preview-target='" + event.target.dataset.mediaPreviewInput + "']");
        if (htmlTargetElements.length) {
            if (event.target.options[event.target.selectedIndex].dataset.mediaPreviewPicture) {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = event.target.options[event.target.selectedIndex].dataset.mediaPreviewPicture);
            } else if (event.target.options[event.target.selectedIndex].dataset.mediaPreviewImage) {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = event.target.options[event.target.selectedIndex].dataset.mediaPreviewImage);
            } else if (event.target.options[event.target.selectedIndex].dataset.mediaPreviewVideo) {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = event.target.options[event.target.selectedIndex].dataset.mediaPreviewVideo);
            } else {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = '');
            }
        }
    });
});