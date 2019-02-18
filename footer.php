  <!-- フッター -->
  <footer class="footer">
    Copyright fujisho All Rights Reserved.
  </footer>
  
  <script src="js/jquery-3.3.1.min.js"></script>
  <script>
    $(function() {
      var $flash = $('.flash-msg');
      if ($flash.length > 0) {
        $flash.slideToggle();
        setTimeout(function(){ $flash.slideToggle(); }, 5000);
      }
    });
  </script>
</body>
</html>