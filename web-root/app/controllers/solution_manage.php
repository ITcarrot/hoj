<?php
	if(!Auth::check())
		become403Page();
	
	if($_POST['del'] == 'del'){
		if(!validateUInt($_POST['blog']))
			die();
		if(!($row = DB::selectFirst("select * from solutions where blog_id = {$_POST['blog']}")))
			die();
		if($row['username'] == $myUser['username']){
			DB::query("delete from solutions where blog_id = {$_POST['blog']}");
		}elseif(isSuperUser($myUser)){
			DB::query("delete from solutions where blog_id = {$_POST['blog']}");
			DB::manage_log('solution', "Removed blog {$_POST['blog']} from problem {$row['problem_id']} solution");
			$reason = HTML::escape($_POST['reason']);
			sendSystemMsg($row['username'], '题解被移除', "我们十分遗憾的通知您，由于“ $reason ”，您的博客#{$row['blog_id']}将不再作为题目#{$row['problem_id']}的题解，我们期待您作出相应的修改后再次提交申请！");
		}
		die();
	}
	if(isSuperUser($myUser)){
		if($_POST['submit'] == 'submit'){
			if(!validateUInt($_POST['blog']))
				die();
			if(!($row = DB::selectFirst("select * from solutions where blog_id = {$_POST['blog']} and status = 0")))
				die();
			if($_POST['type']=='1'){
				DB::query("update solutions set status = 1 where blog_id = {$_POST['blog']}");
				DB::manage_log('solution', "Approved blog {$_POST['blog']} as problem {$row['problem_id']} solution");
				sendSystemMsg($row['username'], '题解审核通过', "您将博客#{$row['blog_id']}作为题目#{$row['problem_id']}的题解的申请已获得通过，十分感谢您为其他用户提供的宝贵的学习资料！");
			}else{
				DB::query("delete from solutions where blog_id = {$_POST['blog']}");
				DB::manage_log('solution', "Denied blog {$_POST['blog']} as problem {$row['problem_id']} solution");
				$reason = HTML::escape($_POST['reason']);
				sendSystemMsg($row['username'], '题解审核不通过', "我们十分遗憾的通知您，由于“ $reason ”，您将博客#{$row['blog_id']}作为题目#{$row['problem_id']}的题解的申请未获得通过，我们期待您作出相应的修改后再次提交申请！");
			}
			die();
		}
	}
?>

<?php echoUOJPageHeader('题解管理') ?>
<h1 class="page-header">题解管理</h1>
<?php if(isSuperUser($myUser)): ?>
	<?php echoLongTable(array('*'), 'solutions', '1', 'order by status asc, problem_id asc',
		'<tr><th>用户</th><th>题目</th><th>博客</th><th>操作</th></tr>', function($row, $num){
			$problem = queryProblemBrief($row['problem_id']);
			echo '<tr>';
			echo '<td>',getUserLink($row['username']),'</td>';
			echo '<td>',getProblemLink($problem, '!id_and_title'),'</td>';
			echo '<td>',getBlogLink($row['blog_id']),'</td>';
			echo '<td>';
			if($row['status'] == 0){
				echo '<button class="btn btn-success" onclick="submit(1,',$num,',',$row['blog_id'],')">通过</button>';
				echo '&nbsp;<input type="text" class="form-control input-sm" style="display:inline;width:auto;" placeholder="不通过理由" id="reason',$num,'">&nbsp;';
				echo '<button class="btn btn-danger" onclick="submit(0,',$num,',',$row['blog_id'],')">不通过</button>';
			}else{
				echo '<input type="text" class="form-control input-sm" style="display:inline;width:auto;" placeholder="删除理由" id="reason',$num,'">&nbsp;';
				echo '<button class="btn btn-danger" onclick="del(',$row['blog_id'],',',$num,')">删除</button>';
			}
			echo '</td>';
			echo '</tr>';
		}, array('get_row_index' => '')) ?>
	</div>
	<script>
		function submit(type, id, blog) {
			if (!confirm('确定' + (type ? '通过' : '不通过') + '该题解？'))
				return;
			$.post('/solution/manage',{
				submit: 'submit',
				type: type,
				blog: blog,
				reason: $('#reason'+id).val()
			},function(){
				window.location.reload();
			}).fail(function(){
				alert('网络连接出错');
			});
		}
	</script>
<?php else: ?>
	<?php echoLongTable(array('*'), 'solutions', "username = '{$myUser['username']}'", 'order by status asc, problem_id asc',
		'<tr><th>题目</th><th>博客</th><th>审核状态</th><th>操作</th></tr>', function($row){
			$problem = queryProblemBrief($row['problem_id']);
			echo '<tr>';
			echo '<td>',getProblemLink($problem, '!id_and_title'),'</td>';
			echo '<td>',getBlogLink($row['blog_id']),'</td>';
			echo '<td>',($row['status'] ? '已通过' : '审核中'),'</td>';
			echo '<td><button class="btn btn-danger" onclick="del(',$row['blog_id'],')">删除</button></td>';
		}, array()) ?>
	<div class="text-right">
		<a class="btn btn-default" href="/solution/new">添加题解</a>
	</div>
<?php endif ?>
<script>
	function del(blog, id){
		if(!confirm("确认删除该题解？"))
			return;
		var reason = "";
		if(id != undefined)
			reason = $('#reason' + id).val();
		$.post('/solution/manage',{
			del: 'del',
			blog: blog,
			reason: reason
		},function(){
			window.location.reload();
		}).fail(function(){
			alert('网络连接出错');
		});
	}
</script>
<?php echoUOJPageFooter() ?>