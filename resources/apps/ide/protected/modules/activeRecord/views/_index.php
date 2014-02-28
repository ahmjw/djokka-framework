<h1>Active Record Builder</h1>

<?php $model->showError(array(
	'open'=>'<div class="alert alert-danger">',
	'close'=>'</div>'
));
if ($success) {
	echo '<div class="alert alert-success">Module is created successfully</div>';
}
$this('Asset')->js("
$('table th input:checkbox').on('click' , function(){
	var that = this;
	$(this).closest('table').find('tr > td:first-child input:checkbox')
	.each(function(){
		this.checked = that.checked;
		$(this).closest('tr').toggleClass('selected');
	});	
});
");
?>

<form method="post">

<button class="btn btn-success btn-lg" name="build">Build Now</button>

<table class="table">
<thead>
<tr>
	<th><input type="checkbox" /></th>
	<th>Table Name</th>
	<th>Class Name</th>
	<th>Status</th>
</tr>
</thead>
<tbody>
<?php
foreach ($tables as $table) {
	echo '<tr>
		<td><input type="checkbox" name="generate[]" /></td>
		<td>' . $table['name'] . '</td>
		<td><input type="text" name="className[]" class="form-control" value="' . $table['class'] . '" /></td>
		<td>' . ($table['is_exists'] ? '<span class="text text-danger"><b>Exists</b></span>':'<span class="text text-success"><b>Ready</b></span>') . '</td>
	</tr>';
}
?>
</tbody>
</table>

</form>