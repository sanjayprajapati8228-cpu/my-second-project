(function () {
  var menuBtn = document.getElementById('adminMenuBtn');
  var sidebar = document.getElementById('adminSidebar');
  if (!sidebar) return;

  if (menuBtn) {
    menuBtn.addEventListener('click', function () {
      sidebar.classList.toggle('show');
    });
  }

  var toggles = sidebar.querySelectorAll('[data-submenu-toggle]');
  if (!toggles.length) return;

  function closeOthers(currentParent) {
    var items = sidebar.querySelectorAll('.has-submenu');
    items.forEach(function (item) {
      if (item === currentParent) return;
      item.classList.remove('open');
      var sub = item.querySelector('.submenu');
      var btn = item.querySelector('[data-submenu-toggle]');
      if (sub) sub.classList.remove('submenu-open');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  toggles.forEach(function (toggle) {
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      var parent = toggle.closest('.has-submenu');
      if (!parent) return;
      var submenu = parent.querySelector('.submenu');
      if (!submenu) return;

      var willOpen = !parent.classList.contains('open');
      closeOthers(parent);

      parent.classList.toggle('open', willOpen);
      submenu.classList.toggle('submenu-open', willOpen);
      toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
  });
})();
