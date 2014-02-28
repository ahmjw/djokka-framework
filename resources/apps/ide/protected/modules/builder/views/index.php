<h1>Project Builder</h1>

<div class="alert alert-info">Only the component with status 'Ready' will be generate</div>

<table class="table">
<thead>
<tr>
	<th>Name</th>
	<th>Path</th>
	<th>Status</th>
</tr>
</thead>
<tbody>
<?php
foreach ($data as $key => $item) {
	echo '<tr>
		<td>' . $key . '</td>
		<td>' . $item['path'] . '</td>
		<td>' . ($item['is_exists'] ? '<span class="text text-danger"><b>Exists</b></span>':'<span class="text text-success"><b>Ready</b></span>') . '</td>
	</tr>';
}
?>
</tbody>
</table>

<form method="post">
<button class="btn btn-success btn-lg" name="build">Build Now</button>
</form>