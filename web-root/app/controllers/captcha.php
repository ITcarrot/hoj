<?php
if ($_POST["answer"]!="")
{
	echo $_SESSION["captcha"];
	if ($_POST["answer"]==$_SESSION["captcha"])
		$_SESSION["validate"]=true;
	return;
}
require ('ValidateCode.class.php');//先把类包含进来，实际路径根据实际情况进行修改。
$_vc = new ValidateCode();		//实例化一个对象
$_vc->doimg();		
$_SESSION['img-captcha'] = $_vc->getCode();
?>

