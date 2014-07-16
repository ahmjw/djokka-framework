<!DOCTYPE html>
<html>
<head>
	<title>Error <?php echo $e->getCode(); ?></title>
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
	border: 1px solid #ccc;
	padding: 20px;
	border-radius: 5px;
	-ms-border-radius: 5px;
	-o-border-radius: 5px;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	box-shadow: 0px 2px 2px #333;
}
.error{
	background: #fee;
	font-size: 16px;
	border-bottom: 4px solid #900;
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
	border-bottom: 4px solid #f93;
	margin-top: 10px;
}
</style>
</head>

<body>
<div class="container">

<h1>Error Message</h1>
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
	echo '<h2>Error Traces</h2>
		<div class="trace-list">';
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