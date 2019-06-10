<?php
	if ($is_preview) {
		$readmore_pos = strpos($blog['content'], '<!-- readmore -->');
		if ($readmore_pos !== false) {
			$content = substr($blog['content'], 0, $readmore_pos).'<p><a href="'.HTML::blog_url(UOJContext::userid(), '/blog/'.$blog['id']).'">阅读更多……</a></p>';
		} else {
			$content = $blog['content'];
		}
		$css = 'style="max-height:580px;overflow:auto;"';
	} else {
		$content = $blog['content'];
		$css = '';
	}
	
	$extra_text = $blog['is_hidden'] ? '<span class="text-muted">[已隐藏]</span> ' : '';
	
	$blog_type = $blog['type'] == 'B' ? 'blog' : 'slide';
?>
<h2><?= $extra_text ?><a class="header-a" href="<?= HTML::blog_url(UOJContext::userid(), '/blog/'.$blog['id']) ?>"><?= $blog['title'] ?></a></h2>
<div class="bot-buffer-md"><?= $blog['post_time'] ?> <strong>By</strong> <?= getUserLink($blog['poster']) ?></div>
<?php if (!$show_title_only): ?>
<div class="panel panel-default">
	<div class="panel-body" <?= $css ?> >
		<?php if ($blog_type == 'blog'): ?>
		<article><?= $content ?></article>
		<?php elseif ($blog_type == 'slide'): ?>
		<article>
			<div class="embed-responsive embed-responsive-16by9">
				<iframe class="embed-responsive-item" src="<?= HTML::blog_url(UOJContext::userid(), '/slide/'.$blog['id']) ?>"></iframe>
			</div>
			<div class="text-right top-buffer-sm">
				<a class="btn btn-default btn-md" href="<?= HTML::blog_url(UOJContext::userid(), '/slide/'.$blog['id'].'?print-pdf') ?>"><span class="glyphicon glyphicon-print"></span> 打印</a>
				<a class="btn btn-default btn-md" href="<?= HTML::blog_url(UOJContext::userid(), '/slide/'.$blog['id']) ?>"><span class="glyphicon glyphicon-fullscreen"></span> 全屏</a>
			</div>
		</article>
		<?php endif ?>
	</div>
	<div class="panel-footer text-right">
		<ul class="list-inline bot-buffer-no">
			<li>
			<?php foreach (queryBlogTags($blog['id']) as $tag): ?>
				<?php echoBlogTag($tag) ?>
			<?php endforeach ?>
			</li>
			<?php if ($is_preview): ?>
  			<li><a href="<?= HTML::blog_url($blog['poster'], '/blog/'.$blog['id']) ?>">阅读全文</a></li>
  			<?php endif ?>
  			<li><a href="<?= HTML::blog_url($blog['poster'], '/blog/'.$blog['id'].'/print') ?>">打印</a></li>
  			<?php if (Auth::check() && (isSuperUser(Auth::user()) || Auth::id() == $blog['poster'])): ?>
			<li><a href="<?=HTML::blog_url($blog['poster'], '/'.$blog_type.'/'.$blog['id'].'/write')?>">修改</a></li>
			<li><a href="<?=HTML::blog_url($blog['poster'], '/blog/'.$blog['id'].'/delete')?>">删除</a></li>
			<?php endif ?>
  			<li><?= getClickZanBlock('B', $blog['id'], $blog['zan']) ?></li>
		</ul>
	</div>
</div>
<?php endif ?>