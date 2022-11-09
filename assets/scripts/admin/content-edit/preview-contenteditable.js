window.addEventListener('load', (event) => {
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
            htmlTargetElements.forEach(function(htmlTargetElement) {
                if (htmlTargetElement.dataset.editContentEscape) {
                    htmlTargetElement.innerText = event.target.value;
                } else {
                    htmlTargetElement.innerHTML = event.target.value;
                }

                if (htmlTargetElement.dataset.editContentHideIfEmpty) {
                    if (htmlTargetElement.innerHTML === '') {
                        htmlTargetElement.style.setProperty('display', 'none');
                    } else if (htmlTargetElement.matches('[data-lang='+getSelectedLanguage()+']')) {
                        htmlTargetElement.style.setProperty('display', '');
                    }
                }
            });
        }
    });

    document.querySelectorAll('[data-edit-content-hide-if-empty]:empty').forEach((htmlElement) => htmlElement.style.setProperty('display', 'none'));

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
            htmlTargetElements.forEach(function(htmlTargetElement) {
              htmlTargetElement.value = event.target.innerHTML;
              htmlTargetElement.setAttribute('value', event.target.innerHTML);//Fixed empty value when element is new and is moved
            });
        }
    });
});
