<?php
	$path = $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/minify/';
	require_once $path . 'Minify.php';
	require_once $path . 'CSS.php';
	require_once $path . 'JS.php';
	require_once $path . 'Exception.php';
	require_once $path . 'Exceptions/BasicException.php';
	require_once $path . 'Exceptions/FileImportException.php';
	require_once $path . 'Exceptions/IOException.php';
	require_once $path . 'ConverterInterface.php';
	require_once $path . 'Converter.php';
	use MatthiasMullie\Minify;
	$path=$_SERVER['DOCUMENT_ROOT'].'/';
	$REQUIRE_LIB=json_decode($_GET['rq'],1);
	header('Pragma: ',1);
	header('Expires:',1);
	header('Last-Modified: Sat, 15 Dec 2018 06:55:58 GMT',1);
	header('Cache-Control: max-age=345600',1);
	if($_GET['tab']=='js'){
		$js=new Minify\JS('');
		header('Content-Type: application/javascript');
		if(!$REQUIRE_LIB){
			$js->add("uojHome = '".HTML::url('/')."'");
			//jQuery (necessary for Bootstrap\'s JavaScript plugins)
			$js->add($path.'/js/jquery.min.js');
			//jQuery autosize
			$js->add($path.'/js/jquery.autosize.min.js');
			$js->add(<<<EOD
$(document).ready(function() {
	$('textarea').autosize();
});
EOD
			);
			//jQuery cookie
			$js->add($path.'/js/jquery.cookie.min.js');
			//jQuery modal
			$js->add($path.'/js/jquery.modal.js');
			//Include all compiled plugins (below), or include individual files as needed
			$js->add($path.'/js/bootstrap.min.js');
			//Bootstrap switch
			$js->add($path.'/js/bootstrap-switch.min.js');
			//Color converter
			$js->add($path.'/js/color-converter.min.js');
			//uoj
			$js->add($path.'/js/uoj.js');
			$js->add(<<<EOD
before_window_unload_message = null;
$(window).on('beforeunload', function() {
	if (before_window_unload_message !== null) {
	    return before_window_unload_message;
	}
});
EOD
			);
			//time
			$js->add('var begin=new Date().getTime();');
			$js->add('var localbegin=new Date().getTime();');
			$js->add($path.'/js/countdown.js');
			$js->add(<<<EOD
$(document).ready(function(){
	var canvas=document.getElementById("timecanvas");
	if (canvas == undefined)
		return;
	var context=canvas.getContext("2d");
	context.scale(0.2,0.2);
	setInterval("draw();",250);
});
EOD
			);
			$js->add(<<<EOD
var week = ['星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
function updateTime() {
	var local=new Date().getTime();
	var cd = new Date(local - localbegin + begin);
	$('#clock-time').html(zeroPadding(cd.getHours(), 2) + ':' + zeroPadding(cd.getMinutes(), 2) + ':' + zeroPadding(cd.getSeconds(), 2));
	$('#clock-date').html(zeroPadding(cd.getFullYear(), 4) + '-' + zeroPadding(cd.getMonth()+1, 2) + '-' + zeroPadding(cd.getDate(), 2) + ' ' + week[cd.getDay()]);
};
function zeroPadding(num, digit) {
	var zero = '';
	for(var i = 0; i < digit; i++) {
		zero += '0';
	}
	return (zero + num).slice(-digit);
};
$(document).ready(function(){
	if ($('#clock-time').length > 0 && $('#clock-date').length > 0)
		setInterval(updateTime, 250);
});
EOD
			);
			//copy
			$js->add($path.'/js/clipboard.min.js');
			$js->add(<<<EOD
$(document).ready(function(){
	var clipboard = new Clipboard('#copy_btn');
	clipboard.on('success', function(e) {
		e.clearSelection();
		var tmp=$(e.trigger).attr("data-clipboard-target");
		var btnid=".for"+tmp.substr(1);
		$(btnid).addClass("btn-success");
		$(btnid).text("复制成功");
		setTimeout(function(){
			$(btnid).removeClass("btn-success");
			$(btnid).text("复制");
		}, 600);
	});
});
EOD
			);
			//标题切换
			$js->add(<<<EOD
var OriginTitile = document.title;    
var titleTime;
document.addEventListener('visibilitychange', function(){
    if (document.hidden){
		document.title ='你不要我了么？qwq - Horse OJ';
	titleTime = setTimeout(function() {
            document.title = OriginTitile;
        }, 1000);
    }else{
         document.title = '你终于回来啦~'; 
        titleTime = setTimeout(function() {
            document.title = OriginTitile;
        }, 1000); 
    }
});
EOD
			);
			//夜间模式开关
			$js->add(<<<EOD
$(document).ready(function(){
	$('#input-night-mode').bootstrapSwitch({
		onText: "已开启",
		onColor: 'primary',
		offText: "已关闭",
		offColor: 'primary',
		labelText: "夜间模式",
		handleWidth: 100
	});
	$('#input-night-mode').on('switchChange.bootstrapSwitch', function(e, state) {
		if(state){
			$.cookie('night-mode', '', {path: '/'});
			$("#night-mode-css").attr("href","/min/css?v=190325&rq=%7B%22night-theme%22%3A%22%22%7D");
			$('#ChineseFlag').css('top','80px');
			$('#ChineseFlag').css('opacity','0');
		}else{
			$.removeCookie('night-mode', {path: '/'});
			$("#night-mode-css").attr("href","");
			$('#ChineseFlag').css('top','0');
			$('#ChineseFlag').css('opacity','1');
		}
	});
});
EOD
			);
		}else{
			//jQuery tag canvas
			if (isset($REQUIRE_LIB['tagcanvas'])){
				$js->add($path.'/js/jquery.tagcanvas.min.js');
			}
			//codemirror
			if (isset($REQUIRE_LIB['blog-editor']) || isset($REQUIRE_LIB['slide-editor']) || isset($REQUIRE_LIB['code-editor']) || isset($REQUIRE_LIB['text-editor'])){
				$js->add($path.'/js/codemirror/codemirror.js');
				$js->add($path.'/js/codemirror/addon/active-line.js');
				$js->add($path.'/js/codemirror/addon/fullscreen.js');
			}
			//UOJ blog editor
			if (isset($REQUIRE_LIB['blog-editor'])){
				$REQUIRE_LIB['jquery.hotkeys'] = '';
				$js->add($path.'/js/marked.js');
				$js->add($path.'/js/blog-editor.js');
				$js->add($path.'/js/codemirror/mode/xml.js');
				$js->add($path.'/js/codemirror/mode/markdown.js');
				$js->add($path.'/js/codemirror/addon/fold/brace-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/comment-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/indent-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/markdown-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/xml-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/foldcode.js');
				$js->add($path.'/js/codemirror/addon/fold/foldgutter.js');
				$js->add($path.'/js/codemirror/addon/edit/matchtags.js');
				$js->add($path.'/js/codemirror/addon/edit/closetag.js');
				$js->add($path.'/js/codemirror/addon/edit/continuelist.js');
				$js->add($path.'/js/codemirror/addon/comment/comment.js');
			}
			//code editor
			if (isset($REQUIRE_LIB['code-editor']) || isset($REQUIRE_LIB['text-editor'])){
				$js->add($path.'/js/code-editor.js');
			}
			if (isset($REQUIRE_LIB['code-editor'])){
				$js->add($path.'/js/codemirror/mode/clike.js');
				$js->add($path.'/js/codemirror/mode/pascal.js');
				$js->add($path.'/js/codemirror/mode/python.js');
				$js->add($path.'/js/codemirror/addon/edit/matchbrackets.js');
				$js->add($path.'/js/codemirror/addon/edit/closebrackets.js');
				$js->add($path.'/js/codemirror/addon/fold/brace-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/comment-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/indent-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/markdown-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/xml-fold.js');
				$js->add($path.'/js/codemirror/addon/fold/foldcode.js');
				$js->add($path.'/js/codemirror/addon/fold/foldgutter.js');
				$js->add($path.'/js/codemirror/addon/comment/comment.js');
				$js->add($path.'/js/codemirror/addon/comment/continuecomment.js');
			}
			//MD5
			if (isset($REQUIRE_LIB['md5'])){
				$js->add($path.'/js/md5.min.js');
			}
			//Bootstrap dialog
			if (isset($REQUIRE_LIB['dialog'])){
				$js->add($path.'/js/bootstrap-dialog.min.js');
			}
			//jquery form
			if (isset($REQUIRE_LIB['jquery.form'])){
				$js->add($path.'/js/jquery.form.min.js');
			}
			//jquery hotkeys
			if (isset($REQUIRE_LIB['jquery.hotkeys'])){
				$js->add($path.'/js/jquery.hotkeys.js');
			}
			//flot
			if (isset($REQUIRE_LIB['flot'])){
				$js->add($path.'/js/flot/jquery.canvaswrapper.js');
				$js->add($path.'/js/flot/jquery.colorhelpers.js');
				$js->add($path.'/js/flot/jquery.flot.js');
				$js->add($path.'/js/flot/jquery.flot.browser.js');
				$js->add($path.'/js/flot/jquery.flot.drawSeries.js');
				$js->add($path.'/js/flot/jquery.flot.hover.js');
				$js->add($path.'/js/flot/jquery.flot.resize.js');
				$js->add($path.'/js/flot/jquery.flot.saturated.js');
				$js->add($path.'/js/flot/jquery.flot.time.js');
				$js->add($path.'/js/flot/jquery.flot.uiConstants.js');
			}
			//morris
			if (isset($REQUIRE_LIB['morris'])){
				$js->add($path.'/js/morris.min.js');
				$REQUIRE_LIB['raphael'] = "";
			}
			//raphael
			if (isset($REQUIRE_LIB['raphael'])){
				$js->add($path.'/js/raphael.min.js');
			}
			//shjs
			if (isset($REQUIRE_LIB['shjs'])){
				$js->add($path.'/js/sh_main.min.js');
				$js->add('$(document).ready(function(){sh_highlightDocument()})');
			}
			//ckeditor
			if (isset($REQUIRE_LIB['ckeditor'])){
				$js->add($path.'/js/ckeditor/ckeditor.js');
			}
			//table2excel
			if (isset($REQUIRE_LIB['excel'])){
				$js->add($path.'/js/jquery.table2excel.js');
			}
			//number
			if (isset($REQUIRE_LIB['num'])){
				$js->add($path.'/js/num.js');
			}
			//reveal
			if (isset($REQUIRE_LIB['reveal'])){
				$js->add($path.'/js/reveal/head.min.js');
				$js->add($path.'/js/reveal/reveal.js');
				$js->add(<<<EOD
Reveal.initialize({
	controls: true,
	progress: true,
	history: true,
	center: true,
	help: true,
	transition: 'slide',
	math: {
		mathjax: '/js/MathJax-2.7.5/MathJax.js',
		config: 'TeX-AMS_HTML-full'
	}
});
EOD
				);
				$js->add($path.'/js/reveal/classList.js');
				$js->add($path.'/js/reveal/highlight.js');
				$js->add($path.'/js/reveal/math.js');
				$js->add($path.'/js/reveal/notes.js');
				$js->add($path.'/js/reveal/search.js');
				$js->add($path.'/js/reveal/zoom.js');
				$js->add('hljs.initHighlightingOnLoad();');
			}
			//user message
			if (isset($REQUIRE_LIB['user-msg'])){
				$js->add($path.'/js/user_msg.js');
			}
			//compile
			if (isset($REQUIRE_LIB['compile'])){
				$js->add($path.'/js/compile.js');
			}
			//canvas-nest
			if (isset($REQUIRE_LIB['canvas-nest'])){
				$js->add($path.'/js/canvas-nest.min.js');
			}
		}
		
		echo $js->minify();
	}elseif($_GET['tab']=='css'){
		$css=new Minify\CSS('');
		header('Content-type: text/css');
		if(!$REQUIRE_LIB){
			//Bootstrap core CSS
			$css->add($path.'/css/bootstrap.min.css');
			//Bootstrap theme
			$css->add($path.'/css/bootstrap-theme.min.css');
			//Bootstrap switch
			$css->add($path.'/css/bootstrap-switch.min.css');
			//Custom styles for this template
			$css->add($path.'/css/uoj-theme.css');
			//time
			$css->add($path.'/css/clock.css');
		}else{
			//codemirror
			if (isset($REQUIRE_LIB['blog-editor']) || isset($REQUIRE_LIB['slide-editor']) || isset($REQUIRE_LIB['code-editor']) || isset($REQUIRE_LIB['text-editor'])){
				$css->add($path.'/css/codemirror/codemirror.css');
				$css->add($path.'/css/codemirror/fullscreen.css');
			}
			//UOJ blog editor
			if (isset($REQUIRE_LIB['blog-editor'])){
				$css->add($path.'/css/blog-editor.css');
				$css->add($path.'/css/codemirror/foldgutter.css');
			}
			//code editor
			if (isset($REQUIRE_LIB['code-editor'])){
				$css->add($path.'/css/codemirror/foldgutter.css');
			}
			//Bootstrap dialog
			if (isset($REQUIRE_LIB['dialog'])){
				$css->add($path.'/css/bootstrap-dialog.min.css');
			}
			//morris
			if (isset($REQUIRE_LIB['morris'])){
				$css->add($path.'/css/morris.css');
			}
			//shjs
			if (isset($REQUIRE_LIB['shjs'])){
				$css->add($path.'/css/sh_typical.min.css');
			}
			//night-theme
			if (isset($REQUIRE_LIB['night-theme'])){
				$css->add($path.'/css/hoj-night-theme.css');
				$css->add($path.'/css/sh_neon.css');
				$css->add($path.'/css/codemirror/seti.css');
			}
			//number
			if (isset($REQUIRE_LIB['num'])){
				$css->add($path.'/css/num.css');
			}
			//reveal
			if (isset($REQUIRE_LIB['reveal'])){
				$css->add($path.'/css/reveal/reveal.css');
				//Code syntax highlighting
				$css->add($path.'/css/reveal/zenburn.css');
			}
			//reveal-print
			if (isset($REQUIRE_LIB['reveal-print'])){
				$css->add($path.'/css/reveal/print/'.$REQUIRE_LIB['reveal-print'].'.css');
			}
			//reveal-theme
			if (isset($REQUIRE_LIB['reveal-theme'])){
				$css->add($path.'/css/reveal/theme/'.$REQUIRE_LIB['reveal-theme'].'.css');
			}
		}

		echo $css->minify();
	}