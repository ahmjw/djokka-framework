<!DOCTYPE html>
<html>
<head>
	<title>Error Reporting</title>
<style type="text/css">
body{
	font-family: Helvetica, Arial, Times;
	color: #666;
	margin-top: 20px;
	margin-bottom: 20px;
	font-size: 12px;
}
.container {
	width: 800px;
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
	width: 700px;
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

<h1>Error Reporting</h1>
	<div class="trace-list">
<?php
foreach ($errors as $error) {
	echo '<div class="trace">
		<div class="row">
			<div class="key"><p>Message :</p></div>
			<div class="value"><p>'.$error['message'].'</p></div>
			<div class="clearfix"></div>
		</div>
		<div class="row">
			<div class="key"><p>Line :</p></div>
			<div class="value"><p>'.$error['file'].'</p></div>
			<div class="clearfix"></div>
		</div>
		<div class="row">
			<div class="key"><p>Line :</p></div>
			<div class="value"><p>'.$error['line'].'</p></div>
			<div class="clearfix"></div>
		</div>
	</div>';
}
?>
</div>

</div>
</body>
</html>