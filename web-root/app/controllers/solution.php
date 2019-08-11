<?php
	requirePHPLib('form');
	
	if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	if(!Auth::check()) {
		become403Page();
	}
	if (!isProblemVisibleToUser($problem, $myUser)) {
		become403Page();
	}
	
	$pag_config = array('page_len' => 1);
	$pag_config['col_names'] = array('*');
	$pag_config['table_name'] = 'blogs';
	$pag_config['cond'] = "is_hidden = 0 and id in (select blog_id from solutions where problem_id = {$problem['id']})";
	$pag_config['tail'] = 'order by zan desc';
	$pag = new Paginator($pag_config);
	foreach ($pag->get() as $idx => $row) {
		$blog = $row;
	}
	
	if(!$pag -> isEmpty()) {
		$comment_form = new UOJForm('comment');
		$comment_form->addVTextArea('comment', '内容', '',
			function($comment) {
				global $myUser;
				if ($myUser == null) {
					return '请先登录';
				}
				if (!$comment) {
					return '评论不能为空';
				}
				if (strlen($comment) > 1000) {
					return '不能超过1000个字节';
				}
				return '';
			},
			null
		);
		$comment_form->handle = function() {
			global $myUser, $blog, $comment_form;
			$comment = HTML::escape($_POST['comment']);
			
			list($comment, $referrers) = uojHandleAtSign($comment, "/blog/{$blog['id']}");
			
			$esc_comment = DB::escape($comment);
			DB::insert("insert into blogs_comments (poster, blog_id, content, reply_id, post_time, zan) values ('{$myUser['username']}', '{$blog['id']}', '$esc_comment', 0, now(), 0)");
			$comment_id = DB::insert_id();
			
			$rank = DB::selectCount("select count(*) from blogs_comments where blog_id = {$blog['id']} and reply_id = 0 and id < {$comment_id}");
			$page = floor($rank / 20) + 1;
			
			$uri = "/blog/{$blog['id']}?page=$page#comment-$comment_id";
			
			foreach ($referrers as $referrer) {
				$content = '有人在博客 ' . $blog['title'] . ' 的评论里提到你：<a href="' . $uri . '">点击此处查看</a>';
				sendSystemMsg($referrer, '有人提到你', $content);
			}
			
			if ($blog['poster'] !== $myUser['username']) {
				$content = '有人回复了您的博客 ' . $blog['title'] . ' ：<a href="' . $uri . '">点击此处查看</a>';
				sendSystemMsg($blog['poster'], '博客新回复通知', $content);
			}
		};
		$comment_form->ctrl_enter_submit = true;
		
		$comment_form->runAtServer();
		
		$reply_form = new UOJForm('reply');
		$reply_form->addHidden('reply_id', '0',
			function($reply_id, &$vdata) {
				global $blog;
				if (!validateUInt($reply_id) || $reply_id == 0) {
					return '您要回复的对象不存在';
				}
				$comment = queryBlogComment($reply_id);
				if (!$comment || $comment['blog_id'] != $blog['id']) {
					return '您要回复的对象不存在';
				}
				$vdata['parent'] = $comment;
				return '';
			},
			null
		);
		$reply_form->addVTextArea('reply_comment', '', '',
			function($comment) {
				global $myUser;
				if ($myUser == null) {
					return '请先登录';
				}
				if (!$comment) {
					return '评论不能为空';
				}
				if (strlen($comment) > 140) {
					return '不能超过140个字节';
				}
				return '';
			},
			null
		);
		$reply_form->handle = function(&$vdata) {
			global $myUser, $blog, $reply_form;
			$comment = HTML::escape($_POST['reply_comment']);
			
			list($comment, $referrers) = uojHandleAtSign($comment, "/blog/{$blog['id']}");
			
			$reply_id = $_POST['reply_id'];
			
			$esc_comment = DB::escape($comment);
			DB::insert("insert into blogs_comments (poster, blog_id, content, reply_id, post_time, zan) values ('{$myUser['username']}', '{$blog['id']}', '$esc_comment', $reply_id, now(), 0)");
			$comment_id = DB::insert_id();
			
			$rank = DB::selectCount("select count(*) from blogs_comments where blog_id = {$blog['id']} and reply_id = 0 and id < {$reply_id}");
			$page = floor($rank / 20) + 1;
			
			$uri = "/blog/{$blog['id']}?page=$page#comment-$reply_id";
			
			foreach ($referrers as $referrer) {
				$content = '有人在博客 ' . $blog['title'] . ' 的评论里提到你：<a href="' . $uri . '">点击此处查看</a>';
				sendSystemMsg($referrer, '有人提到你', $content);
			}
			
			$parent = $vdata['parent'];
			$notified = array();
			if ($parent['poster'] !== $myUser['username']) {
				$notified[] = $parent['poster'];
				$content = '有人回复了您在博客 ' . $blog['title'] . ' 下的评论 ：<a href="' . $uri . '">点击此处查看</a>';
				sendSystemMsg($parent['poster'], '评论新回复通知', $content);
			}
			if ($blog['poster'] !== $myUser['username'] && !in_array($blog['poster'], $notified)) {
				$notified[] = $blog['poster'];
				$content = '有人回复了您的博客 ' . $blog['title'] . ' ：<a href="' . $uri . '">点击此处查看</a>';
				sendSystemMsg($blog['poster'], '博客新回复通知', $content);
			}
		};
		$reply_form->ctrl_enter_submit = true;
		
		$reply_form->runAtServer();
		
		$comments_pag = new Paginator(array(
			'col_names' => array('*'),
			'table_name' => 'blogs_comments',
			'cond' => 'blog_id = ' . $blog['id'] . ' and reply_id = 0',
			'tail' => 'order by id asc',
			'echo_full' => ''
		));
	}
?>
<?php
	$REQUIRE_LIB['mathjax'] = '';
	$REQUIRE_LIB['shjs'] = '';
?>

<?php echoUOJPageHeader(HTML::stripTags($problem['title']) . ' - 题解') ?>
<a class="btn btn-default pull-left" href="/problem/<?= $problem['id'] ?>">返回题目</a>
<a class="btn btn-default pull-right" href="/solution/new?problem=<?= $problem['id'] ?>">添加题解</a>
<h1 class="page-header text-center"><?= $problem['title'] ?> 题解</h1>

<?php if($pag -> isEmpty()): ?>
	<div class="text-center">
		<h2>还没有人发布题解 &gt;_&lt;</h2>
		<p>不如自己发布一篇？</p>
	</div>
<?php else: ?>
	<?= $pag -> pagination() ?>
	<hr/>
	<?php echoBlog($blog) ?>
	<hr/>
	<?= $pag -> pagination() ?>
	<hr/>
	
	<h2>评论 <span class="glyphicon glyphicon-comment"></span></h2>
	<div class="list-group">
	<?php if ($comments_pag->isEmpty()): ?>
		<div class="list-group-item text-muted">暂无评论</div>
	<?php else: ?>
		<?php foreach ($comments_pag->get() as $comment):
			$poster = queryUser($comment['poster']);
			$esc_email = HTML::escape($poster['email']);
			$asrc = HTML::escape(json_decode($poster['setting'], true)['avatar']);
			
			$replies = DB::selectAll("select id, poster, content, post_time from blogs_comments where reply_id = {$comment['id']} order by id");
			foreach ($replies as $idx => $reply) {
				$replyer=queryUser($reply['poster']);
				$replies[$idx]['poster_rating'] = $replyer['rating'];
				$replies[$idx]['poster_email'] = $replyer['email'];
				$replies[$idx]['poster_super'] = $replyer['usergroup'] == 'S' ? 1 : 0;
			}
			$replies_json = json_encode($replies);
		?>

		<div id="comment-<?= $comment['id'] ?>" class="list-group-item">
			<div class="media">
				<div class="media-left comtposterbox">
					<div class="hidden-xs">
						<img class="media-object img-rounded" style="width:80px;max-height:100px;vertical-align:middle;" src="<?= $asrc ?>" alt="图片失效" />
					</div>
				</div>
				<div id="comment-body-<?= $comment['id'] ?>" class="media-body comtbox">
					<div class="row">
						<div class="col-sm-6"><?= getUserLink($poster['username']) ?></div>
						<div class="col-sm-6 text-right"><?= getClickZanBlock('BC', $comment['id'], $comment['zan']) ?></div>
					</div>
					<div class="comtbox1"><?= $comment['content'] ?></div>
					<ul class="text-right list-inline bot-buffer-no"><li><small class="text-muted"><?= $comment['post_time'] ?></small></li><li><a id="reply-to-<?= $comment['id'] ?>" href="#">回复</a></li></ul>
					<?php if ($replies): ?>
						<div id="replies-<?= $comment['id'] ?>" class="comtbox5"></div>
					<?php endif ?>
					<script type="text/javascript">showCommentReplies('<?= $comment['id'] ?>', <?= $replies_json ?>);</script>
				</div>
			</div>
		</div>
		<?php endforeach ?>
	<?php endif ?>
	</div>

	<h3>发表评论</h3>
	<p>可以用@mike来提到mike这个用户，mike会被高亮显示。如果你真的想打“@”这个字符，请用“@@”。</p>
	<?php $comment_form->printHTML() ?>
	<div id="div-form-reply" style="display:none">
		<?php $reply_form->printHTML() ?>
	</div>
	
	<hr/>
	<?= $pag -> pagination() ?>
	
	<?php $setting = json_decode(queryUser($blog['poster'])['setting'], true); ?>
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
		$(".list-group-item").addClass("bg-opacity-3");
		$("pre").addClass("bg-opacity-3");
		$(document.body).css({
			'background':"url(<?= HTML::escape($setting['background']) ?>)",
			'background-position':'center',
			'background-size':'cover',
			'background-attachment':'fixed'
		});
	</script>
	<?php endif ?>
<?php endif ?>

<?php echoUOJPageFooter() ?>