<?php
	$blogs_pag = new Paginator(array(
		'col_names' => array('*'),
		'table_name' => 'blogs',
		'cond' => "poster = '".UOJContext::user()['username']."' and is_hidden = 0",
		'tail' => 'order by post_time desc limit 5',
		'echo_full' => true
	));
	$setting = json_decode(UOJContext::user()['setting'], true);
?>
<?php
	$REQUIRE_LIB['mathjax'] = '';
	$REQUIRE_LIB['shjs'] = '';
?>
<?php echoUOJPageHeader(UOJContext::user()['username'] . '的博客') ?>

<div class="row">
	<div class="col-md-9">
		<?php if ($blogs_pag->isEmpty()): ?>
			<div class="text-muted">此人很懒，什么博客也没留下。</div>
		<?php else: ?>
			<?php foreach ($blogs_pag->get() as $blog): ?>
				<?php echoBlog($blog, array('is_preview' => true)) ?>
			<?php endforeach ?>
		<?php endif ?>
	</div>
	<div class="col-md-3">
		<img class="media-object img-thumbnail center-block" alt="该用户还未设置头像/该用户头像无法显示" src="<?= HTML::escape($setting['avatar']) ?>" />
	</div>
</div>
<?php if (isset($setting['nest'])): ?>
	<script type="text/javascript" color="0,0,255" opacity='1' zIndex="-2" count="99" src="/min/js?rq=%7B%22canvas-nest%22%3A%22%22%7D"></script>
<?php endif ?>
<?php if ($setting['background']!=''): ?>
<script type="text/javascript">
	$(document.body).append('<div class="cover bg-opacity-3"></div>');
	$(".container").css("border-radius","50px");
	$(".container").addClass("bg-opacity-3");
	$(".navbar").addClass("bg-opacity-3");
	$(".panel").addClass("bg-opacity-3");
	$("pre").addClass("bg-opacity-3");
	$(document.body).css({
		'background':"url(<?= HTML::escape($setting['background']) ?>)",
		'background-position':'center',
		'background-size':'cover',
		'background-attachment':'fixed'
	});
</script>
<?php endif ?>
<?php echoUOJPageFooter() ?>