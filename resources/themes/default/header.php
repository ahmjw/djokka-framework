<!DOCTYPE HTML>
<html>
<head>
  <!-- begin meta -->
  <meta charset="utf-8">
  <meta name="description" content="IDE (Integrated Development Environtment) for Djokka Framework">
  <meta name="keywords" content="Djokka, Framework, PHP, IDE, Generator">
  <meta name="author" content="Ahmad Jawahir">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <!-- end meta -->
  
  <title>Djokka Framework IDE</title>
  
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
  <a class="navbar-brand" href="<?php echo $this->link('/index'); ?>?djokka=ide">Home</a>
</div>
</div>
</nav>

<div class="container" id="headline-container">
<div id="headline"></div>
</div>

<div class="container bg-white" id="main-container">
  <div class="row" id="header-wrapper">
  </div>