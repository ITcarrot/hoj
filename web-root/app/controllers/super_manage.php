<?php
	requirePHPLib('form');
	requirePHPLib('judger');

	if ($myUser == null || !isSuperUser($myUser)) {
		become403Page();
	}

	$cur_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
	$tabs_info = array(
		'users' => array(
			'name' => '用户操作',
			'url' => "/super-manage/users"
		),
		'blogs' => array(
			'name' => '博客管理',
			'url' => "/super-manage/blogs"
		),
		'custom-test' => array(
			'name' => '在线IDE记录',
			'url' => '/super-manage/custom-test'
		),
		'log'=>array(
			'name'=>'管理员日志',
			'url'=>'/super-manage/log'
		)
	);
	if (!isset($tabs_info[$cur_tab])) {
		become404Page();
	}
	
	$user_form = new UOJForm('user');
	$user_form->addInput('username', 'text', '用户名', '',
		function ($username) {
			if (!validateUsername($username)) {
				return '用户名不合法';
			}
			if (!queryUser($username)) {
				return '用户不存在';
			}
			if(queryUser($username)['username']=="std"){
				return '请不要欺负可爱的std';
			}
			return '';
		},
		null
	);
	$user_form->addInput('email', 'text', '班级姓名', '',
		function ($username) {
			return '';
		},
		null
	);
	$options = array(
		'setemail'=>'设置班级姓名',
		'ban' => '封禁',
		'deblocking' => '设为普通用户',
		'setcontester' => '设为比赛用户',
		'setsuper'=>'设为管理员',
		'login' => '登录',
		'reset' => '重置密码',
		'delete'=>'删除'
	);
	$user_form->addSelect('op-type', $options, '操作类型', '');
	$user_form->handle = function() {
		global $user_form, $myUser;
		$username = $_POST['username'];
		switch ($_POST['op-type']) {
			case 'setemail':
				DB::update("update user_info set email = '".DB::escape($_POST['email'])."' where username = '{$username}'");
				break;
			case 'ban':
				DB::update("update user_info set usergroup = 'B' where username = '{$username}'");
				break;
			case 'deblocking':
				DB::update("update user_info set usergroup = 'U' where username = '{$username}'");
				break;
			case 'setcontester':
				DB::update("update user_info set usergroup = 'C' where username = '{$username}'");
				break;
			case 'setsuper':
				if($myUser['username'] != 'std') {
					becomeMsgPage('只有std才能设置管理员');
				}
				DB::update("update user_info set usergroup = 'S' where username = '{$username}'");
				break;
			case 'login':
				Auth::login($username);
				$user_form->succ_href = "/";
				break;
			case 'reset':
				$user = queryUser($username);
				$sufs = base64url_encode($user['username'] . "." . md5($user['username'] . "+" . $user['password']));
				$url = HTML::url("/reset-password", array('params' => array('p' => $sufs)));
				becomeMsgPage("<p>请将以下链接发送给用户</p><p><a href='$url'>$url</a></p><p>注意：在用户密码更改前，该链接一直有效，请妥善保管！</p>");
				break;
			case 'delete':
				if(DB::fetch(DB::query("select username from user_info where username='$username' and ac_num=0"))){
					DB::query("delete from user_info where username='$username'");
				}else{
					becomeMsgPage("该用户已ac过题目，不可被删除");
				}
				break;
		}
		DB::manage_log('user',$_POST['op-type'].' '.$username);
	};
	$user_form->extra_validator = function() {
		global $myUser;
		if(!$_COOKIE['can_download']||time()-$_COOKIE['can_download']>300||$_COOKIE['can_download_check']!=md5($_COOKIE['can_download'].$myUser['username'])){
			return '为保障用户的安全，请重新登录，并在登录后5分钟内进行操作！';
		}
		return '';
	};
	$user_form->runAtServer();

	$blog_link_contests = new UOJForm('blog_link_contests');
	$blog_link_contests->addInput('blog_id', 'text', '博客ID', '',
		function ($x) {
			if (!validateUInt($x)) return 'ID不合法';
			if (!queryBlog($x)) return '博客不存在';
			return '';
		},
		null
	);
	$blog_link_contests->addInput('contest_id', 'text', '比赛ID', '',
		function ($x) {
			if (!validateUInt($x)) return 'ID不合法';
			if (!queryContest($x)) return '比赛不存在';
			return '';
		},
		null
	);
	$blog_link_contests->addInput('title', 'text', '标题', '',
		function ($x) {
			return '';
		},
		null
	);
	$options = array(
		'add' => '添加',
		'del' => '删除'
	);
	$blog_link_contests->addSelect('op-type', $options, '操作类型', '');
	$blog_link_contests->handle = function() {
		$blog_id = $_POST['blog_id'];
		$contest_id = $_POST['contest_id'];
		$str = DB::fetch(DB::query("select * from contests where id='${contest_id}'"));
		$all_config = json_decode($str['extra_config'], true);
		$config = $all_config['links'];

		$n = count($config);

		if ($_POST['op-type'] == 'add') {
			$row = array();
			$row[0] = $_POST['title'];
			$row[1] = $blog_id;
			$config[$n] = $row;
		}
		if ($_POST['op-type'] == 'del') {
			for ($i = 0; $i < $n; $i++)
				if ($config[$i][1] == $blog_id) {
					$config[$i] = $config[$n - 1];
					unset($config[$n - 1]);
					break;
				}
		}

		$all_config['links'] = $config;
		$str = json_encode($all_config);
		$str = DB::escape($str);
		DB::query("update contests set extra_config='${str}' where id='${contest_id}'");
		DB::manage_log('blogs',$_POST['op-type'].' blog '.$blog_id.' to(from) contest '.$contest_id);
	};
	$blog_link_contests->runAtServer();

	$blog_link_index = new UOJForm('blog_link_index');
	$blog_link_index->addInput('blog_id2', 'text', '博客ID', '',
		function ($x) {
			if (!validateUInt($x)) return 'ID不合法';
			if (!queryBlog($x)) return '博客不存在';
			return '';
		},
		null
	);
	$blog_link_index->addInput('blog_level', 'text', '置顶级别（删除不用填）', '0',
		function ($x) {
			if (!validateUInt($x)) return '数字不合法';
			if ($x > 3) return '该级别不存在';
			return '';
		},
		null
	);
	$options = array(
		'add' => '添加',
		'del' => '删除'
	);
	$blog_link_index->addSelect('op-type2', $options, '操作类型', '');
	$blog_link_index->handle = function() {
		$blog_id = $_POST['blog_id2'];
		$blog_level = $_POST['blog_level'];
		if ($_POST['op-type2'] == 'add') {
			if (DB::selectFirst("select * from important_blogs where blog_id = {$blog_id}")) {
				DB::update("update important_blogs set level = {$blog_level} where blog_id = {$blog_id}");
			} else {
				DB::insert("insert into important_blogs (blog_id, level) values ({$blog_id}, {$blog_level})");
			}
		}
		if ($_POST['op-type2'] == 'del') {
			DB::delete("delete from important_blogs where blog_id = {$blog_id}");
		}
		DB::manage_log('blogs',$_POST['op-type2'].' blog '.$blog_id.' to(from) anouncement');
	};
	$blog_link_index->runAtServer();

	$blog_deleter = new UOJForm('blog_deleter');
	$blog_deleter->addInput('blog_del_id', 'text', '博客ID', '',
		function ($x) {
			global $myUser;
			if(!$_COOKIE['can_download']||time()-$_COOKIE['can_download']>300||$_COOKIE['can_download_check']!=md5($_COOKIE['can_download'].$myUser['username'])){
				return '为保障数据的安全，请重新登录，并在登录后5分钟内进行操作！';
			}
			if (!validateUInt($x)) {
				return 'ID不合法';
			}
			if (!queryBlog($x)) {
				return '博客不存在';
			}
			return '';
		},
		null
	);
	$blog_deleter->handle = function() {
		deleteBlog($_POST['blog_del_id']);
	};
	$blog_deleter->runAtServer();

	$custom_test_deleter = new UOJForm('custom_test_deleter');
	$custom_test_deleter->addInput('last', 'text', '删除末尾记录', '5',
		function ($x, &$vdata) {
			if (!validateUInt($x)) {
				return '不合法';
			}
			$vdata['last'] = $x;
			return '';
		},
		null
	);
	$custom_test_deleter->handle = function(&$vdata) {
		$all = DB::selectAll("select * from custom_test_submissions order by id asc limit {$vdata['last']}");
		foreach ($all as $submission) {
			$content = json_decode($submission['content'], true);
			unlink(UOJContext::storagePath().$content['file_name']);
		}
		DB::delete("delete from custom_test_submissions order by id asc limit {$vdata['last']}");
	};
	$custom_test_deleter->runAtServer();
?>
<?php
	requireLib('shjs');
	requireLib('morris');
?>
<?php echoUOJPageHeader('系统管理') ?>
<div class="row">
	<div class="col-sm-2">
		<?= HTML::tablist($tabs_info, $cur_tab, 'nav-pills nav-stacked') ?>
	</div>
	<div class="col-sm-10">
		<?php if ($cur_tab === 'users'): ?>
			<h4>用户操作</h4>
			<?php $user_form->printHTML(); ?>
			<script>$('#input-username').autouser();</script>
			<h4>普通用户</h4>
			<form class="bot-buffer-sm">
				<label>用户名</label>
				<div style="display:inline-block">
					<input type="text" name="search_user" id="input-search_user" value="<?= $_GET['search_user']?>" class="form-control input-sm" style="display:inline;width:200px;">
					<script>$('#input-search_user').autouser();</script>
				</div>
				<label>班级姓名</label><input type="text" name="search_real" value="<?= $_GET['search_real']?>" class="form-control input-sm" style="display:inline;width:200px;">
				<button type="submit" class="btn btn-default glyphicon glyphicon-search"></button>
			</form>
			<?php echoLongTable(array('username', 'email'), 'user_info',
			"usergroup='U' and username like '%".DB::escape($_GET['search_user'])."%' and email like '%".DB::escape($_GET['search_real'])."%'",
			'order by username', '<tr><th>用户名</th><th>班级姓名</th></tr>', function($row) {
				echo "<tr><td>".getUserLink($row['username'])."</td><td>${row['email']}</td></tr>";
			}, array()) ?>
			<h4>比赛用户</h4>
			<?php echoLongTable(array('username', 'email'), 'user_info', "usergroup='C'", 'order by username', '<tr><th>用户名</th><th>班级姓名</th></tr>', function($row) {
				echo "<tr><td>".getUserLink($row['username'])."</td><td>${row['email']}</td></tr>";
			}, array()) ?>
			<h4>封禁及注册用户(注册用户请确认班级姓名后再通过审核)</h4>
			<?php echoLongTable(array('username', 'email', 'register_time'), 'user_info', "usergroup='B'", 'order by register_time desc', 
			'<tr><th>用户名</th><th>班级姓名</th><th>注册时间</th></tr>', function($row) {
				echo "<tr><td>".getUserLink($row['username'])."</td><td>${row['email']}</td><td>${row['register_time']}</td></tr>";
			}, array()) ?>
			<h4>管理员</h4>
			<?php echoLongTable(array('username', 'email'), 'user_info', "usergroup='S'", '', '<tr><th>用户名</th><th>班级姓名</th></tr>', function($row) {
				echo "<tr><td>".getUserLink($row['username'])."</td><td>${row['email']}</td></tr>";
			}, array()) ?>
		<?php elseif ($cur_tab === 'blogs'): ?>
			<div>
				<h4>添加到比赛链接</h4>
				<?php $blog_link_contests->printHTML(); ?>
			</div>
			<div>
				<h4>添加到公告</h4>
				<?php $blog_link_index->printHTML(); ?>
			</div>
			<div>
				<h4>删除博客</h4>
				<?php $blog_deleter->printHTML(); ?>
			</div>
		<?php elseif ($cur_tab === 'custom-test'): ?>
			<?php $custom_test_deleter->printHTML() ?>
			<?php
				$submissions_pag = new Paginator(array(
					'col_names' => array('*'),
					'table_name' => 'custom_test_submissions',
					'cond' => '1',
					'tail' => 'order by id asc',
					'page_len' => 5
				));
				foreach ($submissions_pag->get() as $submission){
					$submission_result = json_decode($submission['result'], true);
					echo '<dl class="dl-horizontal">';
					echo '<dt>id</dt>';
					echo '<dd>', "#{$submission['id']}", '</dd>';
					echo '<dt>submit time</dt>';
					echo '<dd>', $submission['submit_time'], '</dd>';
					echo '<dt>submitter</dt>';
					echo '<dd>', $submission['submitter'], '</dd>';
					echo '<dt>judge_time</dt>';
					echo '<dd>', $submission['judge_time'], '</dd>';
					echo '</dl>';
					echoSubmissionContent($submission, 
						array(
							array(
								'name' => 'answer',
								'type' => 'source code',
								'file_name' => 'answer.code'
							),
							array(
								'name' => 'input',
								'type' => 'text',
								'file_name' => 'input.txt'
							)
						));
					echoCustomTestSubmissionDetails($submission_result['details'], "submission-{$submission['id']}-details");
				}
			?>
			<?= $submissions_pag->pagination() ?>
		<?php elseif ($cur_tab === 'log'): ?>
			<h4>搜索（模糊匹配）：</h4>
			<p><form>
				用户名：
				<input type="text" name="log-user" class="form-control input-sm" style="display:inline;width:150px;" value="<?= $_GET['log-user'] ?>">
				&nbsp;IP地址：
				<input type="text" name="log-ip" class="form-control input-sm" style="display:inline;width:150px;" value="<?= $_GET['log-ip'] ?>">
				&nbsp;操作类型：
				<input type="text" name="log-type" class="form-control input-sm" style="display:inline;width:150px;" value="<?= $_GET['log-type'] ?>">
				&nbsp;内容：
				<input type="text" name="log-detail" class="form-control input-sm" style="display:inline;width:150px;" value="<?= $_GET['log-detail'] ?>">
				<input type="submit" class="btn btn-default">
			</form></p>
			<?php echoLongTable(array('*'), 'manage_log',
			"user like '%".DB::escape($_GET['log-user'])."%' and remote_addr like '%".DB::escape($_GET['log-ip'])."%' and type like '%".DB::escape($_GET['log-type'])."%' and detail like '%".DB::escape($_GET['log-detail'])."%'",
			'order by id desc',
			'<tr><th>id</th><th>user</th><th>remote_addr</th><th>time</th><th>type</th><th>detail</th></tr>', function($row) {
				echo "<tr><td>${row['id']}</td><td>",getUserLink($row['user']),"</td><td>${row['remote_addr']}</td>";
				echo "<td>${row['time']}</td><td>${row['type']}</td><td>${row['detail']}</td></tr>";
			}, array('page_len' => 50)) ?>
		<?php endif ?>
	</div>
</div>
<?php echoUOJPageFooter() ?>