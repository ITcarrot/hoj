<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	requirePHPLib('svn');
	
	if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	if (!hasProblemPermission($myUser, $problem)) {
		become403Page();
	}
	
	//init conf
	$submission_requirement=json_decode($problem['submission_requirement'],true);
	$problem_extra_config=json_decode($problem['extra_config'],true);
	$problem_languages = isset($submission_requirement[0]['languages']) ? $submission_requirement[0]['languages'] : $uojSupportedLanguages;
	$data_dir = "/var/uoj_data/${problem['id']}";
	$prepare_dir = "/var/uoj_data/prepare/${problem['id']}";
	$problem_conf = getUOJConf("$data_dir/problem.conf");
	if ($problem_conf === -1) {
		$problem_conf = array();
	}elseif ($problem_conf === -2) {
		$problem_conf = array();
	}
	if(getUOJConfVal($problem_conf, 'with_implementer', 'off')== 'on'){
		$problem_type = 'interactive';
	}elseif(getUOJConfVal($problem_conf, 'submit_answer', 'off')== 'on'){
		$problem_type = 'submit';
	}else{
		$problem_type = 'ordinary';
	}
	function getKthUOJConfVal($conf,$str,$num,$default)
	{
		return getUOJConfVal($conf,$str.'_'.$num,$default);
	}
	
	//file
	function echoFileNotFound($file_name) {
		echo '<h4>', htmlspecialchars($file_name), '<sub class="text-danger"> ', 'file not found', '</sub></h4>';
	}
	function echoFilePre($file_name, $type=null) {
		global $data_dir;
		$file_full_name = $data_dir . '/' . $file_name;

		if(!file_exists($file_full_name)){
			echoFileNotFound($file_name);
			return;
		}
		if($type=='h'){
			$type='cpp';
		}elseif($type=='pas'){
			$type='pascal';
		}
		$salt=uojRandString(10);
		echo '<h4>', htmlspecialchars($file_name), '</h4>';
		echo '<button class="btn btn-xs btn-primary bot-buffer-sm fortarget'.$salt.'" data-clipboard-action="copy" data-clipboard-target="#target'.$salt.'" id="copy_btn">复制</button>';
		echo '<pre id="target'.$salt.'">';
		if(is_string($type)){
			echo '<code class="sh_'.$type.'">';
		}
		echo uojTextEncode(uojFilePreview($file_full_name, 3000), array('html_escape' => true , 'allow_CR' => true));
		if(is_string($type)){
			echo '</code>';
		}
		echo "</pre>";
	}
	
	//General Data's forms
	if($_POST['problem_data_file_submit']=='submit'){
		if ($_FILES["problem_data_file"]["error"] > 0){
  			$errmsg = "Error: ".$_FILES["problem_data_file"]["error"];
			becomeMsgPage('<div>' . $errmsg . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
  		}else{
			$up_filename="/tmp/".rand(0,100000000)."data.zip";
			move_uploaded_file($_FILES["problem_data_file"]["tmp_name"], $up_filename);
			$zip = new ZipArchive;
			if ($zip->open($up_filename) === TRUE){
				$zip->extractTo("/var/uoj_data/{$problem['id']}");
				$zip->close();
				echo "<script>alert('上传成功！')</script>";
			}else{
				$errmsg = "解压失败！";
				becomeMsgPage('<div>' . $errmsg . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
			}
			unlink($up_filename);
			DB::manage_log('upload','upload problem '.$problem['id'].' data');
  		}
		header("Location: {$_SERVER['REQUEST_URI']}");
		die();
	}
	
	$data_form = new UOJForm('data');
	$data_form->handle = function() {
		global $problem, $myUser;
		$ret = svnSyncProblemData($problem, $myUser);
		if ($ret) {
			becomeMsgPage('<div>' . $ret . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
		}
	};
	$data_form->submit_button_config['class_str'] = 'btn btn-danger col-sm-2 col-sm-push-1';
	$data_form->submit_button_config['text'] = '部署数据';
	$data_form->submit_button_config['smart_confirm'] = '';
	$data_form->runAtServer();
	
	$clear_data_form = new UOJForm('clear_data');
	$clear_data_form->handle = function() {
		global $problem;
		svnClearProblemData($problem);
	};
	$clear_data_form->submit_button_config['class_str'] = 'btn btn-danger col-sm-2 col-sm-push-2';
	$clear_data_form->submit_button_config['text'] = '清空题目数据';
	$clear_data_form->submit_button_config['smart_confirm'] = '';
	$clear_data_form->runAtServer();
	
	$hackable_form = new UOJForm('hackable');
	$hackable_form->handle = function() {
		global $problem, $myUser;
		$problem['hackable'] = 1 - $problem['hackable'];
		$ret = svnSyncProblemData($problem, $myUser);
		if ($ret) {
			becomeMsgPage('<div>' . $ret . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
		}
		DB::query("update problems set hackable = {$problem['hackable']} where id = ${problem['id']}");
		DB::manage_log('problems','set problem '.$problem['id'].' hackable='.$problem['hackable']);
	};
	$hackable_form->submit_button_config['class_str'] = 'btn btn-warning col-sm-2';
	$hackable_form->submit_button_config['text'] = $problem['hackable'] ? '禁止使用hack' : '允许使用hack';
	$hackable_form->runAtServer();
	
	if ($problem['hackable']) {
		$test_std_form = new UOJForm('test_std');
		$test_std_form->handle = function() {
			global $myUser, $problem;
			
			$user_std = queryUser('std');
			if (!$user_std) {
				becomeMsgPage('Please create an user named "std"');
			}
			$requirement = json_decode($problem['submission_requirement'], true);
			$zip_file_name = uojRandAvaiableSubmissionFileName();
			$zip_file = new ZipArchive();
			if ($zip_file->open(UOJContext::storagePath().$zip_file_name, ZipArchive::CREATE) !== true) {
				becomeMsgPage('提交失败');
			}
		
			$content = array();
			$content['file_name'] = $zip_file_name;
			$content['config'] = array();
			foreach ($requirement as $req) {
				if ($req['type'] == "source code") {
					$content['config'][] = array("{$req['name']}_language", "C++");
					$content['config'][] = array("{$req['name']}_optimized", "-Ofast");
					$content['config'][] = array("{$req['name']}_cstandard", "-std=c++17");
				}
			}
			$tot_size = 0;
			foreach ($requirement as $req) {
				$zip_file->addFile("/var/uoj_data/{$problem['id']}/std.cpp", $req['file_name']);
				$tot_size += $zip_file->statName($req['file_name'])['size'];
			}
			$zip_file->close();
		
			$content['config'][] = array('validate_input_before_test', 'on');
			$content['config'][] = array('problem_id', $problem['id']);
			$esc_content = DB::escape(json_encode($content));
			$esc_language = DB::escape('C++');
		 	$result = array();
		 	$result['status'] = "Waiting";
		 	$result_json = json_encode($result);
			$is_hidden = $problem['is_hidden'] ? 1 : 0;
			
			DB::insert("insert into submissions (problem_id, submit_time, submitter, content, language, tot_size, status, result, is_hidden) values ({$problem['id']}, now(), '{$user_std['username']}', '$esc_content', '$esc_language', $tot_size, '{$result['status']}', '$result_json', $is_hidden)");
		};
		$test_std_form->succ_href = "/submissions?problem_id={$problem['id']}";
		$test_std_form->submit_button_config['class_str'] = 'btn btn-danger col-sm-2 col-sm-push-1';
		$test_std_form->submit_button_config['text'] = '检验数据正确性';
		$test_std_form->runAtServer();
	}
	
	if($_POST['modify-data-general']=='submit'){
		unset($problem_conf['with_implementer']);
		unset($problem_conf['submit_answer']);
		if($_POST['type']=='submit'){
			$problem_conf['submit_answer']='on';
		}elseif($_POST['type']=='interactive'){
			$problem_conf['with_implementer']='on';
		}
		
		if(validateUInt($_POST['tests'])){
			$problem_conf['n_tests']=$_POST['tests'];
		}else{
			unset($problem_conf['n_tests']);
		}
		if(validateUInt($_POST['ex_tests'])&&getUOJConfVal($problem_conf,'submit_answer','off')!='on'){
			$problem_conf['n_ex_tests']=$_POST['ex_tests'];
		}else{
			unset($problem_conf['n_ex_tests']);
		}
		if(validateUInt($_POST['sample_tests'])&&getUOJConfVal($problem_conf,'submit_answer','off')!='on'){
			$problem_conf['n_sample_tests']=$_POST['sample_tests'];
		}else{
			unset($problem_conf['n_sample_tests']);
		}
		if(validateUInt($_POST['subtasks'])){
			$problem_conf['n_subtasks']=$_POST['subtasks'];
		}else{
			unset($problem_conf['n_subtasks']);
		}
		
		if($_POST['input_pre']){
			$problem_conf['input_pre']=$_POST['input_pre'];
		}else{
			unset($problem_conf['input_pre']);
		}
		if($_POST['input_suf']){
			$problem_conf['input_suf']=$_POST['input_suf'];
		}else{
			unset($problem_conf['input_suf']);
		}
		if($_POST['output_pre']){
			$problem_conf['output_pre']=$_POST['output_pre'];
		}else{
			unset($problem_conf['output_pre']);
		}
		if($_POST['output_suf']){
			$problem_conf['output_suf']=$_POST['output_suf'];
		}else{
			unset($problem_conf['output_suf']);
		}
		
		if($_POST['checker']&&$_POST['checker']!='off'){
			$problem_conf['use_builtin_checker']=$_POST['checker'];
		}else{
			unset($problem_conf['use_builtin_checker']);
		}
		
		if(validateUInt($_POST['time_limit_ms'])&&getUOJConfVal($problem_conf,'submit_answer','off')!='on'){
			$problem_conf['time_limit_ms']=$_POST['time_limit_ms'];
			$problem_conf['time_limit']=ceil((float)$_POST['time_limit_ms']/1000);
		}else{
			unset($problem_conf['time_limit']);
			unset($problem_conf['time_limit_ms']);
		}
		if(validateUInt($_POST['memory_limit'])&&getUOJConfVal($problem_conf,'submit_answer','off')!='on'){
			$problem_conf['memory_limit']=$_POST['memory_limit'];
		}else{
			unset($problem_conf['memory_limit']);
		}
		if(validateUInt($_POST['output_limit'])&&getUOJConfVal($problem_conf,'submit_answer','off')!='on'){
			$problem_conf['output_limit']=$_POST['output_limit'];
		}else{
			unset($problem_conf['output_limit']);
		}
		
		if($_POST['token']&&getUOJConfVal($problem_conf,'with_implementer','off')=='on'){
			$problem_conf['token']=$_POST['token'];
		}else{
			unset($problem_conf['token']);
		}
		if($_POST['answer_unit']&&getUOJConfVal($problem_conf,'with_implementer','off')=='on'){
			$problem_conf['answer_unit_name']=$_POST['answer_unit'];
		}else{
			unset($problem_conf['answer_unit_name']);
		}
		
		if(isset($_POST['format'])){
			unset($problem_extra_config['dont_use_formatter']);
		}else{
			$problem_extra_config['dont_use_formatter']='';
		}
		
		putUOJConf("$data_dir/problem.conf",$problem_conf);
		$esc_extra_config = DB::escape(json_encode($problem_extra_config));
		DB::query("update problems set extra_config = '$esc_extra_config' where id = '{$problem['id']}'");
		header("Location: {$_SERVER['REQUEST_URI']}");
		DB::manage_log('problems','update problem '.$problem['id'].' general test settings');
		die();
	}
	
	//Data Extra's forms
	$n_subtasks=getUOJConfVal($problem_conf,'n_subtasks',0);
	if($n_subtasks>1 && $_POST['modify-subtasks']=='submit'){
		for($i=1;$i<=$n_subtasks;$i++){
			if(validateUInt($_POST['subtask_end_'.$i])){
				$problem_conf['subtask_end_'.$i]=$_POST['subtask_end_'.$i];
			}else{
				unset($problem_conf['subtask_end_'.$i]);
			}
			if(validateUInt($_POST['subtask_score_'.$i])){
				$problem_conf['subtask_score_'.$i]=$_POST['subtask_score_'.$i];
			}else{
				unset($problem_conf['subtask_score_'.$i]);
			}
			
			unset($problem_conf['subtask_dependence_'.$i]);
			for($j=1;isset($problem_conf['subtask_dependence_'.$i.'_'.$j]);$j++){
				unset($problem_conf['subtask_dependence_'.$i.'_'.$j]);
			}
			if(validateUInt($_POST['subtask_dependence_'.$i])){
				$problem_conf['subtask_dependence_'.$i]=$_POST['subtask_dependence_'.$i];
			}else{
				$depends=explode(',',$_POST['subtask_dependence_'.$i]);
				$cnt=0;
				foreach($depends as $depend){
					if(validateUInt($depend)){
						$cnt++;
					}
				}
				if($cnt>0){
					$cnt=0;
					$problem_conf['subtask_dependence_'.$i]='many';
					foreach($depends as $depend){
						if(validateUInt($depend)){
							$cnt++;
							$problem_conf['subtask_dependence_'.$i.'_'.$cnt]=$depend;
						}
					}
				}
			}
		}
		
		putUOJConf("$data_dir/problem.conf",$problem_conf);
		header("Location: {$_SERVER['REQUEST_URI']}");
		DB::manage_log('problems','update problem '.$problem['id'].' subtask settings');
		die();
	}
	
	if($_POST['modify-tests']=='submit'){
		$n_tests=getUOJConfVal($problem_conf,'n_tests',10);
		$n_subtasks=getUOJConfVal($problem_conf,'n_subtasks',0);
		for($i=1;$i<=$n_tests;$i++){
			if(validateUInt($_POST['score_'.$i])&&$n_subtasks==0){
				$problem_conf['point_score_'.$i]=$_POST['score_'.$i];
			}else{
				unset($problem_conf['point_score_'.$i]);
			}
			if(validateUInt($_POST['time_limit_ms_'.$i])){
				$problem_conf['time_limit_ms_'.$i]=$_POST['time_limit_ms_'.$i];
				$problem_conf['time_limit_'.$i]=ceil($_POST['time_limit_ms_'.$i]/1000);
			}else{
				unset($problem_conf['time_limit_ms_'.$i]);
				unset($problem_conf['time_limit_'.$i]);
			}
			if(validateUInt($_POST['memory_limit_'.$i])){
				$problem_conf['memory_limit_'.$i]=$_POST['memory_limit_'.$i];
			}else{
				unset($problem_conf['memory_limit_'.$i]);
			}
		}
		
		putUOJConf("$data_dir/problem.conf",$problem_conf);
		header("Location: {$_SERVER['REQUEST_URI']}");
		DB::manage_log('problems','update problem '.$problem['id'].' test-point settings');
		die();
	}
	
	$problem_conf_content="";
	foreach($problem_conf as $name => $val){
		$problem_conf_content.=$name.' '.$val."\n";
	}
	$problem_conf_form = new UOJForm('problem_conf');
	$problem_conf_form->addVTextArea('problem_conf_write','手动配置problem.conf',$problem_conf_content,function(){},null);
	$problem_conf_form->handle=function(){
		global $data_dir,$problem;
		file_put_contents("$data_dir/problem.conf",$_POST['problem_conf_write']);
		DB::manage_log('problems','update problem '.$problem['id'].' problem.conf');
	};
	$problem_conf_form->runAtServer();
	
	//problem's forms
	$rejudge_form = new UOJForm('rejudge');
	$rejudge_form->handle = function() {
		global $problem;
		rejudgeProblem($problem);
	};
	$rejudge_form->succ_href = "/submissions?problem_id={$problem['id']}";
	$rejudge_form->submit_button_config['class_str'] = 'btn btn-danger col-sm-2 col-sm-push-2 top-buffer-md';
	$rejudge_form->submit_button_config['text'] = '重测该题所有记录';
	$rejudge_form->submit_button_config['smart_confirm'] = '';
	$rejudge_form->runAtServer();
	
	$rejudge_ac_form = new UOJForm('rejudge_ac');
	$rejudge_ac_form->handle = function() {
		global $problem;
		rejudgeProblemAC($problem);
	};
	$rejudge_ac_form->succ_href = "/submissions?problem_id={$problem['id']}";
	$rejudge_ac_form->submit_button_config['class_str'] = 'btn btn-danger col-sm-2 col-sm-push-3 top-buffer-md';
	$rejudge_ac_form->submit_button_config['text'] = '重测该题AC记录';
	$rejudge_ac_form->submit_button_config['smart_confirm'] = '';
	$rejudge_ac_form->runAtServer();
	
	$delete_submissions_form = new UOJForm('delete_submissions');
	$delete_submissions_form->handle = function() {
		global $myUser;
		if(!$_COOKIE['can_download']||time()-$_COOKIE['can_download']>300||$_COOKIE['can_download_check']!=md5($_COOKIE['can_download'].$myUser['username'])){
			becomeMsgPage('为保障用户提交记录的安全，请重新登录，并在登录后5分钟内进行操作！');
		}
		
		global $problem;
		$res = DB::query("select * from submissions where problem_id={$problem['id']}");
		while($submission = DB::fetch($res)){
			$content = json_decode($submission['content'], true);
			unlink(UOJContext::storagePath().$content['file_name']);
			DB::delete("delete from submissions where id = {$submission['id']}");
			updateBestACSubmissions($submission['submitter'], $submission['problem_id']);
			updateBestSubmission($submission['submitter'], $submission['problem_id']);
		}
		DB::manage_log('delete','delete problem '.$problem['id'].' submissions');
	};
	$delete_submissions_form->submit_button_config['class_str'] = 'btn btn-danger col-sm-2 col-sm-push-4 top-buffer-md';
	$delete_submissions_form->submit_button_config['text'] = '删除该题所有记录';
	$delete_submissions_form->submit_button_config['smart_confirm'] = '';
	$delete_submissions_form->runAtServer();
	
	$allow_language_form = new UOJForm('allow_language');
	foreach($uojSupportedLanguages as $language){
		$allow_language_form->addCheckBox($language, $language, in_array($language,$problem_languages));
	}
	$allow_language_form->handle = function() {
		global $submission_requirement,$uojSupportedLanguages,$problem;
		$submission_requirement[0]['languages'] = array();
		foreach($uojSupportedLanguages as $language){
			if(isset($_POST[$language]) ||($language=='Python2.7'&&isset($_POST['Python2_7'])))
				$submission_requirement[0]['languages'][]=$language;
		}
		$esc_submission_requirement = DB::escape(json_encode($submission_requirement));
		DB::query("update problems set submission_requirement = '$esc_submission_requirement' where id = '{$problem['id']}'");
		DB::manage_log('problems','update problem '.$problem['id'].' allow languages');
	};
	$allow_language_form->submit_button_config['class_str'] = 'btn btn-warning btn-block top-buffer-sm';
	$allow_language_form->runAtServer();
	
	$submission_requirement_form = new UOJForm('submission_requirement');
	$submission_requirement_form -> addVTextArea('submission_requirement_json','手动修改设置',$problem['submission_requirement'],
		function($data){
			if(!is_array(json_decode($data,true))){
				return '不是合法的json格式';
			}
			return '';
		},null);
	$submission_requirement_form -> handle = function(){
		global $problem;
		$esc_submission_requirement = DB::escape($_POST['submission_requirement_json']);
		DB::query("update problems set submission_requirement = '$esc_submission_requirement' where id = '{$problem['id']}'");
		DB::manage_log('problems','update problem '.$problem['id'].' submission_requirement');
	};
	$submission_requirement_form->runAtServer();
	
	$view_type_form = new UOJForm('view_type');
	$view_type_form->addVSelect('view_content_type',
		array('NONE' => '禁止',
				'SELF' => '仅自己',
				'ALL_AFTER_AC' => 'AC后',
				'ALL' => '所有人'
		),
		'查看提交文件:',
		$problem_extra_config['view_content_type']
	);
	$view_type_form->addVSelect('view_all_details_type',
		array('NONE' => '禁止',
				'SELF' => '仅自己',
				'ALL_AFTER_AC' => 'AC后',
				'ALL' => '所有人'
		),
		'查看全部详细信息:',
		$problem_extra_config['view_all_details_type']
	);
	$view_type_form->addVSelect('view_details_type',
		array('NONE' => '禁止',
				'SELF' => '仅自己',
				'ALL_AFTER_AC' => 'AC后',
				'ALL' => '所有人'
		),
		'查看测试点详细信息:',
		$problem_extra_config['view_details_type']
	);
	$view_type_form->handle = function() {
		global $problem, $problem_extra_config;
		$config = $problem_extra_config;
		$config['view_content_type'] = $_POST['view_content_type'];
		$config['view_all_details_type'] = $_POST['view_all_details_type'];
		$config['view_details_type'] = $_POST['view_details_type'];
		$esc_config = DB::escape(json_encode($config));
		DB::query("update problems set extra_config = '$esc_config' where id = '{$problem['id']}'");
		DB::manage_log('problems','update problem '.$problem['id'].' code view settings');
	};
	$view_type_form->submit_button_config['class_str'] = 'btn btn-warning btn-block top-buffer-sm';
	$view_type_form->runAtServer();
	
	$extra_config_form = new UOJForm('extra_config');
	$extra_config_form -> addVTextArea('extra_config_json','手动修改设置',$problem['extra_config'],
		function($data){
			if(!is_array(json_decode($data,true))){
				return '不是合法的json格式';
			}
			return '';
		},null);
	$extra_config_form -> handle = function(){
		global $problem;
		$esc_extra_config = DB::escape($_POST['extra_config_json']);
		DB::query("update problems set extra_config = '$esc_extra_config' where id = '{$problem['id']}'");
		DB::manage_log('problems','update problem '.$problem['id'].' extra_config');
	};
	$extra_config_form->runAtServer();
	
	//checker's form
	if(getUOJConfVal($problem_conf, 'use_builtin_checker', '')=='') {
		$chk_form = new UOJForm('chk_form');
		$chk_form->addSourceCodeInput("chk", UOJLocale::get('problems::source code').':chk.cpp', array('C++'));
		$chk_form->handle = function() {
			global $data_dir;
			if ($_POST["chk_upload_type"] == 'editor') {
				file_put_contents($data_dir.'/chk.cpp',$_POST["chk_editor"]);
			}
		};
		$chk_form->submit_button_config['text'] = '保存';
		$chk_form->runAtServer();
	}
	
	//generator's form
	$gen_form = new UOJForm('gen_form');
	$gen_form->addInput('gen_n_tests', 'text', '测试点数量', '10',
		function($n_tests){
			if(!validateUInt($n_tests) || $n_tests <= 0 || $n_tests > 600)
				return '测试点数量应为1~600间的整数';
		}, null);
	$gen_form->extra_validator = function(){
		global $data_dir, $prepare_dir;
		if(file_exists($prepare_dir))
			return "please wait until the last sync finish";
		if(!file_exists($data_dir.'/gen.cpp'))
			return '找不到gen.cpp';
		if(!file_exists($data_dir.'/std.cpp'))
			return '找不到std.cpp';
	};
	$gen_form->handle = function(){
		global $data_dir, $prepare_dir,$problem;
		$n_tests = $_POST['gen_n_tests'];
		$time_limit = (int)(600/$n_tests);
		$output_limit = (int)(512/$n_tests);
		$runner = $_SERVER['DOCUMENT_ROOT']."/app/models/run_program";
		exec("mkdir $prepare_dir");
		session_write_close();
		try{
			$cmd_prefix = "$runner >$prepare_dir/run_compiler_result.txt --in=/dev/null --out=stderr --err=$prepare_dir/compiler_result.txt --tl=10 --ml=512 --ol=64 --type=compiler --work-path=$prepare_dir";
			exec("cp $data_dir/gen.cpp $prepare_dir/gen.cpp");
			exec("$cmd_prefix /usr/bin/g++ -o gen gen.cpp -lm -Ofast -DONLINE_JUDGE -std=c++17");
			$fp = fopen("$prepare_dir/run_compiler_result.txt", "r");
			if (fscanf($fp, '%d %d %d %d', $rs, $used_time, $used_memory, $exit_code) != 4) {
				$rs = 7;
			}
			fclose($fp);
			if ($rs != 0 || $exit_code != 0) {
				if ($rs == 0) {
					throw new Exception("<strong>Generator</strong> : compile error<pre>\n" . uojFilePreview("$prepare_dir/compiler_result.txt", 500) . "\n</pre>");
				} elseif ($rs == 7) {
					throw new Exception("<strong>Generator</strong> : compile error. No comment");
				} else {
					throw new Exception("<strong>Generator</strong> : compile error. Compiler " . judgerCodeStr($rs));
				}
			}
			
			exec("cp $data_dir/std.cpp $prepare_dir/std.cpp");
			exec("$cmd_prefix /usr/bin/g++ -o std std.cpp -lm -Ofast -DONLINE_JUDGE -std=c++17");
			$fp = fopen("$prepare_dir/run_compiler_result.txt", "r");
			if (fscanf($fp, '%d %d %d %d', $rs, $used_time, $used_memory, $exit_code) != 4) {
				$rs = 7;
			}
			fclose($fp);
			if ($rs != 0 || $exit_code != 0) {
				if ($rs == 0) {
					throw new Exception("<strong>Std</strong> : compile error<pre>\n" . uojFilePreview("$prepare_dir/compiler_result.txt", 500) . "\n</pre>");
				} elseif ($rs == 7) {
					throw new Exception("<strong>Std</strong> : compile error. No comment");
				} else {
					throw new Exception("<strong>Std</strong> : compile error. Compiler " . judgerCodeStr($rs));
				}
			}
			
			for($i = 1;$i <= $n_tests;$i++){
				file_put_contents("$prepare_dir/input",$i);
				exec("$runner >$prepare_dir/run_program_result.txt --in=$prepare_dir/input --out=$prepare_dir/hoj$i.in --err=/dev/null --tl=$time_limit --ml=2048 --ol=$output_limit --work-path=$prepare_dir ./gen");
				$fp = fopen("$prepare_dir/run_program_result.txt", "r");
				if (fscanf($fp, '%d %d %d %d', $rs, $used_time, $used_memory, $exit_code) != 4) {
					$rs = 7;
				}
				fclose($fp);
				if ($rs != 0)
					throw new Exception("<strong>Generator in test $i</strong> : " . judgerCodeStr($rs));
				
				exec("$runner >$prepare_dir/run_program_result.txt --in=$prepare_dir/hoj$i.in --out=$prepare_dir/hoj$i.out --err=/dev/null --tl=$time_limit --ml=2048 --ol=$output_limit --work-path=$prepare_dir ./std");
				$fp = fopen("$prepare_dir/run_program_result.txt", "r");
				if (fscanf($fp, '%d %d %d %d', $rs, $used_time, $used_memory, $exit_code) != 4) {
					$rs = 7;
				}
				fclose($fp);
				if ($rs != 0)
					throw new Exception("<strong>Std in test $i</strong> : " . judgerCodeStr($rs));
			}
			for($i = 1;$i <= $n_tests;$i++){
				exec("cp $prepare_dir/hoj$i.in $data_dir/hoj$i.in");
				exec("cp $prepare_dir/hoj$i.out $data_dir/hoj$i.out");
			}
		}catch(Exception $e) {
			exec("rm $prepare_dir -r");
			becomeMsgPage('<div>' . $e->getMessage() . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
		}
		exec("rm $prepare_dir -r");
		session_start();
	};
	$gen_form->submit_button_config['class_str'] = 'btn btn-danger';
	$gen_form->submit_button_config['text'] = '开始生成';
	$gen_form->submit_button_config['smart_confirm'] = '';
	$gen_form->runAtServer();
	
	//stdval's form
	$val_form = new UOJForm('val_form');
	$val_form->addSourceCodeInput("val", UOJLocale::get('problems::source code').':val.cpp', array('C++'));
	$val_form->handle = function() {
		global $data_dir;
		if ($_POST["val_upload_type"] == 'editor') {
			file_put_contents($data_dir.'/val.cpp',$_POST["val_editor"]);
		}
	};
	$val_form->submit_button_config['text'] = '保存';
	$val_form->runAtServer();
	
	//界面
	function echoExData(){
		global $problem_conf,$problem_conf_form;
		$n_subtasks = getUOJConfVal($problem_conf,'n_subtasks',0);
		$n_tests=getUOJConfVal($problem_conf,'n_tests',10);
		if($n_subtasks>1){
			echo '<h3 class="text-center">子任务设置</h3>';
			echo '<form method="post" class="form-horizontal">';
			for($i=1;$i<=$n_subtasks;$i++){
				$startI=getKthUOJConfVal($problem_conf,'subtask_end',$i-1,0)+1;
				$endI=getKthUOJConfVal($problem_conf,'subtask_end',$i,0);
				$tfull=getKthUOJConfVal($problem_conf,'subtask_score',$i,'');
				$tfull_default=floor(100/$n_subtasks);
				if(getKthUOJConfVal($problem_conf,'subtask_dependence',$i,'none')=='many'){
					$depend=array();
					for($j=1;getKthUOJConfVal($problem_conf,'subtask_dependence_'.$i,$j,0)!=0;$j++){
						$depend[]=getKthUOJConfVal($problem_conf,'subtask_dependence_'.$i,$j,0);
					}
					$depend=join($depend,',');
				}else{
					$depend=getKthUOJConfVal($problem_conf,'subtask_dependence',$i,'');
				}
				echo <<<EOD
<div class="form-group">
	<label class="control-label col-sm-2" style="text-align:center">子任务$i</label>
	<label class="control-label col-sm-1" style="float:left">测试点从</label>
	<div class="col-sm-1" style="padding-right:5px;padding-left:5px;">
		<input class="form-control" disabled="true" value="$startI" id="subtask_start_$i">
	</div>
	<label class="control-label" style="float:left">到</label>
	<div class="col-sm-1" style="padding-left:5px;">
		<input class="form-control" value="$endI" name="subtask_end_$i" onchange="update_subtask_end($i)" id="subtask_end_$i">
	</div>
	<label class="control-label col-sm-1 text-right">分值</label>
	<div class="col-sm-2">
		<input class="form-control" name="subtask_score_$i" value="$tfull" placeholder="$tfull_default">
	</div>
	<label class="control-label col-sm-1 text-right">继承于</label>
	<div class="col-sm-2">
		<input class="form-control" name="subtask_dependence_$i" value="$depend">
	</div>
</div>
EOD;
			}
			echo <<<EOD
<input type="hidden" name="modify-subtasks" value="submit">
<div class="text-center">
	<input type="submit" class="btn btn-default" value="提交">
</div>
</form>
EOD;
		}elseif($n_subtasks==1){
			echo <<<EOD
<h3 class="text-center">子任务设置</h3>
<div class="form-group form-horizontal row">
	<label class="control-label col-sm-5" style="text-align:center">子任务1</label>
	<label class="control-label col-sm-1" style="float:left">测试点从</label>
	<div class="col-sm-1" style="padding-right:5px;padding-left:5px;">
		<input class="form-control" disabled="true" value="1">
	</div>
	<label class="control-label" style="float:left">到</label>
	<div class="col-sm-1" style="padding-left:5px;">
		<input class="form-control" value="$n_tests" disabled="true">
	</div>
	<label class="control-label col-sm-1 text-right">分值</label>
	<div class="col-sm-2">
		<input class="form-control" value="100" disabled="true">
	</div>
</div>
EOD;
		}
		
		echo '<h3 class="text-center">测试点设置</h3>';
		echo '<form method="post" class="form-horizontal">';
		$score=floor(100/$n_tests);
		$time_limit_ms=getUOJConfVal($problem_conf,'time_limit_ms',getUOJConfVal($problem_conf,'time_limit',1)*1000);
		$memory_limit=getUOJConfVal($problem_conf,'memory_limit',256);
		for($i=1;$i<=$n_tests;$i++){
			$score_i=getKthUOJConfVal($problem_conf,"point_score",$i,'');
			$time_limit_i=getKthUOJConfVal($problem_conf,'time_limit',$i,'');
			$time_limit_ms_i=getKthUOJConfVal($problem_conf,'time_limit_ms',$i,($time_limit_i=='' ? '' : $time_limit_i*1000));
			$memory_limit_i=getKthUOJConfVal($problem_conf,'memory_limit',$i,'');
			if($n_subtasks == 0){
				echo <<<EOD
<div class="form-group">
	<label class="control-label col-sm-2" style="text-align:center">测试点$i</label>
	<label class="control-label col-sm-1 text-right">分值</label>
	<div class="col-sm-2">
		<input class="form-control" name="score_$i" value="$score_i" placeholder="$score">
	</div>
EOD;
			}else{
				echo <<<EOD
<div class="form-group">
	<label class="control-label col-sm-5" style="text-align:center">测试点$i</label>
EOD;
			}
			echo <<<EOD
	<label class="control-label col-sm-1 text-right">时间限制</label>
	<div class="col-sm-2">
		<input class="form-control" name="time_limit_ms_$i" value="$time_limit_ms_i" placeholder="$time_limit_ms">
	</div>
	<label class="control-label" style="float:left">MS</label>
	<label class="control-label col-sm-1 text-right">空间限制</label>
	<div class="col-sm-2">
		<input class="form-control" name="memory_limit_$i" value="$memory_limit_i" placeholder="$memory_limit">
	</div>
	<label class="control-label" style="float:left">MB</label>
</div>
EOD;
		}
		echo <<<EOD
<div class="form-group">
	<label class="control-label col-sm-5" style="text-align:center">Extra Tests和Hack Test</label>
	<label class="control-label col-sm-1 text-right">时间限制</label>
	<div class="col-sm-2">
		<input class="form-control" value="$time_limit_ms" disabled="1">
	</div>
	<label class="control-label" style="float:left">MS</label>
	<label class="control-label col-sm-1 text-right">空间限制</label>
	<div class="col-sm-2">
		<input class="form-control" value="$memory_limit" disabled="1">
	</div>
	<label class="control-label" style="float:left">MB</label>
</div>
<input type="hidden" name="modify-tests" value="submit">
<div class="text-center">
	<input type="submit" class="btn btn-default" value="提交">
</div>
</form>
EOD;

		echo '<h3 class="text-center">Problem.conf</h3>';
		echo '<div class="row"><div class="col-sm-6">';
		$problem_conf_form->printHTML();
		echo '</div><div class="col-sm-6"><div class="table-responsive">';
		echo '<table class="table table-bordered table-hover table-striped table-text-center"><tbody>';
		foreach($problem_conf as $name => $val){
			echo '<tr><td>',$name,'</td><td>',$val,'</td></tr>';
		}
		echo '</tbody></table></div></div></div>';
		
		echo <<<EOD
<script>
$('#exdata').find('input:text').change(function(){
	if(Number($(this).val())==Number($(this).attr('placeholder'))){
		$(this).val('');
	}
});
</script>
EOD;
	}
	function echoExproblem(){
		global $submission_requirement,$problem_extra_config;
		global $rejudge_form,$rejudge_ac_form,$delete_submissions_form,$allow_language_form,$submission_requirement_form,$view_type_form,$extra_config_form;
		echo '<div class="row">';
		$rejudge_form->printHTML();
		$rejudge_ac_form->printHTML();
		$delete_submissions_form->printHTML();
		echo '</div>';
		echo '<h4>提交文件配置</h4>';
		echo '<div class="row"><div class="col-sm-3 col-sm-push-1">';
		echo '<label class="control-label">修改允许使用的语言</label>';
		$allow_language_form->printHTML();
		echo '</div><div class="col-md-7 col-md-push-2">';
		echo '<pre>',HTML::escape(json_encode($submission_requirement, JSON_PRETTY_PRINT)),'</pre>';
		$submission_requirement_form->printHTML();
		echo '</div></div>';
		echo '<h4>题目其它配置</h4>';
		echo '<div class="row"><div class="col-sm-3 col-sm-push-1">';
		echo '<label class="control-label">修改提交记录可视权限</label>';
		$view_type_form->printHTML();
		echo '</div><div class="col-md-7 col-md-push-2">';
		echo '<pre>',HTML::escape(json_encode($problem_extra_config, JSON_PRETTY_PRINT)),'</pre>';
		$extra_config_form->printHTML();
		echo '</div></div>';
	}
	function echoTest(){
		global $problem_conf;
		$n_tests = getUOJConfVal($problem_conf, 'n_tests', 0);
		for($i=1;$i<=$n_tests;$i++){
			echo '<div class="row"><div class="col-sm-6">';
			echoFilePre(getUOJProblemInputFileName($problem_conf, $i));
			echo '</div><div class="col-sm-6">';
			echoFilePre(getUOJProblemOutputFileName($problem_conf, $i));
			echo '</div></div>';
		}
	}
	function echoExtests(){
		global $problem_conf;
		$n_ex_tests = getUOJConfVal($problem_conf, 'n_ex_tests', 0);
		$n_sample_tests = getUOJConfVal($problem_conf, 'n_sample_tests', 0);
		if($n_sample_tests > 0){
			echo '<h3 class="text-center">Sample Tests</h3>';
		}
		for($i=1;$i<=$n_ex_tests;$i++){
			echo '<div class="row"><div class="col-sm-6">';
			echoFilePre(getUOJProblemExtraInputFileName($problem_conf, $i));
			echo '</div><div class="col-sm-6">';
			echoFilePre(getUOJProblemExtraOutputFileName($problem_conf, $i));
			echo '</div></div>';
			if($i == $n_sample_tests){
				echo '<hr><h3 class="text-center">Extra Tests</h3>';
			}
		}
	}
	function echoChecker(){
		global $problem_conf, $chk_form;
		$chk = getUOJConfVal($problem_conf, 'use_builtin_checker', '');
		if($chk == ''){
			echo '<h4>快捷提交SPJ：</h4>';
			$chk_form->printHTML();
			echo <<<EOD
<script>
	$('#form-group-chk .radio').css('display','none');
	$('#input-chk_language')[0].readonly=1;
	$('#input-chk_optimized')[0].disabled=1;
	$('#input-chk_cstandard')[0].disabled=1;
	$('#input-chk_optimized').val('-Ofast');
	$('#input-chk_cstandard').val('-std=c++17');
</script>
EOD;
			echoFilePre('chk.cpp','cpp');
		}else{
			echo '<h4>use_builtin_checker: ',$chk,'</h4>';
		}
	}
	function echoGenerator(){
		global $gen_form;
		echo '<h3>在线生成数据</h3>';
		echo '<p>上传gen.cpp和std.cpp，在线生成输入文件hojX.in和输出文件hojX.out，X为数据编号</p>';
		echo '<p>gen.cpp中请从标准输入读入一个整数，表示数据编号，并将数据输出到标准输出</p>';
		echo '<p>生成一个输入文件时间不得超过 600/测试点数目s，std运行一次时间不得超过600/测试点数目s，程序运行空间限制2048MB，生成的单个文件大小不得超过512/测试点数目MB</p>';
		$gen_form->printHTML();
		echoFilePre('gen.cpp','cpp');
		echoFilePre('std.cpp','cpp');
	}
	function echoStdVal(){
		global $val_form;
		echo '<h4>快捷提交Val：</h4>';
		$val_form->printHTML();
			echo <<<EOD
<script>
	$('#form-group-val .radio').css('display','none');
	$('#input-val_language')[0].readonly=1;
	$('#input-val_optimized')[0].disabled=1;
	$('#input-val_cstandard')[0].disabled=1;
	$('#input-val_optimized').val('-Ofast');
	$('#input-val_cstandard').val('-std=c++17');
</script>
EOD;
		echoFilePre('val.cpp','cpp');
		echoFilePre('std.cpp','cpp');
	}
	function echoRequire(){
		global $data_dir;
		$dir = $data_dir . '/require';
		if(!is_dir($dir)){
			return;
		}
		$dir = scandir($dir);
		foreach($dir as $file){
			$file = 'require/'.$file;
			if(is_file($data_dir.'/'.$file)){
				$type = substr($file,strrpos($file,'.')+1);
				echoFilePre($file,$type);
			}
		}
	}
	if(isset($_POST['frame'])){
		switch($_POST['frame']){
			case 'exdata':echoExData();break;
			case 'problem':echoExproblem();break;
			case 'test':echoTest();break;
			case 'extest':echoExtests();break;
			case 'chk':echoChecker();break;
			case 'gen':echoGenerator();break;
			case 'hack':echoStdVal();break;
			case 'require':echoRequire();break;
		}
		die();
	}
?>
<?php
	$REQUIRE_LIB['dialog'] = '';
	$REQUIRE_LIB['shjs'] = '';
?>
<?php echoUOJPageHeader(HTML::stripTags($problem['title']) . ' - 数据 - 题目管理') ?>
<h1 class="page-header" align="center">#<?=$problem['id']?> : <?=$problem['title']?> 管理</h1>
<ul class="nav nav-tabs" role="tablist">
	<li><a href="/problem/<?= $problem['id'] ?>/manage/statement" role="tab">编辑</a></li>
	<li><a href="/problem/<?= $problem['id'] ?>/manage/managers" role="tab">管理者</a></li>
	<li class="active"><a href="/problem/<?= $problem['id'] ?>/manage/data" role="tab">数据</a></li>
	<li><a href="/problem/<?=$problem['id']?>" role="tab">返回</a></li>
</ul>

<div class="text-center top-buffer-md" style="color:red;"><strong>上传完数据后记得点击“部署数据”将数据发送到评测机</strong></div>
<div class="row">
	<div class="col-md-2">
		<ul class="nav nav-pills nav-stacked">
			<li id="data-btn" class="active"><a onclick="show_frame('data')">Data General</a></li>
			<li id="exdata-btn"><a onclick="show_frame('exdata')">Data Details</a></li>
			<li id="problem-btn"><a onclick="show_frame('problem')">Problem</a></li>
			<li id="test-btn"><a onclick="show_frame('test')">Tests</a></li>
			<?php if($problem_type != 'submit'):?>
				<li id="extest-btn"><a onclick="show_frame('extest')">Extra Tests</a></li>
			<?php endif ?>
			<li id="chk-btn"><a onclick="show_frame('chk')">Checker</a></li>
			<li id="gen-btn"><a onclick="show_frame('gen')">Generator</a></li>
			<li id="hack-btn"><a onclick="show_frame('hack')">Std &amp; Val</a></li>
			<?php if($problem_type == 'interactive'):?>
				<li id="require-btn"><a onclick="show_frame('require')">Require</a></li>
			<?php endif ?>
		</ul>
	</div>
	
	<div class="col-md-10">
		<div id="data">
			<div class="row top-buffer-md">
				<label class="control-label col-sm-2 text-right">设置数据</label>
				<button type="button" class="btn btn-primary col-sm-2" data-toggle="modal" data-target="#UploadDataModal">上传数据</button>
				<?php $data_form->printHTML();
				$clear_data_form->printHTML(); ?>
			</div>
			<div class="row top-buffer-md">
				<label class="control-label col-sm-2 text-right">
				<?php if ($problem['hackable']): ?>
					<span class="glyphicon glyphicon-ok"></span> hack功能已启用
				<?php else: ?>
					<span class="glyphicon glyphicon-remove"></span> hack功能已禁止
				<?php endif ?>
				</label>
				<?php $hackable_form->printHTML(); 
					if($problem['hackable']){
						$test_std_form->printHTML();
					}?>
			</div>
			<div class="row top-buffer-md">
				<label class="control-label col-sm-2 text-right">下载</label>
				<a href="/download.php?type=testlib.h" class="btn btn-primary col-sm-2">下载testlib.h</a>
				<?php if(Auth::id()=='std'):?>
					<a href="/download.php?type=problem&id=<?=$problem['id']?>" class="btn btn-primary col-sm-2 col-sm-push-1">下载数据</a>
				<?php endif ?>
			</div>
			<h3 class="text-center">数据设置</h3>
			<form method="post" class="form-horizontal">
				<div class="row">
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label col-sm-4">题目类型</label>
							<div class="col-sm-6">
								<select class="form-control" name="type" id="select-type">
									<option value="ordinary">传统题</option>
									<option value="submit">提交答案题</option>
									<option value="interactive">交互题</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">测试点数量</label>
							<div class="col-sm-6">
								<input class="form-control" name="tests" value="<?=getUOJConfVal($problem_conf,'n_tests',10)?>">
							</div>
						</div>
						<div id="div-ex-tests">
							<div class="form-group">
								<label class="control-label col-sm-4">额外测试点数量</label>
								<div class="col-sm-6">
									<input class="form-control" name="ex_tests" value="<?=getUOJConfVal($problem_conf,'n_ex_tests',0)?>">
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-4">样例测试点数量</label>
								<div class="col-sm-6">
									<input class="form-control" name="sample_tests" value="<?=getUOJConfVal($problem_conf,'n_sample_tests',0)?>">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">子任务数量</label>
							<div class="col-sm-6">
								<input class="form-control" name="subtasks" value="<?=getUOJConfVal($problem_conf,'n_subtasks',0)?>">
							</div>
						</div>
						<div class="form-group" id="div-token">
							<label class="control-label col-sm-4">密码</label>
							<div class="col-sm-6">
								<input class="form-control" name="token" value="<?=getUOJConfVal($problem_conf,'token','')?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-4">答案校验器</label>
							<div class="col-sm-6">
								<select class="form-control" name="checker" id="select-checker">
									<option value="lcmp">全文比较，忽略行末空格及文末回车</option>
									<option value="rcmp4">浮点数序列，精度1e-4</option>
									<option value="rncmp">浮点数序列，精度1.5e-5</option>
									<option value="rcmp6">浮点数序列，精度1e-6</option>
									<option value="rcmp9">浮点数序列，精度1e-9</option>
									<option value="off">自定义校验器</option>
								</select>
								<script>$('#select-checker').val('<?=getUOJConfVal($problem_conf,'use_builtin_checker','off')?>');</script>
							</div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label col-sm-3">输入文件</label>
							<div class="col-sm-4" style="padding-right:5px">
								<input class="form-control text-right" name="input_pre" value="<?=getUOJConfVal($problem_conf,'input_pre','input')?>">
							</div>
							<label class="control-label" style="float:left">233.</label>
							<div class="col-sm-3" style="padding-left:5px">
								<input class="form-control" name="input_suf" value="<?=getUOJConfVal($problem_conf,'input_suf','txt')?>">
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-sm-3">输出文件</label>
							<div class="col-sm-4" style="padding-right:5px">
								<input class="form-control text-right" name="output_pre" value="<?=getUOJConfVal($problem_conf,'output_pre','output')?>">
							</div>
							<label class="control-label" style="float:left">233.</label>
							<div class="col-sm-3" style="padding-left:5px">
								<input class="form-control" name="output_suf" value="<?=getUOJConfVal($problem_conf,'output_suf','txt')?>">
							</div>
						</div>
						<div class="form-group" id="div-answer_unit">
							<label class="control-label col-sm-3">交互单元</label>
							<div class="col-sm-6">
								<input class="form-control" name="answer_unit" value="<?=getUOJConfVal($problem_conf,'answer_unit_name','')?>">
							</div>
						</div>
						<div class="form-group text-center">
							<?=HTML::checkbox('format',!isset($problem_extra_config['dont_use_formatter']))?>
						</div>
						<div id="div-limits">
							<div class="form-group">
								<label class="control-label col-sm-3">时间限制</label>
								<div class="col-sm-6">
									<input class="form-control" name="time_limit_ms" value="<?=getUOJConfVal($problem_conf,'time_limit_ms',getUOJConfVal($problem_conf,'time_limit',1)*1000)?>">
								</div>
								<label class="control-label" style="float:left">MS</label>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">空间限制</label>
								<div class="col-sm-6">
									<input class="form-control" name="memory_limit" value="<?=getUOJConfVal($problem_conf,'memory_limit',256)?>">
								</div>
								<label class="control-label" style="float:left">MB</label>
							</div>
							<div class="form-group">
								<label class="control-label col-sm-3">输出限制</label>
								<div class="col-sm-6">
									<input class="form-control" name="output_limit" value="<?=getUOJConfVal($problem_conf,'output_limit',64)?>">
								</div>
								<label class="control-label" style="float:left">MB</label>
							</div>
						</div>
					</div>
				</div>
				<div class="text-center">
					<input type="hidden" name="modify-data-general" value="submit">
					<input type="submit" class="btn btn-default" value="提交">
				</div>
			</form>
		</div>
		
		<div id="exdata" style="display:none"></div>
		<div id="problem" style="display:none"></div>
		<div id="test" style="display:none"></div>
		<?php if($problem_type != 'submit'):?>
			<div id="extest" style="display:none"></div>
		<?php endif ?>
		<div id="chk" style="display:none"></div>
		<div id="gen" style="display:none"></div>
		<div id="hack" style="display:none"></div>
		<?php if($problem_type == 'interactive'):?>
			<div id="require" style="display:none"></div>
		<?php endif ?>
		
		<h3 id="loading-info" style="display:none">页面加载中，请稍候……</h3>
	</div>
</div>

<div class="modal fade" id="UploadDataModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">上传数据</h4>
  			</div>
  			<div class="modal-body">
				<form action="" method="post" enctype="multipart/form-data" role="form">
  					<div class="form-group">
						<label for="exampleInputFile">文件</label>
						<input type="file" name="problem_data_file" id="problem_data_file">
						<p class="help-block">请上传.zip文件</p>
					</div>
					<input type="hidden" name="problem_data_file_submit" value="submit">
  					<button type="submit" class="btn btn-success">上传</button>
				</form>
  			</div>
  			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
  			</div>
		</div>
  	</div>
</div>

<script>
var frames=['problem','data','exdata','test','extest','chk','hack','require','gen'];
function clear_page()
{
	$.each(frames,function(){
		$('#'+this).slideUp('slow');
		$('#'+this+'-btn').removeClass('active');
	});
}
function show_frame(name)
{
	frame=$('#'+name);
	if(frame.css('display')=='block'){
		return;
	}
	clear_page();
	$('#'+name+'-btn').addClass('active');
	if(frame.html()==''){
		$('#loading-info').css('display','block');
		frame.html('<h3>页面加载中，请稍候……</h3>');
		$.post(window.location.href,{frame:name},function(data){
			frame.html(data);
			sh_highlightDocument();
			frame.slideDown('slow');
			$('#loading-info').css('display','none');
			$('textarea').autosize();
		}).fail(function(){
			frame.html('');
			$('#loading-info').css('display','none');
		});
	}else{
		frame.slideDown('slow');
	}
}
function set_problem_type(name)
{
	if(name=='submit'){
		$('#div-ex-tests').slideUp('slow');
		$('#div-limits').slideUp('slow');
	}else{
		$('#div-ex-tests').slideDown('slow');
		$('#div-limits').slideDown('slow');
	}
	if(name=='interactive'){
		$('#div-token').slideDown('slow');
		$('#div-answer_unit').slideDown('slow');
	}else{
		$('#div-answer_unit').slideUp('slow');
		$('#div-token').slideUp('slow');
	}
}
$('#select-type').change(function(){
	set_problem_type($('#select-type').val());
});
function update_subtask_end(id)
{
	target=id+1;
	$('#subtask_start_'+target).val(Number($('#subtask_end_'+id).val())+1);
}
$(document).ready(function(){
	$('#select-type').val('<?= $problem_type ?>');
	set_problem_type('<?= $problem_type ?>');
	$('#input-format').bootstrapSwitch({
		onText: "启用",
		onColor: 'success',
		offText: "禁用",
		offColor: 'danger',
		labelText: "格式化数据",
		handleWidth: 100
	});
});
</script>

<?php echoUOJPageFooter() ?>