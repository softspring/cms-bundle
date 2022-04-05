window.onload = function() {
    document.addEventListener('input', function(event) {
        let module = event.target.closest('.module-edit');
        let preview = event.target.closest('.module-edit').querySelector('.module-preview');

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
    }, true);
};