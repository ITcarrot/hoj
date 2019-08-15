<?php
	//顶部通知
	$top_msg="";
	
	$new_user_msg_num = DB::selectCount("select count(*) from user_msg where receiver = '".Auth::id()."' and read_time is null");
	$new_system_msg_num = DB::selectCount("select count(*) from user_system_msg where receiver = '".Auth::id()."' and read_time is null");
	if(isSuperUser(Auth::user()))
		$solution_request = DB::selectCount("select count(*) from solutions where status = 0");
	else
		$solution_request = 0;
	$new_msg_tot = $new_user_msg_num + $new_system_msg_num + $solution_request;
		
	if ($new_user_msg_num == 0) {
		$new_user_msg_num_html = '';
	} else {
		$new_user_msg_num_html = '<span class="badge">'.$new_user_msg_num.'</span>';
	}
	if ($new_system_msg_num == 0) {
		$new_system_msg_num_html = '';
	} else {
		$new_system_msg_num_html = '<span class="badge">'.$new_system_msg_num.'</span>';
	}
	if ($solution_request == 0) {
		$solution_request_html = '';
	} else {
		$solution_request_html = '<span class="badge">'.$solution_request.'</span>';
	}
	if ($new_msg_tot == 0) {
		$new_msg_tot_html = '';
	} else {
		$new_msg_tot_html = '<sup><span class="badge">'.$new_msg_tot.'</span></sup>';
	}
	
	if (!isset($PageMainTitle)) {
		$PageMainTitle = UOJConfig::$data['profile']['oj-name'];
	}
	if (!isset($ShowPageHeader)) {
		$ShowPageHeader = true;
	}
	if(Auth::check()){
		$request_page=explode('?',$_SERVER['REQUEST_URI'])[0];
		if($_SESSION['last_request']!=$request_page){
			$_SESSION['last_request']=$request_page;
			$str="too young too simple, sometimes naive!";
			$log_id=mt_rand(0,37);
			$access=fopen("/var/www/uoj/app/storage/access_log/$log_id.log","a");
			if(flock($access,LOCK_EX)){
				fwrite($access,UOJTime::$time_now_str."|".Auth::id()."|".$_SERVER['REMOTE_ADDR'].'|'.$request_page."\n");
				flock($access,LOCK_UN);
			}
			fclose($access);
			$cnt=fopen("/var/www/uoj/app/storage/access_log/$log_id.cnt","a");
			if(flock($cnt,LOCK_EX)){
				fwrite($cnt,$str[$log_id]);
				flock($cnt,LOCK_UN);
			}
			fclose($cnt);
		}
	}
?>
<!DOCTYPE html>
<html lang="<?= UOJLocale::locale() ?>">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="referrer" content="no-referrer" />
		<?php if (isset($_GET['locale'])): ?>
			<meta name="robots" content="noindex, nofollow" />
		<?php endif ?>
		<title><?= isset($PageTitle) ? $PageTitle : UOJConfig::$data['profile']['oj-name-short'] ?> - <?= $PageMainTitle ?></title>
		
		<!-- UOJ ico -->
		<link rel="shortcut icon" href="<?= HTML::url('/pictures/UOJ.ico') ?>" />
		
		<!-- HOJ main css -->
		<link rel="stylesheet" href="/min/css?v=190407" />
		<?php if (is_array($REQUIRE_LIB)):?>
		<!-- HOJ extra css -->
		<link rel="stylesheet" href="/min/css?v=190212&rq=<?=urlencode(json_encode($REQUIRE_LIB))?>" />
		<?php endif ?>
		
		<!-- HOJ main js -->
		<script src="/min/js?v=190814"></script>
		<!-- time -->
		<script>begin=new Date("<?=preg_replace('/-/','/',UOJTime::$time_now_str)?>").getTime();</script>
		<?php if (is_array($REQUIRE_LIB)):?>
		<!-- HOJ extra js -->
		<script src="/min/js?v=190805&rq=<?=urlencode(json_encode($REQUIRE_LIB))?>"></script>
		<?php endif ?>
		
		
		<?php if (isset($REQUIRE_LIB['mathjax'])): ?>
		<!-- MathJax -->
		<script type="text/x-mathjax-config">
			MathJax.Hub.Config({
				showProcessingMessages: false,
				tex2jax: {
					inlineMath: [["$", "$"], ["\\\\(", "\\\\)"]],
					processEscapes:true
				},
				menuSettings: {
					zoom: "Hover"
    			}
			});
		</script>
		<script src="/js/MathJax-2.7.5/MathJax.js?config=TeX-MML-AM_CHTML"></script>
		<?php endif ?>
		
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
		<!--night mode-->
		<link type="text/css" rel="stylesheet" href="<?=isset($_COOKIE['night-mode'])?'/min/css?v=190325&rq=%7B%22night-theme%22%3A%22%22%7D':''?>" id="night-mode-css"/>
		
		<!--prefetch DefSans.otf for poem-->
		<link rel="prefetch" href="/fonts/DefSans.otf">
	</head>
	<body role="document">
		<div class="container theme-showcase" role="main">
			<?php if ($ShowPageHeader): ?>
				<?php if(isset($GLOBALS['myUser']['setting']['live2d'])) : ?>
					<div class="waifu">
						<div class="waifu-tips"></div>
						<canvas id="live2d" width="200" height="250" class="live2d"></canvas>
						<div class="waifu-tool">
							<span class="fui-home"></span>
							<span class="fui-chat"></span>
							<span class="fui-eye"></span>
							<span class="fui-user"></span>
							<span class="fui-photo"></span>
							<span class="fui-info-circle"></span>
							<span class="fui-cross"></span>
						</div>
					</div>
					<script src="/js/waifu/waifu-tips.js?v=190106"></script>
					<script src="/js/waifu/live2d.js"></script>
					<script>initModel("/js/waifu/")</script>
					<link rel="stylesheet" href="/css/waifu.css?v=190106" />
				<?php endif ?>
				<?php if($top_msg!=''): ?>
					<div class="bg-info text-center" style="border-radius: 50px;font-size: medium;padding: 5px;margin: 0 10px;"><?=$top_msg?></div>
				<?php endif ?>
				<div class="row">
					<ul class="nav nav-pills pull-right" role="tablist">
						<li class="hidden-xs" role="presentation">
							<span style="display: block; width: 145px; text-align: center; height: 50px; padding-top: 15px;">离 NOIP 2019 还有</span>
						</li>	
						<li class="hidden-xs"><canvas id="timecanvas" height="40px" width="200px" style="padding-left: 10px;padding-top: 5px;"></canvas></li>
						<li><div id="clock">
							<p class="date" id="clock-date"></p>
							<p class="time" id="clock-time"></p>
						</div></li>
						<?php if (Auth::check()): ?>
							<li class="dropdown">
								<a href="#" data-toggle="dropdown" style="display:block;height:50px;padding-top:15px;">
									<span class="uoj-username" data-rating="<?= Auth::user()['rating'] ?>" data-link="0"><?= Auth::id() ?></span> <?= $new_msg_tot_html ?>
								</a>
								<ul class="dropdown-menu" role="menu">
									<li role="presentation"><a href="<?= HTML::url('/user/profile/' . Auth::id()) ?>"><?= UOJLocale::get('my profile') ?></a></li>
									<li role="presentation"><a href="<?= HTML::url('/blogof/' . Auth::id()) ?>">个人博客</a></li>
									<li role="presentation"><a onclick="" target="_blank" href="<?= HTML::url('/user/msg') ?>"><?= UOJLocale::get('private message') ?>&nbsp;&nbsp;<?= $new_user_msg_num_html ?></a></li>
									<li role="presentation"><a onclick="" target="_blank" href="<?= HTML::url('/user/system-msg') ?>"><?= UOJLocale::get('system message') ?>&nbsp;&nbsp;<?= $new_system_msg_num_html ?></a></li>
									<li role="presentation"><a onclick="" target="_blank" href="<?= HTML::url('/solution/manage') ?>">题解管理&nbsp;&nbsp;<?= $solution_request_html ?></a></li>
									<?php if (isSuperUser(Auth::user())): ?>
										<li role="presentation"><a target="_blank" href="<?= HTML::url('/user/upload') ?>">个人上传文件</a></li>
										<li role="presentation"><a href="<?= HTML::url('/super-manage') ?>"><?= UOJLocale::get('system manage') ?></a></li>
										<li role="presentation"><a target="_blank" href="<?= HTML::url('/upload') ?>">上传文件</a></li>
									<?php else : ?>
										<li role="presentation"><a target="_blank" href="<?= HTML::url('/user/upload') ?>">上传文件</a></li>
									<?php endif ?>
									<li role="presentation"><a href="<?= HTML::url('/logout?_token='.crsf_token()) ?>" onclick="return confirm('确认登出吗');" ><?= UOJLocale::get('logout') ?></a></li>
									<li role="presentation"><a href="<?= HTML::url('/logout?all=1&_token='.crsf_token()) ?>" onclick="return confirm('确认让所有设备登出吗');" ><?= '让所有设备'.UOJLocale::get('logout') ?></a></li>
								</ul>
							</li>
						<?php else: ?>
							<li role="presentation"><a style="display:block;height:50px;padding-top:15px;" class="loginbtn" href="<?= HTML::url('/login?from='.$_SERVER['REQUEST_URI']) ?>"><?= UOJLocale::get('login') ?></a></li>
							<li role="presentation"><a style="display:block;height:50px;padding-top:15px;" class="signupbtn" href="<?= HTML::url('/register') ?>"><?= UOJLocale::get('register') ?></a></li>
						<?php endif ?>
					</ul>
					<h1 class="hidden-xs" style="margin-top:5px"><img src="<?= HTML::url('/pictures/UOJ_small.png') ?>" alt="HOJ Logo" class="img-rounded" style="width:39px; height:39px;" /> <?= $PageMainTitle ?></h1>
				</div>
				<?php uojIncludeView($PageNav) ?>
			<?php endif ?>
			<div class="uoj-content">