<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>
		= Snakes =
	</title>
	<?php
		include("rand.php");
		include("script2.php");
	?>
	<style>
		#tik, #pass, #loading
		{
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			font-family: OpenSymbol, DefSans, Ubuntu-L;
			font-size: 32px;
			width: 100%;
			color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
			transition: all 0.5s ease 0s;
			-o-transition: all 0.5s ease 0s;
			-moz-transition: all 0.5s ease 0s;
			-webkit-transition: all 0.5s ease 0s;
		}
		#k
		{
			transition: all 0.5s ease 0s;
			-o-transition: all 0.5s ease 0s;
			-moz-transition: all 0.5s ease 0s;
			-webkit-transition: all 0.5s ease 0s;
		}
		.click
		{
			height: 45px;
			width: 50px;
			padding-top: 2.5px;
			padding-bottom: 2.5px;
			color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
			border: 1px solid;
			line-height: 41px;
			transition: all 0.5s ease 0s;
			-o-transition: all 0.5s ease 0s;
			-moz-transition: all 0.5s ease 0s;
			-webkit-transition: all 0.5s ease 0s;
			margin: 5px;
			float: left;
			cursor: pointer;
		}
		.click:hover
		{	
			background-color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
			color: <?php echo "#".dex(255-0.4*(255-$r)).dex(255-0.4*(255-$g)).dex(255-0.4*(255-$b));?>;
		}
		.used
		{
			height: 45px;
			width: 50px;
			padding-top: 2.5px;
			padding-bottom: 2.5px;
			color: <?php echo "#".dex(255-0.4*(255-$r)).dex(255-0.4*(255-$g)).dex(255-0.4*(255-$b));?>;
			border: 1px solid;
			line-height: 41px;
			transition: all 0.5s ease 0s;
			-o-transition: all 0.5s ease 0s;
			-moz-transition: all 0.5s ease 0s;
			-webkit-transition: all 0.5s ease 0s;
			margin: 5px;
			float: left;
			cursor: default;
		}
		.used:hover
		{	
			background-color: <?php echo "#".dex(255-0.4*(255-$r)).dex(255-0.4*(255-$g)).dex(255-0.4*(255-$b));?>;
			color: <?php echo "#".dex(0.8*$r).dex(0.8*$g).dex(0.8*$b);?>;
		}
		#body
		{
			transition: all 0.5s ease 0s;
			-o-transition: all 0.5s ease 0s;
			-moz-transition: all 0.5s ease 0s;
			-webkit-transition: all 0.5s ease 0s;
			user-select: none;
			-o-user-select: none;
			-moz-user-select: none;
			-webkit-user-select: none;
		}
	</style>
	<script>
		function $(id)
		{
			return document.getElementById(id);
		}
		var q="<?php echo getresult();?>";
		<?php
			session_start();
			$_SESSION["captcha"]=$answer;
			$_SESSION["validate"]=false;
		?>
		var now=0;
		var k=new Array(6);
		var answer;
		var frozen=0;
		var bgcolor="<?php echo "#".dex(255-0.1*(255-$r)).dex(255-0.1*(255-$g)).dex(255-0.1*(255-$b));?>";
		window.onload=function()
		{
			$("body").style.display="";
			setTimeout(function(){
				$("body").style.opacity=1;
				$("loading").style.opacity=0;
				$("body").style.backgroundColor=bgcolor;
				var font = document.createElement('style');
				font.innerHTML = "@font-face{"
					+ "font-family: 'DefSans';"
					+ "src: url('/fonts/DefSans.eot');"
					+ "src: url('/fonts/DefSans.eot') format('embedded-opentype'),"
						+ "url('/fonts/DefSans.otf') format('truetype');"
					+ "}";
				document.getElementsByTagName('head')[0].appendChild(font);
			},100);
		}
		function refresh()
		{
			$("body").style.backgroundColor="#FFFFFF";
			$("body").style.opacity=0;
			setTimeout("window.location.reload()",500);
		}
		function more()
		{
			setTimeout("refresh();",3000);
		}
		function clear()
		{
			$("tik").style.display="none";
		}
		function pass()
		{
			$("tik").style.opacity=0;
			$("pass").style.opacity=1;
			setTimeout("clear();",500);
		}
		function show()
		{
			$("k").style.opacity=1;
			$("!1").innerText=answer[0];
			$("!2").innerText=answer[1];
			$("!3").innerText=answer[2];
			$("!4").innerText=answer[3];
			$("!5").innerText=answer[4];
			more();
		}
		function show_without_more()
		{
			$("k").style.opacity=1;
			$("!1").innerText=answer[0];
			$("!2").innerText=answer[1];
			$("!3").innerText=answer[2];
			$("!4").innerText=answer[3];
			$("!5").innerText=answer[4];
			setTimeout("pass();",2000);
		}
		function tip()
		{
			now=5;
			$("!1").className="click";
			$("!2").className="click";
			$("!3").className="click";
			$("!4").className="click";
			$("!5").className="click";
			validate("Query",0);
		}
		function validate(p,q)
		{
			var xmlhttp;
			if (window.XMLHttpRequest)
				xmlhttp=new XMLHttpRequest();
			else
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			xmlhttp.onreadystatechange=function()
			{
				var ret;
				if (xmlhttp.readyState==4&&xmlhttp.status==200&&xmlhttp.responseText)
				{
					answer=xmlhttp.responseText;
					if (q)
						if (answer==p)
							correct();
						else
							rejected();
					else
						show();
				}
			}
			xmlhttp.open("POST","/captcha",true);
			xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
			xmlhttp.send("answer="+p);
		}
		function check()
		{
			var ans=$("!1").innerText+$("!2").innerText+$("!3").innerText+$("!4").innerText+$("!5").innerText;
			validate(ans,1);
		}
		function rejected()
		{
			frozen=1;
			$("tip").style.opacity=0;
			$("refresh").style.opacity=0;
			setTimeout(function(){
				$("tip").style.display="none";
				$("refresh").style.display="none";
			},500);
			$("!1").innerText="×";
			$("!2").innerText="×";
			$("!3").innerText="×";
			$("!4").innerText="×";
			$("!5").innerText="×";
			setTimeout("show();",1000);
		}
		function correct()
		{
			frozen=1;
			$("tip").style.opacity=0;
			$("refresh").style.opacity=0;
			setTimeout(function(){
				$("tip").style.display="none";
				$("refresh").style.display="none";
			},500);
			$("!1").innerText="√";
			$("!2").innerText="√";
			$("!3").innerText="√";
			$("!4").innerText="√";
			$("!5").innerText="√";
			setTimeout("show_without_more();",1000);
		}
		function p(id)
		{
			if ($("#"+id).className=="used")
				return;
			if (now==5)
				return;
			$("#"+id).className="used";
			now=now+1;
			$("!"+now).innerText=$("#"+id).innerText;
			$("!"+now).className="click";
			k[now]=id;
			if (now==5){
				check();
			}
		}
		function u(id)
		{
			if (frozen==1)
				return;
			if ($("!"+id).className=="used")
				return;
			if (id!=now)
				return;
			if ($("!"+id).innerText!=$("#"+k[id]).innerText)
				return;
			$("#"+k[id]).className="click";
			$("!"+id).className="used";
			$("!"+id).innerText="";
			now=now-1;
		}
	</script>
</head>
<body>
<div id="loading" style="opacity: 1">
	验证码加载中……
</div>
<div id="body" style="opacity: 0;display:none;">
	<center>
		<div id="pass" style="opacity: 0;">
			验证已通过
		</div>
		<div id="tik">
			<div style="height: 186px; width: 186px;">
				<div id="#0" class="click" onclick="p(0);"></div>
				<div id="#1" class="click" onclick="p(1);"></div>
				<div id="#2" class="click" onclick="p(2);"></div>
				<div style="clear: both;"></div>
				<div id="#3" class="click" onclick="p(3);"></div>
				<div id="#4" class="click" onclick="p(4);"></div>
				<div id="#5" class="click" onclick="p(5);"></div>
				<div style="clear: both;"></div>
				<div id="#6" class="click" onclick="p(6);"></div>
				<div id="#7" class="click" onclick="p(7);"></div>
				<div id="#8" class="click" onclick="p(8);"></div>
				<div style="clear: both;"></div>
			</div>
			<div class="used" style="position: absolute; top: 0px; right: 0px; font-size: 18px; cursor: pointer;" onclick="refresh();" id="refresh">
				刷新
			</div>
			<div class="used" style="position: absolute; top: 62px; right: 0px; font-size: 18px; cursor: pointer;" onclick="tip();" id="tip">
				提示
			</div>
			<div id="k" style="opacity: 0;"><?php echo str_replace("\n","",$more);?></div>
			<div style="height: 62px; width: 310px;" id="t">
				<div id="!1" class="used" onclick="u(1);"></div>
				<div id="!2" class="used" onclick="u(2);"></div>
				<div id="!3" class="used" onclick="u(3);"></div>
				<div id="!4" class="used" onclick="u(4);"></div>
				<div id="!5" class="used" onclick="u(5);"></div>
				<div style="clear: both;"></div>
			</div>
		</div>
	</center>
	<script>
		$("#0").innerText=q[0];
		$("#1").innerText=q[1];
		$("#2").innerText=q[2];
		$("#3").innerText=q[3];
		$("#4").innerText=q[4];
		$("#5").innerText=q[5];
		$("#6").innerText=q[6];
		$("#7").innerText=q[7];
		$("#8").innerText=q[8];
	</script>
</div>
</body>
</html>