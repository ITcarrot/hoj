<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	
	if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	if($myUser==NULL){
		become403Page();
	}	
	$problem_content = queryProblemContent($problem['id']);
	
	$contest = validateUInt($_GET['contest_id']) ? queryContest($_GET['contest_id']) : null;
	if ($contest != null) {
		genMoreContestInfo($contest);
		$problem_rank = queryContestProblemRank($contest, $problem);
		if ($problem_rank == null) {
			become404Page();
		} else {
			$problem_letter = chr(ord('A') + $problem_rank - 1);
		}
	}
	
	$is_in_contest = false;
	$ban_in_contest = false;
	if ($contest != null) {
		if (!hasContestPermission($myUser, $contest)) {
			if ($contest['cur_progress'] == CONTEST_NOT_STARTED) {
				become403Page();
			} elseif ($contest['cur_progress'] == CONTEST_IN_PROGRESS) {
				if ($myUser == null || !hasRegistered($myUser, $contest)) {
					becomeMsgPage("<h1>比赛正在进行中</h1><p>很遗憾，您尚未报名。比赛结束后再来看吧～</p>");
				} else {
					$is_in_contest = true;
				}
			} else {
				$ban_in_contest = !isProblemVisibleToUser($problem, $myUser);
			}
		}
	} else {
		if (!isProblemVisibleToUser($problem, $myUser)) {
			become403Page();
		}
	}
	if(isContestUser($myUser)&&(!$contest || $contest['is_open']!=1)){
		becomeMsgPage('请从比赛处查看题目');
	}

	$submission_requirement = json_decode($problem['submission_requirement'], true);
	$problem_extra_config = getProblemExtraConfig($problem);
	if($is_in_contest)
		$problem_extra_config['compile_option'] = json_decode($contest['extra_config']['compile_option'], true);
	
	$can_use_zip_upload = true;
	foreach ($submission_requirement as $req) {
		if ($req['type'] == 'source code') {
			$can_use_zip_upload = false;
		}
	}
	
	function handleUpload($zip_file_name, $content, $tot_size) {
		global $problem, $contest, $myUser, $is_in_contest;
		
		$content['config'][] = array('problem_id', $problem['id']);
		if ($is_in_contest && !isset($contest['extra_config']["problem_{$problem['id']}"])) {
			$content['final_test_config'] = $content['config'];
			$content['config'][] = array('test_sample_only', 'on');
		}
		$esc_content = DB::escape(json_encode($content));

		$language = '/';
		foreach ($content['config'] as $row) {
			if (strEndWith($row[0], '_language')) {
				$language = $row[1];
				break;
			}
		}
		if ($language != '/') {
			Cookie::set('uoj_preferred_language', $language, time() + 60 * 60 * 24 * 365, '/');
		}
		$esc_language = DB::escape($language);
 		
		$result = array();
		$result['status'] = "Waiting";
		if(DB::selectFirst("select id from submissions where (status = 'Waiting' or status = 'Judging') and submitter='${myUser['username']}' and contest_id is NULL order by id limit 1")){
			$result['status'] = "Waiting At Tail";
		}
		$result_json = json_encode($result);
		if ($is_in_contest) {
			DB::update("update contests_registrants set has_participated = 1 where username = '{$myUser['username']}' and contest_id = {$contest['id']}");
			DB::query("insert into submissions (problem_id, contest_id, submit_time, submitter, content, language, tot_size, status, result, is_hidden) values (${problem['id']}, ${contest['id']}, now(), '${myUser['username']}', '$esc_content', '$esc_language', $tot_size, '${result['status']}', '$result_json', 0)");
		} else {
			DB::query("insert into submissions (problem_id, submit_time, submitter, content, language, tot_size, status, result, is_hidden) values (${problem['id']}, now(), '${myUser['username']}', '$esc_content', '$esc_language', $tot_size, '${result['status']}', '$result_json', {$problem['is_hidden']})");
		}
		header('Location: /submission/'.DB::insert_id());
		die();
 	}
	
	if ($can_use_zip_upload) {
		$zip_answer_form = newZipSubmissionForm('zip_answer',
			$submission_requirement,
			'uojRandAvaiableSubmissionFileName',
			'handleUpload');
		$zip_answer_form->extra_validator = function() {
			global $ban_in_contest;
			if ($ban_in_contest) {
				return '请耐心等待比赛结束后题目对所有人可见了再提交';
			}
			return '';
		};
		$zip_answer_form->runAtServer();
	}
	
	$answer_form = newSubmissionForm('answer',
		$submission_requirement,
		'uojRandAvaiableSubmissionFileName',
		'handleUpload');
	$answer_form->extra_validator = function() {
		global $ban_in_contest;
		if ($ban_in_contest) {
			return '请耐心等待比赛结束后题目对所有人可见了再提交';
		}
		return '';
	};
	$answer_form->runAtServer();

	if(isset($_POST['code'])){
		if($_POST['code']==$_SESSION['spider']){
			$_SESSION['spider']=uojRandString(5);
			die($problem_content['statement']);
		}
		die('页面已过期');
	}
?>
<?php
	$REQUIRE_LIB['mathjax'] = '';
	$REQUIRE_LIB['shjs'] = '';
?>
<?php echoUOJPageHeader(HTML::stripTags($problem['title']) . ' - ' . UOJLocale::get('problems::problem')) ?>
<div class="pull-right">
	<?= getClickZanBlock('P', $problem['id'], $problem['zan']) ?>
</div>
<?php if ($contest): ?>
	<div class="page-header row">
		<h1 class="col-md-3 text-left"><small><?= $contest['name'] ?></small></h1>
		<h1 class="col-md-6 text-center"><?= $problem_letter ?>. <?= $problem['title'] ?></h1>
		<div class="col-md-3 text-right" id="contest-countdown"></div>
	</div>
	<h4 class="text-center">时间限制：<?= $problem['time_limit'] ?>ms&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	空间限制：<?= $problem['memory_limit'] ?>M</h4>
	<a role="button" class="btn btn-info pull-right" href="/contest/<?= $contest['id'] ?>/problem/<?= $problem['id'] ?>/statistics">
	<span class="glyphicon glyphicon-stats"></span>
	<?= UOJLocale::get('problems::statistics') ?></a>
	<a role="button" class="btn btn-info pull-right" href="/submissions?problem_id=<?= $problem['id'] ?>"><span class="glyphicon glyphicon-list"></span> 本题提交记录</a>

	<?php if ($contest['cur_progress'] <= CONTEST_IN_PROGRESS): ?>
		<script type="text/javascript">
			checkContestNotice(<?= $contest['id'] ?>, '<?= UOJTime::$time_now_str ?>');
			$('#contest-countdown').countdown(<?= $contest['end_time']->getTimestamp() - UOJTime::$time_now->getTimestamp() ?>);
		</script>
	<?php endif ?>
<?php else: ?>
	<h1 class="page-header text-center">#<?= $problem['id']?>. <?= $problem['title'] ?></h1>
	<h4 class="text-center">时间限制：<?= $problem['time_limit'] ?>ms&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	空间限制：<?= $problem['memory_limit'] ?>M
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h4>
	<a role="button" class="btn btn-info pull-right" href="/problem/<?= $problem['id'] ?>/solution"><span class="glyphicon glyphicon-book"></span> 题解</a>
	<a role="button" class="btn btn-info pull-right" href="/problem/<?= $problem['id'] ?>/statistics">
	<span class="glyphicon glyphicon-stats"></span>
	<?= UOJLocale::get('problems::statistics') ?></a>
	<a role="button" class="btn btn-info pull-right" href="/submissions?problem_id=<?= $problem['id'] ?>"><span class="glyphicon glyphicon-list"></span> 本题提交记录</a>
<?php endif ?>

<ul class="nav nav-tabs" role="tablist">
	<li class="active"><a href="#tab-statement" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-book"></span> <?= UOJLocale::get('problems::statement') ?></a></li>
	<li><a href="#tab-submit-answer" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-upload"></span> <?= UOJLocale::get('problems::submit') ?></a></li>
	<li><a href="https://csacademy.com/app/graph_editor/" target="_blank" role="tab">图论画图工具</a></li>
	<?php if (hasProblemPermission($myUser, $problem)): ?>
	<li><a href="/problem/<?= $problem['id'] ?>/manage/statement" role="tab"><?= UOJLocale::get('problems::manage') ?></a></li>
	<?php endif ?>
	<?php if ($contest): ?>
	<li><a href="/contest/<?= $contest['id'] ?>" role="tab"><?= UOJLocale::get('contests::back to the contest') ?></a></li>
	<?php else: ?>
	<li><a id="return_btn" role="tab" style="cursor:pointer">返回</a></li>
	<?php endif ?>
</ul>
<script>
	$('#return_btn').click(function(){
		var tmp=$('#pdf');
		$('#pdf').remove();
		history.go(-1);
		$('#statement').after(tmp);
		$('#return_btn').remove();
	});
</script>
<div class="tab-content">
	<div class="tab-pane active" id="tab-statement">
		<?php if($problem['hackable']): ?>
			<center><h2>本题开放HACK！</h2></center>
		<?php endif ?>
		<article class="top-buffer-md" id="statement">
			<center>
				<h3>题面加载中，请稍候……</h3>
				<p>若长时间没有反应，请检查网络或刷新重试</p>
			</center>
		</article>
		<?= getEncodeJS('statement') ?>
		<?php if($problem_content['file']):?>
			<iframe src="<?= HTML::escape($problem_content['file'])?>" width="100%" id="pdf" frameborder="no"></iframe>
			<script type="text/javascript">
			$('#pdf').css("height",$(window).height());
			$(window).resize(function(){
				$('#pdf').css("height",$(window).height());
			});
			</script>
			<h4><a href="<?= HTML::escape($problem_content['file'])?>">题面无法正常显示？点击我查看</a></h4>
		<?php endif ?>
	</div>
	<div class="tab-pane" id="tab-submit-answer">
		<?php if (isset($problem_extra_config['compile_option'])): ?>
		<script>window.force_lang_option = <?= "'".json_encode($problem_extra_config['compile_option'])."'" ?></script>
		<?php endif ?>
		<div class="top-buffer-sm"></div>
		<?php if ($can_use_zip_upload): ?>
		<?php $zip_answer_form->printHTML(); ?>
		<hr />
		<strong><?= UOJLocale::get('problems::or upload files one by one') ?><br /></strong>
		<?php endif ?>
		<?php $answer_form->printHTML(); ?>
		<?php if (isset($problem_extra_config['compile_option'])): ?>
		<script>
			$('#input-answer_answer_optimized')[0].disabled=1;
			$('#input-answer_answer_cstandard')[0].disabled=1;
		</script>
		<?php endif ?>
	</div>
</div>
<?php echoUOJPageFooter() ?>