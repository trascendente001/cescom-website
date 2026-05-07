(function () {
  var navbar = document.querySelector('.navbar');
  var btn = document.querySelector('.navbar__toggle');
  if (!navbar || !btn) return;

  btn.addEventListener('click', function () {
    var open = navbar.getAttribute('data-open') === 'true';
    navbar.setAttribute('data-open', String(!open));
    btn.setAttribute('aria-expanded', String(!open));
    btn.setAttribute('aria-label', open ? 'Abrir menú' : 'Cerrar menú');
  });

  // Cerrar menú al hacer click en un enlace
  navbar.querySelectorAll('.navbar__link, .navbar__cta').forEach(function (link) {
    link.addEventListener('click', function () {
      navbar.setAttribute('data-open', 'false');
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-label', 'Abrir menú');
    });
  });

  // Cerrar menú al hacer click fuera del navbar
  document.addEventListener('click', function (e) {
    if (!navbar.contains(e.target)) {
      navbar.setAttribute('data-open', 'false');
      btn.setAttribute('aria-expanded', 'false');
      btn.setAttribute('aria-label', 'Abrir menú');
    }
  });
})();
