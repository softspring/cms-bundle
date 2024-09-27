function _addEditEventListener(eventName, listener, moduleName = null, targetMatcher = null) {
    document.addEventListener(eventName, function (event) {
        if (!event.target) {
            return;
        }

        if (targetMatcher && !event.target.matches(targetMatcher)) {
            return;
        }

        let module = event.target.closest('.cms-module');
        if (!module) {
            return;
        }

        let node = module.closest('[data-collection=node]');
        if (!node) {
            return;
        }

        if (moduleName && moduleName !== node.dataset.moduleId) {
            return;
        }

        let preview = event.target.closest('.cms-module-edit').querySelector('.module-preview');
        let form = event.target.closest('.cms-module-edit').querySelector('.cms-module-form');

        return listener(event.target, module, preview, form, event);
    });
}

function cmsEditModuleListener(moduleName, targetMatcher, eventName, listener) {
    _addEditEventListener(eventName, listener, moduleName, targetMatcher);
}

function cmsEditListener(targetMatcher, eventName, listener) {
    _addEditEventListener(eventName, listener, null, targetMatcher);
}

export {
    cmsEditModuleListener,
    cmsEditListener,
};

