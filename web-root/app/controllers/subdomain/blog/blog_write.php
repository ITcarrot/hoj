<?php
	requirePHPLib('form');
	
	if (!UOJContext::hasBlogPermission()) {
		become403Page();
	}
	if(isContestUser(Auth::user())){
		becomeMsgPage('该功能不对比赛账户开放');
	}
	if (isset($_GET['id'])) {
		if (!validateUInt($_GET['id']) || !($blog = queryBlog($_GET['id'])) || !UOJContext::isHisBlog($blog)) {
			become404Page();
		}
	} else {
		$blog = DB::selectFirst("select * from blogs where poster = '".UOJContext::user()['username']."' and type = 'B' and is_draft = true");
	}
	
	$blog_editor = new UOJBlogEditor();
	$blog_editor->name = 'blog';
	if ($blog) {
		$blog_editor->cur_data = array(
			'title' => $blog['title'],
			'content_md' => $blog['content_md'],
			'content' => $blog['content'],
			'tags' => queryBlogTags($blog['id']),
			'is_hidden' => $blog['is_hidden']
		);
	} else {
		$blog_editor->cur_data = array(
			'title' => '新博客',
			'content_md' => '',
			'content' => '',
			'tags' => array(),
			'is_hidden' => true
		);
	}
	if ($blog && !$blog['is_draft']) {
		$blog_editor->blog_url = "/blog/{$blog['id']}";
	} else {
		$blog_editor->blog_url = null;
	}
	
	function updateBlog($id, $data) {
		DB::update("update blogs set title = '".DB::escape($data['title'])."', content = '".DB::escape($data['content'])."', content_md = '".DB::escape($data['content_md'])."', is_hidden = {$data['is_hidden']}, post_time=now() where id = {$id}");
	}
	function insertBlog($data) {
		DB::insert("insert into blogs (title, content, content_md, poster, is_hidden, is_draft, post_time) values ('".DB::escape($data['title'])."', '".DB::escape($data['content'])."', '".DB::escape($data['content_md'])."', '".Auth::id()."', {$data['is_hidden']}, {$data['is_draft']}, now())");
	}
	
	$blog_editor->save = function($data) {
		global $blog;
		$ret = array();
		if ($blog) {
			if ($blog['is_draft']) {
				if ($data['is_hidden']) {
					updateBlog($blog['id'], $data);
				} else {
					deleteBlog($blog['id']);
					insertBlog(array_merge($data, array('is_draft' => 0)));
					$blog = array('id' => DB::insert_id(), 'tags' => array());
					$ret['blog_write_url'] = HTML::blog_url(Auth::id(),"/blog/{$blog['id']}/write");
					$ret['blog_url'] = "/blog/{$blog['id']}";
				}
			} else {
				updateBlog($blog['id'], $data);
			}
		} else {
			if ($data['is_hidden']) {
				insertBlog(array_merge($data, array('is_draft' => 1)));
				$blog = array('id' => DB::insert_id(), 'tags' => array());
			} else {
				insertBlog(array_merge($data, array('is_draft' => 0)));
				$blog = array('id' => DB::insert_id(), 'tags' => array());
				$ret['blog_write_url'] = HTML::blog_url(Auth::id(),"/blog/{$blog['id']}/write");
				$ret['blog_url'] = "/blog/{$blog['id']}";
			}
		}
		if ($data['tags'] !== $blog['tags']) {
			DB::delete("delete from blogs_tags where blog_id = {$blog['id']}");
			foreach ($data['tags'] as $tag) {
				DB::insert("insert into blogs_tags (blog_id, tag) values ({$blog['id']}, '".DB::escape($tag)."')");
			}
		}
		return $ret;
	};
	
	$blog_editor->runAtServer();
?>
<?php echoUOJPageHeader('写博客') ?>
<div class="text-right">
	<ul class="nav nav-tabs" role="tablist">
		<?php if($blog): ?>
			<li><a href="/solution/new?blog=<?= $blog['id'] ?>">添加到题解</a></li>
		<?php endif ?>
		<li><a id="button-display-hack" style="cursor: pointer;" role="tab">公式编辑器</a></li>
		<div id="div-form-hack" style="display:none" class="">
			<iframe src="http://latex.codecogs.com/eqneditor/editor.php" height="600px" width="100%" frameborder="no" scrolling="no" marginwidth=0 marginheight=0 hspace="0" vspace="-150"></iframe>
		</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#button-display-hack').click(function() {
					$('#div-form-hack').toggle('fast');
				});
			});
		</script>
		<a href="/faq">这玩意儿怎么用？</a>
		<strong style="display:block">提示：在适当地方添加单独一行<code>&lt;!-- readmore --&gt;</code>可以使其在日志列表中的预览显示至此行</strong>
	</ul>
</div>
<?php $blog_editor->printHTML() ?>
<?php echoUOJPageFooter() ?>