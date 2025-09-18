// Sidebar toggle
(function(){
  var toggle = document.getElementById('sidebarToggle');
  var sidebar = document.getElementById('sidebar');
  var backdrop = document.createElement('div');
  backdrop.id = 'sidebarBackdrop';
  document.body.appendChild(backdrop);
  if (toggle && sidebar) {
    toggle.addEventListener('click', function(){
      sidebar.classList.toggle('open');
      backdrop.classList.toggle('show');
    });
  }
  backdrop.addEventListener('click', function(){
    sidebar.classList.remove('open');
    backdrop.classList.remove('show');
  });
})();