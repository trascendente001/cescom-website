(function () {
  var form = document.querySelector('.contact-form');
  if (!form) return;

  var btn = form.querySelector('[type="submit"]');
  var originalBtnText = btn ? btn.textContent : 'Enviar Mensaje';

  // Insert feedback elements after the form
  var successEl = document.createElement('div');
  successEl.className = 'form-feedback form-feedback--success';
  successEl.setAttribute('role', 'alert');
  successEl.setAttribute('aria-hidden', 'true');
  successEl.innerHTML = '<strong>¡Mensaje enviado!</strong> Nos pondremos en contacto con usted a la brevedad.';

  var errorEl = document.createElement('div');
  errorEl.className = 'form-feedback form-feedback--error';
  errorEl.setAttribute('role', 'alert');
  errorEl.setAttribute('aria-hidden', 'true');

  form.parentNode.insertBefore(successEl, form.nextSibling);
  form.parentNode.insertBefore(errorEl, form.nextSibling);

  function showSuccess() {
    form.style.display = 'none';
    errorEl.setAttribute('aria-hidden', 'true');
    successEl.setAttribute('aria-hidden', 'false');
  }

  function showError(message) {
    errorEl.textContent = message || 'Error al enviar. Por favor intente nuevamente.';
    errorEl.setAttribute('aria-hidden', 'false');
    successEl.setAttribute('aria-hidden', 'true');
    if (btn) {
      btn.disabled = false;
      btn.textContent = originalBtnText;
    }
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Enviando...';
    }
    errorEl.setAttribute('aria-hidden', 'true');

    fetch('contacto.php', {
      method: 'POST',
      body: new FormData(form)
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.ok) {
          showSuccess();
        } else {
          showError(data.error);
        }
      })
      .catch(function () {
        showError('Error de conexión. Verifique su internet e intente nuevamente.');
      });
  });
})();
