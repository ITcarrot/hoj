<?php	
	function EchoBlogAndDie($id, $die = true){
		echo DB::selectFirst("select content from blogs where id = $id ",MYSQLI_NUM)[0];
		if($die)
			die();
	}
	
	if(isset($_POST['frame'])){
		switch($_POST['frame']){
			case 1:EchoBlogAndDie(444);
			case 2:EchoBlogAndDie(445);
			case 3:EchoBlogAndDie(446);
			case 4:EchoBlogAndDie(447);
			case 5:EchoBlogAndDie(448);
			case 6:EchoBlogAndDie(23);
			case 7:EchoBlogAndDie(449);
			case 8:EchoBlogAndDie(450);
			case 9:
				EchoBlogAndDie(451,false);
				uojIncludeView('math-editor');
				die();
			case 10:EchoBlogAndDie(141);
			case 11:EchoBlogAndDie(452);
			case 12:
				echo '<div class="embed-responsive embed-responsive-16by9">';
				echo '<iframe class="embed-responsive-item" src="',HTML::blog_url('ITcarrot', '/slide/336'),'"></iframe>';
				echo '</div><div class="text-right top-buffer-sm">';
				echo '<a class="btn btn-default btn-md" href="',HTML::blog_url('ITcarrot', '/slide/336'),'"><span class="glyphicon glyphicon-fullscreen"></span> 全屏</a>';
				echo '</div>';
				die();
			case 13:EchoBlogAndDie(453);
			case 14:EchoBlogAndDie(454);
			case 15:EchoBlogAndDie(455);
			case 16:EchoBlogAndDie(456);
			case 17:EchoBlogAndDie(457);
			case 18:EchoBlogAndDie(458);
			case 19:EchoBlogAndDie(459);
			case 20:EchoBlogAndDie(248);
			case 22:
				EchoBlogAndDie(220,false);
				EchoBlogAndDie(81);
			case 24:EchoBlogAndDie(529);
		}
		die('<div class="text-center"><div style="font-size:233px">404</div><p>唔……未找到该页面……你是从哪里点进来的……&gt;_&lt;……</p></div>');
	}
	
	$REQUIRE_LIB['mathjax'] = '';
	$REQUIRE_LIB['shjs'] = '';
?>
<?php echoUOJPageHeader(UOJLocale::get('help')) ?>
<h2 class="page-header">HOJ使用指南</h2>

<div class="row">
	<div class="col-sm-3">
		<ul class="nav nav-pills nav-stacked">
			<li id="list_btn1"><a onclick="list(1)">访客使用指南</a></li>
			<ul class="nav nav-pills nav-stacked" id="list1" style="display:none;padding-left:1em;">
				<li id="show_btn1"><a onclick="show(1)">注册与登录</a></li>
				<li id="show_btn2"><a onclick="show(2)">查看用户做题情况</a></li>
				<li id="show_btn3"><a onclick="show(3)">查看用户博客</a></li>
			</ul>
			<li id="list_btn2"><a onclick="list(2)">题库使用指南</a></li>
			<ul class="nav nav-pills nav-stacked" id="list2" style="display:none;padding-left:1em;">
				<li id="show_btn4"><a onclick="show(4)">题目与HACK</a></li>
				<li id="show_btn5"><a onclick="show(5)">比赛、练习和天梯</a></li>
				<li id="show_btn6"><a onclick="show(6)">测评环境</a></li>
				<li id="show_btn24"><a onclick="show(24)">题解系统</a></li>
				<li id="show_btn7"><a onclick="show(7)">Rating和活跃度</a></li>
			</ul>
			<li id="list_btn3"><a onclick="list(3)">个人信息及博客指南</a></li>
			<ul class="nav nav-pills nav-stacked" id="list3" style="display:none;padding-left:1em;">
				<li id="show_btn8"><a onclick="show(8)">用户功能与设置</a></li>
				<li id="show_btn9"><a onclick="show(9)">Markdown和MathJax教程</a></li>
				<li id="show_btn10"><a onclick="show(10)">HTML教程</a></li>
				<li id="show_btn11"><a onclick="show(11)">日志系统</a></li>
				<li id="show_btn12"><a onclick="show(12)">幻灯片使用指南</a></li>
			</ul>
			<li id="list_btn4"><a onclick="list(4)">管理员指南</a></li>
			<ul class="nav nav-pills nav-stacked" id="list4" style="display:none;padding-left:1em;">
				<li id="show_btn13"><a onclick="show(13)">网页管理</a></li>
				<li id="show_btn14"><a onclick="show(14)">比赛、练习和天梯管理</a></li>
				<li id="show_btn15"><a onclick="show(15)">题目的基本配置</a></li>
				<li id="show_btn16"><a onclick="show(16)">题目的特殊配置</a></li>
				<li id="show_btn17"><a onclick="show(17)">提交答案题与交互题配置</a></li>
				<li id="show_btn18"><a onclick="show(18)">testlib.h使用指南</a></li>
				<li id="show_btn19"><a onclick="show(19)">HACK配置和SPJ配置</a></li>
			</ul>
			<li id="list_btn5"><a onclick="list(5)">关于HOJ</a></li>
			<ul class="nav nav-pills nav-stacked" id="list5" style="display:none;padding-left:1em;">
				<li id="show_btn20"><a onclick="show(20)">HOJ简介</a></li>
				<li id="show_btn21"><a href="/blog/122">Bug反馈</a></li>
				<li id="show_btn22"><a onclick="show(22)">更新日志</a></li>
				<li id="show_btn23"><a href="/copyright">版权说明</a></li>
			</ul>
		</ul>
	</div>
	<div class="col-lg-9" id="content" style="overflow:auto">
		<h3 id="loading" class="text-center" style="display:none">内容拉取中……</h3>
	</div>
</div>
<script>
$('#content').css("height",$(window).height()-40);
$(window).resize(function(){
	$('#content').css("height",$(window).height()-40);
});
for(var i=1;i<=24;i++)
	$('#content').append('<div id="content' + i + '" style="display:none"></div>');
function list(id){
	var frame=$('#list'+id),i;
	if(frame.css('display')=='block')
		return;
	for(i=1;i<=5;i++){
		$('#list'+i).slideUp('slow');
		$('#list_btn'+i).removeClass('active');
	}
	$('#list_btn'+id).addClass('active');
	frame.slideDown('slow');
}
function show(id){
	var frame=$('#content'+id),i;
	if(frame.css('display') == 'block')
		return;
	for(i=1;i<=24;i++){
		$('#content'+i).slideUp('slow');
		$('#show_btn'+i).removeClass('active');
	}
	$('#show_btn'+id).addClass('active');
	if(frame.html() == ''){
		$('#loading').css('display','block');
		frame.html('<h3>内容拉取中……</h3>');
		$.post(window.location.href,{frame:id},function(data){
			frame.html(data);
			frame.uoj_highlight();
			sh_highlightDocument();
			MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
			frame.slideDown('slow');
			$('#loading').css('display','none');
		}).fail(function(){
			frame.html('');
			$('#loading').css('display','none');
		});
	}else{
		frame.slideDown('slow');
	}
}
</script>
<?php echoUOJPageFooter() ?>