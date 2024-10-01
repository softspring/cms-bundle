import {cmsEditListener} from './event-listeners';
import {registerFeature} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_content_edit_config_errors', () => {
    document.addEventListener("collection.node.add.after", showConfigErrors);
    document.addEventListener("collection.node.insert.after", showConfigErrors);
    showConfigErrors();
});

function showConfigErrors() {
    document.querySelectorAll('[data-edit-config-error]').forEach((element) => console.error(element.dataset.editConfigError));
}
