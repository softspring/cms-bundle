window.addEventListener('load', (event) => {
    document.querySelectorAll('[data-color-type=toggler]').forEach(function(togglerHtmlElement) {
        colorDatePicker(togglerHtmlElement);
    });

    function colorDatePicker(toggler) {
        let widget = toggler.closest('.input-group').querySelector('[data-color-type=widget]');

        if (!widget) {
            return;
        }

        widget.disabled = !toggler.checked;

        if (widget.disabled) {
            widget.classList.add('disabled')
        } else {
            widget.classList.remove('disabled')
        }

        if (!widget.hasAttribute('data-edit-bgcolor-input')) return;

        let modulePreview = widget.closest('.cms-module-edit').querySelector('.module-preview');
        let htmlTargetElements = modulePreview.querySelectorAll("[data-edit-bgcolor-target='" + widget.dataset.editBgcolorInput + "']");
        if (htmlTargetElements.length) {

            if (widget.disabled) {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.style.backgroundColor = null);
            } else {
                htmlTargetElements.forEach((htmlTargetElement) => htmlTargetElement.style.backgroundColor = widget.value);
            }
        }
    }

    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.matches('[data-color-type=toggler]')) {
            return;
        }

        colorDatePicker(event.target);
    });

    document.addEventListener('change', function (event) {
        if (!event.target || !event.target.matches('[data-color-type=widget]')) {
            return;
        }

        let widget = event.target;
        let toggler = widget.closest('.input-group').querySelector('[data-color-type=toggler]');

        if (!toggler) {
            return;
        }

        if (widget.value && !toggler.checked) {
            toggler.checked = true;
        }

        colorDatePicker(toggler);
    });
});
