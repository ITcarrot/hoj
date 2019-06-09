<?php
	error_reporting(E_ALL ^ E_NOTICE);
	function validateUInt($x) { // [0, 1000000000)
		if (!is_string($x)) {
			return false;
		}
		if ($x === '0') {
			return true;
		}
		return preg_match('/^[1-9][0-9]{0,8}$/', $x);
	}
	
	$name='Judger Name';
	$passwd='Judger Password';
	$web='http://10.248.5.4 (Website)'
	
	if(validateUInt($_GET['id'])&&$_GET['password']===$passwd){
		set_time_limit(0);
		$que=fopen("/var/uoj_data/queue","w");
		flock($que,LOCK_EX);
		echo exec('cd /var/uoj_data ; rm data.zip ;rm -r '.$_GET['id'].';' );
		echo exec('cd /var/uoj_data ; wget --post-data="judger_name='.$name.'&password='.$passwd.'"  "'.$web.'/judge/download/problem/'.$_GET['id'].'" -O data.zip ;');
		echo exec('cd /var/uoj_data ; unzip data.zip ;rm data.zip ;');
		flock($que,LOCK_UN);
		fclose($que);
	}
	
	$rs=array();
	exec('top -n 2 -d 0.1 -b 2>&1',$rs);
	$len=count($rs);
	for($i=6;$i<$len;$i++)
		if($rs[$i]=='')
			break;
	$mem=array();
	exec('free -h 2>&1',$mem);
?>
<meta charset="utf-8" />
<h3 style="text-align:center"><?= $name ?></h3>
<pre><?php
	for($j=$i+1;$j<=$i+5;$j++)
		echo '<p>',htmlspecialchars($rs[$j]),'</p>';
	for($j=0;$j<4;$j++)
		echo '<p>',htmlspecialchars($mem[$j]),'</p>';
?></pre>