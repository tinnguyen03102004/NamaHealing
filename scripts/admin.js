(function () {
  'use strict';

  function ready() {
    var actionContainers = Array.from(document.querySelectorAll('[data-admin-actions]'));
    var openMenus = new Set();
    var modal = null;
    var modalName = null;
    var modalError = null;
    var cancelButton = null;
    var confirmButton = null;
    var backdrop = null;
    var activeFormId = null;

    function closeMenu(container) {
      if (!container) {
        return;
      }
      var menu = container.querySelector('[data-admin-actions-menu]');
      var toggle = container.querySelector('[data-admin-actions-toggle]');
      if (menu && !menu.hasAttribute('hidden')) {
        menu.setAttribute('hidden', '');
        container.classList.remove('is-open');
        openMenus.delete(container);
      }
      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      }
    }

    function closeAllMenus() {
      openMenus.forEach(closeMenu);
      openMenus.clear();
    }

    function closeModal() {
      if (!modal || modal.hasAttribute('hidden')) {
        return;
      }
      modal.setAttribute('hidden', '');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('admin-modal-open');
      activeFormId = null;
      if (confirmButton) {
        confirmButton.disabled = false;
        confirmButton.classList.remove('is-loading');
      }
    }

    actionContainers.forEach(function (container) {
      var toggle = container.querySelector('[data-admin-actions-toggle]');
      var menu = container.querySelector('[data-admin-actions-menu]');
      if (!toggle || !menu) {
        return;
      }

      toggle.addEventListener('click', function (event) {
        event.preventDefault();
        var isHidden = menu.hasAttribute('hidden');
        closeAllMenus();
        if (isHidden) {
          menu.removeAttribute('hidden');
          container.classList.add('is-open');
          toggle.setAttribute('aria-expanded', 'true');
          openMenus.add(container);
        } else {
          closeMenu(container);
        }
      });
    });

    document.addEventListener('click', function (event) {
      if (!openMenus.size) {
        return;
      }
      var target = event.target;
      var shouldClose = true;
      openMenus.forEach(function (container) {
        if (container.contains(target)) {
          shouldClose = false;
        }
      });
      if (shouldClose) {
        closeAllMenus();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        closeAllMenus();
        closeModal();
      }
    });

    document.addEventListener('submit', function (event) {
      var form = event.target;
      if (!(form instanceof HTMLFormElement)) {
        return;
      }
      if (!form.matches('[data-admin-action-form]')) {
        return;
      }
      event.preventDefault();

      var submitButton = form.querySelector('[type="submit"]');
      var originalText = null;
      if (submitButton) {
        originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.classList.add('is-loading');
      }

      var errorElement = form.querySelector('[data-admin-action-error]');
      if (errorElement) {
        errorElement.hidden = true;
      }

      var action = form.getAttribute('action') || window.location.href;
      var method = (form.getAttribute('method') || 'post').toUpperCase();
      var formData = new FormData(form);

      fetch(action, {
        method: method,
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      }).then(function (response) {
        if (!response.ok) {
          throw new Error('Request failed');
        }
        return response;
      }).then(function () {
        window.location.reload();
      }).catch(function (error) {
        console.error(error);
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.classList.remove('is-loading');
          if (originalText !== null) {
            submitButton.textContent = originalText;
          }
        }
        if (errorElement) {
          var message = errorElement.getAttribute('data-message') || errorElement.textContent || '';
          errorElement.textContent = message;
          errorElement.hidden = false;
        } else {
          alert('Unable to complete the request.');
        }
      });
    });

    modal = document.getElementById('admin-delete-modal');
    if (!modal) {
      return;
    }

    modalName = modal.querySelector('[data-admin-modal-name]');
    modalError = modal.querySelector('[data-admin-modal-error]');
    cancelButton = modal.querySelector('[data-admin-modal-cancel]');
    confirmButton = modal.querySelector('[data-admin-modal-confirm]');
    backdrop = modal.querySelector('[data-admin-modal-dismiss]');

    function openModal(formId, studentName) {
      activeFormId = formId;
      if (modalName) {
        modalName.textContent = studentName || '';
      }
      if (modalError) {
        modalError.hidden = true;
      }
      if (confirmButton) {
        confirmButton.disabled = false;
        confirmButton.classList.remove('is-loading');
      }
      modal.removeAttribute('hidden');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('admin-modal-open');
    }

    document.addEventListener('click', function (event) {
      var trigger = event.target.closest('[data-admin-delete-trigger]');
      if (!trigger) {
        return;
      }
      event.preventDefault();
      closeAllMenus();
      var formId = trigger.getAttribute('data-delete-form');
      if (!formId) {
        return;
      }
      var studentName = trigger.getAttribute('data-student-name') || '';
      openModal(formId, studentName);
    });

    function submitDelete() {
      if (!activeFormId) {
        return;
      }
      var form = document.getElementById(activeFormId);
      if (!form || !(form instanceof HTMLFormElement)) {
        return;
      }
      if (modalError) {
        modalError.hidden = true;
      }
      if (confirmButton) {
        confirmButton.disabled = true;
        confirmButton.classList.add('is-loading');
      }
      var action = form.getAttribute('action') || window.location.href;
      var method = (form.getAttribute('method') || 'post').toUpperCase();
      var formData = new FormData(form);

      fetch(action, {
        method: method,
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      }).then(function (response) {
        if (!response.ok) {
          throw new Error('Request failed');
        }
        return response;
      }).then(function () {
        window.location.reload();
      }).catch(function (error) {
        console.error(error);
        if (confirmButton) {
          confirmButton.disabled = false;
          confirmButton.classList.remove('is-loading');
        }
        if (modalError) {
          modalError.hidden = false;
        } else {
          alert('Unable to delete student.');
        }
      });
    }

    if (cancelButton) {
      cancelButton.addEventListener('click', function (event) {
        event.preventDefault();
        closeModal();
      });
    }

    if (backdrop) {
      backdrop.addEventListener('click', function (event) {
        event.preventDefault();
        closeModal();
      });
    }

    if (confirmButton) {
      confirmButton.addEventListener('click', function (event) {
        event.preventDefault();
        submitDelete();
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
  } else {
    ready();
  }
})();
