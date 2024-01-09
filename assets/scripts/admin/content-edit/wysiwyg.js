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
 * - sfs_cms.content_edit.wysiwyg.focusin: dispatched when the element is focused
 * - sfs_cms.content_edit.wysiwyg.focusout: dispatched when the element is unfocused
 *
 * Global configuration:
 * - window.sfs_cms_tinymce_base_url: the base url for tinymce (default: /build/tinymce)
 * - window.sfs_cms_tinymce_default_toolbar: the default toolbar to use as default
 * - window.sfs_cms_tinymce_default_valid_elements: the valid elements to use as default
 * - window.sfs_cms_tinymce_default_plugins: the plugins to use as default (space separated list)
 */

import 'tinymce/tinymce';
import {contentEditableUpdateInputsFromElement} from './contenteditable';

/**
 * Create a tinymce wysiwyg editor
 * @param {HTMLElement} element
 * @private
 */
function _createWysiwygTinyMCE(element) {
    const predeterminatedDefaultToolbar = ['bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | link anchor code |  numlist bullist | codesample',
                                                   'outdent indent | forecolor removeformat | ltr rtl | fontsize blocks | charmap emoticons'];
    const defaultToolbar = window.sfs_cms_tinymce_default_toolbar !== undefined ? window.sfs_cms_tinymce_default_toolbar : predeterminatedDefaultToolbar;
    const toolbar = element.dataset.editContentWysiwygToolbar ? element.dataset.editContentWysiwygToolbar : defaultToolbar;

    const predeterminatedDefaultValidElements = 'strong,em,span[style],a[href],p[style],ul,ol,li,br,hr,h1,h2,h3,h4,h5,h6';
    const defaultValidElements = window.sfs_cms_tinymce_default_valid_elements !== undefined ? window.sfs_cms_tinymce_default_valid_elements : predeterminatedDefaultValidElements;
    const validElements = element.dataset.editContentWysiwygValidElements ? element.dataset.editContentWysiwygValidElements : defaultValidElements;

    const predeterminatedDefaultPlugins = 'image link media lists autolink anchor pagebreak charmap emoticons';
    const defaultPlugins = window.sfs_cms_tinymce_default_plugins !== undefined ? window.sfs_cms_tinymce_default_plugins : predeterminatedDefaultPlugins;
    const plugins = element.dataset.editContentWysiwygPlugins ? element.dataset.editContentWysiwygPlugins : defaultPlugins;

    const predeterminatedValidStyles = '{ "*": "font-size,font-family,color,text-decoration,text-align" }';
    const defaultValidStyles = window.sfs_cms_tinymce_default_valid_styles !== undefined ? window.sfs_cms_tinymce_default_valid_styles : predeterminatedValidStyles;
    const validStyles = JSON.parse(element.dataset.editContentWysiwygValidStyles ? element.dataset.editContentWysiwygValidStyles : defaultValidStyles);

    tinymce.init({
        selector: '#' + element.id,
        base_url: window.sfs_cms_tinymce_base_url !== undefined ? window.sfs_cms_tinymce_base_url : '/build/tinymce',
        highlight_on_focus: true,
        menubar: false,
        inline: true,
        hidden_input: false,
        plugins: plugins,
        toolbar: toolbar,
        valid_elements: validElements,
        valid_styles: validStyles,
        setup: (editor) => {
            editor.on('change', (event) => {
                contentEditableUpdateInputsFromElement(element);
            });
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
function _init() {
    // dispatch event on focusin in a data-edit-content-wysiwyg element
    document.addEventListener('focusin', function (event) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-wysiwyg')) return;
        event.preventDefault();
        event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.wysiwyg.focusin', {bubbles: true}));
    });

    // dispatch event on focusout in a data-edit-content-wysiwyg element
    document.addEventListener('focusout', function (e) {
        if (!event.target || !event.target.hasAttribute('data-edit-content-wysiwyg')) return;
        event.preventDefault();
        event.target.dispatchEvent(new CustomEvent('sfs_cms.content_edit.wysiwyg.focusout', {bubbles: true}));
    });

    // create wysiwyg on focus
    document.addEventListener('sfs_cms.content_edit.wysiwyg.focusin', (event) => {
        event.preventDefault();
        createWysiwyg(event.target);
    });

    // destroy wysiwyg on focusout
    document.addEventListener('sfs_cms.content_edit.wysiwyg.focusout', (event) => {
        event.preventDefault();
        // destroyWysiwyg(event.target); <-- disabled because on tinymce modals and windows openings it loses focus and gets destroyed
    });
}

// init wysiwyg on window load
window.addEventListener('load', _init);
