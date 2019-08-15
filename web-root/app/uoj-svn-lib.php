<?php
	// Actually, these things should be done by main_judger so that the code would be much simpler.
	// However, this lib exists due to some history issues.
	
	function svnJudger($id){
		$result=DB::query("select * from judger_info where ip!=''");
		$msg='';
		while($row=DB::fetch($result)){
			if(file_get_contents('http://'.$row['ip'].':89/?id='.$id.'&password='.$row['password'])===false)
				$msg.="<p>与评测机{$row['judger_name']}同步失败！</p>";
		}
		if($msg!=''&&Auth::check())
			becomeMsgPage($msg);
	}
	
	function svnNewProblem($id) {
		exec("cd /var/uoj_data;mkdir $id ; rm $id.zip; zip $id.zip $id -r -q");
	}
	class UOJProblemConfException extends Exception {
		public function __construct($message) {
			parent::__construct("<strong>problem.conf</strong> : $message");
		}
	}
	class UOJFileNotFoundException extends Exception {
		public function __construct($file_name) {
			parent::__construct("file <strong>" . htmlspecialchars($file_name) . '</strong> not found');
		}
	}
	
	function svnClearProblemData($problem) {
		$id = $problem['id'];
		if (!validateUInt($id)) {
			error_log("svnClearProblemData: hacker detected");
			return "invalid problem id";
		}
		
		global $myUser;
		if(!$_COOKIE['can_download']||time()-$_COOKIE['can_download']>300||$_COOKIE['can_download_check']!=md5($_COOKIE['can_download'].$myUser['username'])){
			becomeMsgPage('为保障数据的安全，请重新登录，并在登录后5分钟内进行操作！');
		}
		
		exec("rm /var/uoj_data/$id -r");
		DB::manage_log('delete','delete problem '.$problem['id'].' data');
		svnNewProblem($id);
		svnJudger($id);
	}
	
	class SvnSyncProblemDataHandler {
		private $problem, $user;
		private $data_dir, $prepare_dir;
		private $requirement, $problem_extra_config;
		private $problem_conf, $final_problem_conf;
		private $allow_files;
		
		public function __construct($problem, $user) {
			$this->problem = $problem;
			$this->user = $user;
		}
		
		private function copy_to_prepare($file_name) {
			if (!isset($this->allow_files[$file_name])) {
				throw new UOJFileNotFoundException($file_name);
			}
			$src = escapeshellarg("{$this->data_dir}/$file_name");
			$dest = escapeshellarg("{$this->prepare_dir}/$file_name");
			if (isset($this->problem_extra_config['dont_use_formatter']) || !is_file("{$this->data_dir}/$file_name")) {
				exec("cp $src $dest -r", $output, $ret);
			} else {
				exec($_SERVER['DOCUMENT_ROOT']."/app/models/formatter <$src >$dest", $output, $ret);
			}
			if ($ret) {
				throw new UOJFileNotFoundException($file_name);
			}
		}
		private function copy_file_to_prepare($file_name) {
			if (!isset($this->allow_files[$file_name]) || !is_file("{$this->data_dir}/$file_name")) {
				throw new UOJFileNotFoundException($file_name);
			}
			$this->copy_to_prepare($file_name);
		}
		
		private function compile_at_prepare($name, $config = array()) {
			$runner = $_SERVER['DOCUMENT_ROOT']."/app/models/run_program";
			
			if (!isset($config['src'])) {
				$config['src'] = "$name.cpp";
			}
			
			if (isset($config['path'])) {
				exec("mv {$this->prepare_dir}/$name.cpp {$this->prepare_dir}/{$config['path']}/$name.cpp");
				$work_path = "{$this->prepare_dir}/{$config['path']}";
			} else {
				$work_path = $this->prepare_dir;
			}
			$cmd_prefix = "$runner >{$this->prepare_dir}/run_compiler_result.txt --in=/dev/null --out=stderr --err={$this->prepare_dir}/compiler_result.txt --tl=10 --ml=512 --ol=64 --type=compiler --work-path={$work_path}";
			exec("$cmd_prefix /usr/bin/g++ -o $name {$config['src']} -lm -Ofast -DONLINE_JUDGE -std=c++17");
			
			
			$fp = fopen("{$this->prepare_dir}/run_compiler_result.txt", "r");
			if (fscanf($fp, '%d %d %d %d', $rs, $used_time, $used_memory, $exit_code) != 4) {
				$rs = 7;
			}
			fclose($fp);
			
			unlink("{$this->prepare_dir}/run_compiler_result.txt");
			
			if ($rs != 0 || $exit_code != 0) {
				if ($rs == 0) {
					throw new Exception("<strong>$name</strong> : compile error<pre>\n" . uojFilePreview("{$this->prepare_dir}/compiler_result.txt", 500) . "\n</pre>");
				} elseif ($rs == 7) {
					throw new Exception("<strong>$name</strong> : compile error. No comment");
				} else {
					throw new Exception("<strong>$name</strong> : compile error. Compiler " . judgerCodeStr($rs));
				}
			}
			
			unlink("{$this->prepare_dir}/compiler_result.txt");
			
			if (isset($config['path'])) {
				exec("mv {$this->prepare_dir}/{$config['path']}/$name.cpp {$this->prepare_dir}/$name.cpp");
				exec("mv {$this->prepare_dir}/{$config['path']}/$name {$this->prepare_dir}/$name");
			}
		}
		
		public function handle() {
			$id = $this->problem['id'];
			if (!validateUInt($id)) {
				error_log("svnSyncProblemData: hacker detected");
				return "invalid problem id";
			}

			$this->data_dir = "/var/uoj_data/$id";
			$this->prepare_dir = "/var/uoj_data/prepare/$id";

			if (file_exists($this->prepare_dir)) {
				return "please wait until the last sync finish";
			}
			session_write_close();
			
			try {
				$this->requirement = array();
				$this->problem_extra_config = json_decode($this->problem['extra_config'], true);

				mkdir($this->prepare_dir, 0755);
				if (!is_file("{$this->data_dir}/problem.conf")) {
					throw new UOJFileNotFoundException("problem.conf");
				}

				$this->problem_conf = getUOJConf("{$this->data_dir}/problem.conf");
				$this->final_problem_conf = $this->problem_conf;
				if ($this->problem_conf === -1) {
					throw new UOJFileNotFoundException("problem.conf");
				} elseif ($this->problem_conf === -2) {
					throw new UOJProblemConfException("syntax error");
				}

				$this->allow_files = array_flip(array_filter(scandir($this->data_dir), function($x){return $x !== '.' && $x !== '..';}));
				
				if (isset($this->allow_files['require']) && is_dir("{$this->data_dir}/require")) {
					$this->copy_to_prepare('require');
				}
				$n_tests = getUOJConfVal($this->problem_conf, 'n_tests', '10');
				$time_limit=getUOJConfVal($this->problem_conf, 'time_limit', '1');
				$time_limit_ms=getUOJConfVal($this->problem_conf, 'time_limit_ms', $time_limit*1000);
				$memory_limit=getUOJConfVal($this->problem_conf, 'memory_limit', '256');
				if (!validateUInt($n_tests) || $n_tests <= 0) {
					throw new UOJProblemConfException("n_tests must be a positive integer");
				}
				if (!validateUInt($time_limit) || $time_limit <= 0) {
					throw new UOJProblemConfException("time_limit must be a positive integer");
				}
				if (!validateUInt($memory_limit) || $memory_limit <= 0 || $memory_limit > 2048) {
					throw new UOJProblemConfException("memory_limit must be an integer between 1 and 2048");
				}
				if($n_tests * $time_limit > 600){
					throw new UOJProblemConfException("One problem should use no more than 600s to test");
				}
				DB::query("update problems set time_limit=$time_limit_ms,memory_limit=$memory_limit where id=$id");
				
				for ($num = 1; $num <= $n_tests; $num++) {
					$input_file_name = getUOJProblemInputFileName($this->problem_conf, $num);
					$output_file_name = getUOJProblemOutputFileName($this->problem_conf, $num);
					$this->copy_file_to_prepare($input_file_name);
					$this->copy_file_to_prepare($output_file_name);
				}
				if (isset($this->problem_conf['use_builtin_checker'])) {
					if (!preg_match('/^[a-zA-Z0-9_]{1,20}$/', $this->problem_conf['use_builtin_checker'])) {
						throw new Exception("<strong>" . htmlspecialchars($this->problem_conf['use_builtin_checker']) . "</strong> is not a valid checker");
					}
				} else {
					$this->copy_file_to_prepare('chk.cpp');
					$this->compile_at_prepare('chk', array('need_include_header' => true));
				}
				if (isset($this->problem_conf['submit_answer']) && $this->problem_conf['submit_answer'] == 'on') {
					if ($this->problem['hackable']) {
						throw new UOJProblemConfException("the problem can't be hackable if submit_answer is on");
					}
					for ($num = 1; $num <= $n_tests; $num++) {
						$input_file_name = getUOJProblemInputFileName($this->problem_conf, $num);
						$output_file_name = getUOJProblemOutputFileName($this->problem_conf, $num);
						$this->requirement[] = array('name' => "output$num", 'type' => 'text', 'file_name' => $output_file_name);
					}
				} else {
					$n_ex_tests = getUOJConfVal($this->problem_conf, 'n_ex_tests', '0');
					if (!validateUInt($n_ex_tests) || $n_ex_tests < 0) {
						throw new UOJProblemConfException("n_ex_tests must be a non-nagative integer");
					}
					if(($n_ex_tests + $n_tests) * $time_limit > 700){
						throw new UOJProblemConfException("One problem should use no more than 700s to test all the tests");
					}
					for ($num = 1; $num <= $n_ex_tests; $num++) {
						$input_file_name = getUOJProblemExtraInputFileName($this->problem_conf, $num);
						$output_file_name = getUOJProblemExtraOutputFileName($this->problem_conf, $num);
						$this->copy_file_to_prepare($input_file_name);
						$this->copy_file_to_prepare($output_file_name);
					}
					if ($this->problem['hackable']) {
						$this->copy_file_to_prepare('std.cpp');
						if (isset($this->problem_conf['with_implementer']) && $this->problem_conf['with_implementer'] == 'on') {
							$this->compile_at_prepare('std',
								array(
									'src' => 'implementer.cpp std.cpp',
									'path' => 'require'
								)
							);
						} else {
							$this->compile_at_prepare('std');
						}
						$this->copy_file_to_prepare('val.cpp');
						$this->compile_at_prepare('val', array('need_include_header' => true));
					}
					$n_sample_tests = getUOJConfVal($this->problem_conf, 'n_sample_tests', '0');
					if (!validateUInt($n_sample_tests) || $n_sample_tests < 0) {
						throw new UOJProblemConfException("n_sample_tests must be a non-nagative integer");
					}
					if ($n_sample_tests > $n_ex_tests) {
						throw new UOJProblemConfException("n_sample_tests can't be greater than n_ex_tests");
					}
					for ($num = 1; $num <= $n_sample_tests; $num++) {
						$input_file_name = getUOJProblemExtraInputFileName($this->problem_conf, $num);
						$output_file_name = getUOJProblemExtraOutputFileName($this->problem_conf, $num);
					}
					$this->requirement[] = array('name' => 'answer', 'type' => 'source code', 'file_name' => 'answer.code');
					$orig_requirement = json_decode($this->problem['submission_requirement'],true);
					if(isset($orig_requirement[0]['languages'])){
						$this->requirement[0]['languages'] = $orig_requirement[0]['languages'];
					}
				}
				putUOJConf("{$this->prepare_dir}/problem.conf", $this->final_problem_conf);

				$esc_requirement = DB::escape(json_encode($this->requirement));
				DB::update("update problems set submission_requirement = '$esc_requirement' where id = $id");
			} catch (Exception $e) {
				exec("rm {$this->prepare_dir} -r");
				return $e->getMessage();
			}
			
			exec("cd /var/uoj_data/prepare; rm ../$id.zip; zip ../$id.zip $id -r -q");
			exec("rm {$this->prepare_dir} -r");
			svnJudger($id);
			
			session_start();
			return '';
		}
	}
	
	function svnSyncProblemData($problem, $user = null) {
		DB::manage_log('sync','sync problem '.$problem['id'].' data');
		return (new SvnSyncProblemDataHandler($problem, $user))->handle();
	}
	function svnAddExtraTest($problem, $input_file_name, $output_file_name) {
		$id = $problem['id'];

		$cur_dir = "/var/uoj_data/$id";
		
		$problem_conf = getUOJConf("$cur_dir/problem.conf");
		if ($problem_conf == -1 || $problem_conf == -2) {
			return $problem_conf;
		}
		$problem_conf['n_ex_tests'] = getUOJConfVal($problem_conf, 'n_ex_tests', 0) + 1;
		
		$new_input_name = getUOJProblemExtraInputFileName($problem_conf, $problem_conf['n_ex_tests']);
		$new_output_name = getUOJProblemExtraOutputFileName($problem_conf, $problem_conf['n_ex_tests']);
		
		putUOJConf("$cur_dir/problem.conf", $problem_conf);
		move_uploaded_file($input_file_name, "$cur_dir/$new_input_name");
		move_uploaded_file($output_file_name, "$cur_dir/$new_output_name");

		if (svnSyncProblemData($problem) === '') {
			rejudgeProblemAC($problem);
		} else {
			error_log('hack successfully but sync failed.');
		}
	}