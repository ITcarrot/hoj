<?php
	if (!isset($_GET['id']) || !validateUInt($_GET['id']) || !($blog = queryBlog($_GET['id'])) || !UOJContext::isHis($blog)) {
		become404Page();
	}
	if ($blog['is_hidden'] && !UOJContext::hasBlogPermission()) {
		become403Page();
	}
?>
<?php echoUOJPageHeader('打印博客 - ' . HTML::stripTags($blog['title']), array('ShowPageHeader' => false, 'REQUIRE_LIB' => array('mathjax' => '', 'shjs' => ''))); ?>
<article>
	<?= $blog['content'] ?>
</article>
<button class="btn btn-default btn-lg" id="print_button" onclick="window.print()"><span class="glyphicon glyphicon-print"></span>打印</button>
<script>
	$('body').css('width','794px');
	$('body').css('margin','auto');
	$('.container').css('width','100%');
</script>
<style>
	#copy_btn{
		display:none;
	}
	#print_button{
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
<?php echoUOJPageFooter(array('ShowPageFooter' => false)); ?>