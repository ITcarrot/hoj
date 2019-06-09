<?php
	if(isset($_COOKIE['last_register_time'])){
		$time_remain=600-time()+$_COOKIE['last_register_time'];
		if($time_remain>0){
			becomeMsgPage("<h1>请不要频繁注册</h1><p>注册页面将于 $time_remain 秒后重新启用</p>");
		}
	}
	function handleRegisterPost() {
		if (!crsf_check()) {
			return '页面已过期';
		}
		if (!isset($_POST['username'])) {
			return "无效表单";
		}
		if (!isset($_POST['password'])) {
			return "无效表单";
		}
		if(!isset($_POST['realname']) || $_POST['realname']==''){
			return 'emailfailed';
		}

		$validate="";
		$validate=$_POST["captcha"];
		if($validate!=$_SESSION['img-captcha']){
			return "captchafailed";
		}
		$username = $_POST['username'];
		$password = $_POST['password'];
		if(!validateUInt($_POST['grade']) || $_POST['grade']<1800  || !validateUInt($_POST['banji']) || strlen($_POST['realname'])<4){
			return 'emailfailed';
		}
		$email=$_POST['grade'].'届';
		if($_POST['level']=='senior'){
			$email.='高';
		}elseif($_POST['level']=='junior'){
			$email.='初';
		}else{
			return 'emailfailed';
		}
		$email.=$_POST['banji'].'班'.$_POST['realname'];
		
		if (!validateUsername($username)) {
			return "失败：无效用户名。";
		}
		if (queryUser($username)) {
			return "失败：用户名已存在。";
		}
		if (!validatePassword($password)) {
			return "失败：无效密码。";
		}
		
		$password = getPasswordToStore($password, $username);
		$esc_email = DB::escape($email);
		
		if (!DB::selectCount("SELECT COUNT(*) FROM user_info"))
			DB::query("insert into user_info (username, email, password, register_time, usergroup, setting) values ('$username', '$esc_email', '$password', now(), 'S', '{\"avatar\":\"/pictures/no-avatar.jpeg\"}')");
		else
			DB::query("insert into user_info (username, email, password, register_time, usergroup, setting) values ('$username', '$esc_email', '$password', now(), 'B', '{\"avatar\":\"/pictures/no-avatar.jpeg\"}')");
		setcookie('last_register_time',time(),time()+600);
		return "欢迎你！" . $username . "，你已成功注册，请等待管理员审核。";
	}
	
	if (isset($_POST['register'])) {
		echo handleRegisterPost();
		die();
	} elseif (isset($_POST['check_username'])) {
		$username = $_POST['username'];
		if (validateUsername($username) && !queryUser($username)) {
			echo '{"ok" : true}';
		} else {
			echo '{"ok" : false}';
		}
		die();
	}
?>
<?php
	$REQUIRE_LIB['md5'] = '';
	$REQUIRE_LIB['dialog'] = '';
?>
<?php echoUOJPageHeader(UOJLocale::get('register')) ?>
<h2 class="page-header"><?= UOJLocale::get('register') ?></h2>
<form id="form-register" class="form-horizontal">
	<div id="div-email" class="form-group">
		<label for="input-email" class="col-sm-2 control-label">验证信息</label>
		<div class="col-sm-3">
			<input name="grade" id="input-grade" type="text" maxlength="4" style="width:40px;display:inline;padding:5px;" placeholder="2021" class="form-control">届
			<select name="level" id="input-level" class="form-control" style="display:inline;width:65px;padding:5px;">
				<option value="senior">高中</option>
				<option value="junior">初中</option>
			</select>
			<input name="banji" id="input-banji" type="text" maxlength="2" class="form-control" style="display:inline;width:29px;padding:5px;" placeholder="1">班
			<input name="realname" id="input-realname" type="text" class="form-control" style="display:inline;width:90px;padding:5px;" maxlength="20" placeholder="张三">
			<span class="help-block" id="help-email"></span>
		</div>
	</div>
	<div id="div-username" class="form-group">
		<label for="input-username" class="col-sm-2 control-label"><?= UOJLocale::get('username') ?></label>
		<div class="col-sm-3">
			<input type="text" class="form-control" id="input-username" name="username" placeholder="<?= UOJLocale::get('enter your username') ?>" maxlength="20" />
			<span class="help-block" id="help-username"></span>
		</div>
	</div>
	<div id="div-password" class="form-group">
		<label for="input-password" class="col-sm-2 control-label"><?= UOJLocale::get('password') ?></label>
		<div class="col-sm-3">
			<input type="password" class="form-control" id="input-password" name="password" placeholder="<?= UOJLocale::get('enter your password') ?>" maxlength="20" />
			<input type="password" class="form-control top-buffer-sm" id="input-confirm_password" placeholder="<?= UOJLocale::get('re-enter your password') ?>" maxlength="20" />
			<span class="help-block" id="help-password"></span>
		</div>
	</div>
    <div id="div-captcha" class="form-group">
		<label for="input-captcha" class="col-sm-2 control-label">验证码</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" id="input-captcha" name="captcha" placeholder="请输入验证码" maxlength="4" />
			<span class="help-block" id="help-captcha"></span>
		</div>
		<img title="点击刷新" src="/captcha" align="absbottom" onclick="this.src='/captcha?'+Math.random();"></img>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-3">
			<button type="submit" id="button-submit" class="btn btn-default"><?= UOJLocale::get('submit') ?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
function checkUsernameNotInUse() {
	var ok = false;
	$.ajax({
		url : '/register',
		type : 'POST',
		dataType : 'json',
		async : false,
		data : {
			check_username : '',
			username : $('#input-username').val()
		},
		success : function(data) {
			ok = data.ok;
		},
		error :	function(XMLHttpRequest, textStatus, errorThrown) {
			alert(XMLHttpRequest.responseText);
			ok = false;
		}
	});
	return ok;
}
function validateRegisterPost() {
	var ok = true;
	ok &= getFormErrorAndShowHelp('username', function(str) {
		var err = validateUsername(str);
		if (err)
			return err;
		if (!checkUsernameNotInUse())
			return '该用户名已被人使用了。';
		return '';
	})
	ok &= getFormErrorAndShowHelp('password', validateSettingPassword);
	return ok;
}
function submitRegisterPost() {
	if (!validateRegisterPost()) {
		return;
	}
	$.post('/register', {
		_token : "<?= crsf_token() ?>",
		register : '',
		username : $('#input-username').val(),
		grade	: $('#input-grade').val(),
		level	: $('#input-level').val(),
		banji	: $('#input-banji').val(),
		realname	: $('#input-realname').val(),
		captcha : $('#input-captcha').val(),
		password : md5($('#input-password').val(), "<?= getPasswordClientSalt() ?>")
	}, function(msg) {
		if (/^欢迎你！/.test(msg)) {
			BootstrapDialog.show({
				title	 : '注册成功',
				message : msg,
				type		: BootstrapDialog.TYPE_SUCCESS,
				buttons: [{
					label: '好的',
					action: function(dialog) {
						dialog.close();
					}
				}],
				onhidden : function(dialog) {
					var prevUrl = document.referrer;
					if (!prevUrl) {
						prevUrl = '/';
					};
					window.location.href = prevUrl;
				}
			});
		 }else if (msg == 'captchafailed') {
			$('#div-captcha').addClass('has-error');
			$('#help-captcha').html('验证码错误');
		 }else if (msg == 'emailfailed') {
			$('#div-email').addClass('has-error');
			$('#help-email').html('验证信息不合法');
		} else {
			BootstrapDialog.show({
				title	 : '注册失败',
				message : msg,
				type		: BootstrapDialog.TYPE_DANGER,
				buttons: [{
					label: '好的',
					action: function(dialog) {
						dialog.close();
					}
				}],
			});
		}
	});
}
$(document).ready(function() {
	$('#form-register').submit(function(e) {
		submitRegisterPost();
		return false;
	});
});
</script>
<?php echoUOJPageFooter() ?>