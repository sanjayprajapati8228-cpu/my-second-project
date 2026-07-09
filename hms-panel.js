(function () {
  window.toggleSidebar = function toggleSidebar() {
    var sidebar = document.getElementById("sidebar");
    if (!sidebar) return;
    sidebar.classList.toggle("show");
  };
})();

