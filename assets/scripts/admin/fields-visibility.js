window.addEventListener('load', _init);

function showFields(fieldsConcatenated) {
    const fields = fieldsConcatenated.split(',');
    fields.forEach((field) => {
        const fieldContainer = document.querySelector('[data-field-container="' + field + '"]');
        if (fieldContainer) {
            fieldContainer.show();
        }
    });
}

function hideFields(fieldsConcatenated) {
    const fields = fieldsConcatenated.split(',');
    fields.forEach((field) => {
        const fieldContainer = document.querySelector('[data-field-container="' + field + '"]');
        if (fieldContainer) {
            fieldContainer.hide();
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

    base.querySelectorAll('select').forEach((select) => {
        const selectedOption = select.options[select.selectedIndex];

        if (selectedOption) {
            selectedOption.dataset.showFields !== undefined && showFields(selectedOption.dataset.showFields);
            selectedOption.dataset.hideFields !== undefined && hideFields(selectedOption.dataset.hideFields);
            selectedOption.dataset.emptyFields !== undefined && emptyFields(selectedOption.dataset.emptyFields);
        }
    });
}

function _init() {

    initFields(document);

    document.addEventListener("polymorphic.node.insert.after", function (event) {
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