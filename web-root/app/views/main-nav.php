<div class="navbar navbar-default" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="<?= HTML::url('/') ?>">HOJ</a>
		</div>
		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li><a href="/contests"><?= UOJLocale::get('contests') ?></a></li>
				<li><a href="/exercises">练习</a></li>
				<?php if(Auth::id()):?>
					<?php if(!isContestUser(Auth::user())):?>
					<li><a href="/ladder">天梯</a></li>
					<li><a href="/problems"><?= UOJLocale::get('problems') ?></a></li>
					<li><a href="/submissions"><?= UOJLocale::get('submissions') ?></a></li>
					<li><a href="/hacks"><?= UOJLocale::get('hacks') ?></a></li>
					<?php endif ?>
				<li><a target="_blank" href="/ide">在线IDE</a></li>
				<?php endif?>
				<li><a href="/blogs"><?= UOJLocale::get('blogs') ?></a></li>
				<li><a href="/faq"><?= UOJLocale::get('help') ?></a></li>
				<?php if(Auth::check()):?>
				<li><a href="/server">评测机</a></li>
				<?php endif?>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</div>