<?php echo $this->getLayout('header'); ?>
    <div class="row-fluid">
      <div class="col-md-12"><?php echo $this->getContent(); ?></div>
    </div>

      <div class="footer">
        <p>&copy; Djokka 2013</p>
      </div>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo $this->assetUrl('bootstrap/js/bootstrap-dropdown.js'); ?>"></script>
  </body>
</html>