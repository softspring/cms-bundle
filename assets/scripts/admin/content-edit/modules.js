import { filterCurrentTranslatableElementsLanguage } from './locale-filter-preview';
import { getPolymorphicCollectionLastIndex } from '../../../../../polymorphic-form-type/assets/scripts/polymorphic-form-type-vanilla';

window.addEventListener('load', (event) => {
    function moduleFocus(module) {
        allLostFocus();
        module.classList.add('active');

        document.getElementById('content-form').classList.remove('d-none');
        // If has form to show
        if(module.closest('div').querySelector('.active >.cms-module-body > .cms-module-edit > .cms-module-form')) {
            document.querySelectorAll('[data-polymorphic=collection]').forEach((element) => element.classList.add('has-form'));
        }
    }

    function allLostFocus() {
        document.getElementById('content-form').classList.add('d-none');
        document.querySelectorAll('.cms-module').forEach((element) => element.classList.remove('active'));
        document.querySelectorAll('[data-polymorphic=collection]').forEach((element) => element.classList.remove('has-form'));
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
        if (event.target && (event.target.hasAttribute('data-cms-module-form-close') || event.target.matches('[data-polymorphic-action=delete]'))) {
            if (event.target.matches('[data-polymorphic-action=delete]')) {
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

    document.addEventListener("polymorphic.node.insert.after", function (event) { // (1)
        const tinymceFields = event.target.getElementsByClassName('tinymce');
        if (tinymceFields) {
            for (let i = 0; i < tinymceFields.length; i++) {
                const tinymceField = tinymceFields[i];
                tinymce.init({
                    selector: '#' + tinymceField.id,
                    plugins: '',
                });
            }
        }
    });

    document.addEventListener("polymorphic.node.add.after", function (event) { // (1)
        const tinymceFields = event.target.getElementsByClassName('tinymce');
        if (tinymceFields) {
            for (let i = 0; i < tinymceFields.length; i++) {
                const tinymceField = tinymceFields[i];
                tinymce.init({
                    selector: '#' + tinymceField.id,
                    plugins: '',
                });
            }
        }
    });

    document.addEventListener("polymorphic.node.insert.after", function (event) { // (1)
        var module = event.target.querySelector('.cms-module');
        if (module) {
            moduleFocus(module);
        }
        filterCurrentTranslatableElementsLanguage();
    });

    document.addEventListener("polymorphic.node.add.after", function (event) { // (1)
        var module = event.target.querySelector('.cms-module');
        if (module) {
            moduleFocus(module);
        }
        filterCurrentTranslatableElementsLanguage();
    });

    const prototypesModal = document.getElementById('module_prototypes_collection_modal');
    let modulesCollection;
    let modulesCollectionInsertIndex;
    let insertElement;

    prototypesModal && prototypesModal.addEventListener('show.bs.modal', function (event) {
        insertElement = event.relatedTarget;

        insertElement.classList.add('selected');

        if (insertElement.dataset.polymorphicCollection !== undefined) {
            modulesCollection = document.getElementById(insertElement.dataset.polymorphicCollection);
        } else {
            modulesCollection = insertElement.closest('[data-polymorphic=collection]');
        }

        // get collection elements
        const nodeRow = insertElement.closest('[data-polymorphic=node]');
        const modulesAllowed = modulesCollection.dataset.modulesAllowed.split(',');

        let action = 'Add';
        modulesCollectionInsertIndex = null;
        if (insertElement.matches('[data-polymorphic-position=after]')) {
            action = 'Insert after';
            modulesCollectionInsertIndex = nodeRow ? nodeRow.dataset.index : null;
        } else if (insertElement.matches('[data-polymorphic-position=before]')) {
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
     * @param {PolymorphicEvent} event
     */
    document.addEventListener("polymorphic.node.insert.before", function (event) {
        event.collection(modulesCollection);
        event.position(modulesCollectionInsertIndex !== null ? modulesCollectionInsertIndex : getPolymorphicCollectionLastIndex(modulesCollection) + 1);

        const moduleThumbnail = event._originEvent.target;
        let prototype = moduleThumbnail.dataset.polymorphicPrototype;
        // COLLECTION
        // content_content_form_data_main_1_modules_0
        // content_content_form[data][main][1][modules][0]
        // PROTOTYPE
        // content_content_form_module_prototypes_collection____MODULE____class
        // content_content_form[module_prototypes_collection][___MODULE___]
        // RESULT
        // content_content_form_data_main_1_modules_0_modules_0_class
        // content_content_form[data][main][1][modules][0][modules][0][class]
        prototype = prototype.replace(new RegExp('content_content_form_module_prototypes_collection', 'g'), modulesCollection.id);
        prototype = prototype.replace(new RegExp('content_content_form\\[module_prototypes_collection\\]', 'g'), modulesCollection.dataset.fullName);
        event.prototype(prototype);
    });

    /**
     * @param {PolymorphicEvent} event
     */
    document.addEventListener("polymorphic.node.insert.after", function (event) {
        if (event.collection().dataset.moduleRowClass === undefined) {
            return;
        }

        event.node().classList.remove(...event.node().classList);
        event.node().classList.add(event.collection().dataset.moduleRowClass);

        moduleFocus(event.node().querySelector('.cms-module'));
    });
});
