<?php
	if (Auth::check()) {
		redirectTo('/');
	}

	function handleLoginPost() {
		if (!crsf_check()) {
			return 'expired';
		}
		if (!isset($_POST['username'])) {
			return "failed";
		}
		if (!isset($_POST['password'])) {
			return "failed";
		}
		
		$validate="";
		$validate=$_POST["captcha"];
		if($validate!=$_SESSION['img-captcha']&&$_SESSION["validate"]==false){
			return "captchafailed";
		}
		$username = $_POST['username'];
		$password = $_POST['password'];
		if (!validateUsername($username)) {
			return "failed";
		}
		if (!validatePassword($password)) {
			return "failed";
		}
		$user = queryUser($username);
		if (!$user || !checkPassword($user, $password)) {
			return "failed";
		}
		if ($user['usergroup'] == 'B') {
			return "banned";
		}

		Auth::login($user['username']);
		return "ok";
	}

	if (isset($_POST['login'])) {
		echo handleLoginPost();
		die();
	}
?>
<?php
	$REQUIRE_LIB['md5'] = '';
?>
<?php echoUOJPageHeader(UOJLocale::get('login')) ?>

<h2 class="page-header"><?= UOJLocale::get('login') ?></h2>
<form id="form-login" class="form-horizontal" method="post">
	<div class="row">
		<div class="col-sm-5">
			<div id="div-username" class="form-group">
				<label for="input-username" class="col-sm-5 control-label"><?= UOJLocale::get('username') ?></label>
				<div class="col-sm-6">
					<input type="text" class="form-control" id="input-username" name="username" placeholder="<?= UOJLocale::get('enter your username') ?>" maxlength="20" />
					<script>$('#input-username').autouser()</script>
					<span class="help-block" id="help-username"></span>
				</div>
			</div>
			<div id="div-password" class="form-group">
				<label for="input-password" class="col-sm-5 control-label"><?= UOJLocale::get('password') ?></label>
				<div class="col-sm-6">
					<input type="password" class="form-control" id="input-password" name="password" placeholder="<?= UOJLocale::get('enter your password') ?>" maxlength="20" />
					<span class="help-block" id="help-password"></span>
				</div>
			</div>
			<div id="div-captcha" class="form-group">
				<label for="input-captcha" class="col-sm-5 control-label">验证码（二选一）</label>
				<div class="col-sm-6">
					<p><input type="text" class="form-control" id="input-captcha" name="captcha" placeholder="请输入验证码" maxlength="4" autocomplete="off" /></p>
					<p><img title="点击刷新" src="/captcha" align="absbottom" onclick="this.src='/captcha?'+Math.random();" id="img-captcha"></img></p>
					<span class="help-block" id="help-captcha"></span>
					<div style="height:50px;"></div>
				</div>
			</div>
		</div>
		<div class="col-sm-7">
			<iframe scrolling="no" src="/poem" style="border-width: 0px; border: none; width: 310px; height: 300px;" id="poem-captcha"></iframe>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-3">
			<a style="cursor: pointer;" id="login_failed">无法登录？&nbsp;</a>
			<button type="submit" id="button-submit" class="btn btn-default text-center"><?= UOJLocale::get('submit') ?></button>
			<a id="forgot_pw" style="cursor: pointer;">&nbsp;忘记密码？</a>
			<script type="text/javascript">
				$("#login_failed").click(function(){
					alert("请不要禁用该网页的COOKIE，否则会导致登录失败\n如果你的系统时间不对，也可能导致登录失败");
				});
				$("#forgot_pw").click(function(){
					alert("请向管理员索要重置密码的链接");
				});
			</script>
		</div>
	</div>
</form>

<script type="text/javascript">
function validateLoginPost() {
	var ok = true;
	ok &= getFormErrorAndShowHelp('username', validateUsername);
	ok &= getFormErrorAndShowHelp('password', validatePassword);
	return ok;
}

function submitLoginPost() {
	if (!validateLoginPost()) {
		return false;
	}

	$.post('/login', {
		_token : "<?= crsf_token() ?>",
		login : '',
		username : $('#input-username').val(),
		captcha : $('#input-captcha').val(),
		password : md5($('#input-password').val(), "<?= getPasswordClientSalt() ?>")
	}, function(msg) {
		if (msg == 'ok') {
			var prevUrl = '<?=$_GET['from']?>';
			if (prevUrl == '' || /.*\/login.*/.test(prevUrl) || /.*\/register.*/.test(prevUrl) || /.*\/reset-password.*/.test(prevUrl)) {
				prevUrl = '/';
			};
			window.location.href = prevUrl;
		} else if (msg == 'banned') {
			$('#div-username').addClass('has-error');
			$('#help-username').html('用户已被禁用。');
		} else if (msg == 'expired') {
			$('#div-username').addClass('has-error');
			$('#help-username').html('页面已过期。');
		}else if (msg == 'captchafailed') {
			$('#div-captcha').addClass('has-error');
			$('#help-captcha').html('验证码错误');
			$('#img-captcha').attr('src','/captcha?'+Math.random());
		} else {
			$('#div-username').addClass('has-error');
			$('#help-username').html('用户名或密码错误。');
			$('#div-password').addClass('has-error');
			$('#help-captcha').html('');
			$('#help-password').html('用户名或密码错误。');
			$('#img-captcha').attr('src','/captcha?'+Math.random());
			$('#poem-captcha').attr('src','/poem?'+Math.random());
		}
	});
	return true;
}

$('#form-login').submit(function(e) {
	e.preventDefault();
	submitLoginPost();
});
</script>
<?php echoUOJPageFooter() ?>