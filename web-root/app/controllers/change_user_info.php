<?php
	if (!Auth::check()) {
		redirectToLogin();
	}
	function handlePost() {
		global $myUser;
		if (!isset($_POST['old_password']))
			return '无效表单';
		$old_password = $_POST['old_password'];
		if (!validatePassword($old_password) || !checkPassword($myUser, $old_password))
			return "失败：密码错误。";
		if ($_POST['ptag']){
			$password = $_POST['password'];
			if (!validatePassword($password))
				return "失败：无效密码。";
			$password = getPasswordToStore($password, $myUser['username']);
			DB::update("update user_info set password = '$password' where username = '{$myUser['username']}'");
		}
		if ($_POST['Qtag']){
			$qq = $_POST['qq'];
			if (!validateQQ($qq))
				return "失败：无效QQ。";
			$esc_qq = DB::escape($qq);
			DB::update("update user_info set qq = '$esc_qq' where username = '{$myUser['username']}'");
		}else
			DB::update("update user_info set QQ = NULL where username = '{$myUser['username']}'");
		if ($_POST['sex'] == "U" || $_POST['sex'] == 'M' || $_POST['sex'] == 'F'){
			$sex = $_POST['sex'];
			$esc_sex = DB::escape($sex);
			DB::update("update user_info set sex = '$esc_sex' where username = '{$myUser['username']}'");
		}
		if (validateMotto($_POST['motto'])) {
			$purifier = HTML::pruifier();
			$esc_motto = DB::escape($purifier->purify($_POST['motto']));
			if($esc_motto!=''&&isContestUser(Auth::user()))
				$esc_motto='自定义格言功能不对比赛账户开放';
			DB::update("update user_info set motto = '$esc_motto' where username = '{$myUser['username']}'");
		}
		$setting = DB::escape($_POST['setting']);
		DB::update("update user_info set setting = '$setting' where username = '{$myUser['username']}'");
		return "ok";
	}
	if (isset($_POST['change'])) {
		die(handlePost());
	}
?>
<?php
	$REQUIRE_LIB['dialog'] = '';
	$REQUIRE_LIB['md5'] = '';
?>
<?php echoUOJPageHeader(UOJLocale::get('modify my profile')) ?>
<h2 class="page-header"><?= UOJLocale::get('modify my profile') ?></h2>
<form id="form-update" class="form-horizontal">
	<h4><?= UOJLocale::get('please enter your password for authorization') ?></h4>
	<div id="div-old_password" class="form-group">
		<label for="input-old_password" class="col-sm-2 control-label"><?= UOJLocale::get('password') ?></label>
		<div class="col-sm-3">
			<input type="password" class="form-control" name="old_password" id="input-old_password" placeholder="<?= UOJLocale::get('enter your password') ?>" maxlength="20" />
			<span class="help-block" id="help-old_password"></span>
		</div>
	</div>
	<h4><?= UOJLocale::get('please enter your new profile') ?></h4>
	<div id="div-password" class="form-group">
		<label for="input-password" class="col-sm-2 control-label"><?= UOJLocale::get('new password') ?></label>
		<div class="col-sm-3">
			<input type="password" class="form-control" id="input-password" name="password" placeholder="<?= UOJLocale::get('enter your new password') ?>" maxlength="20" />
			<input type="password" class="form-control top-buffer-sm" id="input-confirm_password" placeholder="<?= UOJLocale::get('re-enter your new password') ?>" maxlength="20" />
			<span class="help-block" id="help-password"><?= UOJLocale::get('leave it blank if you do not want to change the password') ?></span>
		</div>
	</div>
	<div id="div-qq" class="form-group">
		<label for="input-qq" class="col-sm-2 control-label"><?= UOJLocale::get('QQ') ?></label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="qq" id="input-qq" value="<?= $myUser['qq'] != 0 ? $myUser['qq'] : '' ?>" placeholder="<?= UOJLocale::get('enter your QQ') ?>" maxlength="50" />
			<span class="help-block" id="help-qq"></span>
		</div>
	</div>
	<div id="div-sex" class="form-group">
		<label for="input-sex" class="col-sm-2 control-label"><?= UOJLocale::get('sex') ?></label>
		<div class="col-sm-3">
			<select class="form-control" id="input-sex"  name="sex">
				<option value="U"<?= Auth::user()['sex'] == 'U' ? ' selected="selected"' : ''?>><?= UOJLocale::get('refuse to answer') ?></option>
				<option value="M"<?= Auth::user()['sex'] == 'M' ? ' selected="selected"' : ''?>><?= UOJLocale::get('male') ?></option>
				<option value="F"<?= Auth::user()['sex'] == 'F' ? ' selected="selected"' : ''?>><?= UOJLocale::get('female') ?></option>
			</select>
		</div>
	</div>
	<div id="div-motto" class="form-group">
		<label for="input-motto" class="col-sm-2 control-label"><?= UOJLocale::get('motto') ?></label>
		<div class="col-sm-3">
			<textarea class="form-control" id="input-motto"  name="motto"><?=HTML::escape($myUser['motto'])?></textarea>
			<span class="help-block" id="help-motto"></span>
		</div>
	</div>
	<h4>温馨提示：在本站引用本站文件可直接使用“上传文件”中提供的地址，不需要在前面加<code>http://10.248.5.4</code>或其它hoj域名</h4>
	<div id="div-avatar" class="form-group">
		<label for="input-avatar" class="col-sm-2 control-label">头像设置</label>
		<div class="col-sm-3">
			<textarea class="form-control" id="input-avatar"  name="avatar"><?=$myUser['setting']['avatar']?></textarea>
			<span class="help-block" id="help-avatar"></span>
		</div>
	</div>
	<div id="div-background" class="form-group">
		<label for="input-background" class="col-sm-2 control-label">背景设置</label>
		<div class="col-sm-3">
			<textarea class="form-control" id="input-background"  name="background"><?=$myUser['setting']['background']?></textarea>
			<span class="help-block" id="help-background"></span>
		</div>
	</div>
	<div id="div-live2d" class="form-group">
		<label for="input-live2d" class="col-sm-2 control-label">看板娘</label>
		<div class="col-sm-3">
			<?=HTML::checkbox('live2d',isset($myUser['setting']['live2d']))?>
			<span class="help-block" id="help-live2d"></span>
		</div>
	</div>
	<div id="div-nest" class="form-group">
		<label for="input-nest" class="col-sm-2 control-label">博客粒子背景</label>
		<div class="col-sm-3">
			<?=HTML::checkbox('nest',isset($myUser['setting']['nest']))?>
			<span class="help-block" id="help-nest"></span>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-3">
			<button type="submit" id="button-submit" class="btn btn-default"><?= UOJLocale::get('submit') ?></button>
		</div>
	</div>
</form>

<script type="text/javascript">
	$('#input-live2d').bootstrapSwitch({
		onText: "已开启",
		onColor: 'primary',
		offText: "已关闭",
		offColor: 'primary',
		labelText: "看板娘",
		handleWidth: 100
	});
	$('#input-nest').bootstrapSwitch({
		onText: "已开启",
		onColor: 'primary',
		offText: "已关闭",
		offColor: 'primary',
		labelText: "博客粒子背景",
		handleWidth: 100
	});
	function validateUpdatePost() {
		var ok = true;
		ok &= getFormErrorAndShowHelp('old_password', validatePassword);

		if ($('#input-password').val().length > 0)
			ok &= getFormErrorAndShowHelp('password', validateSettingPassword);
		if ($('#input-qq').val().length > 0)
			ok &= getFormErrorAndShowHelp('qq', validateQQ);
		return ok;
	}
	function submitUpdatePost() {
		if (!validateUpdatePost())
			return;
		var config = {};
		if($('#input-avatar').val().length == 0)
			$('#input-avatar').val('/pictures/no-avatar.jpeg');
		config['avatar'] = escape($('#input-avatar').val());
		config['background'] = escape($('#input-background').val());
		if($('#input-live2d')[0].checked)
			config['live2d'] = '';
		if($('#input-nest')[0].checked)
			config['nest'] = '';
		$.post('/user/modify-profile', {
			change   : '',
			ptag     : $('#input-password').val().length,
			Qtag     : $('#input-qq').val().length,
			password : md5($('#input-password').val(), "<?= getPasswordClientSalt() ?>"),
			old_password : md5($('#input-old_password').val(), "<?= getPasswordClientSalt() ?>"),
			qq       : $('#input-qq').val(),
			sex      : $('#input-sex').val(),
			motto    : $('#input-motto').val(),
			setting  : JSON.stringify(config)
		}, function(msg) {
			if (msg == 'ok') {
				BootstrapDialog.show({
					title   : '修改成功',
					message : '用户信息修改成功',
					type    : BootstrapDialog.TYPE_SUCCESS,
					buttons : [{
						label: '好的',
						action: function(dialog) {
							dialog.close();
						}
					}],
					onhidden : function(dialog) {
						window.location.href = '/user/profile/<?=$myUser['username']?>';
					}
				});
			} else {
				BootstrapDialog.show({
					title   : '修改失败',
					message : msg,
					type    : BootstrapDialog.TYPE_DANGER,
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
	$(document).ready(function(){
		$('#form-update').submit(function(e) {
			submitUpdatePost();
			e.preventDefault();
		});
	});
</script>
<?php echoUOJPageFooter() ?>