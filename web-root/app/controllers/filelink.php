<?php
	if(!$_GET['tab']){
		become404Page();
	}
	if($myUser==null){
		become403Page();
	}
	if(isset($_POST['code'])){
		if($_POST['code']==$_SESSION['spider']||('1'.$_POST['code'])==$_SESSION['spider']){
			if($_SESSION['spider'][0]=='1'){
				$_SESSION['spider']=uojRandString(5);
			}else{
				$_SESSION['spider']='1'.$_SESSION['spider'];
			}
			$esc_name=DB::escape($_GET['tab']);
			$file=DB::selectFirst("select file from files where name='$esc_name' limit 1",MYSQLI_NUM)[0];
			if(!$file){
				become404Page();
			}
			$file= '/var/uoj_data/web/'.$file;
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $file);
			if ($mimetype === false) {
				become404Page();
			}
			finfo_close($finfo);
			header("Content-type: $mimetype");
			header("X-Sendfile: $file");
			die();
		}
		becomeMsgPage('页面已过期');
	}
	echo getEncodeJS('content',1);
?>
<center>
	<h3>文件加载中，请稍候……</h3>
	<p>若长时间没有反应，请检查网络或刷新重试</p>
</center>
