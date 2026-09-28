import {registerFeature} from '@softspring/cms-bundle/scripts/tools.js';

registerFeature('admin_content_edit_preview_toggle', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    /**
     * Toggles content from input
     *
     * The toggle target element must have the "data-edit-content-toggle-target" attribute
     * The input field must have the "data-edit-content-toggle-input"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-toggle-input')) return;

        let modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        let visible = event.target.type == 'checkbox' ? event.target.checked : event.target.value;

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-content-toggle-target='" + event.target.dataset.editContentToggleInput + "']");
        if (htmlTargetElements.length) {
            htmlTargetElements.forEach(function (htmlTargetElement) {
                if (visible) {
                    htmlTargetElement.classList.remove('d-none');
                } else {
                    htmlTargetElement.classList.add('d-none');
                }
            });
        }
    });

    /**
     * Toggles content from choice (select or radio elements)
     *
     * The toggle target element must have the "data-edit-content-toggle-choice-target" attribute
     *  and data-edit-content-toggle-choice-target-values that have a list of options values
     * The choice field must have the "data-edit-content-toggle-choice"
     * Both data attributes must have the same value (as identificator)
     */
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-toggle-choice')) return;

        updateChoice(event.target);
    });

    function initializeChoices() {
        // Initialize all choice elements on load
        document.querySelectorAll('[data-edit-content-toggle-choice]').forEach(function(choiceField) {
            updateChoice(choiceField);
        });
    }

    document.addEventListener("collection.node.add.after", function () { // (1)
        initializeChoices();
    });

    document.addEventListener("collection.node.insert.after", function () { // (1)
        initializeChoices();
    });

    initializeChoices();

    function updateChoice(choiceField) {
        // Check that it is a select or radio input
        let isSelect = choiceField.tagName === 'SELECT';

        // TODO add support for radio buttons

        if (!isSelect) {
            console.error('data-edit-content-toggle-choice feature only can be applied to selects')
            return;
        }

        let modulePreview = choiceField.closest('.cms-module-edit').querySelector('.module-preview');
        let selectedValue = choiceField.type == 'radio' ? choiceField.checked : choiceField.value;

        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-content-toggle-choice-target='" + choiceField.dataset.editContentToggleChoice + "']");

        if (htmlTargetElements.length) {
            htmlTargetElements.forEach(function (htmlTargetElement) {
                let targetValues = htmlTargetElement.dataset.editContentToggleChoiceTargetValues;

                // If it has specific values, check whether the selected value is in the list
                if (targetValues) {
                    let valuesArray = targetValues.split(',').map(v => v.trim());
                    let shouldShow = valuesArray.includes(String(selectedValue));

                    if (shouldShow) {
                        htmlTargetElement.classList.remove('d-none');
                    } else {
                        htmlTargetElement.classList.add('d-none');
                    }
                } else {
                    // If it has no specific values, use boolean behavior
                    if (selectedValue) {
                        htmlTargetElement.classList.remove('d-none');
                    } else {
                        htmlTargetElement.classList.add('d-none');
                    }
                }
            });
        }
    }
};
