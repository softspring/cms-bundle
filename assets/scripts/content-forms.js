window.addEventListener('load', (event) => {

    /* ****************************************************************************************************** *
     * CONTENT MODULES MANAGEMENT
     * ****************************************************************************************************** */

    function moduleFocus(module) {
        allLostFocus();
        module.classList.add('active');
        document.getElementById('content-form').classList.remove('d-none');
    }

    function allLostFocus() {
        document.getElementById('content-form').classList.add('d-none');
        document.querySelectorAll('.cms-module').forEach((element) => element.classList.remove('active'));
    }

    // close module edit form
    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.hasAttribute('data-cms-module-form-close')) return;
        event.preventDefault();
        event.stopPropagation();
        allLostFocus();
    });

    // on module focus get focus
    document.addEventListener('click', function (event) {
        // prevent focus on close button
        if (event.target && event.target.hasAttribute('data-cms-module-form-close')) return;

        for (i = 0; i < event.composedPath().length; i++) {
            if (event.composedPath()[i] instanceof Element && event.composedPath()[i].matches('.cms-module')) {
                moduleFocus(event.composedPath()[i]);
                return;
            }
        }
    });

    /* ****************************************************************************************************** *
     * LANGUAGE DEPENDANT FIELDS
     * ****************************************************************************************************** */

    function selectTranslatableElementsLanguage(language) {
        document.querySelectorAll('[data-lang]').forEach((el) => el.style.setProperty('display', 'none'));
        document.querySelectorAll('[data-lang='+language+']').forEach((el) => el.style.setProperty('display', ''));
    }

    function filterCurrentTranslatableElementsLanguage() {
        const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
        contentEditionLanguageSelector.addEventListener('change', function (event) {
            selectTranslatableElementsLanguage(event.target.value);
        });

        selectTranslatableElementsLanguage(contentEditionLanguageSelector.value);
    }

    filterCurrentTranslatableElementsLanguage();

    /* ****************************************************************************************************** *
     * INLINE HTML EDITION
     * ****************************************************************************************************** */

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

    /**
     * Previews content from input
     *
     * The preview target element must have the "data-edit-content-target" attribute
     * The input field must have the "data-edit-content-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-content-target='" + event.target.dataset.editContentInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = event.target.value);
        }
    });

    /**
     * Sets input value from html editable element
     *
     * The preview target element must have the "data-edit-content-target" attribute
     * The input field must have the "data-edit-content-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-target')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.cms-module-form');

        let htmlTargetElements = moduleForm.querySelectorAll("[data-edit-content-input='" + event.target.dataset.editContentTarget + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.value = event.target.innerHTML);
        }
    });

    /**
     * Shows an image preview
     *
     * The preview target element must have the "data-image-preview-target" attribute
     * The select option must have the "data-image-preview-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.hasAttribute('data-image-preview-input')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = moduleForm.querySelectorAll("[data-image-preview-target='" + event.target.dataset.imagePreviewInput + "']");
        if (htmlTargetElements.length) {
            if (event.target.options[event.target.selectedIndex].dataset.imagePreviewPicture) {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = event.target.options[event.target.selectedIndex].dataset.imagePreviewPicture);
            } else if (event.target.options[event.target.selectedIndex].dataset.imagePreviewImage) {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = event.target.options[event.target.selectedIndex].dataset.imagePreviewImage);
            } else {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.innerHTML = '');
            }
        }
    });

    /* ****************************************************************************************************** *
     * CONFIGURE CUSTOM MODULES
     * ****************************************************************************************************** */

    document.addEventListener("add_polymorphic_node", function (event) { // (1)
        const tinymceFields = event.target.getElementsByClassName('tinymce');
        if (tinymceFields) {
            for (let i = 0; i < tinymceFields.length; i++) {
                const tinymceField = tinymceFields[i];
                tinymce.init({
                    selector: '#' + tinymceField.id,
                    plugins: '',
                });
            }
        }
    });

    document.addEventListener("add_polymorphic_node", function (event) { // (1)
        var module = event.target.querySelector('.cms-module');
        if (module) {
            moduleFocus(module);
        }
        filterCurrentTranslatableElementsLanguage();
    });

    document.addEventListener("removed_polymorphic_node", function (event) { // (1)
        // console.log(event);
    });
});
