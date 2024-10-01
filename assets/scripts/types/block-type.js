(function () {
    if (!window.__sfs_cms_types_block_type_registered) {
        window.addEventListener('load', _register);
    }
    window.__sfs_cms_types_block_type_registered = true;
})();

function _register() {
    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.matches('[data-block-message-select]')) {
            return;
        }

        blockMessageSelect(event.target);
    });

    function blockMessageSelect (select) {
        let selectedChoice = select.options[select.selectedIndex];

        [...select.parentElement.querySelectorAll('[data-block-message-when]')].forEach(function (message) {
            let show = selectedChoice.value !== '';

            if (message.dataset.blockWhenNotEsi !== undefined && selectedChoice.dataset.blockEsi !== undefined) {
                show &= false;
            }

            if (message.dataset.blockWhenEsi !== undefined && selectedChoice.dataset.blockEsi == undefined) {
                show &= false;
            }

            if (message.dataset.blockWhenNotSchedulable !== undefined && selectedChoice.dataset.blockSchedulable !== undefined) {
                show &= false;
            }

            if (message.dataset.blockWhenSchedulable !== undefined && selectedChoice.dataset.blockSchedulable === undefined) {
                show &= false;
            }

            show ? message.classList.remove('d-none') : message.classList.add('d-none');
        });
    }

    // on load, process mesages
    [...document.querySelectorAll('[data-block-message-select]')].forEach((select) => blockMessageSelect(select));

    // on module add, process messages
    document.addEventListener("collection.node.add.after", function (event) {
        [...event.node().querySelectorAll('[data-block-message-select]')].forEach((select) => blockMessageSelect(select));
    });

    // on module insert, process messages
    document.addEventListener("collection.node.insert.after", function (event) {
        [...event.node().querySelectorAll('[data-block-message-select]')].forEach((select) => blockMessageSelect(select));
    });
}
