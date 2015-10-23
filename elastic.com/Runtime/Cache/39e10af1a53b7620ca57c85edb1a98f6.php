<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Elasticsearch管理界面</title>
<!-- Bootstrap core CSS -->
<link href="Public/bootstrap.min.css" rel="stylesheet">
<link href="data:text/css;charset=utf-8," data-href="Public/bootstrap-theme.min.css" rel="stylesheet" id="bs-theme-stylesheet">
<style>
.navbar-header a,nav a { color:#6f5499; }
.icon-bar { background:#6f5499; }
</style>
</head>
<body>
<div class="container">
    <div class="navbar-header" style="color:#6f5499;">
      <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="/" class="navbar-brand">Elasticsearch</a>
    </div>
    <nav class="collapse navbar-collapse bs-navbar-collapse">
      <ul class="nav navbar-nav">
        <li class="active">
          <a href="/">首页</a>
        </li>
        <li>
          <a href="<?php echo U('Index/singer');?>">歌手管理</a>
        </li>
        <li>
          <a href="<?php echo U('Index/song');?>">歌曲管理</a>
        </li>
        <li>
          <a href="<?php echo U('Index/genre');?>">电台管理</a>
        </li>
	<li>
	  <a href="<?php echo U('Index/genre');?>">API文档</a>
	</li>
      </ul>
    </nav>
</div>
<div class="container">

</div>
<script src="Public/jquery.min.js"></script>
<script src="Public/bootstrap.min.js"></script>
</body>
</html>