// import {Alert} from 'bootstrap';

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

/**
 * Muestra un alert de Bootstrap que se auto-cierra.
 * @param {string} message El mensaje a mostrar en el alert.
 * @param {'primary'|'secondary'|'success'|'danger'|'warning'|'info'|'light'|'dark'} type El tipo de alert de Bootstrap.
 * @param {number} durationMs La duración en milisegundos antes de que el alert empiece a desvanecerse (por defecto 1000ms).
 * @param alertContainerSelector
 */
function showAlert(message, type = 'info', durationMs = 1000, alertContainerSelector = 'body') {
    const container = document.querySelector(alertContainerSelector);

    let alertContainer = container.querySelector('#showAlertContainer');
    if (!alertContainer) {
        container.appendChild(alertContainer = document.createElement('div')).setAttribute('id', 'showAlertContainer');
        alertContainer.className = 'fixed-top w-50 float-right m-5';
    }

    // Create the div element for the alert
    const alertDiv = document.createElement('div');
    // Add Bootstrap classes for the alert and animation
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');

    // Add the message to the alert
    alertDiv.textContent = message;

    // Optional: add a manual close button, although it will auto-close
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.dataset.bsDismiss = 'alert'; // Bootstrap attribute for closing
    closeButton.setAttribute('aria-label', 'Close');
    alertDiv.appendChild(closeButton);

    // Add the alert to the container
    alertContainer.appendChild(alertDiv);

    // Auto-dismiss: Initialize the Bootstrap Alert component and then close it.
    // A small delay is needed before closing it so that 'show' applies and the 'fade' animation works.
    setTimeout(() => {
        hideAlert(alertDiv); // Call the function to show the alert
    }, durationMs); // Wait for the specified duration before closing

    // Optional: Remove the element from the DOM after the closing animation has finished
    // (Bootstrap takes about 500ms for the 'fade' animation)
    alertDiv.addEventListener('closed.bs.alert', function () {
        alertDiv.remove();
    });
}

async function hideAlert(alertDiv) {
    const { Alert } = await import('bootstrap');
    const bsAlert = new Alert(alertDiv); // Initialize the Bootstrap Alert object
    bsAlert.close(); // Call the .close() method
}

export {
    registerFeature,
    addTargetEventListener,
    callForeachSelector,
    showAlert
}
