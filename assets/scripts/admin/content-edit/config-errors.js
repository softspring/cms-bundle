function showConfigErrors() {
    document.querySelectorAll('[data-edit-config-error]').forEach((element) => console.error(element.dataset.editConfigError));
}

window.addEventListener('load', function() {
    showConfigErrors();
});

document.addEventListener("collection.node.add.after", function (event) { // (1)
    showConfigErrors();
});

document.addEventListener("collection.node.insert.after", function (event) { // (1)
    showConfigErrors();
});
