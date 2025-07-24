import {addTargetEventListener, registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('types_section_type', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    // configure sections show url
    addTargetEventListener('select[data-section-widget]', 'change', sectionShowUrl);
    [...document.querySelectorAll('select[data-section-widget]')].forEach((inputSelect) => sectionShowUrl(inputSelect));

    document.addEventListener('change', function (event) {
        if (!event.target ||
            (!event.target.matches('[data-section-message-select]') && !event.target.matches('[data-section-mode-message-select]'))
        ) {
            return;
        }

        sectionMessageSelect(event.target.closest('[data-section-message-container]'));
    });

    // on load, process mesages
    [...document.querySelectorAll('[data-section-message-container]')].forEach((container) => sectionMessageSelect(container));

    // on module add, process messages
    document.addEventListener("collection.node.add.after", function (event) {
        [...event.node().querySelectorAll('[data-section-message-container]')].forEach((container) => sectionMessageSelect(container));
    });

    // on module insert, process messages
    document.addEventListener("collection.node.insert.after", function (event) {
        [...event.node().querySelectorAll('[data-section-message-container]')].forEach((container) => sectionMessageSelect(container));
    });
}

function sectionShowUrl(selectInput) {
    if (selectInput.selectedIndex) {
        selectInput.nextElementSibling.classList.remove('d-none');
        selectInput.nextElementSibling.setAttribute('href', selectInput.options[selectInput.selectedIndex].dataset.sectionUrl);
    } else {
        selectInput.nextElementSibling.classList.add('d-none');
    }
}

function sectionMessageSelect(container) {
    const sectionSelect = container.querySelector('[data-section-message-select]');
    const sectionModeSelect = container.querySelector('[data-section-mode-message-select]');

    let sectionSelectedChoice = sectionSelect.options[sectionSelect.selectedIndex];
    let sectionModeSelectedChoice = sectionModeSelect ? sectionModeSelect.options[sectionModeSelect.selectedIndex] : null;

    let sectionSelectedChoiceValue = sectionSelectedChoice ? sectionSelectedChoice.value : '';
    let sectionModeSelectedChoiceValue = sectionModeSelectedChoice ? sectionModeSelectedChoice.value : '';

    [...container.querySelectorAll('[data-section-message-when]')].forEach(function (message) {
        let show = sectionSelectedChoiceValue !== '' || sectionModeSelectedChoiceValue !== '';

        if (message.dataset.sectionWhenModeEmbedded !== undefined && sectionModeSelectedChoiceValue !== 'embedded') {
            show &= false;
        }

        if (message.dataset.sectionWhenModeEsiAjax !== undefined && sectionModeSelectedChoiceValue === 'embedded') {
            show &= false;
        }

        if (message.dataset.sectionWhenSectionDraft !== undefined && sectionSelectedChoice.dataset.sectionDraft === undefined) {
            show &= false;
        }

        if (message.dataset.sectionWhenSectionPublished !== undefined && sectionSelectedChoice.dataset.sectionDraft !== undefined) {
            show &= false;
        }

        if (message.dataset.sectionWhenSectionTtl !== undefined && sectionSelectedChoice.dataset.sectionTtl === undefined) {
            show &= false;
        } else {
            const ttl = message.querySelector('.ttl');
            if (ttl) {
                ttl.innerText = sectionSelectedChoice.dataset.sectionTtl + 's';
            }
        }

        if (message.dataset.sectionWhenSectionNoTtl !== undefined && sectionSelectedChoice.dataset.sectionTtl !== undefined) {
            show &= false;
        }

        show ? message.classList.remove('d-none') : message.classList.add('d-none');
    });
}