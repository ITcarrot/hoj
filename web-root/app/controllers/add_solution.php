<?php 
	if(!Auth::check())
		become403Page();
	
	function val() {
		$res = array(false, '', '');
		if(!validateUInt($_POST['problem'])){
			$res[0] = true;
			$res[1] = '不合法的题目编号';
		}elseif(!($problem = queryProblemBrief($_POST['problem']))){
			$res[0] = true;
			$res[1] = '题目不存在';
		}
		if(!validateUInt($_POST['blog'])){
			$res[0] = true;
			$res[2] = '不合法的博客编号';
		}elseif(!($blog = queryBlog($_POST['blog']))){
			$res[0] = true;
			$res[2] = '博客不存在';
		}elseif($blog['poster'] != Auth::id()){
			$res[0] = true;
			$res[2] = '不是您的博客';
		}
		if($res[0])
			return $res;
		return array(false, $problem, $blog);
	}
	
	if ($_POST['check'] == 'check'){
		$val_res = val();
		if($val_res[0])
			die(json_encode($val_res));
		$res = array(false, '', '', '');
		$res[1] = '确认将#'.$val_res[2]['id'].'博客"'.$val_res[2]['title'].'"作为#'.$val_res[1]['id'].'题目"'.$val_res[1]['title'].'"的题解？';
		if($problem2 = DB::selectFirst("select problem_id from solutions where blog_id = {$val_res[2]['id']};",MYSQLI_NUM)[0])
			$res[2] = '将会撤下您的这篇博客在#'.$problem2.'下的题解！';
		if($blog2 = DB::selectFirst("select blog_id from solutions where problem_id = {$val_res[1]['id']} and username = '{$myUser['username']}';",MYSQLI_NUM)[0])
			$res[3] = '将会撤下您在该题原有题解博客#'.$blog2;
		die(json_encode($res));
	}
	if ($_POST['submit'] == 'submit'){
		$val_res = val();
		if($val_res[0])
			die($val_res[1]."\n".$val_res[2]);
		DB::query("delete from solutions where blog_id = {$val_res[2]['id']};");
		DB::query("delete from solutions where problem_id = {$val_res[1]['id']} and username = '{$myUser['username']}';");
		DB::query("insert into solutions value({$val_res[1]['id']}, {$val_res[2]['id']}, '{$myUser['username']}', 0)");
		die('提交成功，请等候管理员审核');
	}
?>

<?php echoUOJPageHeader('添加题解') ?>

<h1 class="page-header">添加题解</h1>
<form method="post" class="form-horizontal">
	<div class="form-group">
		<label class="col-sm-2 control-label">题目编号</label>
		<div class="col-sm-3">
			<input class="form-control" type="text" id="problem" value="<?= $_GET['problem'] ?>">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">博客编号</label>
		<div class="col-sm-3">
			<input class="form-control" type="text" id="blog" value="<?= $_GET['blog'] ?>">
		</div>
	</div>
</form>
<div class="text-center">
	<button onclick="submit()" class="btn btn-default">提交</button>
</div>
<script>
	function submit() {
		$.post('/solution/new',{
			check: 'check',
			problem: $('#problem').val(),
			blog: $('#blog').val()
		},function(data){
			if(data[0]){
				alert(data[1]+'\n'+data[2]);
				return;
			}
			if(confirm(data[1]+'\n'+data[2]+'\n'+data[3])){
				$.post('/solution/new',{
					submit: 'submit',
					problem: $('#problem').val(),
					blog: $('#blog').val()
				},function(data){
					alert(data);
				}).fail(function(){
					alert('提交失败');
				});
			}
		},'json').fail(function(){
			alert('提交失败');
		});
	}
</script>

<?php echoUOJPageFooter() ?>