<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- 新 Bootstrap 核心 CSS 文件 -->
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap.min.css">
<!-- 可选的Bootstrap主题文件（一般不用引入） -->
<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
<title>Elasticsearch</title>
<style>
html, body, div, h1, h2, h3, h4, h5, h6, p, td, tr, ul, ol, li, table, dl, dd, dt, img, form { margin:0; padding:0 }
body { font-family:'微软雅黑'; font-size:12px; line-height:180%;}
table { border-collapse:collapse; border-spacing:0; border:0; padding:0; margin:0 }
img { border:none; }
ol, ul, li { list-style:none }
caption, th { text-decoration:none; }
</style>
</head>
<body>
<div class="container">
	<div class="row">
		<div style=" margin:30px auto; border-bottom:1px solid #eee;"><h3>Elasticsearch</h3></div>
	</div>
	<div class="row" style="margin-bottom:30px;">
		<form class="form-inline" action="index.php?m=Index&a=index" method="get">
  			<div class="form-group">
    				<div class="input-group">
      				<input type="text" class="form-control" id="" name="keywords" placeholder="请输入关键字" style="width:500px;" />
    				</div>
  			</div>
  			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">提交</button>
		</form>
	</div>
	<div class="row">
		<div class="panel panel-primary">
			<div class="panel-heading">
    				<h3 class="panel-title">搜索结果：</h3>
  			</div>
			<div class="panel-body">
    				<ul>
				      <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$va): $mod = ($i % 2 );++$i;?><li><?php echo ($va["title"]); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
				</ul>
  			</div>
		</div>
	</div>
</div>
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="http://cdn.bootcss.com/jquery/1.11.2/jquery.min.js"></script>
<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="http://cdn.bootcss.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</body>
</html>