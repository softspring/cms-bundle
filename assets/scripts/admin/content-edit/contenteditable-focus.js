import {getInputsFromElement, getPreviewElementsFromInput} from './contenteditable';
import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_contenteditable_focus', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    document.addEventListener('focusin', function (event) {
        if (event.target && event.target.hasAttribute('data-edit-content-input')) {
            event.preventDefault();
            event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.content_editable.input.focus', {bubbles: true}));
        }
        if (event.target && event.target.hasAttribute('data-edit-content-target')) {
            event.preventDefault();
            event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.content_editable.target.focus', {bubbles: true}));
        }
    });

    document.addEventListener('focusout', function (event) {
        if (event.target && event.target.hasAttribute('data-edit-content-input')) {
            event.preventDefault();
            event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.content_editable.input.blur', {bubbles: true}));
        }
        if (event.target && event.target.hasAttribute('data-edit-content-target')) {
            event.preventDefault();
            event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.content_editable.target.blur', {bubbles: true}));
        }
    });

    // focus and blur events
    document.addEventListener('sfs_cms.content_edit.content_editable.target.focus', function (event) {
        event.preventDefault();
        contentEditableFocusElement(event.target);
    });
    document.addEventListener('sfs_cms.content_edit.content_editable.target.blur', function (event) {
        event.preventDefault();
        contentEditableBlurElement(event.target);
    });
    document.addEventListener('sfs_cms.content_edit.content_editable.input.focus', function (event) {
        event.preventDefault();
        contentEditableFocusInput(event.target);
    });
    document.addEventListener('sfs_cms.content_edit.content_editable.input.blur', function (event) {
        event.preventDefault();
        contentEditableBlurInput(event.target);
    });
    const collapseElementList = [].slice.call(document.querySelectorAll('.collapse'))
    collapseElementList.map(function (collapseEl) {
        collapseEl.addEventListener('shown.bs.collapse', function () {
            this.scrollIntoView({
                behavior: 'smooth',
                block: 'end',
            });
        })
    });
}

function contentEditableFocusElement(previewElement) {
    const inputs = getInputsFromElement(previewElement);

    if (!inputs.length) {
        return;
    }

    inputs[0].classList.add('border', 'border-success');
    const accordionItem = inputs[0].closest('.accordion-item').querySelector('.accordion-button.collapsed');
    accordionItem && accordionItem.click();

    inputs[0].closest('.accordion-item').scrollIntoView({
        behavior: 'smooth',
        block: 'end',
    });
}

function contentEditableBlurElement(previewElement) {
    const inputs = getInputsFromElement(previewElement);

    if (!inputs.length) {
        return;
    }

    inputs[0].classList.remove('border', 'border-success');
}

function contentEditableFocusInput(inputElement) {
    const previews = getPreviewElementsFromInput(inputElement);

    if (!previews.length) {
        return;
    }

    previews.map(preview => preview.classList.add('border', 'border-success'));
}

function contentEditableBlurInput(inputElement) {
    const previews = getPreviewElementsFromInput(inputElement);

    if (!previews.length) {
        return;
    }

    previews.map(preview => preview.classList.remove('border', 'border-success'));
}
