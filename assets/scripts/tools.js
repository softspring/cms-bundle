import {Alert} from 'bootstrap';

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

    // Crea el elemento div para el alert
    const alertDiv = document.createElement('div');
    // Añade las clases de Bootstrap para el alert y la animación
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');

    // Añade el mensaje al alert
    alertDiv.textContent = message;

    // Opcional: añade un botón de cierre manual, aunque se auto-cerrará
    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close';
    closeButton.dataset.bsDismiss = 'alert'; // Atributo de Bootstrap para cerrar
    closeButton.setAttribute('aria-label', 'Close');
    alertDiv.appendChild(closeButton);

    // Añade el alert al contenedor
    alertContainer.appendChild(alertDiv);

    // Auto-dismiss: Inicializa el componente Alert de Bootstrap y luego lo cierra.
    // Necesitamos una pequeña demora antes de cerrarlo para que 'show' se aplique y la animación 'fade' funcione.
    setTimeout(() => {
        const bsAlert = new Alert(alertDiv); // Inicializa el objeto Alert de Bootstrap
        bsAlert.close(); // Llama al método .close()
    }, durationMs); // Espera la duración especificada antes de cerrar

    // Opcional: Elimina el elemento del DOM después de que la animación de cierre haya terminado
    // (Bootstrap tarda unos 500ms en la animación 'fade')
    alertDiv.addEventListener('closed.bs.alert', function () {
        alertDiv.remove();
    });
}

export {
    registerFeature,
    addTargetEventListener,
    callForeachSelector,
    showAlert
}