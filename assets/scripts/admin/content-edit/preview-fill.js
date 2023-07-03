window.addEventListener('load', (event) => {

    const kebabize = (str) => str.replace(/[A-Z]+(?![a-z])|[A-Z]/g, ($, ofs) => (ofs ? "-" : "") + $.toLowerCase());

    /**
     * Adds id field to target element
     *
     * The preview target element must have the "data-edit-id-target" attribute
     * The input field must have the "data-edit-id-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('change', function (event) {
        if (!event.target) return;

        let moduleEdit = event.target.closest('.cms-module-edit');

        if (!moduleEdit) {
            return;
        }

        let dataAttributes = Object.keys(event.target.dataset);
        dataAttributes.forEach(function (dataAttribute) {
            if (!dataAttribute.startsWith('editFillInput')) { return; }

            let targetField = dataAttribute.replace('editFillInput', '');
            let kebabizedTargetField = kebabize(targetField);

            let sourceField = event.target.dataset[dataAttribute];
            let kebabizedSourceField = kebabize(sourceField);
            let sourceElement = event.target.options == undefined ? event.target : event.target.options[event.target.selectedIndex];

            let htmlTargetElements = moduleEdit.querySelectorAll("[data-edit-fill-target-" + kebabizedTargetField + "]");
            if (htmlTargetElements.length) {
                htmlTargetElements.forEach(function (htmlTargetElement) {
                    let value = sourceElement.getAttribute('data-'+kebabizedSourceField);
                    if (htmlTargetElement.hasAttribute('data-lang') && sourceElement.hasAttribute('data-'+kebabizedSourceField+'-'+htmlTargetElement.dataset.lang)) {
                        value = sourceElement.getAttribute('data-'+kebabizedSourceField+'-'+htmlTargetElement.dataset.lang);
                    }
                    if (htmlTargetElement.hasAttribute('data-input-lang') && sourceElement.hasAttribute('data-'+kebabizedSourceField+'-'+htmlTargetElement.dataset.inputLang)) {
                        value = sourceElement.getAttribute('data-'+kebabizedSourceField+'-'+htmlTargetElement.dataset.inputLang);
                    }

                    if (htmlTargetElement.tagName === 'INPUT') {
                        htmlTargetElement.value = value;
                        htmlTargetElement.dispatchEvent(new Event('change'));
                    } else {
                        htmlTargetElement.innerHTML = value;
                    }
                });
            }
        });
    });
});
