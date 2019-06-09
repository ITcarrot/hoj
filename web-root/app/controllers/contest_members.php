<?php
	requirePHPLib('form');
	
	if (!validateUInt($_GET['id']) || !($contest = queryContest($_GET['id']))) {
		become404Page();
	}
	genMoreContestInfo($contest);
	
	$has_contest_permission = hasContestPermission($myUser, $contest);
	$show_ip = $has_contest_permission;
	
	if ($contest['cur_progress'] == CONTEST_NOT_STARTED) {
		$iHasRegistered = $myUser != null && hasRegistered($myUser, $contest);
	
		if ($iHasRegistered) {
			$unregister_form = new UOJForm('unregister');
			$unregister_form->handle = function() {
				global $myUser, $contest;
				DB::query("delete from contests_registrants where username = '{$myUser['username']}' and contest_id = {$contest['id']}");
				updateContestPlayerNum($contest);
			};
			$unregister_form->submit_button_config['class_str'] = 'btn btn-danger btn-xs';
			$unregister_form->submit_button_config['text'] = '取消报名';
			$unregister_form->succ_href = "/contests";
		
			$unregister_form->runAtServer();
		}
	}
	if ($has_contest_permission && $contest['cur_progress'] < CONTEST_FINISHED) {
		$pre_rating_form = new UOJForm('pre_rating');
		$pre_rating_form->handle = function() {
			global $contest;
			foreach (DB::selectAll("select * from contests_registrants where contest_id = {$contest['id']}") as $reg) {
				$user = queryUser($reg['username']);
				DB::update("update contests_registrants set user_rating = {$user['rating']} where contest_id = {$contest['id']} and username = '{$user['username']}'");
			}
			DB::manage_log('contest','recalculate contest '.$contest['id'].' participant rating before the contest');
		};
		$pre_rating_form->submit_button_config['align'] = 'right';
		$pre_rating_form->submit_button_config['class_str'] = 'btn btn-warning';
		$pre_rating_form->submit_button_config['text'] = '重新计算参赛前的 rating';
		$pre_rating_form->submit_button_config['smart_confirm'] = '';
			
		$pre_rating_form->runAtServer();
	}
	if (isSuperUser($myUser) && $contest['cur_progress'] == CONTEST_IN_PROGRESS) {
		$forces = new UOJForm('attend');
		$forces->addInput('username', 'text', '用户名', '',
			function($str){
				global $contest;
				if(!validateUsername($str))
					return '不合法的用户名';
				if(!($user = queryUser($str)))
					return '用户不存在';
				if(!hasRegistered($user, $contest))
					return '用户尚未报名本次比赛';
				return '';
			}, null);
		$forces->handle = function(){
			global $contest;
			$username = $_POST['username'];
			DB::query("update contests_registrants set has_participated = 1 where username='$username' and contest_id = {$contest['id']}");
			DB::manage_log('contest',"force $username attend contest {$contest['id']}");
			sendSystemMsg($username, '强制参赛', "您已经被管理员强制加入比赛{$contest['name']}，您将参与Rating的计算，祝您取得理想的成绩！");
		};
		$forces->submit_button_config['smart_confirm'] = '';
		$forces->runAtServer();
	}
?>
<?php echoUOJPageHeader(HTML::stripTags($contest['name']) . ' - ' . UOJLocale::get('contests::contest registrants')) ?>

<h1 class="text-center"><?= $contest['name'] ?></h1>
<?php if ($contest['cur_progress'] == CONTEST_NOT_STARTED): ?>
	<?php if ($iHasRegistered): ?>
		<div class="pull-right">
			<?php $unregister_form->printHTML(); ?>
		</div>
		<div><a style="color:green">已报名</a></div>
	<?php else: ?>
		<div>当前尚未报名，您可以<a style="color:red" href="/contest/<?= $contest['id'] ?>/register">报名</a>。</div>
	<?php endif ?>
<div class="top-buffer-sm"></div>
<?php endif ?>
<?php if(isset($forces)): ?>
	<h3>强制用户参赛</h3>
	<?php $forces->printHTML() ?>
	<div class="top-buffer-md"></div>
	<script>$('#input-username').autouser();</script>
<?php endif ?>

<?php
	if ($show_ip) {
		$header_row = '<tr><th>#</th><th>'.UOJLocale::get('username').'</th><th>remote_addr</th><th>rating</th><th style="width:4em">参赛</th></tr>';
	
		$ip_owner = array();
		foreach (DB::selectAll("select * from contests_registrants where contest_id = {$contest['id']} order by user_rating asc") as $reg) {
			$user = queryUser($reg['username']);
			$ip_owner[$user['remote_addr']] = $reg['username'];
		}
	} else {
		$header_row = '<tr><th>#</th><th>'.UOJLocale::get('username').'</th><th>rating</th><th style="width:4em">参赛</th></tr>';
	}
	
	echoLongTable(array('*'), 'contests_registrants', "contest_id = {$contest['id']}", 'order by user_rating desc',
		$header_row,
		function($contest, $num) {
			global $myUser;
			global $show_ip, $ip_owner;
			
			$user = queryUser($contest['username']);
			$user_link = getUserLink($contest['username'], $contest['user_rating']);
			if (!$show_ip) {
				echo '<tr>';
			} else {
				if ($ip_owner[$user['remote_addr']] != $user['username']) {
					echo '<tr class="danger">';
				} else {
					echo '<tr>';
				}
			}
			echo '<td>'.$num.'</td>';
			echo '<td>'.$user_link.'</td>';
			if ($show_ip) {
				echo '<td>'.$user['remote_addr'].'</td>';
			}
			echo '<td>'.$contest['user_rating'].'</td>';
			if ($contest['has_participated'] == 1)
				echo '<td style="background:#2CBD2E"></td>';
			else
				echo '<td></td>';
			echo '</tr>';
		},
		array('page_len' => 50,
			'get_row_index' => '',
			'print_after_table' => function() {
				global $pre_rating_form;
				if (isset($pre_rating_form)) {
					$pre_rating_form->printHTML();
				}
			}
		)
	);
?>
<?php echoUOJPageFooter() ?>