<?php
	requirePHPLib('form');
	
	if (!validateUInt($_GET['id'])) {
		become404Page();
	}
	$exercise=DB::fetch(DB::query("select * from exercise where id ={$_GET['id']}"));
	if(!$exercise){
		become404Page();
	}
	
	if (isset($_GET['tab'])) {
		$cur_tab = $_GET['tab'];
	} else {
		$cur_tab = 'dashboard';
	}
	
	$msg="";
	if(!Auth::id()||isContestUser(Auth::user())){
		$cur_tab='standings';
		$msg="禁止查看";
	}
	$result=DB::query("select * from exercise_depend where id={$exercise['id']}");
	while($depend=DB::fetch($result)){
		if (isSuperUser($myUser)||$myUser==null){
			break;
		}
		$finish=true;
		$result=DB::query("select * from exercise_problem where exercise={$depend['depend']}");
		while($row=DB::fetch($result)){
			$finish_this=DB::fetch(DB::query("select * from best_ac_submissions where submitter = '{$myUser['username']}' and problem_id={$row['problem']}"));
			if(!$finish_this){
				$finish=false;
			}
		}
		if(! $finish){
			$depend_name=DB::fetch(DB::query("select * from exercise where id={$depend['depend']}"));
			$msg.='<p><a href="/exercise/'.$depend['depend'].'">'.$depend_name['name'].'</a></p>';
		}
	}
	if($msg!=""){
		$msg="<h4>在查看本练习前请先完成以下练习：</h4>".$msg;
	}
	
	function echoDashboard() {
		global $myUser, $exercise;
		$finish=true;
		echo '<div class="table-responsive">';
		echo '<table class="table table-bordered table-hover table-striped table-text-center">';
		echo '<thead>';
		echo '<th style="width:5em"></th>';
		echo '<th style="width:5em">#</th>';
		echo '<th>', UOJLocale::get('problems::problem'), '</th>';
		echo '</thead>';
		echo '<tbody>';
		$exercise_problems = DB::query("select * from exercise_problem where exercise={$exercise['id']} order by problem");
		for ($i = 0;$row=DB::fetch($exercise_problems); $i++) {
			$problem = DB::fetch(DB::query("select * from problems where id={$row['problem']}"));
			if (DB::fetch(DB::query("select * from best_ac_submissions where submitter = '{$myUser['username']}' and problem_id={$row['problem']}"))) {
				echo '<tr class="success">';
			} else {
				echo '<tr>';
				$finish=false;
			}
			$score=DB::selectFirst("select score from best_submissions where problem_id={$row['problem']} and submitter='{$myUser['username']}'");
			echo '<td>',($score?('<span class="uoj-score" data-max="100">'.$score['score'].'</span>'):''),'</td>';
			echo '<td>', chr(ord('A') + $i), '</td>';
			echo '<td>';
			if ($problem['is_hidden'])
				echo '<span class="text-muted">[已隐藏]</span> ';
			echo '<a href="/problem/', $row['problem'], '">', $problem['title'], '</a>', '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
		echo '<h3>说明</h3>';
		echo'<article class="top-buffer-md">',$exercise['information'],'</article>';
	}
	
	function echoSubmissions() {
		global $exercise, $myUser;
		echoSubmissionsList("problem_id in (select problem from exercise_problem where exercise={$exercise['id']})", 'order by id desc', array(), $myUser);
	}
	
	function echoStandings($link=1) {
		global $exercise;
		
		echo <<<EOD
<div class="row bot-buffer-sm"><center><form>
	&nbsp;&nbsp;用户名：<input class="form-control input-sm" style="display:inline;width:200px;" type="text" name="user" value="{$_GET['user']}" placeholder="模糊匹配">
	<div class="visible-xs top-buffer-sm"></div>
	班级姓名：<input class="form-control input-sm" style="display:inline;width:200px;" type="text" name="email" placeholder="模糊匹配" value="{$_GET['email']}">
	<div class="visible-xs top-buffer-sm"></div>
	提交记录从&nbsp;<input class="form-control input-sm" style="display:inline;width:75px;" type="text" name="id_from" placeholder="1" value="{$_GET['id_from']}">
	到&nbsp;<input class="form-control input-sm" style="display:inline;width:75px;" type="text" name="id_to" placeholder="正无穷" value="{$_GET['id_to']}">
	<button type="submit" class="btn btn-default btn-sm glyphicon glyphicon-search"></button>
</form></center></div>	
EOD;
		echo '<meta http-equiv="refresh" content="30;url=/exercise/'.$exercise['id'].'/standings">';
		
		$result=DB::query("select problem from exercise_problem where exercise={$exercise['id']} order by problem");
		$problem_no=array();
		$problems=array();
		for($i=3;$row=DB::fetch($result,MYSQLI_NUM);$i++){
			$problem_no[(int)$row[0]]=$i;
			$problems[] = ($link==1 ? (int)$row[0] : 0);
		}
		$details_width=$i;
		
		$cond="select id, problem_id, submitter, score from submissions where problem_id in (select problem from exercise_problem where exercise={$exercise['id']})";
		if($_GET['user'])
			$cond.=" and submitter like '%".DB::escape($_GET['user'])."%'";
		if($_GET['email'])
			$cond.=" and submitter in (select username from user_info where email like binary '%".DB::escape($_GET['email'])."%')";
		if(validateUInt($_GET['id_from']))
			$cond.=" and id>={$_GET['id_from']}";
		if(validateUInt($_GET['id_to']))
			$cond.=" and id<={$_GET['id_to']}";
		$cond.=" order by id";
		
		$result=DB::query($cond);
		$details=array();//sum_score, ak_time, [username, rating, email, isSuper], problem_score...
		while($row=DB::fetch($result,MYSQLI_NUM)){
			if(!isset($details[$row[2]])){
				$details[$row[2]][]=0;
				$details[$row[2]][]=0;
				$user=queryUser($row[2]);
				$details[$row[2]][]=array($user['username'],(int)$user['rating'],$user['email'],$user['usergroup']=='S'?1:0);
				for($i=3;$i<$details_width;$i++){
					$details[$row[2]][]=-1;
				}
			}
			if($row[3]==NULL){
				$row[3]=0;
			}
			if($details[$row[2]][$problem_no[$row[1]]]<$row[3]){
				if($details[$row[2]][$problem_no[$row[1]]]==-1){
					$details[$row[2]][$problem_no[$row[1]]]=0;
				}
				$details[$row[2]][0]+=$row[3]-$details[$row[2]][$problem_no[$row[1]]];
				$details[$row[2]][$problem_no[$row[1]]]=(int)$row[3];
				$details[$row[2]][1]=$row[0];
			}
		}
		usort($details,function($l,$r){
			if($l[0]==$r[0])
				return $l[1]-$r[1];
			return $r[0]-$l[0];
		});
		foreach($details as &$detail){
			$detail[1]=DB::selectFirst("select submit_time from submissions where id=".$detail[1],MYSQLI_NUM)[0];
		}
		unset($detail);
		
		echo '<div id="standings"></div>';
		echo '<script>';
		echo 'problems=',json_encode($problems),';';
		echo 'standings=',json_encode($details),';';
		echo '$(document).ready(showExerciseStandings());';
		echo '</script>';
	}
	
	$tabs_info = array(
		'dashboard' => array(
			'name' => '题目列表',
			'url' => "/exercise/{$exercise['id']}"
		),
		'submissions' => array(
			'name' => '提交记录',
			'url' => "/exercise/{$exercise['id']}/submissions"
		),
		'standings' => array(
			'name' => '完成情况',
			'url' => "/exercise/{$exercise['id']}/standings"
		)
	);
	
	if (!isset($tabs_info[$cur_tab])) {
		become404Page();
	}
	if(isSuperUser($myUser)){
		$exercise_type= new UOJForm('exercise_type');
		$types = array(
			'' => '未设置',
			'contest' => '模拟赛',
			'puji' => '普及练习',
			'tigao' => '提高练习',
			'noi' => '省选+练习'
		);
		$exercise_type->addVSelect('ex_type',$types,'设置练习类型:',$exercise['type']);
		$exercise_type->handle=function(){
			global $exercise;
			$esc_type=DB::escape($_POST['ex_type']);
			DB::query("update exercise set type='$esc_type' where id={$exercise['id']}");
			DB::manage_log('exercise','set exercise '.$exercise['id'].' type='.$esc_type);
		};
		$exercise_type->submit_button_config['class_str'] = 'btn btn-warning btn-block top-buffer-sm';
		$exercise_type->runAtServer();
	}
	if(isset($_POST['code'])){
		if($_POST['code']==$_SESSION['spider']){
			$_SESSION['spider']=uojRandString(5);
			if($cur_tab == 'standings'){
				echoStandings($msg==''?'1':'0');
			}elseif($msg!=''){
				echo $msg;
			}elseif($cur_tab == 'submissions'){
				echoSubmissions();
			}elseif($cur_tab == 'dashboard'){
				if(isSuperUser($myUser)){
					echo '<div class="col-sm-10">';
				}
				echoDashboard();
				if(isSuperUser($myUser)){
					echo '</div><div class="col-sm-2">';
					echo '<a href="/exercise/',$exercise['id'],'/manage" class="btn btn-primary btn-block">管理</a>';
					$exercise_type->printHTML();
					echo '</div>';
				}	
			}
			die();
		}
		die('页面已过期');
	}
?>
<?php
	$REQUIRE_LIB['mathjax'] = '';
	$REQUIRE_LIB['shjs'] = '';
?>
<?php echoUOJPageHeader(HTML::stripTags($exercise['name']) . ' - ' . $tabs_info[$cur_tab]['name'] . ' - ' . '练习') ?>
<div class="text-center">
	<h1><?= $exercise['name'] ?></h1>
</div>
<div class="row">
	<?php if(Auth::id()&&!isContestUser(Auth::user())):?>
		<?= HTML::tablist($tabs_info, $cur_tab) ?>
	<?php endif ?>
	<div class="top-buffer-md" id="content">
		<center>
			<h3>页面加载中，请稍候……</h3>
			<p>若长时间没有反应，请检查网络或刷新重试</p>
		</center>
	</div>
	<?= getEncodeJS('content') ?>
</div>
<?php echoUOJPageFooter() ?>