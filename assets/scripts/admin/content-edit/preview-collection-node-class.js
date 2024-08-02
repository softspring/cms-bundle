window.addEventListener('load', (event) => {
    document.addEventListener('input', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-collection-node-class')) return;

        let nodeClass = event.target.value;
        const modulePreview = event.target.closest('.cms-module-edit').querySelector('.module-preview');
        const moduleEditForm = modulePreview.nextElementSibling;
        const customClassInput = moduleEditForm.querySelector("input[type=text][data-edit-collection-node-class='" + event.target.dataset.editCollectionNodeClass + "']");

        if (event.target.nodeName == 'SELECT' && event.target.value && customClassInput.value) {
            customClassInput.value = '';
            customClassInput.setAttribute('value', '');
        }

        if (!nodeClass) {
            if (!customClassInput.value) {
                customClassInput.value = 'col';
                customClassInput.setAttribute('value', 'col');
            }

            nodeClass = customClassInput.value;
        }

        let collectionTargetElements = modulePreview.querySelectorAll("[data-edit-collection-node-class-target='" + event.target.dataset.editCollectionNodeClass + "'] > [data-collection=collection]");
        if (collectionTargetElements.length) {
            collectionTargetElements.forEach(function (collectionTargetElement) {
                collectionTargetElement.setAttribute('data-module-row-class', nodeClass);
                [...collectionTargetElement.querySelectorAll(':scope > [data-collection=node]')].forEach((node) => node.setAttribute('class', nodeClass));
                [...collectionTargetElement.querySelectorAll(':scope > .insert-module-at-the-end')].forEach((node) => node.setAttribute('class', 'insert-module-at-the-end '+nodeClass));
            });
        }
    });

    function showOrHideCustomClass(selectElement) {
        const modulePreview = selectElement.closest('.cms-module-edit').querySelector('.module-preview');
        const moduleEditForm = modulePreview.nextElementSibling;
        const customClassInput = moduleEditForm.querySelector("input[type=text][data-edit-collection-node-class='" + selectElement.dataset.editCollectionNodeClass + "']");
        const customClassInputRow = customClassInput.parentElement.parentElement;

        if (selectElement.value) {
            customClassInputRow.classList.add('d-none');
        } else {
            customClassInputRow.classList.remove('d-none');

            if (!customClassInput.value) {
                customClassInput.value = 'col';
                customClassInput.setAttribute('value', 'col');
            }
        }
    }

    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-collection-node-class') || event.target.nodeName != 'SELECT') return;
        showOrHideCustomClass(event.target);
    });

    [...document.querySelectorAll('select[data-edit-collection-node-class]')].forEach((select) => showOrHideCustomClass(select));
});
