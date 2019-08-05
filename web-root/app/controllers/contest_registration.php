<?php
	requirePHPLib('form');
	if (!validateUInt($_GET['id']) || !($contest = queryContest($_GET['id']))) {
		become404Page();
	}
	genMoreContestInfo($contest);
	
	if ($myUser == null) {
		redirectToLogin();
	} elseif (hasContestPermission($myUser, $contest) || hasRegistered($myUser, $contest)) {
		redirectTo('/contests');
	} elseif (isContestUser(Auth::user())&&$contest['is_open']!=1) {
		die("<script>alert('该场比赛暂不开放！');window.location.href='/contests';</script>");
	}
	
	$register_form = new UOJForm('register');
	$register_form->handle = function() {
		global $myUser, $contest;
		DB::query("insert into contests_registrants (username, user_rating, contest_id, has_participated) values ('{$myUser['username']}', {$myUser['rating']}, {$contest['id']}, 0)");
		updateContestPlayerNum($contest);
	};
	$register_form->submit_button_config['class_str'] = 'btn btn-primary';
	$register_form->submit_button_config['text'] = '报名比赛';
	$register_form->succ_href = "/contests";
	
	$register_form->runAtServer();
?>
<?php echoUOJPageHeader(HTML::stripTags($contest['name']) . ' - 报名') ?>
<div class="text-center">
	<h1><?= $contest['name'] ?></h1>
	<?php if(isset($contest['extra_config']['unrated'])):?>
		<h4 style="color:red;">本次比赛不计rating</h4>
	<?php else:?>
		<h4>本次比赛rating变化上限为<font color="red"><?= isset($contest['extra_config']['rating_k']) ? $contest['extra_config']['rating_k'] : 400 ?></font></h4>
	<?php endif ?>
</div>
<h1>比赛规则</h1>
<ul><h4>
	<li>比赛报名后不算正式参赛，报名后进了比赛页面也不算参赛，看了题目也不算参赛，<font color="red">提交代码后（包括CE的代码）才算正式参赛</font>。如果未正式参赛则不算rating。</li>
	<li>比赛中请使用标准输入输出</li>
	<li>每题得分以最后一次提交为准，Compile Error不计提交次数</li>
	<li>比赛排名按总分为第一关键字，本次比赛排名的第二关键字为<font color="red"><?=$contest['extra_config']['standings_version'] >= 2 ? '程序总运行时间' : '罚时' ?></font></li>
	<li>如无特别说明，比赛期间提交结果<font color="red">不是最终结果</font>，只要返回<span style="color: rgb(0, 204, 0);">100</span>或<font color="blue">Judgement Failed</font>都是提交成功</li>
	<li>请遵守比赛规则，一位选手在一场比赛内不得报名多个账号，选手之间不能交流或者抄袭代码，如果被检测到将以0分处理或者封禁。</li>
</h4></ul>
<?php $register_form->printHTML(); ?>
<?php echoUOJPageFooter() ?>