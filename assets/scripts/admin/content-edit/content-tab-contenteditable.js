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

function _init() {
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Tab') return;
        if (!event.target || !event.target.matches('[contenteditable=true]')) return;

        event.preventDefault();

        insertTabIntoContentEditableElement(event.target);

        return true;
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Tab') return;
        if (!event.target || !event.target.matches('input[data-allow-tabs],textarea[data-allow-tabs]')) return;

        event.preventDefault();

        insertTabForInput(event.target);

        return true;
    });
}

// init behaviour on window load
window.addEventListener('load', _init);