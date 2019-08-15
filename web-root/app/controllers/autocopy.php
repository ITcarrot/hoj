<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	requirePHPLib('svn');

	if (!isSuperUser($myUser)) {
		become403Page();
	}

	$bzoj_form = new UOJForm('bzoj');
	$bzoj_form->addInput(
		'bzoj_pid', 'text', '题号', 2333,
		function($str) {
			if (!validateUInt($str))
				return '不合法的题号 (请输入数字)';
			if ($id = DB::selectFirst("select id from problems where title like '[bzoj$str]%' limit 1",MYSQLI_NUM)[0])
				return "不合法的题号 (题目已经存在，题号：$id)";
			$url = "http://10.248.5.3/bzoj/p/".$str.".html";
			$content = file_get_contents($url);
			if (strlen($content) == 0)
				return '不合法的题号 (未找到该题或服务器10.248.5.3未启动)';
			return '';
		},
		null
	);
	$bzoj_form->handle = function(&$vdata) {
		$pid = $_POST['bzoj_pid'];
		$url = "http://10.248.5.3/bzoj/p/".$pid.".html";
		$content = file_get_contents($url);
		$title = strstr($content, "<title>");
		$title = substr($title, 14);
		$title = substr($title, 0, strpos($title, "</title>"));
		$title = DB::escape("[bzoj".$pid."]".$title);
		$time_limit = strstr($content, "时间限制：");
		$time_limit = substr($time_limit, 15);
		$time_limit = substr($time_limit, 0, strpos($time_limit, "s"));
		$mem_limit = strstr($content, "空间限制：");
		$mem_limit = substr($mem_limit, 15);
		$mem_limit = substr($mem_limit, 0, strpos($mem_limit, "M"));
		$content = strstr($content, '<main class="content">');
		$content = substr($content, 0, strpos($content, '</main>')+7);
		$esc_config = DB::escape('{"view_content_type":"ALL_AFTER_AC","view_all_details_type":"ALL","view_details_type":"ALL"}');
		$esc_sconfig = DB::escape('[{"name":"answer","type":"source code","file_name":"answer.code"}]');
		
		DB::query("insert into problems (title, is_hidden, submission_requirement, extra_config, hackable) values ('$title', 1, '$esc_sconfig', '$esc_config', 0)");
		$id = DB::insert_id();
		DB::query("insert into problems_contents (id, statement, statement_md, file) values ($id, '', '', '')");
		svnNewProblem($id);
		DB::manage_log('problems','autocopy add problem '.$id);
		
		for($i=1;;$i++){
			if(!preg_match('/<img.+?src="(\.\..+?)"/',$content,$matches))
				break;
			$url = EscapeShellCmd('http://10.248.5.3/bzoj/p/'.$matches[1]);
			exec("wget -O {$_SERVER['DOCUMENT_ROOT']}/pictures/{$id}_$i $url");
			$content = str_replace($matches[1],"/pictures/{$id}_$i",$content);
		}
		$content_md=$content;
		$salt=0;
		while(preg_match('/<pre>/',$content)){
			$salt++;
			$content=preg_replace('/<pre>/','<button class="btn btn-xs btn-primary bot-buffer-sm fortarget'.$salt.'" data-clipboard-action="copy" data-clipboard-target="#target'.$salt.'" id="copy_btn">复制</button><pre id="target'.$salt.'">',$content,1);
		}
		DB::query("update problems_contents set statement='".DB::escape($content)."', statement_md='".DB::escape($content_md)."' where id=$id");
		DB::manage_log('problems','autocopy edit problem '.$id.' statement');
		
		$data_dir = "/var/uoj_data/$id/";
		exec("cd $data_dir ; wget ftp://bzoj_data@10.248.5.3/$pid/* ;");
		if(!file_exists($data_dir.'1.in'))
			becomeMsgPage("下载数据失败！");
		
		$bash_filename = "/tmp/".mt_rand().".sh";
		$bash_file = "for files in $(ls $data_dir)\n";
		$bash_file .= 'do mv '.$data_dir.'$files '.$data_dir.'bzoj$files'."\n";
		$bash_file .= "done\n";
		file_put_contents($bash_filename,$bash_file);
		exec("bash $bash_filename");
		unlink($bash_filename);
		
		$data_dir_arr = scandir($data_dir);
		$data_cnt = floor((count($data_dir_arr)-1)/2);
		$time_limit_ms = ceil((float)$time_limit * 1000 / $data_cnt);
		$time_limit = (int)$time_limit;
		$time_limit = ceil($time_limit/$data_cnt);
		$set_filename = $data_dir."problem.conf";
		$set_file = fopen($set_filename, "w");
		fwrite($set_file, "use_builtin_checker lcmp\n");
		fwrite($set_file, "n_tests $data_cnt\n");
		fwrite($set_file, "n_ex_tests 0\n");
		fwrite($set_file, "n_sample_tests 0\n");
		fwrite($set_file, "input_pre bzoj\n");
		fwrite($set_file, "input_suf in\n");
		fwrite($set_file, "output_pre bzoj\n");
		fwrite($set_file, "output_suf out\n");
		fwrite($set_file, "time_limit $time_limit\n");
		fwrite($set_file, "time_limit_ms $time_limit_ms\n");
		fwrite($set_file, "memory_limit $mem_limit\n");
		fclose($set_file);
		DB::manage_log('problems','autocopy update problem '.$id.' data and settings');
		$ret = svnSyncProblemData(queryProblemBrief($id));
		if ($ret)
			becomeMsgPage('<div>'.$ret.'</div>');
		DB::query("update problems set is_hidden=0 where id=$id");
		DB::manage_log('problems',"autocopy set problem $id is_hidden = 0");
	};
	$bzoj_form->submit_button_config['smart_confirm'] = '';
	$bzoj_form->runAtServer();
	
	if($_POST['check_loj']=='loj'){
		$str = $_POST['pid'];
		if (!validateUInt($str))
			die('不合法的题号 (请输入数字)');
		if ($id = DB::selectFirst("select id from problems where title like '[loj$str]%' limit 1",MYSQLI_NUM)[0])
			die("不合法的题号 (题目已经存在，题号：$id)");
		$url = "https://loj.ac/problem/".$str."/export";
		$content = file_get_contents($url);
		if (strlen($content) == 0)
			die('无法访问LOJ');
		die($content);
	}
	if($_POST['submit_loj']=='loj'){
		$pid = $_POST['pid'];
		$time_limit_ms = $_POST['time_limit'];
		$time_limit = ceil((float)$time_limit_ms/1000);
		$memory_limit = $_POST['memory_limit'];
		if(!validateUInt($pid) || !validateUInt($time_limit_ms) || !validateUInt($memory_limit))
			die('不合法的题号或时空限制');
		$content = $_POST['content'];
		$content_md = $_POST['content_md'];
		$title = DB::escape("[loj".$pid."]".$_POST['title']);
		$esc_config = DB::escape('{"view_content_type":"ALL_AFTER_AC","view_all_details_type":"ALL","view_details_type":"ALL"}');
		$esc_sconfig = DB::escape('[{"name":"answer","type":"source code","file_name":"answer.code"}]');
		
		DB::query("insert into problems (title, is_hidden, submission_requirement, extra_config, hackable) values ('$title', 1, '$esc_sconfig', '$esc_config', 0)");
		$id = DB::insert_id();
		DB::query("insert into problems_contents (id, statement, statement_md, file) values ($id, '', '', '')");
		svnNewProblem($id);
		DB::manage_log('problems','autocopy add problem '.$id);
		
		$salt=0;
		while(preg_match('/<pre>/',$content)){
			$salt++;
			$content=preg_replace('/<pre>/','<button class="btn btn-xs btn-primary bot-buffer-sm fortarget'.$salt.'" data-clipboard-action="copy" data-clipboard-target="#target'.$salt.'" id="copy_btn">复制</button><pre id="target'.$salt.'">',$content,1);
		}
		if($_POST['extra_file'] == 'true'){
			$url = EscapeShellCmd("https://loj.ac/problem/$pid/download/additional_file");
			$esc_name=DB::escape($id.'_1.zip');
			$used=DB::fetch(DB::query("select name from files where name='$esc_name' limit 1"));
			if($used)
				die('附加文件：文件名已被使用');
			do {
				$fileName =uojRandString(20);
			} while (file_exists("/var/uoj_data/web/".$fileName));
			exec('wget -O /var/uoj_data/web/'.$fileName.' '.$url);
			if(!file_exists("/var/uoj_data/web/".$fileName))
				die('下载附加文件失败');
			DB::query("insert into files(name,file) value('$esc_name','$fileName')");
			$content.='<h4><a href="/files/'.$id.'_1.zip">点击下载附加文件</a></h4>';
			$content_md.="\n\n".'<h4><a href="/files/'.$id.'_1.zip">点击下载附加文件</a></h4>';
		}
		DB::query("update problems_contents set statement='".DB::escape($content)."', statement_md='".DB::escape($content_md)."' where id=$id");
		DB::manage_log('problems','autocopy edit problem '.$id.' statement');
		
		session_write_close();
		$data_dir = "/var/uoj_data/$id/";
		$tmp_dir = '/tmp/autocopy'.$id;
		exec("rm -r $tmp_dir; mkdir $tmp_dir");
		$url = EscapeShellCmd("https://loj.ac/problem/$pid/testdata/download");
		exec("wget -O $tmp_dir/data.zip $url");
		if(!is_file("$tmp_dir/data.zip")){
			exec("rm -r $tmp_dir");
			die('下载数据失败！');
		}
		exec("cd $tmp_dir; unzip data.zip");
		session_start();
		
		$n_tests = 0;
		$set_filename = $data_dir."problem.conf";
		$set_file = fopen($set_filename, "w");
		if(is_file("$tmp_dir/data.yml")
			&& is_array($config = yaml_parse(file_get_contents("$tmp_dir/data.yml")))
			&& is_array($config['subtasks'])
			&& isset($config['inputFile']) && isset($config['outputFile'])){
			$n_subtasks = 0;
			foreach($config['subtasks'] as $subtask){
				$n_subtasks++;
				foreach($subtask['cases'] as $test){
					$n_tests++;
					$if=str_replace('#',$test,$config['inputFile']);
					$of=str_replace('#',$test,$config['outputFile']);
					exec(escapeshellcmd("cp $tmp_dir/$if $data_dir/loj$n_tests.in"));
					exec(escapeshellcmd("cp $tmp_dir/$of $data_dir/loj$n_tests.out"));
				}
				fwrite($set_file, "subtask_end_$n_subtasks $n_tests\n");
				fwrite($set_file, "subtask_score_$n_subtasks {$subtask['score']}\n");
			}
			fwrite($set_file, "n_subtasks $n_subtasks\n");
		}else{
			$file_list = array();
			exec("ls $tmp_dir | grep .in$ 2>&1",$file_list);
			foreach($file_list as $file){
				$file_pre = substr($file,0,-3);
				if(is_file("$tmp_dir/$file_pre.ans")){
					$n_tests++;
					exec(escapeshellcmd("cp $tmp_dir/$file $data_dir/loj$n_tests.in"));
					exec(escapeshellcmd("cp $tmp_dir/$file_pre.ans $data_dir/loj$n_tests.out"));
				}elseif(is_file("$tmp_dir/$file_pre.out")){
					$n_tests++;
					exec(escapeshellcmd("cp $tmp_dir/$file $data_dir/loj$n_tests.in"));
					exec(escapeshellcmd("cp $tmp_dir/$file_pre.out $data_dir/loj$n_tests.out"));
				}
			}
		}
		fwrite($set_file, "use_builtin_checker lcmp\n");
		fwrite($set_file, "n_tests $n_tests\n");
		fwrite($set_file, "input_pre loj\n");
		fwrite($set_file, "input_suf in\n");
		fwrite($set_file, "output_pre loj\n");
		fwrite($set_file, "output_suf out\n");
		if($_POST['type']=='submit-answer'){
			fwrite($set_file, "submit_answer on\n");
		}else{
			fwrite($set_file, "time_limit $time_limit\n");
			fwrite($set_file, "time_limit_ms $time_limit_ms\n");
			fwrite($set_file, "memory_limit $memory_limit\n");
		}
		fclose($set_file);
		DB::manage_log('problems','autocopy update problem '.$id.' data and settings');
		
		exec("rm -r $tmp_dir");
		$ret = svnSyncProblemData(queryProblemBrief($id));
		if ($ret)
			die($ret);
		DB::query("update problems set is_hidden=0 where id=$id");
		DB::manage_log('problems',"autocopy set problem $id is_hidden = 0");
		die('自动加题成功');
	}
	
	$REQUIRE_LIB['blog-editor'] = '';
?>

<?php echoUOJPageHeader('自动加题') ?>

<div class="row">
	<h2 class="page-header" align="center">
		[BZOJ]自动加题
	</h2>
	<p class="text-center">
		请在输入框中输入题号。 
	</p>
	<div class="row">
		<?php $bzoj_form->printHTML();?>
	</div>
	<p class="text-center" style="padding-top: 50px; color: #CCCCCC;">
		@Snakes
	</p>
</div>

<div class="row">
	<h2 class="page-header" align="center">
		[LOJ]自动加题
	</h2>
	<p class="text-center">
		请在输入框中输入题号，不支持Special Judge或交互题，不继承子任务计分方式。
	</p>
	<div class="row">
		<div class="form-horizontal">
			<div id="div-loj" class="form-group">
				<label for="input-loj" class="col-sm-2 control-label">题号</label>
				<div class="col-sm-3">
					<input type="text" class="form-control" id="input-loj" value="2333">
					<span class="help-block" id="help-loj"></span>
				</div>
			</div>
			<div class="text-center">
				<button id="submit-loj" class="btn btn-default">提交</button>
			</div>
		</div>
	</div>
</div>

<script>
	var loj_pid;
	$('#submit-loj').click(function(){
		if (!confirm('你真的要提交吗？'))
			return;
		loj_pid = $('#input-loj').val()
		$.post('/autocopy',{
				check_loj: 'loj',
				pid: loj_pid
			},function(data){
				try {
					data = JSON.parse(data)
				} catch (e) {
					showErrorHelp('loj', data);
					return;
				}
				if(!data['success']) {
					showErrorHelp('loj', '题目不存在');
					return;
				}
				data = data['obj'];
				if(data["type"] == "interaction") {
					showErrorHelp('loj', '不支持自动添加交互题');
					return;
				}
				$('#div-loj').removeClass('has-error');
				$('#help-loj').text('正在自动加题，请稍候……');
				
				var content, content_md="";
				content_md += '\n\n#### 题目描述：\n' + data['description'];
				content_md += '\n\n#### 输入格式：\n' + data['input_format'];
				content_md += '\n\n#### 输出格式：\n' + data['output_format'];
				content_md += '\n\n#### 样例\n' + data['example'];
				content_md += '\n\n#### 数据范围与提示\n' + data['limit_and_hint'];
				content = marked(content_md);
				$.post('/autocopy',{
						submit_loj: 'loj',
						pid: loj_pid,
						title: data['title'],
						content: content,
						content_md: content_md,
						type: data['type'],
						time_limit: data['time_limit'],
						memory_limit: data['memory_limit'],
						extra_file: data['have_additional_file']
					},function(data){
						alert(data);
					}).always(function(){
						$('#help-loj').text('');
					});
			});
	});
</script>
	
<?php echoUOJPageFooter() ?>