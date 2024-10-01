(function () {
    if (!window.__sfs_cms_content_edit_preview_fill_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_content_edit_preview_fill_registered = true;
})();

function showFields(fieldsConcatenated) {
    const fields = fieldsConcatenated.split(',');
    fields.forEach((field) => {
        const fieldContainer = document.querySelector('[data-field-container="' + field + '"]');
        if (fieldContainer) {
            fieldContainer.showElement();
        }
    });
}

function hideFields(fieldsConcatenated) {
    const fields = fieldsConcatenated.split(',');
    fields.forEach((field) => {
        const fieldContainer = document.querySelector('[data-field-container="' + field + '"]');
        if (fieldContainer) {
            fieldContainer.hideElement();
        }
    });
}

function emptyFields(fieldsConcatenated) {
    const fields = fieldsConcatenated.split(',');
    fields.forEach((field) => {
        const fieldContainer = document.querySelector('[data-field-container="' + field + '"]');
        if (fieldContainer) {
            fieldContainer.querySelectorAll('input, select, textarea').forEach((input) => {
                input.value = '';
            });
        }
    });
}

function initFields(base) {
    base.querySelectorAll('input[type=radio]').forEach((radio) => {
        if (radio.checked) {
            radio.dataset.showFields !== undefined && showFields(radio.dataset.showFields);
            radio.dataset.hideFields !== undefined && hideFields(radio.dataset.hideFields);
            radio.dataset.emptyFields !== undefined && emptyFields(radio.dataset.emptyFields);
        }
    });

    base.querySelectorAll('input[type=checkbox]').forEach((checkbox) => {
        if (checkbox.checked) {
            checkbox.dataset.showFieldsIfChecked !== undefined && showFields(checkbox.dataset.showFieldsIfChecked);
            checkbox.dataset.hideFieldsIfChecked !== undefined && hideFields(checkbox.dataset.hideFieldsIfChecked);
            checkbox.dataset.emptyFieldsIfChecked !== undefined && emptyFields(checkbox.dataset.emptyFieldsIfChecked);
        } else {
            checkbox.dataset.showFieldsIfUnchecked !== undefined && showFields(checkbox.dataset.showFieldsIfUnchecked);
            checkbox.dataset.hideFieldsIfUnchecked !== undefined && hideFields(checkbox.dataset.hideFieldsIfUnchecked);
            checkbox.dataset.emptyFieldsIfUnchecked !== undefined && emptyFields(checkbox.dataset.emptyFieldsIfUnchecked);
        }
    });

    base.querySelectorAll('select').forEach((select) => {
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption) {
            selectedOption.dataset.showFields !== undefined && showFields(selectedOption.dataset.showFields);
            selectedOption.dataset.hideFields !== undefined && hideFields(selectedOption.dataset.hideFields);
            selectedOption.dataset.emptyFields !== undefined && emptyFields(selectedOption.dataset.emptyFields);
        }
    });
}

function _register() {

    initFields(document);

    document.addEventListener("collection.node.insert.after", function (event) {
        initFields(event.target);
    });

    // RADIO BUTTONS

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'radio' || !event.target.matches('[data-show-fields]')) return;
        showFields(event.target.dataset.showFields);
    });

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'radio' || !event.target.matches('[data-hide-fields]')) return;
        hideFields(event.target.dataset.hideFields);
    });

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'radio' || !event.target.matches('[data-empty-fields]')) return;
        emptyFields(event.target.dataset.emptyFields);
    });

    // CHECKBOX BUTTONS

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'checkbox' || !event.target.matches('[data-show-fields-if-checked]')) return;
        event.target.checked && showFields(event.target.dataset.showFieldsIfChecked);
    });
    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'checkbox' || !event.target.matches('[data-show-fields-if-unchecked]')) return;
        !event.target.checked && showFields(event.target.dataset.showFieldsIfUnchecked);
    });

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'checkbox' || !event.target.matches('[data-hide-fields-if-checked]')) return;
        event.target.checked && hideFields(event.target.dataset.hideFieldsIfChecked);
    });
    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'checkbox' || !event.target.matches('[data-hide-fields-if-unchecked]')) return;
        !event.target.checked && hideFields(event.target.dataset.hideFieldsIfUnchecked);
    });

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'checkbox' || !event.target.matches('[data-empty-fields-if-checked]')) return;
        event.target.checked && emptyFields(event.target.dataset.emptyFieldsIfChecked);
    });
    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'INPUT' || event.target.getAttribute('type') !== 'checkbox' || !event.target.matches('[data-empty-fields-if-unchecked]')) return;
        !event.target.checked && emptyFields(event.target.dataset.emptyFieldsIfUnchecked);
    });


    // SELECTS

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'SELECT') return;
        const selectedOption = event.target.options[event.target.selectedIndex];
        if (!selectedOption || !selectedOption.matches('[data-show-fields]')) return;
        showFields(selectedOption.dataset.showFields);
    });

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'SELECT') return;
        const selectedOption = event.target.options[event.target.selectedIndex];
        if (!selectedOption || !selectedOption.matches('[data-hide-fields]')) return;
        hideFields(selectedOption.dataset.hideFields);
    });

    document.addEventListener('change', function (event) {
        if (event.target.tagName !== 'SELECT') return;
        const selectedOption = event.target.options[event.target.selectedIndex];
        if (!selectedOption || !selectedOption.matches('[data-empty-fields]')) return;
        emptyFields(selectedOption.dataset.emptyFields);
    });
}
