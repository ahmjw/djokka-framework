<?php echo $this->getLayout('header'); ?>

    <div class="row-fluid">
      <div class="col-md-4" id="sidebar"></div>
      <div class="col-md-8"><?php echo $this->getContent(); ?></div>
    </div>
    
<?php echo $this->getLayout('footer'); ?>