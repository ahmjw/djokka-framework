<?php echo $this->getLayout('header'); ?>

    <div class="row-fluid">
      <div class="col-md-4" id="sidebar">
      	<div class="list-group">
      		<a class="list-group-item" href="<?php echo $this->link('/builder'); ?>?djokka=ide">Project Builder</a>
          <a class="list-group-item" href="<?php echo $this->link('/browser'); ?>?djokka=ide">Project Browser</a>
          <a class="list-group-item" href="<?php echo $this->link('/modulecreator'); ?>?djokka=ide">Module Builder</a>
      		<a class="list-group-item" href="<?php echo $this->link('/activeRecord'); ?>?djokka=ide">Active Record Builder</a>
      	</div>
      </div>
      <div class="col-md-8"><?php echo $this->getContent(); ?></div>
    </div>

<?php echo $this->getLayout('footer'); ?>