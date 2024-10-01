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
 */
function addTargetEventListener(targetMatcher, eventName, listener) {
    document.addEventListener(eventName, function (event) {
        if (!event.target) {
            return;
        }

        if (targetMatcher && !event.target.matches(targetMatcher)) {
            return;
        }

        return listener(event.target, event);
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