window.addEventListener('load', (event) => {
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        if (!event.target || !event.target.hasAttribute('data-edit-content-enter-inserts-module')) return;

        if (event.ctrlKey) {
            event.target.innerHTML += '<br/>';
            // TODO MOVE CURSOR TO END
            return;
        }

        const module = event.target.closest('[data-collection=node]')
        const moduleCollection = event.target.closest('[data-collection=collection]')
        const prototypes = document.getElementById('module_prototypes_collection_modal');
        const moduleButton = prototypes.querySelector('[data-collection-action=insert][data-module-id=' + event.target.dataset.editContentEnterInsertsModule + ']');

        // module does not exists
        if (!moduleButton) {
            console.error('Module '+event.target.dataset.editContentEnterInsertsModule+' not found. Check data-edit-content-enter-inserts-module attribute');
            return;
        }

        moduleButton.dataset.collectionTarget = moduleCollection.id;
        moduleButton.dataset.collectionInsertPosition = '' + (parseInt(module.dataset.collectionIndex) + 1);
        moduleButton.click();

        event.preventDefault();
        return false;
    });
});