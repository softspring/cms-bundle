HTMLElement.prototype.showElement = function () {
    this.classList.remove("d-none", "hidden");
    return this;
};

HTMLElement.prototype.hideElement = function () {
    this.classList.add("d-none", "hidden");
    return this;
};

function registerFeature(module, callable) {
    if (!window[`__sfs_cms_${module}_registered`]) {
        window.addEventListener('load', callable);
    }
    window[`__sfs_cms_${module}_registered`] = true;
}

/**
 * @param {string} targetMatcher CSS selector to match the target
 * @param {string} eventName Event name to listen
 * @param {function} listener Listener to call
 * @param {number} parentNodeDeep Parent node deep to search (0 = only the target, 1 = parent, 2 = grandparent, etc)
 */
function addTargetEventListener(targetMatcher, eventName, listener, parentNodeDeep = 0) {
    document.addEventListener(eventName, function (event) {
        if (!targetMatcher) {
            return;
        }

        if (!event.target) {
            return;
        }

        if (event.target.matches(targetMatcher)) {
            return listener(event.target, event);
        }

        let currentNode = event.target;
        while (parentNodeDeep > 0) {
            if (currentNode.parentNode.matches(targetMatcher)) {
                return listener(currentNode.parentNode, event);
            }
            currentNode = currentNode.parentNode;
            parentNodeDeep--;
        }
    });
}

/**
 * @param {string} targetMatcher CSS selector to match the target
 * @param {function} callback Callback to call
 */
function callForeachSelector(targetMatcher, callback) {
    document.querySelectorAll(targetMatcher).forEach(function (target) {
        callback(target);
    });
}

export {
    registerFeature,
    addTargetEventListener,
    callForeachSelector,
}