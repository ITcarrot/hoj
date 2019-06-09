<?php
	requirePHPLib('form');
	
	if (!validateUInt($_GET['id'])) {
		become404Page();
	}
	$exercise=DB::fetch(DB::query("select * from exercise where id ={$_GET['id']}"));
	if(!$exercise){
		become404Page();
	}
	
	if (!isSuperUser($myUser)) {
		become403Page();
	}
	
	$problem_editor = new UOJBlogEditor();
	$problem_editor->cur_data = array(
		'title' => $exercise['name'],
		'content_md' => $exercise['information_md'],
		'content' => $exercise['information'],
		'tags'=>array('该功能无效'),
		'is_exercise_editor'=>true
	);
	$problem_editor->label_text = array_merge($problem_editor->label_text, array(
		'title' => '练习标题',
		'content' => '说明的内容',
	));
	
	$problem_editor->save = function($data) {
		global $exercise;
		DB::update("update exercise set name = '".DB::escape($data['title'])."' where id = {$exercise['id']}");
		DB::update("update exercise set information= '".DB::escape($data['content'])."', information_md = '".DB::escape($data['content_md'])."' where id = {$exercise['id']}");
		DB::manage_log('exercise','edit exercise '.$exercise['id'].' information and title');
	};
	
	$problems_form = newAddDelCmdForm('problems',
		function($cmd) {
			if (!preg_match('/^(\d+)\s*(\[\S+\])?$/', $cmd, $matches)) {
				return "无效题号";
			}
			$problem_id = $matches[1];
			if (!validateUInt($problem_id) || !($problem = queryProblemBrief($problem_id))) {
				return "不存在题号为{$problem_id}的题";
			}
			if (!hasProblemPermission(Auth::user(), $problem)) {
				return "无权添加题号为{$problem_id}的题";
			}
			return '';
		},
		function($type, $cmd) {
			global $exercise;
			
			if (!preg_match('/^(\d+)\s*(\[\S+\])?$/', $cmd, $matches)) {
				return "无效题号";
			}
			
			$problem_id = $matches[1];
			
			if ($type == '+') {
				DB::insert("insert into exercise_problem (exercise,problem) values ({$exercise['id']}, $problem_id)");
			} else if ($type == '-') {
				DB::delete("delete from exercise_problem where exercise = {$exercise['id']} and problem = $problem_id");
			}
			DB::manage_log('exercise','update exercise '.$exercise['id'].' problems '.$type.$problem_id);
		}
	);
	
	$depend_form = newAddDelCmdForm('exercises',
		function($cmd) {
			if (!preg_match('/^(\d+)\s*(\[\S+\])?$/', $cmd, $matches)) {
				return "无效练习号";
			}
			$exercise_id = $matches[1];
			if (!validateUInt($exercise_id) || !(DB::fetch(DB::query("select id from exercise where id =$exercise_id")))) {
				return "不存在编号为{$exercise_id}的练习";
			}
			return '';
		},
		function($type, $cmd) {
			global $exercise;
			
			if (!preg_match('/^(\d+)\s*(\[\S+\])?$/', $cmd, $matches)) {
				return "无效练习号";
			}
			
			$exercise_id = $matches[1];
			
			if ($type == '+') {
				DB::insert("insert into exercise_depend (id,depend) values ({$exercise['id']}, $exercise_id)");
			} else if ($type == '-') {
				DB::delete("delete from exercise_depend where id = {$exercise['id']} and depend = $exercise_id");
			}
			DB::manage_log('exercise','update exercise '.$exercise['id'].' dependents '.$type.$exercise_id);
		}
	);
	
	$problem_editor->runAtServer();
	$problems_form->runAtServer();
	$depend_form->runAtServer();
	
	if(isset($_POST['code'])&&$_POST['code']!=$_SESSION['spider']){
		die('页面已过期');
	}
?>
<?php if($_POST['code']==$_SESSION['spider']): ?>
	<?php $_SESSION['spider']=uojRandString(5); ?>
	<div class="tab-pane active" id="tab-information">
		<?php $problem_editor->printHTML() ?>
	</div>
	
	<div class="tab-pane" id="tab-problems">
		<table class="table table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th>试题名</th>
				</tr>
			</thead>
			<tbody>
<?php
	$result = DB::query("select problem from exercise_problem where exercise = ${exercise['id']} order by problem asc");
	while ($row = DB::fetch($result, MYSQLI_ASSOC)) {
		$problem = queryProblemBrief($row['problem']);
		echo '<tr>', '<td>', $problem['id'], '</td>', '<td>', getProblemLink($problem), ' </td>', '</tr>';
	}
?>
			</tbody>
		</table>
		<p class="text-center">命令格式：命令一行一个，+233表示把题号为233的试题加入练习，-233表示把题号为233的试题从练习中移除</p>
		<?php $problems_form->printHTML(); ?>
	</div>
	<div class="tab-pane" id="tab-depend">
		<table class="table table-hover">
			<thead>
				<tr>
					<th>#</th>
					<th>练习名</th>
				</tr>
			</thead>
			<tbody>
<?php
	$result = DB::query("select depend from exercise_depend where id = ${exercise['id']} order by depend asc");
	while ($row = DB::fetch($result, MYSQLI_ASSOC)) {
		$exercise = DB::fetch(DB::query("select id,name from exercise where id={$row['depend']}"));
		echo '<tr>', '<td>', $exercise['id'], '</td>', '<td><a href="/exercise/',$exercise['id'],'">',$exercise['name'], '</a> </td>', '</tr>';
	}
?>
			</tbody>
		</table>
		<p class="text-center">命令格式：命令一行一个，+233表示把该练习需要先完成233号练习才可查看，-233表示把233号练习从条件中移除</p>
		<?php $depend_form->printHTML(); ?>
	</div>
<?php else :?>
	<?php echoUOJPageHeader(HTML::stripTags($exercise['name']) . ' - 练习管理') ?>
	<h1 class="page-header" align="center"><?=$exercise['name']?> 管理</h1>
	<ul class="nav nav-tabs" role="tablist">
		<li class="active"><a href="#tab-information" role="tab" data-toggle="tab">说明</a></li>
		<li><a href="#tab-problems" role="tab" data-toggle="tab">试题</a></li>
		<li><a href="#tab-depend" role="tab" data-toggle="tab">继承关系</a></li>
		<li><a href="/exercise/<?=$exercise['id']?>" role="tab">返回</a></li>
	</ul>
	<div class="tab-content top-buffer-sm" id="content">
		<center>
			<h3>页面加载中，请稍候……</h3>
			<p>若长时间没有反应，请检查网络或刷新重试</p>
		</center>
	</div>
	<?= getEncodeJS('content') ?>
	<?php echoUOJPageFooter() ?>
<?php endif ?>
