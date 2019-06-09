<?php
	$content_p = strpos($content, "\n");
	$theme = substr($content, 0, $content_p);
	$slide_content = substr($content, $content_p + 1);
	
	if (!in_array($theme,array('beige','black','blood','league','moon','night','serif','simple','sky','solarized','white'))) {
		$theme = 'moon';
	}
?>
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">

		<title><?= isset($PageTitle) ? $PageTitle : UOJConfig::$data['profile']['oj-name-short'] ?> - <?= isset($PageMainTitle) ? $PageMainTitle : UOJConfig::$data['profile']['oj-name'] ?></title>

		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui">

		<?= HTML::css_link('/min/css?rq=%7B%22reveal%22%3A%22%22%7D') ?>
		<link rel="stylesheet" type="text/css" href="<?= HTML::url('/min/css?rq=%7B%22reveal-theme%22%3A%22'.$theme.'%22%7D') ?>" id="theme">

		<!-- Printing and PDF exports -->
		<script>
			var link = document.createElement('link');
			link.rel = 'stylesheet';
			link.type = 'text/css';
			link.href = window.location.search.match(/print-pdf/gi) ? '<?= HTML::url('/min/css?rq=%7B%22reveal-print%22%3A%22pdf%22%7D') ?>' : '<?= HTML::url('/min/css?rq=%7B%22reveal-print%22%3A%22paper%22%7D') ?>';
			document.getElementsByTagName('head')[0].appendChild(link);
			
			if (window.location.search.match(/print-pdf/gi)) {
				window.onload = function() {
					var print_button = document.createElement('button');
					print_button.id = 'print_button';
					print_button.innerHTML = '打印';
					print_button.onclick = function() {
						window.print();
					};
					document.getElementsByTagName('body')[0].appendChild(print_button);
				}
			}
		</script>
		<style>
			#print_button {
				display: inline-block;
				margin-bottom: 0;
				font-weight: 400;
				text-align: center;
				vertical-align: middle;
				-ms-touch-action: manipulation;
				touch-action: manipulation;
				cursor: pointer;
				background-image: none;
				border: none;
				box-shadow: 0 0 8px 4px rgba(0,0,0,.035);
				white-space: nowrap;
				-webkit-user-select: none;
				-moz-user-select: none;
				-ms-user-select: none;
				user-select: none;
				color: #060606;
				border-color: #aeaeae;
				background-color: #fff;
				padding: 10px 16px;
				font-size: 18px;
				line-height: 1.33;
				border-radius: 6px;
				position: fixed;
				top: 10%;
				right: 20%;
				z-index:2333;
			}
			@media print {
				#print_button {
					display: none;
				}
			}
		</style>

		<!--[if lt IE 9]>
			<script src="<?= HTML::url('/js/reveal/html5shiv.js') ?>"></script>
		<![endif]-->
	</head>
	<body>
		<div class="reveal">
			<div class="slides"><?= $slide_content ?></div>
		</div>

		<script src="<?= HTML::url('/min/js?rq=%7B%22reveal%22%3A%22%22%7D') ?>"></script>
	</body>
</html>