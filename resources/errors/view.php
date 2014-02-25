<!DOCTYPE html>
<html>
<head>
	<title>Error</title>
<style type="text/css">
body{
	font-family: Helvetica, Arial, Times;
	color: #666;
	margin-top: 20px;
	margin-bottom: 20px;
	font-size: 12px;
}
.container {
	width: 600px;
	margin: auto;
}
.error{
	background: #fdd;
}
.row p{
	margin: 5px;
}
.key{
	width: 100px;
	float: left;
	font-weight: bold;
	text-align: right;
}
.value{
	width: 500px;
	float: right;
}
.clearfix{
	clear: both;
}

.trace-list{
}
.trace{
	background: #ffc;
	border-bottom: 1px solid #ddd;
}
</style>
</head>

<body>
<div class="container">

<div class="error">
<div class="row">
	<div class="key"><p>Message :</p></div>
	<div class="value"><p><?php echo $e->getMessage(); ?></p></div>
	<div class="clearfix"></div>
</div>

<div class="row">
	<div class="key"><p>File :</p></div>
	<div class="value"><p><?php echo $e->getFile(); ?></p></div>
	<div class="clearfix"></div>
</div>

<div class="row">
	<div class="key"><p>Line :</p></div>
	<div class="value"><p><?php echo $e->getLine(); ?></p></div>
	<div class="clearfix"></div>
</div>
</div>

<?php
$trace = $e->getTrace();
if(!empty($trace)) {
	echo '<div class="trace-list">';
	foreach ($trace as $item) {
		echo '<div class="trace">';
		if (isset($item['function'])) {
			$class = isset($item['class']) ? $item['class'].$item['type'] : null;
			echo '<div class="row">
				<div class="key"><p>Function :</p></div>
				<div class="value"><p>'.$class.$item['function'].'()</p></div>
				<div class="clearfix"></div>
			</div>';
		}
		if (isset($item['file'])) {
			echo '<div class="row">
				<div class="key"><p>File :</p></div>
				<div class="value"><p>'.$item['file'].'</p></div>
				<div class="clearfix"></div>
			</div>';
		}
		if (isset($item['line'])) {
			echo '<div class="row">
				<div class="key"><p>Line :</p></div>
				<div class="value"><p>'.$item['line'].'</p></div>
				<div class="clearfix"></div>
			</div>';
		}
		echo '</div>';
	}
	echo '</div>';
}
?>

</div>
</body>
</html>