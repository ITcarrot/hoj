<?php
	requirePHPLib('judger');
	switch ($_GET['type']) {
		case 'problem':
			if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
				become404Page();
			}
			
			if ($myUser['username']!='std') {
				become404Page();
			}

			if(!$_COOKIE['can_download']||time()-$_COOKIE['can_download']>900||$_COOKIE['can_download_check']!=md5($_COOKIE['can_download'].$myUser['username'])){
				becomeMsgPage('为保障数据的安全，请重新登录，并在登录后5分钟内进行操作！');
			}

			$id = $_GET['id'];
			
			$file_name = "/var/uoj_data/$id.zip";
			$download_name = "problem_$id.zip";
			DB::manage_log('download','download problem '.$id.' data');
			break;
		case 'testlib.h':
			$file_name = "/var/uoj_data/testlib.h";
			$download_name = "testlib.h";
			break;
		default:
			become404Page();
	}
	
	$finfo = finfo_open(FILEINFO_MIME);
	$mimetype = finfo_file($finfo, $file_name);
	if ($mimetype === false) {
		become404Page();
	}
	finfo_close($finfo);
	
	header("X-Sendfile: $file_name");
	header("Content-type: $mimetype");
	header("Content-Disposition: attachment; filename=$download_name");
?>


