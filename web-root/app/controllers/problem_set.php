<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	requirePHPLib('svn');

	if($myUser==NULL){
		become403Page();
	}
	
	if(isContestUser(Auth::user())){
		becomeMsgPage('该功能不对比赛账户开放');
	}
	if (isSuperUser($myUser)) {
		$new_problem_form = new UOJForm('new_problem');
		$new_problem_form->handle = function() {
			$esc_config = DB::escape('{"view_content_type":"ALL_AFTER_AC","view_details_type":"ALL","view_all_details_type":"ALL"}');
			$esc_sconfig=DB::escape('[{"name":"answer","type":"source code","file_name":"answer.code"}]');
			DB::query("insert into problems (title, is_hidden, submission_requirement,extra_config) values ('New Problem', 1, '$esc_sconfig','$esc_config')");
			$id = DB::insert_id();
			DB::query("insert into problems_contents (id, statement, statement_md) values ($id, '', '')");
			DB::manage_log('problems','add problem '.$id);
			svnNewProblem($id);
		};
		$new_problem_form->submit_button_config['class_str'] = 'btn btn-primary';
		$new_problem_form->submit_button_config['text'] = UOJLocale::get('problems::add new');
		$new_problem_form->submit_button_config['smart_confirm'] = '';

		$new_problem_form->runAtServer();
	}

	function echoProblem($problem) {
		global $myUser;
		if (isProblemVisibleToUser($problem, $myUser)) {
			if ($problem['submission_id']) {
				echo '<tr class="text-center success">';
			} else {
				echo '<tr class="text-center">';
			}
			echo '<td>';
			if($score=DB::selectFirst("select score from best_submissions where submitter='{$myUser['username']}' and problem_id={$problem['id']}"))
				echo '<a href="/submissions?problem_id=',$problem['id'],'&submitter=',$myUser['username'],'"><span class="uoj-score" data-max="100">',$score['score'],'</span></a>';
			echo '</td>';
			echo '<td>#', $problem['id'], '</td>';
			echo '<td class="text-left">';
			if($problem['is_hidden']){
				echo '<span class="text-muted">[已隐藏]</span> ';
			}
			echo '<a href="/problem/', $problem['id'], '">', $problem['title'], '</a>';
			if (!isset($_COOKIE['not_show_tags_mode'])) {
				foreach (queryProblemTags($problem['id']) as $tag) {
					echo '<a class="uoj-problem-tag">', '<span class="badge">', HTML::escape($tag), '</span>', '</a>';
				}
			}
			echo '</td>';
			if (!isset($_COOKIE['not_show_submit_mode'])) {
                $perc = $problem['submit_num'] > 0 ? round(100 * $problem['ac_num'] / $problem['submit_num']) : 0;
                $qid=$problem['id'];
                $fff=$perc?'%':'';
                echo <<<EOD
                <td><a href="/submissions?problem_id={$problem['id']}&min_score=100&max_score=100">{$problem['ac_num']}</a></td>
                <td>
                    <div class="progress bot-buffer-no">
                        <div class="progress-bar progress-bar-success" id="buffer$qid" role="progressbar" aria-valuenow="$perc" aria-valuemin="0" aria-valuemax="100" style="width: 0%; min-width: 30px;"></div>
                    </div>
                </td>
<script type="text/javascript">
        setTimeout(function(){
            var i=-1;
            $("#buffer$qid").css("width",$perc+"%");
            var timer = setInterval(function(){
                i++;
                var k='%';
                k=i+k;
                $("#buffer$qid").text(i+"$fff");
                if(i >= $perc) clearInterval(timer);
            },1)
        },300);
</script>
EOD;
			}
			echo '<td class="text-center';
			if($problem['id']==1)
				echo ' hahaha';
			echo '">', getClickZanBlock('P', $problem['id'], $problem['zan']), '</td>';
			echo '</tr>';
		}
	}

	$cur_tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
		
	$search_tag = null;
	if ($cur_tab == 'template') {
		$search_tag = "模板题";
	}
	if (isset($_GET['tag'])) {
		$search_tag = $_GET['tag'];
	}
	if ($search_tag) {
		$cond= "'".DB::escape($search_tag)."' in (select tag from problems_tags where problems_tags.problem_id = problems.id)";
	}elseif($_GET['search']){
				$cond= "id='".DB::escape($_GET['search'])."' or title like '%".$_GET['search']."%' or problems.id in (select id from problems_contents where statement like '%".DB::escape($_GET['search'])."%')";
		}else {
		$cond = '1';
	}

	$header = '<tr>';
	$header .= '<th style="width:0;">&nbsp;</th>';
	$header .= '<th class="text-center" style="width:0;">ID</th>';
	$header .= '<th>'.UOJLocale::get('problems::problem').'</th>';
	if (!isset($_COOKIE['not_show_submit_mode'])) {
		$header .= '<th class="text-center" style="width:0;">'.UOJLocale::get('problems::ac').'</th>';
		$header .= '<th class="text-center" style="width:200px;">'.UOJLocale::get('problems::ac ratio').'</th>';
	}
	$header .= '<th class="text-center" style="width:170px">'.UOJLocale::get('appraisal').'</th>';
	$header .= '</tr>';

	$tabs_info = array(
		'all' => array(
			'name' => UOJLocale::get('problems::all problems'),
			'url' => "/problems"
		),
		'template' => array(
			'name' => UOJLocale::get('problems::template problems'),
			'url' => "/problems/template"
		)
	);

	$pag_config = array('page_len' => 50);
	$pag_config['col_names'] = array('*');
	$pag_config['table_name'] = "problems left join best_ac_submissions on best_ac_submissions.submitter = '{$myUser['username']}' and problems.id = best_ac_submissions.problem_id";
	$pag_config['cond'] = $cond;
	$pag_config['tail'] = "order by id asc";
	$pag = new Paginator($pag_config);

	$tag_list=array();
	$result = DB::query("select distinct tag from problems_tags");
	while($row=DB::fetch($result,MYSQLI_NUM))
		$tag_list[]= iconv('UTF-8', 'GBK//IGNORE',HTML::escape($row[0]));
	sort($tag_list,SORT_STRING);
	$tag_list_len=count($tag_list);
	for($i=0;$i<$tag_list_len;$i++)
		$tag_list[$i]=iconv('GBK', 'UTF-8//IGNORE', $tag_list[$i]);
?>
<?php echoUOJPageHeader(UOJLocale::get('problems')) ?>
<div class="row">
<div class="col-sm-9">
<?php
	echo $pag->pagination();

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
</div>
<div class="col-sm-3">
	<div class="panel panel-default">
	<div class="panel-body">
		<div class="problem-set-list">
			<?= HTML::tablist($tabs_info, $cur_tab, 'nav-pills') ?>
		</div>
		<form class="top-buffer-md text-center" style="display:flex">
			<input class="form-control input-sm" style="display:inline;width:200px;" type="text" name="search" placeholder="输入关键字查找题目">
			<button type="submit" class="btn btn-primary btn-sm glyphicon glyphicon-search" style="margin-left:5px"></button>
		</form>
		<div class="checkbox text-center">
			<label class="checkbox-inline" for="input-show_tags_mode"><input type="checkbox" id="input-show_tags_mode" <?= isset($_COOKIE['not_show_tags_mode']) ? '': 'checked="checked"'?>/> <?= UOJLocale::get('problems::show tags') ?></label>
			<label class="checkbox-inline" for="input-show_submit_mode"><input type="checkbox" id="input-show_submit_mode" <?= isset($_COOKIE['not_show_submit_mode']) ? '': 'checked="checked"'?>/> <?= UOJLocale::get('problems::show statistics') ?></label>
		</div>
		<?php if(isSuperUser($myUser)):?>
			<div class="row">
			<div class="col-sm-6">
				<?php $new_problem_form->printHTML() ?>
			</div>
			<div class="col-sm-6">
				<div class="text-center">
					<a href="/autocopy" class="btn btn-primary">自动加题</a>
				</div>
			</div>
			</div>
		<?php endif ?>
	</div>
	</div>
	<div class="panel panel-info">
		<div class="panel-heading">
			<h4 class="panel-title">标签</h4>
		</div>
		<div class="panel-body" style="line-height:200%">
			<?php foreach($tag_list as $tag):?>
				<a class="uoj-problem-tag"><span class="badge"><?=$tag?></span></a>&nbsp;
			<?php endforeach ?>
		</div>
	</div>
</div>
</div>
<script type="text/javascript">
$('#input-show_tags_mode').click(function() {
	if (this.checked) {
		$.removeCookie('not_show_tags_mode', {path: '/problems'});
	} else {
		$.cookie('not_show_tags_mode', '', {path: '/problems'});
	}
	location.reload();
});
$('#input-show_submit_mode').click(function() {
	if (this.checked) {
		$.removeCookie('not_show_submit_mode', {path: '/problems'});
	} else {
		$.cookie('not_show_submit_mode', '', {path: '/problems'});
	}
	location.reload();
});
</script>
<?php echoUOJPageFooter() ?>

