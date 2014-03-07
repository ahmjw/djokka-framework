<h1>Module Builder</h1>

<?php $model->showError(array(
	'open'=>'<div class="alert alert-danger">',
	'close'=>'</div>'
));
if ($success) {
	echo '<div class="alert alert-success">Module is created successfully</div>';
}
?>

<form method="post">

<div class="form-group clearfix">
	<label for="input-name1" class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
	<div class="col-lg-9">
		<input type="text" id="input-name1" class="form-control pull-left" name="name" placeholder="Module route (Example: <module>/<submodule>)" value="<?php echo $model->name; ?>" style="width:95%" />
	</div>
</div>

<div class="form-group">
	<div class="col-lg-offset-3 col-lg-9">
		<button class="btn btn-success btn-md" name="build">Build Now</button>
	</div>
</div>

</form>