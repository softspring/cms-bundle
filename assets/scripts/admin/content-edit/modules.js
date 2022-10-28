import { filterCurrentTranslatableElementsLanguage } from './locale-filter-preview';

window.addEventListener('load', (event) => {
    function moduleFocus(module) {
        allLostFocus();
        module.classList.add('active');

        document.getElementById('content-form').classList.remove('d-none');
        // If has form to show
        if(module.closest('div').querySelector('.active >.cms-module-body > .cms-module-edit > .cms-module-form')) {
            document.querySelectorAll('.polymorphic-collection-widget').forEach((element) => element.classList.add('has-form'));
        }
    }

    function allLostFocus() {
        document.getElementById('content-form').classList.add('d-none');
        document.querySelectorAll('.cms-module').forEach((element) => element.classList.remove('active'));
        document.querySelectorAll('.polymorphic-collection-widget').forEach((element) => element.classList.remove('has-form'));

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
        if (event.target && (event.target.hasAttribute('data-cms-module-form-close') || event.target.matches('.polymorphic-remove-node-button'))) {
            if (event.target.matches('.polymorphic-remove-node-button')) {
                allLostFocus();
            }
            return;
        }

        for (i = 0; i < event.composedPath().length; i++) {
            if (event.composedPath()[i] instanceof Element && event.composedPath()[i].matches('.cms-module')) {
                moduleFocus(event.composedPath()[i]);
                return;
            }
        }
    });

    document.addEventListener("add_polymorphic_node", function (event) { // (1)
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

    document.addEventListener("add_polymorphic_node", function (event) { // (1)
        var module = event.target.querySelector('.cms-module');
        if (module) {
            moduleFocus(module);
        }
        filterCurrentTranslatableElementsLanguage();
    });

    document.addEventListener("removed_polymorphic_node", function (event) { // (1)
        // console.log(event);
    });
});
