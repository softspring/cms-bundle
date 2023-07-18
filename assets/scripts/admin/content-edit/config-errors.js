function showConfigErrors() {
    document.querySelectorAll('[data-edit-config-error]').forEach((element) => console.error(element.dataset.editConfigError));
}

window.addEventListener('load', function() {
    showConfigErrors();
});

document.addEventListener("polymorphic.node.add.after", function (event) { // (1)
    showConfigErrors();
});

document.addEventListener("polymorphic.node.insert.after", function (event) { // (1)
    showConfigErrors();
});
