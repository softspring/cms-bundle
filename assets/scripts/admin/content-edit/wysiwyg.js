/**
 * Content edit wysiwyg editors
 *
 * Configuration:
 *
 * - data-edit-content-wysiwyg: the wysiwyg editor to use (tinymce)
 *
 * Configuration for tinymce:
 *
 * - data-edit-content-wysiwyg-toolbar: the toolbar to use (default: undo redo | bold italic underline)
 * - data-edit-content-wysiwyg-valid-elements: the valid elements to use (default: strong,em,span[style],a[href],p)
 *
 * Example:
 *
 * <div data-edit-content-wysiwyg="tinymce"
 *      data-edit-content-wysiwyg-toolbar="undo redo | bold italic underline"
 *      data-edit-content-wysiwyg-valid-elements="strong,em,span[style],a[href],p"
 * ></div>
 *
 * Extending events:
 * - sfs_cms.edit_content.wysiwyg.focusin: dispatched when the element is focused
 * - sfs_cms.edit_content.wysiwyg.focusout: dispatched when the element is unfocused
 */

import 'tinymce/tinymce';

/**
 * Create a tinymce wysiwyg editor
 * @param {HTMLElement} element
 * @private
 */
function _createWysiwygTinyMCE(element) {
    const defaultToolbar = 'undo redo | bold italic underline';
    const defaultValidElements = 'strong,em,span[style],a[href],p';

    const toolbar = element.dataset.editContentWysiwygToolbar ? element.dataset.editContentWysiwygToolbar : defaultToolbar;
    const validElements = element.dataset.editContentWysiwygValidElements ? element.dataset.editContentWysiwygValidElements : defaultValidElements;

    tinymce.init({
        selector: '#' + element.id,
        base_url: '/build/tinymce',
        highlight_on_focus: true,
        menubar: false,
        inline: true,
        hidden_input: false,
        plugins: [
            'lists',
            'autolink'
        ],
        toolbar: toolbar,
        valid_elements: validElements,
        valid_styles: {
            '*': 'font-size,font-family,color,text-decoration,text-align'
        },
        setup: (editor) => {
            editor.on('click', (event) => {
                console.log('Editor was clicked: ' + element.id)
            });
            editor.on('change', (event) => {
                console.log('Editor was changed: ' + element.id);
            });
            editor.on('detach', (event) => {
                console.log('Editor was detached: ' + element.id)
            });
            editor.on('activate', (event) => {
                console.log('Editor was activated: ' + element.id);
            });
            editor.on('deactivate', (event) => {
                console.log('Editor was deactivated: ' + element.id);
            });
            editor.on('remove', (event) => {
                console.log('Editor was removed: ' + element.id);
            });
        },
        init_instance_callback: (editor) => {
            console.log(`Editor: ${editor.id} is now initialized.`);
        },
        min_height: 30,
    });
}

/**
 * Create a wysiwyg editor
 * @param {HTMLElement} element
 * @private
 */
function createWysiwyg(element) {
    // create id if not exists
    if (!element.id) {
        element.id = 'wswg' + Math.random().toString(36).substr(2, 9);
    }

    switch (element.dataset.editContentWysiwyg) {
        case 'tinymce':
            _createWysiwygTinyMCE(element);
            break;

        default:
            console.error('Wysiwyg ' + element.dataset.editContentWysiwyg + ' not implemented');
    }
}

/**
 * Destroy a wysiwyg editor
 * @param {HTMLElement} element
 * @private
 */
function destroyWysiwyg(element) {
    switch (event.target.dataset.editContentWysiwyg) {
        case 'tinymce':
            tinymce.remove(element.dataset.editContentWysiwygInstance);
            break;
    }
}

/**
 * Init wysiwyg editors
 * @private
 */
function _initCmsWysiwyg() {
    // dispatch event on focusin in a data-edit-content-wysiwyg element
    document.addEventListener('focusin', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-wysiwyg')) return;
        event.preventDefault();
        event.target.dispatchEvent(new CustomEvent('sfs_cms.edit_content.wysiwyg.focusin', {bubbles: true}));
    });

    // dispatch event on focusout in a data-edit-content-wysiwyg element
    document.addEventListener('focusout', function (e) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-wysiwyg')) return;
        event.preventDefault();
        event.target.dispatchEvent(new CustomEvent('sfs_cms.edit_content.wysiwyg.focusout', {bubbles: true}));
    });

    // create wysiwyg on focus
    document.addEventListener('sfs_cms.edit_content.wysiwyg.focusin', (event) => {
        event.preventDefault();
        createWysiwyg(event.target);
    });

    // destroy wysiwyg on focusout
    document.addEventListener('sfs_cms.edit_content.wysiwyg.focusout', (event) => {
        event.preventDefault();
        destroyWysiwyg(event.target);
    });
}

// init wysiwyg on window load
window.addEventListener('load', _initCmsWysiwyg);
