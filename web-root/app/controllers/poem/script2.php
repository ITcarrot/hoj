<?php
	function dex($i)
	{
		$i=dechex($i);
		if (strlen($i)==1)
			$i="0".$i;
		return $i;
	}
	session_start();
	if ((!$nostyle)&&(!$show)&&(!$now))
	{
		$r=rand(0,255);
		$g=rand(0,255);
		$b=rand(0,255);
		include ("style2.php");
	}
?>
