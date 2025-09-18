    </main>
  </div> <!-- layout -->

  <!--<footer class="text-center py-3 bg-dark text-light mt-auto">-->
  <!--  <small>© <?php echo date('Y'); ?> Real Estate Bankaii. All Rights Reserved.</small>-->
  <!--</footer>-->

  <script>
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const main = document.querySelector('.main');

    if(toggleBtn){
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        main.classList.toggle('full');
      });
    }
  </script>
  
  <!-- Bootstrap CSS -->
<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">-->

<!-- Bootstrap JS + Popper -->
<!--<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>-->
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>-->

</body>
</html>
