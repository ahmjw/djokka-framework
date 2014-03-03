<h1>Active Record Builder</h1>

<?php $model->showError(array(
	'open'=>'<div class="alert alert-danger">',
	'close'=>'</div>'
));
if ($success) {
	echo '<div class="alert alert-success">Global model is created successfully</div>';
}
?>

<form role="form" class="form-input" method="post">

<div class="form-group clearfix">
	<div class="col-lg-3"><label for="input-tableName1" class="control-label">Table name <span class="text-danger">*</span> </label></div>
	<div class="col-lg-6">
		<?php echo $this->lib('Html')->select('tableName', $model->tableName, $tables, array('class'=>'form-control')); ?>
	</div>
</div>

<div class="form-group clearfix">
	<label for="input-className1" class="col-lg-3 control-label">Class name <span class="text-danger">*</span> </label>
	<div class="col-lg-6">
		<input type="text" id="input-className1" class="form-control pull-left" name="className" placeholder="" value="<?php echo $model->className; ?>" />
	</div>
</div>

<div class="form-group">
	<div class="col-lg-offset-3 col-lg-9">
		<button class="btn btn-success btn-md" name="build">Build Now</button>
	</div>
</div>

</form>