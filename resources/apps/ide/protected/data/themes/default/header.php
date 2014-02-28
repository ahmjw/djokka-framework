<!DOCTYPE HTML>
<html>
<head>
  <!-- begin meta -->
  <meta charset="utf-8">
  <meta name="description" content="My builded web using Djokka Framework IDE">
  <meta name="keywords" content="Web, Site, PHP, Djokka, Framework">
  <meta name="author" content="Anonymous">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <!-- end meta -->
  
  <title>My Builded Web</title>
  
  <link href="<?php echo $this->assetUrl('css/bootstrap.min.css'); ?>" rel="stylesheet">
  <link href="<?php echo $this->themeUrl('css/style.css'); ?>" rel="stylesheet">

  <script src="<?php echo $this->assetUrl('js/jquery-1.11.0.min.js'); ?>"></script>
</head>
<body>

<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
<div class="container" id="navbar-container">
<!-- Brand and toggle get grouped for better mobile display -->
<div class="navbar-header">
  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
    <span class="sr-only">Toggle navigation</span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  <a class="navbar-brand" href="<?php echo $this->link('/index'); ?>">Home</a>
</div>

<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse navbar-ex1-collapse">
  <ul class="nav navbar-nav">
  </ul>
  <form class="navbar-form navbar-right" role="search" action="">
    <div class="form-group">
      <input name="s" type="text" class="form-control" placeholder="Search item here">
    </div>
    <button type="submit" class="btn btn-default">OK</button>
  </form>
</div><!-- /.navbar-collapse -->
</div>
</nav>

<div class="container" id="headline-container">
<div id="headline"></div>
</div>

<div class="container bg-white" id="main-container">
  <div class="row" id="header-wrapper">
  </div>