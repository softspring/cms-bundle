import {cmsEditListener} from './event-listeners';
import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_content_enter_inserts_module', _init);

function _init() {
    cmsEditListener('[data-edit-content-enter-inserts-module]', 'keydown', onEditContentEnterInsertsModule);
}

function onEditContentEnterInsertsModule(inputElement, module, preview, form, event) {
    if (event.key !== 'Enter') return;

    if (event.ctrlKey) {
        inputElement.innerHTML += '<br/>';
        // TODO MOVE CURSOR TO END
        return;
    }

    module = inputElement.closest('[data-collection=node]')
    const moduleCollection = inputElement.closest('[data-collection=collection]')
    const prototypes = document.getElementById('module_prototypes_collection_modal');
    const moduleButton = prototypes.querySelector('[data-collection-action=insert][data-module-id=' + inputElement.dataset.editContentEnterInsertsModule + ']');

    // module does not exists
    if (!moduleButton) {
        console.error('Module ' + inputElement.dataset.editContentEnterInsertsModule + ' not found. Check data-edit-content-enter-inserts-module attribute');
        return;
    }

    moduleButton.dataset.collectionTarget = moduleCollection.id;
    moduleButton.dataset.collectionInsertPosition = '' + (parseInt(module.dataset.collectionIndex) + 1);
    moduleButton.click();

    event.preventDefault();
    return false;
}
