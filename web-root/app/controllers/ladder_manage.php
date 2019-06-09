<?php
	if(!isSuperUser($myUser)){
		become403Page();
	}
	
	$cur_tab=$_GET['tab'];
	if($cur_tab!='rumen'&&$cur_tab!='puji'&&$cur_tab!='tigao'&&$cur_tab!='noi')
		$cur_tab='shengxuan';
	$tabs_info = array(
		'rumen' => array(
			'name' => '入门',
			'url' => "/ladder/manage?tab=rumen"
		),
		'puji' => array(
			'name' => '普及',
			'url' => "/ladder/manage?tab=puji"
		),
		'tigao' => array(
			'name' => '提高',
			'url' => "/ladder/manage?tab=tigao"
		),
		'shengxuan' => array(
			'name' => '省选',
			'url' => "/ladder/manage?tab=shengxuan"
		),
		'noi' => array(
			'name' => 'NOI',
			'url' => "/ladder/manage?tab=noi"
		)
	);
	
	if($_POST['id']){
		if(!validateUInt($_POST['id'])||!DB::fetch(DB::query("select id from exercise where id=".$_POST['id'])))
			becomeMsgPage('练习编号不合法');
		if(!validateUInt($_POST['r']))
			becomeMsgPage('行号不合法');
		if(!validateUInt($_POST['c'])||$_POST['c']>9)
			becomeMsgPage('列号不合法');
		if($_POST['add']=='add'){
			DB::query("delete from ladder where exercise_id={$_POST['id']} and type='$cur_tab'");
			DB::query("insert into ladder (exercise_id,r,c,type) value ({$_POST['id']},{$_POST['r']},{$_POST['c']},'$cur_tab')");
		}else
			DB::query("update ladder set r={$_POST['r']},c={$_POST['c']} where exercise_id={$_POST['id']} and type='$cur_tab'");
		echo '<script>location.replace(location.href);</script>';
	}
	if($_POST['del']){
		if(!validateUInt($_POST['del']))
			becomeMsgPage('练习编号不合法');
		DB::query("delete from ladder where exercise_id={$_POST['del']} and type='$cur_tab'");
		echo '<script>location.replace(location.href);</script>';
	}
	if($_POST['tag']){
		if(!validateUInt($_POST['r']))
			becomeMsgPage('行号不合法');
		$esc_tag=DB::escape(htmlspecialchars($_POST['tag']));
		$esc_ori_tag=DB::escape($_POST['oritag']);
		if($_POST['add']=='add'){
			DB::query("delete from ladder where tag='$esc_tag' and type='$cur_tab'");
			DB::query("insert into ladder (tag,r,type) value ('$esc_tag',{$_POST['r']},'$cur_tab')");
		}else{
			DB::query("delete from ladder where tag='$esc_tag' and type='$cur_tab'");
			DB::query("update ladder set r={$_POST['r']},tag='$esc_tag' where tag='$esc_ori_tag' and type='$cur_tab'");
		}
		echo '<script>location.replace(location.href);</script>';
	}
	if($_POST['deltag']){
		$esc_tag=DB::escape(htmlspecialchars($_POST['deltag']));
		DB::query("delete from ladder where tag='$esc_tag' and type='$cur_tab'");
		echo '<script>location.replace(location.href);</script>';
	}
?>
<?php echoUOJPageHeader('天梯管理') ?>
<div class="row">
<div class="col-sm-4"></div>
<div class="col-sm-4 text-center">
	<?=HTML::tablist($tabs_info, $cur_tab, 'nav-pills')?>
</div>
<div class="col-sm-4 text-right">
	<a href="/ladder?tab=<?=$cur_tab?>" class="btn btn-primary">返回</a>
</div>
</div>
<div class="row">
<div class="col-sm-7">
	<h3 class="text-center">设置天梯练习</h3>
	<h3>教程：</h3>
	<h4>请选择恰当的分类（见顶栏）</h4>
	<h4>网页可以视为一个宽12格，高正无穷的矩形</h4>
	<h4>行坐标确定练习的相对行位置，行号相同的会被显示在同一行</h4>
	<h4>列坐标确定练习的绝对列位置，0表示最靠左，9表示最靠右，每个练习宽度为3格</h4>
	<h4>乱设置的列坐标可能导致练习重叠</h4>
	<h4>练习之间的继承关系请进入练习中设置</h4>
	<div class="table-responsive top-buffer-md">
		<table class="table table-bordered table-hover table-striped table-text-center">
		<thead><tr>
		<th>练习ID</th>
		<th>左上角行坐标</th>
		<th>左上角列坐标</th>
		<th>操作</th>
		</tr></thead>
		<tbody>
	<?php
		$re=DB::query("select * from ladder where type='".$cur_tab."' and tag is null order by r asc, c asc");
		while($row=DB::fetch($re)){
			echo<<<EOD
<tr>
	<form method="post">
	<th><input type="hidden" name="id" value="{$row['exercise_id']}">#{$row['exercise_id']}</th>
	<th><input type="text" name="r" value="{$row['r']}" class="form-control input-sm"></th>
	<th><input type="text" name="c" value="{$row['c']}" class="form-control input-sm"></th>
	<th><input type="submit" value="修改" class="btn btn-primary">
	</form>
	<form method="post" style="display:inline">
	<input type="hidden" name="del" value="{$row['exercise_id']}">
	<input type="submit" value="删除" class="btn btn-danger">
	</form></th>
</tr>
EOD;
		}
	?>
		<tr><form method="post">
			<input type="hidden" name="add" value="add">
			<th><input type="text" name="id" value="" class="form-control input-sm"></th>
			<th><input type="text" name="r" value="" class="form-control input-sm"></th>
			<th><input type="text" name="c" value="" class="form-control input-sm"></th>
			<th><input type="submit" value="添加" class="btn btn-primary"></th>
		</form></tr>
		</tbody>
		</table>
	</div>
</div>
<div class="col-sm-5">
	<h3 class="text-center">设置标签</h3>
	<h3>教程：</h3>
	<h4>请选择恰当的分类（见顶栏）</h4>
	<h4>行坐标确定练习和标签的相对行位置，标签占一整行，请不要让练习和标签同处一行，也不要让多个标签同处一行</h4>
	<h4>请勿添加多个同名标签</h4>
	<div class="table-responsive top-buffer-md">
		<table class="table table-bordered table-hover table-striped table-text-center">
		<thead><tr>
		<th>标签内容</th>
		<th>行坐标</th>
		<th>操作</th>
		</tr></thead>
		<tbody>
		<?php
		$re=DB::query("select * from ladder where type='".$cur_tab."' and exercise_id is null order by r asc, c asc");
		while($row=DB::fetch($re)){
			echo<<<EOD
<tr>
	<form method="post">
	<input type="hidden" name="oritag" value="{$row['tag']}">
	<th><input type="text" name="tag" value="{$row['tag']}" class="form-control input-sm"></th>
	<th><input type="text" name="r" value="{$row['r']}" class="form-control input-sm"></th>
	<th><input type="submit" value="修改" class="btn btn-primary">
	</form>
	<form method="post" style="display:inline">
	<input type="hidden" name="deltag" value="{$row['tag']}">
	<input type="submit" value="删除" class="btn btn-danger">
	</form></th>
</tr>
EOD;
		}
	?>
		<tr><form method="post">
			<input type="hidden" name="add" value="add">
			<th><input type="text" name="tag" value="" class="form-control input-sm"></th>
			<th><input type="text" name="r" value="" class="form-control input-sm"></th>
			<th><input type="submit" value="添加" class="btn btn-primary"></th>
		</form></tr>
		</tbody>
		</table>
	</div>
</div>
</div>
</div>
<?php echoUOJPageFooter() ?>

