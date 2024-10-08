import {registerFeature} from '../../tools';

registerFeature('admin_content_edit_config_errors', _init);

function _init() {
    document.addEventListener("collection.node.add.after", showConfigErrors);
    document.addEventListener("collection.node.insert.after", showConfigErrors);
    showConfigErrors();
}

function showConfigErrors() {
    document.querySelectorAll('[data-edit-config-error]').forEach((element) => console.error(element.dataset.editConfigError));
}
