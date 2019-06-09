<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	
	if(!Auth::check()){
		become403Page();
	}
	
	$custom_test_submission = DB::selectFirst("select * from custom_test_submissions where submitter = '".Auth::id()."' order by id desc limit 1");
	$custom_test_submission_result = json_decode($custom_test_submission['result'], true);
	
	if ($_GET['get'] == 'custom-test-status-details') {
		if ($custom_test_submission == null) {
			echo json_encode(null);
		} else if ($custom_test_submission['status'] != 'Judged') {
			echo json_encode(array(
				'judged' => false,
				'html' => getSubmissionStatusDetails($custom_test_submission)
			));
		} else {
			ob_start();
			echo '<h4>评测机：'.$custom_test_submission['judger'].'</h4>';
			if($custom_test_submission_result['compile_result']&&$custom_test_submission_result['error']!='Compile Error'){
				echo '<strong>编译信息</strong>';
				echo '<pre id="compile_result">',HTML::escape($custom_test_submission_result['compile_result']),'</pre>';
			}
			$styler = new CustomTestSubmissionDetailsStyler();
			echoJudgementDetails($custom_test_submission_result['details'], $styler, 'custom_test_details');
			$result = ob_get_contents();
			ob_end_clean();
			echo json_encode(array(
				'judged' => true,
				'html' => getSubmissionStatusDetails($custom_test_submission),
				'result' => $result
			));
		}
		die();
	}
	
	function handleCustomTestUpload($zip_file_name, $content, $tot_size) {
		global $problem, $contest, $myUser;
		
		$content['config'][] = array('custom_test', 'on');
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
		$result_json = json_encode($result);
		
		DB::insert("insert into custom_test_submissions (submit_time, submitter, content, status, result) values (now(), '{$myUser['username']}', '$esc_content', '{$result['status']}', '$result_json')");
 	}
	
	$custom_test_form = newSubmissionForm('custom_test',
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
		),
		function() {
			return uojRandAvaiableFileName('/tmp/');
		},
		'handleCustomTestUpload');
	$custom_test_form->appendHTML(<<<EOD
<div id="div-custom_test_result"></div>
EOD
	);
	$custom_test_form->succ_href = 'none';
	$custom_test_form->extra_validator = function() {
		global $custom_test_submission;
		if ($custom_test_submission && $custom_test_submission['status'] != 'Judged') {
			return '上一个测评尚未结束';
		}
		return '';
	};
	$custom_test_form->ctrl_enter_submit = true;
	$custom_test_form->setAjaxSubmit(<<<EOD
function(response_text) {custom_test_onsubmit(response_text)}
EOD
	);
	$custom_test_form->submit_button_config['text'] = UOJLocale::get('problems::run').'(Ctrl+Enter)';
	$custom_test_form->runAtServer();
?>
<?php 
	$REQUIRE_LIB['compile'] = "";
?>
<?php echoUOJPageHeader('在线IDE') ?>

<script>
function custom_test_onsubmit(response_text) {
	if (response_text != '') {
		$('#div-custom_test_result').html('<div class="text-danger">' + response_text + '</div>');
		return;
	}
	var update = function() {
		var can_next = true;
		$.get('/ide', {get:'custom-test-status-details'}, 
			function(data) {
				if (data.judged === undefined) {
					$('#div-custom_test_result').html('<div class="text-danger">error</div>');
				} else {
					var judge_status = $('<table class="table table-bordered table-text-center"><tr class="info">' + data.html + '</tr></table>');
					$('#div-custom_test_result').empty();
					$('#div-custom_test_result').append(judge_status);
					if (data.judged) {
						var judge_result = $(data.result);
						judge_result.css('display', 'none');
						$('#div-custom_test_result').append(judge_result);
						$('#compile_result').hoj_compile_result();
						$('#custom_test_details_details_accordion_collapse_custom_test .panel-body pre').attr('id','run_result');
						$('#custom_test_details_details_accordion_collapse_custom_test .panel-body pre').before('<a class="btn btn-xs btn-primary bot-buffer-sm forrun_result" data-clipboard-action="copy" data-clipboard-target="#run_result" id="copy_btn">复制</a>');
						judge_status.hide(500);
						judge_result.slideDown(500);
						can_next = false;
					}
				}
			}, 'json')
		.always(function() {
			if (can_next) {
				setTimeout(update, 500);
			}
		});
	};
	setTimeout(update, 500);
}
</script>

<h3 class="text-center">HOJ在线IDE</h3>
<h4 class="text-center">时间限制20s&nbsp;&nbsp;&nbsp;&nbsp;空间限制2048MB</h4>
<div class="top-buffer-sm"></div>
<?php $custom_test_form->printHTML(); ?>

<?php echoUOJPageFooter() ?>