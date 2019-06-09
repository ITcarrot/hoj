<?php
	if(!Auth::check()||isContestUser(Auth::user())){
		become403Page();
	}
	
	$cur_tab=$_GET['tab'];
	if($cur_tab!='rumen'&&$cur_tab!='puji'&&$cur_tab!='tigao'&&$cur_tab!='noi')
		$cur_tab='shengxuan';
	$tabs_info = array(
		'rumen' => array(
			'name' => '入门',
			'url' => "/ladder?tab=rumen"
		),
		'puji' => array(
			'name' => '普及',
			'url' => "/ladder?tab=puji"
		),
		'tigao' => array(
			'name' => '提高',
			'url' => "/ladder?tab=tigao"
		),
		'shengxuan' => array(
			'name' => '省选',
			'url' => "/ladder?tab=shengxuan"
		),
		'noi' => array(
			'name' => 'NOI',
			'url' => "/ladder?tab=noi"
		)
	);
	
	function isExerciseFinish($id)
	{
		global $myUser;
		$re=DB::query("select * from exercise_problem where exercise=$id");
		$cnt=0;
		while($row=DB::fetch($re)){
			$finish_this=DB::selectFirst("select * from best_ac_submissions where submitter = '{$myUser['username']}' and problem_id={$row['problem']}");
			if(!$finish_this)
				$cnt++;
		}
		return $cnt;
	}
	function echoExercise($id)
	{
		$exercise=DB::selectFirst("select name from exercise where id=".$id);
		$re=DB::query("select depend from exercise_depend where id=$id");
		while($row=DB::fetch($re)){
			$msg=isExerciseFinish($row['depend']);
			if($msg!=0){
				$name=DB::selectFirst("select name from exercise where id={$row['depend']}")['name'];
				echo <<<EOD
<div class="panel ladder-lock text-center">
	<div class="panel-heading">
		<h4 class="panel-title">#{$id}：{$exercise['name']}</h4>
	</div>
	<div class="panel-body">
		<h4 class="panel-title" style="display:inline;">
		<span style="border-radius:4px;background:#474747;color:white;">&nbsp;未解锁&nbsp;</span>
		</h4>
		<span style="font-size: 16px;">需完成<a href="/exercise/{$id}">{$name}</a></span>
	</div>
</div>			
EOD;
				return;
			}
		}
		$msg=isExerciseFinish($id);
		if($msg!=0){
			$tot=DB::selectFirst("select count(*) from exercise_problem where exercise=$id",MYSQLI_NUM)[0];
			$per=(int)(($tot-$msg)*100/$tot);
			echo <<<EOD
<a href="/exercise/{$id}" class="a-ladder">
<div class="panel ladder-unlock text-center">
	<div class="panel-heading">
		<h4 class="panel-title">#{$id}：{$exercise['name']}</h4>
	</div>
	<div class="panel-body">
		<h4 class="panel-title">
		<span style="border-radius:4px;background:#EE4E00;color:white;">&nbsp;可AK&nbsp;</span>
		还剩{$msg}题
		</h4>
		<div class="progress bot-buffer-no top-buffer-sm">
			<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="$per" aria-valuemin="0" aria-valuemax="100" style="width: $per%; min-width: 30px;">$per%</div>
		</div>
	</div>
</div>
</a>
EOD;
			return;
		}
		echo <<<EOD
<a href="/exercise/{$id}" class="a-ladder">
<div class="panel ladder-finish text-center">
	<div class="panel-heading">
		<h4 class="panel-title">#{$id}：{$exercise['name']}</h4>
	</div>
	<div class="panel-body">
		<h4 class="panel-title">
		<span style="border-radius:4px;background:#20BF1E;color:white;">&nbsp;已AK&nbsp;</span>
		%%%
		</h4>
	</div>
</div>
</a>
EOD;
	}
?>
<?php echoUOJPageHeader('天梯') ?>
<div class="row">
<h4 class="col-sm-1 text-right">难度</h4>
<div class="col-sm-4">
	<?=HTML::tablist($tabs_info, $cur_tab, 'nav-pills')?>
</div>
<h4 class="col-sm-1 text-right"><nobr>算法标签</nobr></h4>
<div class="col-sm-5">
<?php
	$tag_num=array();
	$tag_cnt=0;
	$re=DB::query("select * from ladder where type='".$cur_tab."' and tag is not null order by r asc");
	echo '<ul class="nav nav-pills" role="tablist">';
	while($row=DB::fetch($re)){
		$tag_cnt++;
		echo '<li><a href="#tag'.$tag_cnt.'">',$row['tag'],'</a></li>';
		$tag_num[$row['tag']]=$tag_cnt;
	}
	echo '</ul>';
?>
</div>
<?php if(isSuperUser($myUser)):?>
<div class="col-sm-1 text-right">
	<a href="/ladder/manage?tab=<?=$cur_tab?>" class="btn btn-primary">管理</a>
</div>
<?php endif ?>
</div>
<?php
	$re=DB::query("select * from ladder where type='".$cur_tab."' order by r asc,c asc");
	$last=$now=DB::fetch($re);
	$cnt=-1;
	if($now){
		echo '<div class="top-buffer-md"></div><div class="row">';
		do{
			if($now['tag']){
				echo '</div>';
				echo '<a name="tag'.$tag_num[$now['tag']].'"><hr></a>';
				echo '<h4 class="text-center">',$now['tag'],'</h4>';
				echo '<div class="row">';
				$last=$now;
				continue;
			}
			if($now['r']==$last['r'])
				$cnt++;
			else{
				$cnt=0;
				echo '</div><div class="row">';
			}
			echo '<div class="col-sm-3 col-sm-push-',$now['c']-3*$cnt,'">';
			echoExercise($now['exercise_id']);
			echo '</div>';
			$last=$now;
		}while($now=DB::fetch($re));
		echo '</div>';
	}
?>
<?php echoUOJPageFooter() ?>

