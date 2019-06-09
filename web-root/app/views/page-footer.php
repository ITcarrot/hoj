<?php
	if (!isset($ShowPageFooter)) {
		$ShowPageFooter = true;
	}
?>
			</div>
			<?php if ($ShowPageFooter): ?>
			<div class="uoj-footer">
<!--禁用语言切换
				<p>
					<a href="<?= HTML::url(UOJContext::requestURI(), array('params' => array('locale' => 'zh-cn'))) ?>"><img src="//img.uoj.ac/utility/flags/24/cn.png" alt="中文" /></a> 
					<a href="<?= HTML::url(UOJContext::requestURI(), array('params' => array('locale' => 'en'))) ?>"><img src="//img.uoj.ac/utility/flags/24/gb.png" alt="English" /></a>
				</p>
-->
				<div class="text-center">
					<?=HTML::checkbox('night-mode',isset($_COOKIE['night-mode']))?>
				</div>
				<ul class="list-inline top-buffer-md">
					<li><?= UOJConfig::$data['profile']['oj-name'] ?></li>
					<?php if (UOJConfig::$data['profile']['ICP-license']!=''): ?>
					 |<li><a target="_blank" href="http://www.miitbeian.gov.cn"><?= UOJConfig::$data['profile']['ICP-license'] ?></a></li>
					<?php endif ?>
					 |<li><a target="_blank" href="https://github.com/ITcarrot/hoj">开源项目</a></li>
				</ul>
				<p><a href="/copyright">Copyright &copy; 2018-2019 ITcarrot and dddmh. All rights reserved.</a></p>
				<center>
				<div style="width:120px;height:80px;overflow:hidden;margin-bottom:10px;">
				<img src="/pictures/ChineseFlag" style="width:120px;height:80px;position:relative;
				top:<?=isset($_COOKIE['night-mode'])?'80px':'0'?>;
				opacity:<?=isset($_COOKIE['night-mode'])?'0':'1'?>;
				transition:top 5s ease-in-out, opacity 5s cubic-bezier(0, 0.3, 1, 0.7);" id="ChineseFlag">
				</div>
				</center>
			</div>
			<?php endif ?>
		</div>
		<!-- /container -->
	</body>
</html>