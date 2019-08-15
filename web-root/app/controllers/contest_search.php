<?php
	$query=array();
	if($_GET['search']){
		$query=explode(',',$_GET['search']);
		$len=count($query);
		for($i=0;$i<$len;$i++){
			if(!validateUInt($query[$i]) || !($contest = queryContest($query[$i])))
				unset($query[$i]);
			if(isset($contest) && isset($query[$i]) && $contest['status'] != 'finished')
				unset($query[$i]);
		}
	}
	$query=array_values($query);
	$len=count($query);
	if($len > 0){
		$header1=$header2='';
		$problem_pos=array();
		$problem_cnt=0;
		for($i=0;$i<$len;$i++){
			$name=DB::selectFirst("select name from contests where id={$query[$i]}",MYSQLI_NUM)[0];
			$res=DB::query("select problem_id from contests_problems where contest_id={$query[$i]} order by problem_id");
			$cnt=0;
			while($row=DB::fetch($res,MYSQLI_NUM)){
				if(Auth::check())
					$header2.='<th style="width: 4em;"><a href="'.HTML::url('/problem/'.$row[0]).'">'.chr(ord('A') + $cnt).'</a></th>';
				else
					$header2.='<th style="width: 4em;">'.chr(ord('A') + $cnt).'</th>';
				$cnt++;
				$problem_pos[$row[0]]=$problem_cnt;
				$problem_cnt++;
			}
			$header1.='<th colspan="'.$cnt.'"><a href="'.HTML::url('/contest/'.$query[$i]).'">'.$name.'</a></th>';
		}
		
		$ranking=array();//username, score, [T1 score, submission_id], ... 
		$res=DB::query('select submission_id, submitter, problem_id, score from contests_submissions where contest_id in ('.join(',',$query).')');
		while($row=DB::fetch($res)){
			if(!isset($ranking[$row['submitter']])){
				$ranking[$row['submitter']]=array($row['submitter'],0);
			}
			$ranking[$row['submitter']][$problem_pos[$row['problem_id']]+2]=array($row['score'],$row['submission_id']);
			$ranking[$row['submitter']][1]+=$row['score'];
		}
		usort($ranking,function($l,$r){
			if($l[1]==$r[1])
				return strcmp($l[0],$r[0]);
			return $l[1]<$r[1];
		});
	}
	
	$REQUIRE_LIB['excel']='';
?>
<?php echoUOJPageHeader('比赛成绩统计') ?>

<form class="text-center bot-buffer-md">
	<label class="control-label">搜索比赛编号</label>
	<input class="form-control input-sm" style="width:160px;display:inline;" type="text" name="search" placeholder="输入比赛编号，以,分割" value="<?= join(',',$query) ?>">
	<button type="submit" class="btn btn-primary btn-sm glyphicon glyphicon-search"></button>
	<button class="btn btn-primary btn-sm glyphicon glyphicon-save" id="save_btn" <?= $len==0 ? 'disabled="1"' : '' ?>></button>
</form>
<?php if($len > 0): ?>
<div class="table-responsive">
	<table id="result" class="table table-bordered table-striped table-text-center table-vertical-middle table-condensed">
		<thead>
			<tr>
				<th colspan="2"></th>
				<?= $header1 ?>
			</tr>
			<tr>
				<th style="width: 28em;"><?=UOJLocale::get('username')?></th>
				<th style="width: 4em;">总分</th>
				<?= $header2 ?>
			</tr>
		</thead>
		<tbody>
			<?php
				foreach($ranking as $player){
					echo '<tr>';
					echo '<td>',getUserLink($player[0]),'</td>';
					echo '<td><span class="uoj-score" data-max="',$problem_cnt*100,'">',$player[1],'</span></td>';
					for($i=0;$i<$problem_cnt;$i++){
						echo '<td>';
						if(isset($player[$i+2])){
							if(Auth::check())
								echo '<a href="',HTML::url('/submission/'.$player[$i+2][1]),'">';
							echo '<span class="uoj-score">',$player[$i+2][0],'</span>';
							if(Auth::check())
								echo '</a>';
						}
						echo '</td>';
					}
					echo '</tr>';
				}
			?>
		</tbody>
	</table>
</div>

<script type="text/javascript">
	$("#save_btn").click(function(){
		$("#result").table2excel({    
			exclude: ".noExl",    
			name: "Excel Document Name",    
			filename: "比赛成绩统计",    
			exclude_img: true,    
			exclude_links: true,    
			exclude_inputs: true    
		});    
	});
</script>
<?php endif ?>
<?php echoUOJPageFooter() ?>