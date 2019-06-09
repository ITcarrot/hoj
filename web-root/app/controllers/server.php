<?php
	if(!Auth::check()){
		become403Page();
	}
	if($_GET['xqq']=='C20180089'){
		ob_start();
		echoSubmissionsList('status not like "Judged%"', 'order by id asc', array('judge_time_hidden' => '','table_config' => array('echo_full' => '')), $myUser);
		$list=ob_get_contents();
		ob_end_clean();
		$res=DB::query('select * from judger_info');
		while($row=DB::fetch($res)){
			$cont=@file_get_contents("http://{$row['ip']}:89");
			if(!$cont)
				$cont = '<h3 class="text-center">'.$row['judger_name'].'</h3><p>该评测机已离线！</p>';
			$judger_info.=$cont;
		}
		die(json_encode(array($list,$judger_info)));
	}
	echoUOJPageHeader('评测机状态');
?>
<div class="row">
	<div class="col-sm-8" id="judger_info"></div>
	<div class="col-sm-4">
		<table class="table table-bordered table-hover table-striped table-text-center">
			<thead>
				<tr><th colspan="233">统计信息区前五行是系统整体的统计信息。</th></tr>
			</thead>
			<tbody>
				<tr><td colspan="233">1. 第一行是任务队列信息</td></tr>
				<tr><td>12:38:33</td><td>当前时间</td></tr>
				<tr><td>up 50days</td><td>系统运行时间，格式为时:分</td></tr>
				<tr><td>1 user</td><td>当前登录用户数</td></tr>
				<tr><td>load average: 0.06, 0.60, 0.48</td><td>系统负载，即任务队列的平均长度。 三个数值分别为  1分钟、5分钟、15分钟前到现在的平均值。</td></tr>

				<tr><td colspan="233">2. 第二、三行为进程和CPU的信息</td></tr>
				<tr><td>Tasks: 29 total</td><td>进程总数</td></tr>
				<tr><td>1 running</td><td>正在运行的进程数</td></tr>
				<tr><td>28 sleeping</td><td>睡眠的进程数</td></tr>
				<tr><td>0 stopped</td><td>停止的进程数</td></tr>
				<tr><td>0 zombie</td><td>僵尸进程数</td></tr>
				<tr><td>Cpu(s): 0.3% us</td><td>用户空间占用CPU百分比</td></tr>
				<tr><td>1.0% sy</td><td>内核空间占用CPU百分比</td></tr>
				<tr><td>0.0% ni</td><td>用户进程空间内改变过优先级的进程占用CPU百分比</td></tr>
				<tr><td>98.7% id</td><td>空闲CPU百分比</td></tr>

				<tr><td colspan="233">3. 第四五行为内存信息。</td></tr>
				<tr><td>Mem: 191272k total</td><td>物理内存总量</td></tr>
				<tr><td>173656k used</td><td>使用的物理内存总量</td></tr>
				<tr><td>17616k free</td><td>空闲内存总量</td></tr>
				<tr><td>22052k buffers</td><td>用作内核缓存的内存量</td></tr>
				<tr><td><nobr>Swap: 192772k total</nobr></td><td>交换区总量</td></tr>
				<tr><td>0k used</td><td>使用的交换区总量</td></tr>
				<tr><td>192772k free</td><td>空闲交换区总量</td></tr>
				<tr><td>123988k cached</td><td>缓冲的交换区总量。 内存中的内容被换出到交换区，而后又被换入到内存，但使用过的交换区尚未被覆盖， 该数值即为这些内容已存在于内存中的交换区的大小。相应的内存再次被换出时可不必再对交换区写入。</td></tr>
			</tbody>
		</table>
	</div>
</div>
<h3 class="text-center">评测队列</h3>
<div id="list"></div>
<script type="text/javascript">
	function update(){
		$.get(window.location.href,{xqq:'C20180089'},function(data){
			$("#list").html(data[0]);
			$('#list').uoj_highlight();
			$('#judger_info').html(data[1]);
		},'json').always(function(){
			setTimeout(update,1000);
		});
	}
	update();
</script>
<?php echoUOJPageFooter() ?>