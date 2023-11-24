import { getSelectedLanguage } from './filter-preview';

window.addEventListener('load', (event) => {
    let cssCodeNotValidatedWarningShown = false;

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
                let value = event.target.value;

                if (htmlTargetElement.dataset.editContentValidate) {
                    switch (htmlTargetElement.dataset.editContentValidate) {
                        case 'html':
                            let valueToParse = '<body>'+value+'</body>';
                            valueToParse = valueToParse.replace(new RegExp('\n', 'g'), '');
                            let parser = new DOMParser();
                            let doc = parser.parseFromString(valueToParse, "application/xml");
                            let errorNode = doc.querySelector('parsererror');
                            if (errorNode) {
                                value = errorNode.innerText;
                                htmlTargetElement.classList.add('text-error');
                                htmlTargetElement.classList.add('border');
                                htmlTargetElement.classList.add('border-danger');
                            } else {
                                htmlTargetElement.classList.remove('text-error');
                                htmlTargetElement.classList.remove('border');
                                htmlTargetElement.classList.remove('border-danger');
                            }
                            break;

                        case 'css':
                            if (!cssCodeNotValidatedWarningShown) {
                                console.warn('Sorry, css code is not yet validated');
                                cssCodeNotValidatedWarningShown = true;
                            }
                            break;
                    }
                }

                if (htmlTargetElement.dataset.editContentEscape) {
                    htmlTargetElement.innerText = value;
                } else {
                    value = value.replace(new RegExp('href', 'g'), 'href-invalidate');
                    htmlTargetElement.innerHTML = value;
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

        let htmlInputElements = moduleForm.querySelectorAll("[data-edit-content-input='" + event.target.dataset.editContentTarget + "']");
        if (htmlInputElements.length) {
            htmlInputElements.forEach(function(htmlInputElement) {
              htmlInputElement.value = event.target.innerHTML;
              htmlInputElement.setAttribute('value', event.target.innerHTML);//Fixed empty value when element is new and is moved
            });
        }

        if (event.target.dataset.editContentHideIfEmpty) {
            if (event.target.innerHTML === '') {
                event.target.style.setProperty('display', 'none');
            } else if (event.target.matches('[data-lang='+getSelectedLanguage()+']')) {
                event.target.style.setProperty('display', '');
            }
        }
    });

    /** ************************************************************************************************************ *
     * Highlighting input and target elements on focus on linked elements
     * ************************************************************************************************************* */

    /**
     * Show accordion item, same as click on it
     */
    function accordionItemShow(accordionItem)    {
        let formGroupCollapse = accordionItem.querySelector('.collapse');
        let formGroupButton = accordionItem.querySelector('.accordion-header button');

        formGroupCollapse.classList.add('show');
        formGroupCollapse.classList.remove('d-none');
        formGroupButton.setAttribute('aria-expanded', 'true');
        formGroupButton.classList.remove('collapsed');
    }

    /**
     * Hide accordion item
     */
    function accordionItemHide(accordionItem)    {
        let formGroupCollapse = accordionItem.querySelector('.collapse');
        let formGroupButton = accordionItem.querySelector('.accordion-header button');

        formGroupCollapse.classList.remove('show');
        formGroupCollapse.classList.add('d-none');
        formGroupButton.setAttribute('aria-expanded', 'false');
        formGroupButton.classList.add('collapsed');
    }

    /**
     * Show form group accordion item, and hide the rest
     */
    function showFormGroup(accordionElement)    {
        accordionItemShow(accordionElement);

        let accordion = accordionElement.closest('.accordion');
        let accordionItems = accordion.querySelectorAll('.accordion-item');
        accordionItems.forEach(function(accordionItem) {
            if (accordionItem !== accordionElement) {
                accordionItemHide(accordionItem);
            }
        });
    }

    /**
     * On focus in and input, highlights the target element
     */
    window.addEventListener('focusin', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-content-target='" + event.target.dataset.editContentInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach(function(htmlTargetElement) {
                htmlTargetElement.scrollIntoView({ behavior: "smooth", block: "center", inline: "center" });
                htmlTargetElement.classList.add('edit-content-linked-focus');
            });
        }
    });

    /**
     * On focus in a content editable element, highlights the input element
     */
    window.addEventListener('focusin', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-target')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.cms-module-form');

        let htmlInputElements = moduleForm.querySelectorAll("[data-edit-content-input='" + event.target.dataset.editContentTarget + "']");
        if (htmlInputElements.length) {
            htmlInputElements.forEach(function(htmlInputElement) {
                htmlInputElement.scrollIntoView({ behavior: "smooth", block: "center", inline: "center" });
                htmlInputElement.classList.add('edit-content-linked-focus');
                showFormGroup(htmlInputElement.closest('.accordion-item'));
            });
        }
    });

    /**
     * On focus out and input, un-highlights the target element
     */
    window.addEventListener('focusout', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-content-target='" + event.target.dataset.editContentInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach(function(htmlTargetElement) {
                htmlTargetElement.classList.remove('edit-content-linked-focus');
            });
        }
    });

    /**
     * On focus out a content editable element, un-highlights the input element
     */
    window.addEventListener('focusout', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-target')) return;

        let moduleForm = event.target.closest('.cms-module-edit').querySelector('.cms-module-form');

        let htmlInputElements = moduleForm.querySelectorAll("[data-edit-content-input='" + event.target.dataset.editContentTarget + "']");
        if (htmlInputElements.length) {
            htmlInputElements.forEach(function(htmlInputElement) {
                htmlInputElement.classList.remove('edit-content-linked-focus');
            });
        }
    });
});
