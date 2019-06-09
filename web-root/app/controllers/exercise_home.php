<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	requirePHPLib('svn');

	if (isSuperUser($myUser)) {
		$new_problem_form = new UOJForm('new_problem');
		$new_problem_form->handle = function() {
			DB::query("insert into exercise (name) value ('New Exercise')");
			DB::manage_log('exercise','add exercise '.DB::insert_id());
		};
		$new_problem_form->submit_button_config['align'] = 'right';
		$new_problem_form->submit_button_config['class_str'] = 'btn btn-primary';
		$new_problem_form->submit_button_config['text'] = '新建练习';
		$new_problem_form->submit_button_config['smart_confirm'] = '';

		$new_problem_form->runAtServer();
	}

	function echoProblem($exercise) {
	    global $myUser;
	    $not_finish=0;
		$tot=0;
	    $result=DB::query("select * from exercise_problem where exercise={$exercise['id']}");
	    while($row=DB::fetch($result)){
		    $finish_this=DB::fetch(DB::query("select * from best_ac_submissions where submitter = '{$myUser['username']}' and problem_id={$row['problem']}"));
		    if(!$finish_this){
			    $not_finish++;
		    }
			$tot++;
	    }
	    if ($not_finish==0) {
		    echo '<tr class="text-center success">';
			$per=100;
	    } else {
		    echo '<tr class="text-center">';
			$per=(int)(($tot-$not_finish)*100/$tot);
	    }
        $fff=$per?'%':'';
        $qid=$exercise['id'];
        echo '<td>#', $exercise['id'], '</td>';
        echo '<td class="text-left"><a href="/exercise/', $exercise['id'], '">', $exercise['name'], '</a></td>';
        echo <<<EOD
<td><div class="progress bot-buffer-no top-buffer-no">
    <div class="progress-bar progress-bar-success" id="buffer$qid" role="progressbar" aria-valuenow="$per" aria-valuemin="0" aria-valuemax="100" style="width: 0%; min-width: 20px;"></div>
</div></td>
<script type="text/javascript">
        setTimeout(function(){
            var i=-1;
            $("#buffer$qid").css("width",$per+"%");
            var timer = setInterval(function(){
                i++;
                var k='%';
                k=i+k;
                $("#buffer$qid").text(i+"$fff");
                if(i >= $per) clearInterval(timer);
            },1)
        },300);
</script>
EOD;
		switch($exercise['type']){
			case 'contest':
				echo '<td>模拟赛</td>';
				break;
			case 'puji':
				echo '<td>练习：入门~普及</td>';
				break;
			case 'tigao':
				echo '<td>练习：提高</td>';
				break;
			case 'noi':
				echo '<td>练习：省选及以上</td>';
				break;
			default:
				echo '<td>无</td>';
				break;
		}
		echo '</tr>';
	}

	$header = '<tr>';
	$header .= '<th class="text-center" style="width:5em;">ID</th>';
	$header .= '<th>练习名称</th>';
	$header .= '<th class="text-center" style="width:250px;">进度</th>';
	$header .= '<th class="text-center" style="width:150px;">分类</th>';
	$header .= '</tr>';

	$tabs_info = array(
		'all' => array(
			'name' => '所有练习',
			'url' => "/exercises"
		),
		'contest' => array(
			'name' => '模拟赛',
			'url' => "/exercises?type=contest"
		),
		'puji' => array(
			'name' => '练习:入门~普及',
			'url' => "/exercises?type=puji"
		),
		'tigao' => array(
			'name' => '练习：提高',
			'url' => "/exercises?type=tigao"
		),
		'noi' => array(
			'name' =>'练习：省选及以上',
			'url' => "/exercises?type=noi"
		)
	);
	switch($_GET['type']){
		case 'contest':
			$cur_tab='contest';
			break;
		case 'puji':
			$cur_tab='puji';
			break;
		case 'tigao':
			$cur_tab='tigao';
			break;
		case 'noi':
			$cur_tab='noi';
			break;
		default:
			$cur_tab='all';
	}
		
	$cond='1';
	if($cur_tab!='all'){
		$cond="type='".$cur_tab."'";
	}
	if($_GET['search']){
		$esc_search=DB::escape($_GET['search']);
		$cond="id='".$esc_search."' or name like '%".$esc_search."%' or information like '%".$esc_search."%'";
	}
	$pag_config = array('page_len' => 50);
	$pag_config['col_names'] = array('*');
	$pag_config['table_name'] = "exercise";
	$pag_config['cond'] = $cond;
	$pag_config['tail'] = "order by id desc";
	$pag = new Paginator($pag_config);
?>
<?php echoUOJPageHeader("练习") ?>

<div class="row">
	<div class="col-sm-8">
		<?= HTML::tablist($tabs_info, $cur_tab, 'nav-pills') ?>
	</div>
	<div class="col-sm-4">
		<form class="text-right" style="position: relative;top: 9px;">
			<input class="form-control input-sm" style="display:inline;width:200px;" type="text" name="search" placeholder="输入关键字查找练习">
			<button type="submit" class="btn btn-primary btn-sm glyphicon glyphicon-search"></button>
		</form>
	</div>
</div>

<div class="row">
	<div class="col-sm-2"></div>
	<div class="col-sm-8"><?php echo $pag->pagination(); ?></div>
	<div class="col-sm-2">
<?php
	if(isSuperUser($myUser)){
		$new_problem_form->printHTML();
		echo '<div class="top-buffer-sm"></div>';
	}
?>
	</div>
</div>
<?php
	echo '<div class="table-responsive">';
	echo '<table class="table table-bordered table-hover table-striped">';
	echo '<thead>';
	echo $header;
	echo '</thead>';
	echo '<tbody>';
		
	foreach ($pag->get() as $idx => $row) {
		echoProblem($row);
	}

	echo '</tbody>';
	echo '</table>';
	echo '</div>';

	echo $pag->pagination();
?>
<?php echoUOJPageFooter() ?>
