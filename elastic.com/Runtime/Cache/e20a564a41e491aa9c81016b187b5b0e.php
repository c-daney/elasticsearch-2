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
.page {text-align:center;}
.page .current{color: #FFF;font-size: 14px;padding: 4px 12px;display: inline-block;background: #0DA4D3;margin-right: 5px;border-radius: 4px;}
.page a{color: #484848;font-size: 14px;padding: 4px 12px;display: inline-block;background: #FFF;margin-right: 5px;border: 1px solid #DDD;border-radius: 4px;}
.page a:hover { background:#0DA4D3; color:#fff; text-decoration:none; }
</style>
<!--<script src="Public/jquery.min.js"></script>-->
<script src="http://apps.bdimg.com/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="Public/bootstrap.min.js"></script>
<script type="text/javascript">
	$(function(){
		$('.checkAll').click(function(){
			var checkOne = $('.checkone');
			if(this.checked){
				checkOne.prop('checked',true);
			}else{
				checkOne.prop('checked',false);
			}
		});
	})
	/* 按钮点击提交到指定页面 */
	function btnSubmit(form, url) {
	  $(form).attr('action', url).submit();
	}
</script>
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
        <li>
          <a href="<?php echo U('Index/singer');?>">歌手管理</a>
        </li>
        <li class="active">
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
	<h4 style="font-size:16px; padding-left:12px;padding-bottom:12px;">电台管理列表</h4>
	<p style="border-bottom: 1px solid #eee;padding-bottom:12px; margin-bottom:12px;padding-left:12px;"><a href="<?php echo U('Index/genre');?>" title="">genre电台</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo U('Index/mood');?>" title="" style="font-weight:700;font-size:18px;">mood电台</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo U('Index/style');?>" title="">style电台</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo U('Index/theme');?>" title="">theme电台</a></p>
	<form metod="get" action="<?php echo U('Index/mood');?>" name="form2" id="getData" class="form-inline" style="margin-bottom:20px;">
		<input type="hidden" name="m" value="Index" />
		<input type="hidden" name="a" value="mood" />
		<select name="status" class="form-control" onChange="document.getElementById('getData').submit();" style="width:200px;">
			<option value="1" <?php if(in_array(($_GET['status']), explode(',',"1"))): ?>selected="selected"<?php endif; ?>>查看所有有效mood电台</option>
			<option value="0" <?php if(in_array(($_GET['status']), explode(',',"0"))): ?>selected="selected"<?php endif; ?>>查看所有无mood效电台</option>
		</select>
		<input type="text" name="keywords" class="form-control" value="<?php echo ($_GET['keywords']); ?>" placeholder="输入关键字" style="width:350px; margin-left:20px;" />
		<input type="submit" value="搜索" class="btn btn-primary" />
	</form>
	<form method="post" action="" name="form">
	<table class="table table-hover table-bordered">
		<tr style="background:#f3f3f3;"><th>选择</th><th>ID</th><th>电台名</th><th>封面图</th><th>状态</th><th>操作</th></tr>
		<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$va): $mod = ($i % 2 );++$i;?><tr><td><input type="checkbox" name="id[]" value="<?php echo ($va["typeid"]); ?>" class="checkone" /></td><td><?php echo ($va["typeid"]); ?></td><td><?php echo ($va["title"]); ?></td><td><a href="<?php echo ($va["coversrc"]); ?>" title="<?php echo ($va["coversrc"]); ?>" target="_blank"><?php echo (msubstr($va["coversrc"],0,35)); ?></a></td><td><?php echo ($va["status"]); ?></td><td><?php if(!isset($_GET['status']) || $_GET['status'] == 1): ?><a href="<?php echo U('Index/delMoodOne',array('id'=>$va['typeid']));?>" onclick="return confirm('确认要执行删除操作吗？继续，该条数据状态将标为0')">删除</a><?php else: ?><a href="<?php echo U('Index/recoverMoodOne',array('id'=>$va['typeid']));?>" onclick="return confirm('确认要执行恢复操作吗？继续，该条数据状态将标为1')">恢复</a><?php endif; ?> &nbsp;&nbsp;<a href="<?php echo U('Index/updateMoodOne',array('id'=>$va['typeid']));?>" title="">更新</a></td></tr><?php endforeach; endif; else: echo "" ;endif; ?>
		<tr><td><input type="checkbox" class="checkAll" /></td><td colspan="5"><?php if(!isset($_GET['status']) || $_GET['status'] == 1): ?><input type="button" class="btn btn-info btn-xs" value="删除所选" onclick="if (confirm('确定要删除选中的mood电台吗？确认，所选电台状态将标为0')) { btnSubmit('form[name=form]', 'index.php?m=Index&a=delMood');} else { return false;}"><?php else: ?><input type="button" class="btn btn-info btn-xs" value="恢复所选" onclick="if(confirm('确定要恢复状态为无效的mood电台吗？确认，所选电台状态将标为1')){ btnSubmit('form[name=form]','index.php?m=Index&a=recoverMood');}"><?php endif; ?></td></tr>
		<tr><td colspan="6" class="page"><?php echo ($page); ?></td></tr>
	</table>	
	</form>
</div>
</body>
</html>