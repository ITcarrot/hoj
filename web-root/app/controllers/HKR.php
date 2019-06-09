<?php
	if(!Auth::id()||$_SERVER['REMOTE_ADDR']=='10.248.5.201'||$_SERVER['REMOTE_ADDR']=='10.248.5.202'||Auth::id()=='BingoWong' || Auth::id()=='hezelin' || Auth::id()=='liangzexian' || Auth::id()=='hfoi'){
		Header("Location: /");
		die();
	}
	if($_GET['birthday']==19260817){
		$file= '/var/uoj_data/web/HKR.Ogg';
		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $file);
		finfo_close($finfo);
		header("Content-type: $mimetype");
		header("X-Sendfile: $file");
	}
?>
<?php echoUOJPageHeader('About HongKongReporter') ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<center>


<h1>感谢您的时间！+1s</h1>
<h2>感谢您的时间！+1s</h2>
<h3>感谢您的时间！+1s</h3>

<video src="/HKR?birthday=19260817" autoplay="autoplay" controls="controls" loop="loop" >
Failed to play the video!
</video>


<h3>感谢您的时间！+1s</h3>
<h2>感谢您的时间！+1s</h2>
<h1>感谢您的时间！+1s</h1>

</center>
<?php echoUOJPageFooter() ?>
