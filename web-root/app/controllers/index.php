<?php
	$blogs = DB::selectAll("select blogs.id, title, poster, post_time from important_blogs, blogs where is_hidden = 0 and important_blogs.blog_id = blogs.id order by level desc, important_blogs.blog_id desc limit 5");
	$liveness=getLivenessList();
	$access_cnt=0;
	for($i=0;$i<38;$i++){
		$access_cnt+=filesize("/var/www/uoj/app/storage/access_log/$i.cnt");
	}
	
	$REQUIRE_LIB['num']='';
?>
<?php echoUOJPageHeader('HOJ') ?>
<div class="row">
	<div class="col-sm-9"><div class="panel panel-default"><div class="panel-body">
		<div class="row">
			<div class="col-md-9"><table class="table">
				<thead><tr>
					<th><?= UOJLocale::get('announcements') ?></th>
					<th></th>
					<th></th>
				</tr></thead>
				<tbody>
					<?php $now_cnt = 0; ?>
					<?php foreach ($blogs as $blog): ?>
						<?php
							$now_cnt++;
							$new_tag = '';
							if ((time() - strtotime($blog['post_time'])) / 3600 / 24 <= 7) {
								$new_tag = '<sup style="color:red">&nbsp;new</sup>';
							}
						?>
						<tr>
							<td><a href="/blog/<?= $blog['id'] ?>"><?= $blog['title'] ?></a><?= $new_tag ?></td>
							<td><?= getUserLink($blog['poster']) ?></td>
							<td><small><?= $blog['post_time'] ?></small></td>
						</tr>
					<?php endforeach ?>
					<?php for ($i = $now_cnt + 1; $i <= 5; $i++): ?>
						<tr><td colspan="233">&nbsp;</td></tr>
					<?php endfor ?>
					<tr><td class="text-right" colspan="233"><a href="/announcements" class="btn btn-default"><?= UOJLocale::get('all the announcements') ?></a></td></tr>
				</tbody>
			</table></div>
			<div class="col-md-3">
				<center><img class="media-object img-thumbnail" src="/pictures/UOJ.png" alt="HOJ logo" id="logo" style="height:160px;"></center>
				<?php if(Auth::check()):?>
					<h4>您是第</h4>
					<center><div id="dataNums"></div></center>
					<script>
						$(function(){
							$("#dataNums").rollNumDaq({
								deVal:<?=$access_cnt?>
							});
						});
					</script>
					<h4 class="pull-right">位访客</h4>
				<?php endif ?>
			</div>
		</div>
	</div></div></div>
	<div class="col-sm-3"><div class="panel panel-default"><div class="panel-body">
		<table class="table text-center">
			<thead><tr>
				<th class="text-center" colspan="233"><h3 style="margin-top:0;margin-bottom:0;">活跃度排行榜</h3></th>
			</tr></thead>
			<tbody>
				<?php
					$len=count($liveness);
					for($i=0;$i<$len && $i<5;$i++){
						echo '<tr>';
						echo '<td>',getUserLink($liveness[$i][0]),'</td>';
						echo '<td><span class="uoj-username" data-link="0" data-rating="',($liveness[$i][1]+1000),'">',$liveness[$i][1],'</span></td>';
						echo '</tr>';
					}
					for($i=$len;$i<5;$i++)
						echo '<tr><td>&nbsp;</td><td></td></tr>';
				?>
				<tr><td class="text-center" colspan="233"><a href="/liveness" class="btn btn-default">查看全部</a></td></tr>
			</tbody>
		</table>
	</div></div></div>
</div>
<div class="row">
	<div class="col-md-10">
		<h3 class="text-center" style="margin-bottom:20px;"><?= UOJLocale::get('top rated') ?></h3>
		<?php echoRanklist(array('echo_full' => '', 'top12' => '')) ?>
		<div class="text-center">
			<a href="/ranklist" class="btn btn-default"><?= UOJLocale::get('view all') ?></a>
		</div>
	</div>
	<div class="col-md-2">
		<h3>&nbsp;</h3>
		<?php if(Auth::id()&&!isContestUser(Auth::user())):?>
			<div class="panel panel-default panel-jump"><div class="panel-body text-center"><form id="form-problem">
				<h3>跳转到题目</h3>
				<input id="input-problem" class="form-control input-sm" type="text" style="max-width:115px;margin: 0 auto 10px auto;" placeholder="输入题目编号">
				<input type="submit" class="btn btn-primary btn-sm" value="跳转">
			</form></div></div>
		<?php endif ?>
		<div class="panel panel-default panel-jump"><div class="panel-body text-center"><form id="form-exercise">
			<h3>跳转到练习</h3>
			<input id="input-exercise" class="form-control input-sm" type="text" style="max-width:115px;margin: 0 auto 10px auto;" placeholder="输入练习编号">
			<input type="submit" class="btn btn-primary btn-sm" value="跳转">
		</form></div></div>
		<div class="panel panel-default panel-jump"><div class="panel-body text-center"><form id="form-user">
			<h3>跳转到用户</h3>
			<div style="max-width:115px;margin: 0 auto 10px auto;">
				<input class="form-control input-sm" type="text" id="input-user" placeholder="输入用户名">
				<script>$('#input-user').autouser();</script>
			</div>
			<input type="submit" class="btn btn-primary btn-sm" value="跳转">
		</form></div></div>
		<div class="panel panel-default panel-jump"><div class="panel-body text-center">
			<h4>友情链接</h4>
			<p><a href='http://www.hsfz.net.cn' target="_blank">华南师大附中</a></p>
			<p><a href='http://uoj.ac' target="_blank">UOJ</a><p>
		</div></div>
	</div>
</div>
<script>
function jump(name, url_pre) {
	$('#form-' + name).submit(function(e) {
		e.preventDefault();
		window.location.href = url_pre + $('#input-' + name).val();
	});
}
jump('problem', '/problem/');
jump('exercise', '/exercise/');
jump('user', '/user/profile/');
</script>
<?php echoUOJPageFooter() ?>