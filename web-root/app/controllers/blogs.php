<?php
	requirePHPLib('form');
	
	function echoBlogCell($blog) {
		echo '<tr>';
		echo '<td>' . getBlogLink($blog['id']) . '</td>';
		echo '<td>' . getUserLink($blog['poster']) . '</td>';
		echo '<td>' . $blog['post_time'] . '</td>';
		echo '</tr>';
	}
	$header = <<<EOD
	<tr>
		<th width="60%">标题</th>
		<th width="20%">发表者</th>
		<th width="20%">发表日期</th>
	</tr>
EOD;
	$config = array('page_len' => 15);
	$config['table_classes'] = array('table', 'table-hover');
	
	if(isSuperUser($myUser)){
		$cond='1';
	}elseif(Auth::check()){
		$cond='is_hidden = 0';
	}else{
		$cond='is_hidden = 3';
	}
	if($_GET['search']){
		$esc_search=DB::escape($_GET['search']);
		$cond.=" and (id='$esc_search' or title like '%$esc_search%' or content	like '%$esc_search%')";
	}
?>
<?php echoUOJPageHeader(UOJLocale::get('blogs')) ?>
<div class="pull-right">
	<form style="display:inline">
		<input class="form-control input-sm" style="display:inline;width:200px;" type="text" name="search" placeholder="输入关键字查找博客">
		<button type="submit" class="btn btn-primary btn-sm glyphicon glyphicon-search" style="vertical-align:top;"></button>
	</form>
<?php if (Auth::check()): ?>
	<a href="<?= HTML::blog_url(Auth::id(), '/') ?>" class="btn btn-default btn-sm">我的博客首页</a>
<?php endif ?>
</div>
<h3>博客总览</h3>
<h6 class="text-right" style="color:red">博客中的内容只反映作者本人的意见，不代表本站立场</h6>
<?php echoLongTable(array('id', 'poster', 'title', 'post_time', 'zan'), 'blogs', $cond, 'order by post_time desc', $header, 'echoBlogCell', $config); ?>
<?php echoUOJPageFooter() ?>
