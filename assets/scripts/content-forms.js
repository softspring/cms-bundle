var underscored = require("underscore.string/underscored");
var slugify = require("underscore.string/slugify");

window.onload = function() {
    function moduleFocus(module)
    {
        allLostFocus();
        module.classList.add('active');
        document.getElementById('content-form').classList.remove('d-none');
    }

    function allLostFocus()
    {
        document.getElementById('content-form').classList.add('d-none');
        document.querySelectorAll('.cms-module').forEach((element) => element.classList.remove('active'));
    }

    // close module edit form
    document.addEventListener('click', function(event) {
        if (!event.target || !event.target.hasAttribute('data-cms-module-form-close')) return;
        event.preventDefault();
        event.stopPropagation();
        allLostFocus();
    });

    // on module focus get focus
    document.addEventListener('click', function (event) {
        // prevent focus on close button
        if (event.target && event.target.hasAttribute('data-cms-module-form-close')) return;

        for (i=0 ; i < event.composedPath().length ; i++) {
            if (event.composedPath()[i] instanceof Element && event.composedPath()[i].matches('.cms-module')) {
                moduleFocus(event.composedPath()[i]);
                return;
            }
        }
    });

    String.prototype.removeAccents = function() {
        return this.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    document.addEventListener('keyup', function(event) {
        if (!event.target.matches('[data-generate-underscore]') && !event.target.matches('[data-generate-slug]')) return;

        // generate underscore
        var element = document.querySelector('['+event.target.dataset.generateUnderscore+']');
        if (element && element.value === underscored(event.target.lastValue||'')) {
            element.value = underscored(event.target.value).removeAccents();
        }

        // generate slug
        var element = document.querySelector('['+event.target.dataset.generateSlug+']');
        if (element && element.value.replace(/^\/+/, '').replace(/\/+$/, '') === slugify(event.target.lastValue||'')) {
            element.value = slugify(event.target.value).removeAccents();
        }

        event.target.lastValue = event.target.value.removeAccents();
    });

    document.addEventListener('keyup', function(event) {
        if (!event.target.matches('.snake-case')) return;
        event.target.value = underscored(event.target.value);
    });

    document.addEventListener('keyup', function(event) {
        if (!event.target.matches('.sluggize')) return;
        event.target.value = slugify(event.target.value);
    });

    document.addEventListener('input', function(event) {
        let module = event.target.closest('.cms-module-edit');

        if (!module) return;

        let preview = event.target.closest('.cms-module-edit').querySelector('.module-preview');

        if (module && event.target.matches('[data-content-edit-field]')) {
            let inputElement = module.querySelector("[data-content-edit-preview='"+event.target.dataset.contentEditField+"']");
            inputElement.value = event.target.innerHTML;
        }

        if (module && event.target.matches('[data-content-edit-preview]')) {
            let htmlElement = module.querySelector("[data-content-edit-field='"+event.target.dataset.contentEditPreview+"']");
            htmlElement.innerHTML = event.target.value;
        }

        if (preview && event.target.matches('[data-content-edit-id]')) {
            let htmlElement = preview.querySelector("[data-content-edit-id-target='"+event.target.dataset.contentEditId+"']");
            htmlElement.id = event.target.value;
        }

        if (preview && event.target.matches('[data-content-edit-class]')) {
            let htmlElement = preview.querySelector("[data-content-edit-class-target='"+event.target.dataset.contentEditClass+"']");
            htmlElement.className = htmlElement.dataset.contentEditClassDefault+' '+event.target.value;
        }

        if (preview && event.target.matches('[data-content-edit-color]')) {
            let htmlElement = preview.querySelector("[data-content-edit-color-target='"+event.target.dataset.contentEditColor+"']");
            htmlElement.style.backgroundColor = event.target.value;
        }
    }, true);

    document.addEventListener("add_polymorphic_node", function(event) { // (1)
        const tinymceFields = event.target.getElementsByClassName('tinymce');
        if (tinymceFields) {
            for (let i = 0; i < tinymceFields.length; i++) {
                const tinymceField = tinymceFields[i];
                tinymce.init({
                    selector: '#'+tinymceField.id,
                    plugins: '',
                });
            }
        }
    });

    document.addEventListener("removed_polymorphic_node", function(event) { // (1)
        // console.log(event);
    });
};