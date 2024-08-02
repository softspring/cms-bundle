import { filterCurrentFilterElements } from './filter-preview';
import { CollectionEvent,getCollectionLastIndex } from '@softspring/collection-form-type/scripts/collection-form-type';
import { Modal } from 'bootstrap';

window.addEventListener('load', (event) => {
    function moduleFocus(module) {
        allLostFocus();
        module.classList.add('active');

        document.getElementById('content-form').classList.remove('d-none');
        // If has form to show
        if(module.closest('div').querySelector('.active >.cms-module-body > .cms-module-edit > .cms-module-form')) {
            document.querySelectorAll('[data-collection=collection]').forEach((element) => element.classList.add('has-form'));
        }
    }

    function allLostFocus() {
        document.getElementById('content-form').classList.add('d-none');
        document.querySelectorAll('.cms-module').forEach((element) => element.classList.remove('active'));
        document.querySelectorAll('[data-collection=collection]').forEach((element) => element.classList.remove('has-form'));
    }

    // close module edit form
    document.addEventListener('click', function (event) {
        if (!event.target || !event.target.hasAttribute('data-cms-module-form-close')) return;
        event.preventDefault();
        event.stopPropagation();
        allLostFocus();
    });

    // on module focus get focus
    document.addEventListener('click', function (event) {
        // prevent focus on close button
        if (event.target && (event.target.hasAttribute('data-cms-module-form-close') || event.target.matches('[data-collection-action=delete]'))) {
            if (event.target.matches('[data-collection-action=delete]')) {
                allLostFocus();
            }
            return;
        }

        for (let i = 0; i < event.composedPath().length; i++) {
            if (event.composedPath()[i] instanceof Element && event.composedPath()[i].matches('.cms-module')) {
                moduleFocus(event.composedPath()[i]);
                return;
            }
        }
    });

    document.addEventListener("collection.node.insert.after", function (event) { // (1)
        var module = event.target.querySelector('.cms-module');
        if (module) {
            moduleFocus(module);
        }
        filterCurrentFilterElements();
    });

    document.addEventListener("collection.node.add.after", function (event) { // (1)
        var module = event.target.querySelector('.cms-module');
        if (module) {
            moduleFocus(module);
        }
        filterCurrentFilterElements();
    });

    document.addEventListener("collection.node.duplicate.after", function (event) { // (1)
        var module = event.target.querySelector('.cms-module');
        if (module) {
            moduleFocus(module);
        }
        filterCurrentFilterElements();
    });

    const prototypesModal = document.getElementById('module_prototypes_collection_modal');
    let modulesCollection;
    let modulesCollectionInsertIndex;
    let insertElement;

    prototypesModal && prototypesModal.addEventListener('show.bs.modal', function (event) {
        insertElement = event.relatedTarget;

        insertElement.classList.add('selected');

        if (insertElement.dataset.collectionTarget !== undefined) {
            modulesCollection = document.getElementById(insertElement.dataset.collectionTarget);
        } else {
            modulesCollection = insertElement.closest('[data-collection=collection]');
        }

        // get collection elements
        const nodeRow = insertElement.closest('[data-collection=node]');
        const modulesAllowed = modulesCollection.dataset.modulesAllowed.split(',');

        let action = 'Add';
        modulesCollectionInsertIndex = null;
        if (insertElement.matches('[data-collection-position=after]')) {
            action = 'Insert after';
            modulesCollectionInsertIndex = nodeRow ? nodeRow.dataset.index : null;
        } else if (insertElement.matches('[data-collection-position=before]')) {
            action = 'Insert before';
            modulesCollectionInsertIndex = nodeRow ? nodeRow.dataset.index : null;
        }

        // hide not allowed modules
        prototypesModal.querySelectorAll('[data-module-id]').forEach((el) => modulesAllowed.includes(el.dataset.moduleId) ? el.parentElement.classList.remove('d-none') : el.parentElement.classList.add('d-none'));

        prototypesModal.querySelectorAll('.modal-modules-group').forEach(function (modulesGroup) {
            if (modulesGroup.querySelectorAll('.modal-module:not(.d-none)').length) {
                modulesGroup.classList.remove('d-none');
            } else {
                modulesGroup.classList.add('d-none');
            }
        });
    });

    prototypesModal && prototypesModal.addEventListener('hide.bs.modal', function (event) {
        [...document.getElementsByClassName('insert-module')].forEach((element) => element.classList.remove('selected'));
        // insertElement.classList.remove('selected');
    });

    /**
     * @param {CollectionEvent} event
     */
    document.addEventListener("collection.node.insert.before", function (event) {
        if (modulesCollection) {
            event.collection(modulesCollection);
            event.position(modulesCollectionInsertIndex !== null ? modulesCollectionInsertIndex : getCollectionLastIndex(modulesCollection) + 1);
        } else if (event.target && event.target.dataset.collectionTarget !== undefined) {
            modulesCollection = document.getElementById(event.target.dataset.collectionTarget);
        } else {
            throw new Error('No collection target found for module');
        }

        const moduleThumbnail = event._originEvent.target;
        let prototype = moduleThumbnail.dataset.prototype ?? moduleThumbnail.dataset.collectionPrototype;
        // COLLECTION
        // version_create_form_data_main_1_modules_0
        // version_create_form[data][main][1][modules][0]
        // PROTOTYPE
        // version_create_form_module_prototypes_collection____MODULE____class
        // version_create_form[module_prototypes_collection][___MODULE___]
        // RESULT
        // version_create_form_data_main_1_modules_0_modules_0_class
        // version_create_form[data][main][1][modules][0][modules][0][class]
        prototype = prototype.replace(new RegExp('version_create_form_module_prototypes_collection', 'g'), modulesCollection.id);
        prototype = prototype.replace(new RegExp('version_create_form\\[module_prototypes_collection\\]', 'g'), modulesCollection.dataset.fullName);
        event.prototype(prototype);

        // reset variables
        modulesCollection = null;
        modulesCollectionInsertIndex = null;

        const modal = Modal.getInstance(prototypesModal);
        modal && modal.hide();
    });

    /**
     * @param {CollectionEvent} event
     */
    document.addEventListener("collection.node.insert.after", function (event) {
        if (!event.collection() || !event.node() || event.collection().dataset.moduleRowClass === undefined) {
            return;
        }

        event.node().classList.remove(...event.node().classList);
        event.node().classList.add(event.collection().dataset.moduleRowClass);

        if (event.collection().dataset.moduleRowClass == 'col') {
            const down = event.node().querySelector(':scope > .cms-module > .cms-module-header > .cms-module-buttons > [data-collection-action=down] .bi-chevron-down');
            down.classList.remove('bi-chevron-down');
            down.classList.add('bi-chevron-right');
            const up = event.node().querySelector(':scope > .cms-module > .cms-module-header > .cms-module-buttons > [data-collection-action=up] .bi-chevron-up');
            up.classList.remove('bi-chevron-up');
            up.classList.add('bi-chevron-left');
        }

        const modal = Modal.getInstance(prototypesModal);
        modal && modal.hide();

        moduleFocus(event.node().querySelector('.cms-module'));

        // on insert focus into first contenteditable element
        const contentEditableElement = event.node().querySelector('[contenteditable]');
        if (contentEditableElement) {
            contentEditableElement.focus();
        }
    });

    function checkMaxInputVars()
    {
        let currentInputVars = document.querySelectorAll('input, textarea, select').length;
        const buttons = document.querySelectorAll('#submitBtnGroupDrop1,#defaultSubmitBtn');
        const maxInputVarsMessage = document.getElementById('maxInputVarsMessage');
        const maxInputVars = maxInputVarsMessage.dataset.maxInputVars;

        if (currentInputVars > maxInputVars) {
            [...buttons].forEach((button) => button.classList.add('disabled'));
            maxInputVarsMessage.classList.remove('d-none');
        } else {
            [...buttons].forEach((button) => button.classList.remove('disabled'));
            maxInputVarsMessage.classList.add('d-none');
        }
    }

    document.addEventListener("collection.node.delete.after", checkMaxInputVars);
    document.addEventListener("collection.node.insert.after", checkMaxInputVars);
    document.addEventListener("collection.node.add.after", checkMaxInputVars);
    document.addEventListener("collection.node.duplicate.after", checkMaxInputVars);
});
