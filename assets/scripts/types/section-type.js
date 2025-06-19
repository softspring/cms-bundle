import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('types_section_type', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.matches('[data-section-message-select]')) {
            return;
        }

        sectionMessageSelect(event.target);
    });

    // on load, process mesages
    [...document.querySelectorAll('[data-section-message-select]')].forEach((select) => sectionMessageSelect(select));

    // on module add, process messages
    document.addEventListener("collection.node.add.after", function (event) {
        [...event.node().querySelectorAll('[data-section-message-select]')].forEach((select) => sectionMessageSelect(select));
    });

    // on module insert, process messages
    document.addEventListener("collection.node.insert.after", function (event) {
        [...event.node().querySelectorAll('[data-section-message-select]')].forEach((select) => sectionMessageSelect(select));
    });
}

function sectionMessageSelect (select) {
    let selectedChoice = select.options[select.selectedIndex];

    [...select.parentElement.querySelectorAll('[data-section-message-when]')].forEach(function (message) {
        let show = selectedChoice.value !== '';

        // if (message.dataset.sectionWhenNotEsi !== undefined && selectedChoice.dataset.sectionEsi !== undefined) {
        //     show &= false;
        // }
        //
        // if (message.dataset.sectionWhenEsi !== undefined && selectedChoice.dataset.sectionEsi == undefined) {
        //     show &= false;
        // }
        //
        // if (message.dataset.sectionWhenNotSchedulable !== undefined && selectedChoice.dataset.sectionSchedulable !== undefined) {
        //     show &= false;
        // }

        if (message.dataset.sectionWhenDraft !== undefined && selectedChoice.dataset.sectionDraft === undefined) {
            show &= false;
        }

        show ? message.classList.remove('d-none') : message.classList.add('d-none');
    });
}