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
        document.querySelectorAll('[data-lang='+language+']').forEach((el) => el.classList.remove('d-none'));
        document.querySelectorAll('[data-lang]:not([data-lang='+language+'])').forEach((el) => el.classList.add('d-none'));
    }

    const contentEditionLanguageSelector = document.getElementById('contentEditionLanguageSelection');
    contentEditionLanguageSelector.addEventListener('change', function (event) {
        selectTranslatableElementsLanguage(event.target.value);
    });

    selectTranslatableElementsLanguage(contentEditionLanguageSelector.value);

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

        let htmlTargetElement = modulePreview.querySelector("[data-edit-id-target='" + event.target.dataset.editIdInput + "']");
        if (htmlTargetElement) {
            htmlTargetElement.id = event.target.value;
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

        let htmlTargetElement = modulePreview.querySelector("[data-edit-class-target='" + event.target.dataset.editClassInput + "']");
        if (htmlTargetElement) {
            htmlTargetElement.className = htmlTargetElement.dataset.editClassDefault + ' ' + event.target.value;
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

        let htmlTargetElement = modulePreview.querySelector("[data-edit-bgcolor-target='" + event.target.dataset.editBgcolorInput + "']");
        if (htmlTargetElement) {
            htmlTargetElement.style.backgroundColor = event.target.value;
            // htmlTargetElement.style.setProperty('background-color', event.target.value, 'important');
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

        let htmlTargetElement = modulePreview.querySelector("[data-edit-content-target='" + event.target.dataset.editContentInput + "']");
        if (htmlTargetElement) {
            htmlTargetElement.innerHTML = event.target.value;
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

        let htmlTargetElement = moduleForm.querySelector("[data-edit-content-input='" + event.target.dataset.editContentTarget + "']");
        if (htmlTargetElement) {
            htmlTargetElement.value = event.target.innerHTML;
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

    document.addEventListener("removed_polymorphic_node", function (event) { // (1)
        // console.log(event);
    });
});