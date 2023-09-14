import {
    getPolymorphicCollectionLastIndex
} from '@softspring/polymorphic-form-type/scripts/polymorphic-form-type';

window.addEventListener('load', (event) => {
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;
        if (!event.target || !event.target.hasAttribute('data-edit-content-enter-inserts-module')) return;

        if (event.ctrlKey) {
            event.target.innerHTML += '<br/>';
            // TODO MOVE CURSOR TO END
            return;
        }

        const module = event.target.closest('[data-polymorphic=node]')
        const moduleCollection = event.target.closest('[data-polymorphic=collection]')
        const prototypes = document.getElementById('module_prototypes_collection_modal');
        const moduleButton = prototypes.querySelector('[data-polymorphic-action=insert][data-module-id=' + event.target.dataset.editContentEnterInsertsModule + ']');

        // module does not exists
        if (!moduleButton) {
            console.error('Module '+event.target.dataset.editContentEnterInsertsModule+' not found. Check data-edit-content-enter-inserts-module attribute');
            return;
        }

        moduleButton.dataset.polymorphicCollection = moduleCollection.id;
        moduleButton.dataset.polymorphicCollectionInsertPosition = '' + (parseInt(module.dataset.index) + 1);
        moduleButton.click();

        event.preventDefault();
        return false;
    });
});