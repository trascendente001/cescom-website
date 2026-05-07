(function () {
  var navbar = document.querySelector('.navbar');
  var btn = document.querySelector('.navbar__toggle');
  if (!navbar || !btn) return;

  var backdrop = null;

  function openMenu() {
    navbar.setAttribute('data-open', 'true');
    btn.setAttribute('aria-expanded', 'true');
    btn.setAttribute('aria-label', 'Cerrar menú');
    document.body.style.overflow = 'hidden';

    backdrop = document.createElement('div');
    backdrop.className = 'navbar-backdrop';
    backdrop.addEventListener('click', closeMenu);
    document.body.appendChild(backdrop);
  }

  function closeMenu() {
    navbar.setAttribute('data-open', 'false');
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-label', 'Abrir menú');
    document.body.style.overflow = '';

    if (backdrop) {
      backdrop.removeEventListener('click', closeMenu);
      backdrop.parentNode && backdrop.parentNode.removeChild(backdrop);
      backdrop = null;
    }
  }

  btn.addEventListener('click', function () {
    navbar.getAttribute('data-open') === 'true' ? closeMenu() : openMenu();
  });

  navbar.querySelectorAll('.navbar__link, .navbar__cta').forEach(function (link) {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && navbar.getAttribute('data-open') === 'true') closeMenu();
  });
})();
