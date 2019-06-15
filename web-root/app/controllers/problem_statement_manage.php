<?php
	requirePHPLib('form');
	
	if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	if (!hasProblemPermission($myUser, $problem)) {
		become403Page();
	}
	
	if($_POST['file'])
		DB::query("update problems_contents set file='".DB::escape($_POST['file'])."' where id=".$problem['id']);
	else if($_POST['set_file'])
		DB::query("update problems_contents set file='' where id=".$problem['id']);
	
	$problem_content = queryProblemContent($problem['id']);
	$problem_tags = queryProblemTags($problem['id']);
	
	$problem_editor = new UOJBlogEditor();
	$problem_editor->name = 'problem';
	$problem_editor->blog_url = "/problem/{$problem['id']}";
	$problem_editor->cur_data = array(
		'title' => $problem['title'],
		'content_md' => $problem_content['statement_md'],
		'content' => $problem_content['statement'],
		'tags' => $problem_tags,
		'is_hidden' => $problem['is_hidden']
	);
	$problem_editor->label_text = array_merge($problem_editor->label_text, array(
		'view blog' => '查看题目',
		'blog visibility' => '题目可见性'
	));
	
	$problem_editor->save = function($data) {
		global $problem, $problem_tags;
		DB::update("update problems set title = '".DB::escape($data['title'])."' where id = {$problem['id']}");
		DB::update("update problems_contents set statement = '".DB::escape($data['content'])."', statement_md = '".DB::escape($data['content_md'])."' where id = {$problem['id']}");
		
		if ($data['tags'] !== $problem_tags) {
			DB::delete("delete from problems_tags where problem_id = {$problem['id']}");
			foreach ($data['tags'] as $tag) {
				DB::insert("insert into problems_tags (problem_id, tag) values ({$problem['id']}, '".DB::escape($tag)."')");
			}
		}
		if ($data['is_hidden'] != $problem['is_hidden'] ) {
			DB::update("update problems set is_hidden = {$data['is_hidden']} where id = {$problem['id']}");
			DB::update("update submissions set is_hidden = {$data['is_hidden']} where problem_id = {$problem['id']}");
			DB::update("update hacks set is_hidden = {$data['is_hidden']} where problem_id = {$problem['id']}");
		}
		DB::manage_log('problems','edit problem '.$problem['id'].' statement');
	};
	
	$problem_editor->runAtServer();
	
	if(isset($_POST['code'])){
		if($_POST['code']==$_SESSION['spider']){
			$_SESSION['spider']=uojRandString(5);
			$problem_editor->printHTML();
			die();
		}
		die('页面已过期');
	}
?>
<?php echoUOJPageHeader(HTML::stripTags($problem['title']) . ' - 编辑 - 题目管理') ?>
<h1 class="page-header" align="center">#<?=$problem['id']?> : <?=$problem['title']?> 管理</h1>
<ul class="nav nav-tabs" role="tablist">
	<li class="active"><a href="/problem/<?= $problem['id'] ?>/manage/statement" role="tab">编辑</a></li>
	<li><a href="/problem/<?= $problem['id'] ?>/manage/managers" role="tab">管理者</a></li>
	<li><a href="/problem/<?= $problem['id'] ?>/manage/data" role="tab">数据</a></li>
	<li><a href="/problem/<?=$problem['id']?>" role="tab">返回</a></li>
	<li><a id="button-display-hack" style="cursor: pointer;" role="tab">公式编辑器</a></li>

	<div id="div-form-hack" style="display:none">
		<?php uojIncludeView('math-editor') ?>
	</div>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#button-display-hack').click(function() {
				$('#div-form-hack').toggle('fast');
			});
		});
	</script>
</ul>
<div class="row top-buffer-md">
	<div class="col-sm-3 text-right">
		<label class="top-buffer-sm">设置文件题面：</label>
	</div>
	<form method="post" target="file_set">
		<div class="col-sm-6">
			<input type="text" class="form-control input-sm" name="file" placeholder="输入文件地址" value="<?=$problem_content['file']?>">
		</div>
		<div class="col-sm-3">
			<input type="submit" class="btn btn-default" value="设置" name="set_file">
		</div>
	</form>
	<iframe name="file_set" style="display:none;"></iframe>
</div>
<div id="editor">
	<center>
		<h3>页面加载中，请稍候……</h3>
		<p>若长时间没有反应，请检查网络或刷新重试</p>
	</center>
</div>
<?= getEncodeJS('editor') ?>
<?php echoUOJPageFooter() ?>