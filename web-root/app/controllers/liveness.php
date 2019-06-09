<?php
	$list=getLivenessList();
	$len=count($list);
	echoUOJPageHeader('活跃度排行榜');
?>
<div class="table-responsive">
<table class="table table-bordered table-hover table-striped table-text-center">
<thead>
	<tr>
		<th style="width: 5em;">#</th>
		<th style="width: 17em;">用户名</th>
		<th style="width: 53em;">格言</th>
		<th style="width: 5em;">活跃度</th>
	</tr>
</thead>
<tbody>
	<?php
	for($i=0;$i<$len && $list[$i][1]>0;$i++){
		echo '<tr>';
		echo '<td>',$i+1,'</td>';
		echo '<td>',getUserLink($list[$i][0]),'</td>';
		$tmp=$list[$i][2];
		$pat='/<\/{0,1}[A-Za-z]+?.*?';
		$pat.='>/';
		$tmp=preg_replace($pat,'',$tmp);
		echo '<td align="center" style="max-width: 1px;overflow: hidden; text-overflow:ellipsis;white-space: nowrap"><nobr>' . HTML::escape($tmp) . '</nobr></td>';
		echo '<td><span class="uoj-username" data-link="0" data-rating="',($list[$i][1]+1000),'">',$list[$i][1],'</span></td>';
		echo '</tr>';
	}
	?>
</tbody>
</table>
</div>
<?php echoUOJPageFooter() ?>