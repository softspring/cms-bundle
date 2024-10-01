import * as bootstrap from 'bootstrap';
import {registerFeature,addTargetEventListener} from '@softspring/cms-bundle/scripts/tools';

registerFeature('admin_confirm_modal', _init);

/**
 * Init behaviour
 * @private
 */
function _init() {
    addTargetEventListener('[data-confirm-modal]', 'click', onConfirmModalClick);
}

function onConfirmModalClick(confirmModal, event) {
    event.preventDefault();

    const title = event.target.dataset.confirmModalTitle || '';
    const message = decodeMessage(event.target.dataset.confirmModal || '');
    const confirmButton = event.target.dataset.confirmModalConfirmButton || 'Continue';
    const confirmButtonType = event.target.dataset.confirmModalConfirmButtonType || 'primary';
    const cancelButton = event.target.dataset.confirmModalCancelButton || 'Cancel';

    if (!event.target.href) {
        console.error('Only links are supported for confirm modal');
        return;
    }

    confirmModalLink(title, message, confirmButton, confirmButtonType, cancelButton, event.target.href);

    return false;
}

function decodeMessage(encoded) {
    return decodeURIComponent(atob(encoded).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
}

function confirmModalLink(title, message, confirmButton, confirmButtonType, cancelButton, url) {
    const modalId = createModal(title, message, confirmButton, confirmButtonType, cancelButton, url);
    const modalDiv = document.getElementById(modalId);
    const modal = new bootstrap.Modal(modalDiv, {
        keyboard: false
    });
    modal.show();
}

function createModal(title, message, confirmButton, confirmButtonType, cancelButton, url=null) {
    const modalRandomId = Math.random().toString(36).substring(7);

    let modalHeader = '';
    if (title) {
        modalHeader = `<div class="modal-header">
        <h5 class="modal-title">${title}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>`;
    }

    let confirmButtonElement = '';
    if (url) {
        confirmButtonElement = `<a href="${url}" class="btn btn-${confirmButtonType}">${confirmButton}</a>`;
    } else {
        confirmButtonElement = `<button type="button" class="btn btn-${confirmButtonType}">${confirmButton}</button>`;
    }

    const modalHtml = `
    <div class="modal" tabindex="-1" id="${modalRandomId}">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      ${modalHeader}  
      <div class="modal-body">${message}</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">${cancelButton}</button>
        ${confirmButtonElement}
      </div>
    </div>
  </div>
</div>`;

    const modalNode = document.createElement('div');
    document.body.appendChild(modalNode);
    modalNode.outerHTML = modalHtml;

    return modalRandomId;
}

