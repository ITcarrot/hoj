<?php
	requirePHPLib('form');
	requirePHPLib('judger');

	if($myUser==NULL){
		become403Page();
	}	
	if (!validateUInt($_GET['id']) || !($submission = querySubmission($_GET['id']))) {
		become404Page();
	}
	$submission_result = json_decode($submission['result'], true);
	
	$problem = queryProblemBrief($submission['problem_id']);
	$problem_extra_config = getProblemExtraConfig($problem);
	$problem_conf = getUOJConf("/var/uoj_data/${problem['id']}/problem.conf");
	
	if ($submission['contest_id']) {
		$contest = queryContest($submission['contest_id']);
		genMoreContestInfo($contest);
	} else {
		$contest = null;
	}
	if (!isSubmissionVisibleToUser($submission, $problem, $myUser)) {
		become403Page();
	}
	if(isContestUser($myUser)&&(!$contest || $contest['is_open']!=1)){
		becomeMsgPage('该功能不对比赛账户开放');
	}
	
	$out_status = explode(', ', $submission['status'])[0];
	
	if($_GET['judging_details']=='AC'){
		if($out_status=='Judged'){
			die('[-1]');
		}
		$res = array();
		$status_details = explode (',',$submission['status_details']);
		$id = (int)preg_replace('/^[\s\S]*?Test #([0-9]+)[\s\S]*?$/','$1',$status_details[0]);
		if(preg_match('/Extra/',$status_details[0]))
			$id*=-1;
		$res[]=array($id,$submission['judger']);
		$len=count($status_details);
		for($i=1;$i<$len;$i++){
			$row=explode(':',$status_details[$i]);
			$res[(int)$row[0]]=preg_replace('/\n/','',$row[1]);
		}
		die(json_encode($res));
	}
	
	$user_submission=$myUser['username']==$submission['submitter'];
	
	$hackable = $submission['score'] == 100 && $problem['hackable'] == 1;
	if ($hackable && !isContestUser(Auth::user())) {
		$hack_form = new UOJForm('hack');	
		
		$hack_form->addTextFileInput('input', '输入数据');
		$hack_form->addCheckBox('use_formatter', '帮我整理文末回车、行末空格、换行符', true);
		$hack_form->handle = function(&$vdata) {
			global $myUser, $problem, $submission;
			if ($myUser == null) {
				redirectToLogin();
			}
			
			if ($_POST["input_upload_type"] == 'file') {
				$tmp_name = UOJForm::uploadedFileTmpName("input_file");
				if ($tmp_name == null) {
					becomeMsgPage('你在干啥……怎么什么都没交过来……？');
				}
			}
			
			$fileName = uojRandAvaiableTmpFileName();
			$fileFullName = UOJContext::storagePath().$fileName;
			if ($_POST["input_upload_type"] == 'editor') {
				file_put_contents($fileFullName, $_POST['input_editor']);
			} else {
				move_uploaded_file($_FILES["input_file"]['tmp_name'], $fileFullName);
			}
			$input_type = isset($_POST['use_formatter']) ? "USE_FORMATTER" : "DONT_USE_FORMATTER";
			DB::insert("insert into hacks (problem_id, submission_id, hacker, owner, input, input_type, submit_time, details, is_hidden) values ({$problem['id']}, {$submission['id']}, '{$myUser['username']}', '{$submission['submitter']}', '$fileName', '$input_type', now(), '', {$problem['is_hidden']})");
		};
		$hack_form->succ_href = "/hacks";
		
		$hack_form->runAtServer();
	}

	if (hasProblemPermission($myUser, $problem)) {
		$rejudge_form = new UOJForm('rejudge');
		$rejudge_form->handle = function() {
			global $submission;
			rejudgeSubmission($submission);
		};
		$rejudge_form->submit_button_config['class_str'] = 'btn btn-primary';
		$rejudge_form->submit_button_config['text'] = '重新测试';
		$rejudge_form->submit_button_config['align'] = 'right';
		$rejudge_form->runAtServer();
	}
	
	if (isSuperUser($myUser)) {
		$delete_form = new UOJForm('delete');
		$delete_form->handle = function() {
			global $submission;
			$content = json_decode($submission['content'], true);
			unlink(UOJContext::storagePath().$content['file_name']);
			DB::delete("delete from submissions where id = {$submission['id']}");
			updateBestACSubmissions($submission['submitter'], $submission['problem_id']);
			updateBestSubmission($submission['submitter'], $submission['problem_id']);
			DB::manage_log('delete','delete submission '.$submission['id'].' (problem '.$submission['problem_id'].', submitter '.$submission['submitter'].')');
		};
		$delete_form->submit_button_config['class_str'] = 'btn btn-danger';
		$delete_form->submit_button_config['text'] = '删除此提交记录';
		$delete_form->submit_button_config['align'] = 'right';
		$delete_form->submit_button_config['smart_confirm'] = '';
		$delete_form->succ_href = "/submissions";
		$delete_form->runAtServer();
	}
	
	$should_show_content = hasViewPermission($problem_extra_config['view_content_type'], $myUser, $problem, $submission);
	$should_show_all_details = hasViewPermission($problem_extra_config['view_all_details_type'], $myUser, $problem, $submission);
	$should_show_details = hasViewPermission($problem_extra_config['view_details_type'], $myUser, $problem, $submission);
	$should_show_details_to_me = isSuperUser($myUser);
	if (explode(', ', $submission['status'])[0] != 'Judged') {
		$should_show_all_details = false;
	}
	if ($contest != null && $contest['cur_progress'] == CONTEST_IN_PROGRESS) {
		if ($contest['extra_config']["problem_{$submission['problem_id']}"] === 'no-details') {
			$should_show_details = false;
		}
	}
	if (!isSubmissionFullVisibleToUser($submission, $contest, $problem, $myUser)) {
		$should_show_content = $should_show_all_details = false;
	}
	if ($contest != null && hasContestPermission($myUser, $contest)) {
		$should_show_details_to_me = true;
		$should_show_content = true;
		$should_show_all_details = true;
	}
	
	if ($should_show_all_details) {
		$styler = new SubmissionDetailsStyler();
		if (!$should_show_details) {
			$styler->fade_all_details = true;
			$styler->show_small_tip = false;
		}
	}
?>
<?php 
	$REQUIRE_LIB['shjs'] = "";
	$REQUIRE_LIB['compile'] = "";
?>
<?php echoUOJPageHeader(UOJLocale::get('problems::submission').' #'.$submission['id']) ?>
<?php echoSubmissionsListOnlyOne($submission, array(), $myUser) ?>

<?php if ($out_status!='Judged'):?>
<div class="panel panel-info">
	<div class="panel-heading">
		<h4 class="panel-title">详细</h4>
	</div>
	<div class="panel-body">	
		<div class="panel-group">
		<h4 id="judger-id"></h4>
		<div class="row" style="padding-left:15px;padding-right:15px;">
			<?php for($i=1;$i<=$problem_conf['n_tests'];$i++):?>
			<div class="panel panel-uoj-jgf col-sm-4" style="padding-left:0;padding-right:0;margin-top:0;" id="test-info-<?= $i ?>">
				<div class="panel-heading">
					<div class="row">
						<div class="col-sm-5">
							<h4 class="panel-title">Test #<?= $i ?>: </h4>
						</div>
						<div class="col-sm-7">Waiting</div>
					</div>
				</div>
			</div>
			<?php if($i%3==0) :?>
				</div><div class="row" style="padding-left:15px;padding-right:15px;margin-top:5px;">
			<?php endif ?>
			<?php endfor ?>
			<?php for($i=1;$i<=$problem_conf['n_ex_tests'];$i++):?>
			<div class="panel panel-uoj-jgf col-sm-4" style="padding-left:0;padding-right:0;margin-top:0;" id="test-info--<?= $i ?>">
				<div class="panel-heading">
					<div class="row">
						<div class="col-sm-5">
							<h4 class="panel-title">ExTest #<?= $i ?>: </h4>
						</div>
						<div class="col-sm-7">Waiting</div>
					</div>
				</div>
			</div>
			<?php if(($i+$problem_conf['n_tests'])%3==0) :?>
				</div><div class="row" style="padding-left:15px;padding-right:15px;margin-top:5px;">
			<?php endif ?>
			<?php endfor ?>
		</div>
	</div>
</div>
<script type="text/javascript">
tests=<?=$problem_conf['n_tests']?>;
function update_point_info(id,info){
	var frame,class_name;
	frame=$('#test-info-'+id);
	class_name='panel panel-uoj-';
	if(info == undefined)
		info='Skipped';
	switch(info){
		case 'Accepted':class_name+='accepted';break;
		case 'Acceptable Answer':
		case 'Judging':class_name+='acceptable-answer';break;
		case 'Time Limit Exceeded':class_name+='tle';break;
		case 'Runtime Error':
		case 'Dangerous Syscalls':class_name+='re';break;
		case 'Memory Limit Exceeded':class_name+='mle';break;
		case 'Skipped':
		case 'Judgment Failed':class_name+='jgf';break;
		default:class_name+='wrong';break;
	}
	class_name+=' col-sm-4';
	frame.attr('class',class_name);
	frame.find('.col-sm-7').text(info);
}
function update_judging_details(){
	$.get(window.location.href,{judging_details:'AC'},function(data){
		if(data[0]==-1){
			location.reload();
		}
		$('#judger-id').text(data[0][1]);
		if(data[0][0]<0){
			for(var i=1;i<=tests;i++)
				update_point_info(i,data[i]);
			for(var i=-1;i>data[0][0];i--)
				update_point_info(i,data[i]);
		}else{
			for(var i=1;i<data[0][0];i++)
				update_point_info(i,data[i]);
		}
		if(data[0][0]!=0)
			update_point_info(data[0][0],'Judging');
	},'json');
}
update_judging_details();
setInterval(update_judging_details,500);
</script>
<?php endif ?>

<?php if ($should_show_all_details): ?>
	<div class="panel panel-info">
		<div class="panel-heading">
			<h4 class="panel-title"><?= UOJLocale::get('details') ?></h4>
		</div>
		<div class="panel-body">
			<h4>评测机：<?=$submission['judger']?></h4>
			<?php if($submission['result_error']!='Compile Error' && $submission_result['compile_result']): ?>
			<strong>编译信息</strong>
			<pre id="compile_result"><?= HTML::escape($submission_result['compile_result']) ?></pre>
			<?php endif ?>
			<?php echoJudgementDetails($submission_result['details'], $styler, 'details') ?>
			<?php if ($should_show_details_to_me): ?>
				<?php if (isset($submission_result['final_result'])): ?>
					<hr />
					<?php echoSubmissionDetails($submission_result['final_result']['details'], 'final_details') ?>
				<?php endif ?>
				<?php if ($styler->fade_all_details): ?>
					<hr />
					<?php echoSubmissionDetails($submission_result['details'], 'final_details') ?>
				<?php endif ?>
			<?php endif ?>
		</div>
	</div>
<?php endif ?>

<?php if ($should_show_content || isSuperUser($myUser) || $user_submission): ?>
	<?php echoSubmissionContent($submission, getProblemSubmissionRequirement($problem)) ?>
	<?php if ($hackable): ?>
		<p class="text-center">
			这程序好像有点Bug，我给组数据试试？ <button id="button-display-hack" type="button" class="btn btn-danger btn-xs">Hack!</button>
		</p>
		<div id="div-form-hack" style="display:none" class="bot-buffer-md">
			<?php $hack_form->printHTML() ?>
		</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#button-display-hack').click(function() {
					$('#div-form-hack').toggle('fast');
				});
			});
		</script>
	<?php endif ?>
<?php endif ?>

<?php if (isset($rejudge_form)): ?>
	<?php $rejudge_form->printHTML() ?>
<?php endif ?>

<?php if (isset($delete_form)): ?>
	<div class="top-buffer-sm">
		<?php $delete_form->printHTML() ?>
	</div>
<?php endif ?>
<?php echoUOJPageFooter() ?>