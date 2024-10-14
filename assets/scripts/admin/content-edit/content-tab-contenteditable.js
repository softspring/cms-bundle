import {cmsEditListener} from './event-listeners';
import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_content_tab_contenteditable', _init);

function _init() {
    cmsEditListener('[contenteditable=true]', 'keydown', onContentEditableTab);
    cmsEditListener('input[data-allow-tabs],textarea[data-allow-tabs]', 'keydown', onInputTab);
}

function onContentEditableTab(inputElement, module, preview, form, event) {
    if (event.key !== 'Tab') return;

    event.preventDefault();

    insertTabIntoContentEditableElement(inputElement);

    return true;
}

function onInputTab(inputElement, module, preview, form, event) {
    if (event.key !== 'Tab') return;

    event.preventDefault();

    insertTabForInput(inputElement);

    return true;
}

function insertTabIntoContentEditableElement(element) {
    const doc = element.ownerDocument.defaultView;
    const sel = doc.getSelection();
    const range = sel.getRangeAt(0);

    const tabNode = document.createTextNode("\u00a0\u00a0\u00a0\u00a0");
    range.insertNode(tabNode);

    range.setStartAfter(tabNode);
    range.setEndAfter(tabNode);
    sel.removeAllRanges();
    sel.addRange(range);
}

function insertTabForInput(element) {
    const start = element.selectionStart;
    const end = element.selectionEnd;

    // set textarea value to: text before caret + tab + text after caret
    element.value = element.value.substring(0, start) + "\t" + element.value.substring(end);

    // put caret at right position again
    element.selectionStart = element.selectionEnd = start + 1;
}
