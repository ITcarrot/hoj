<?php

class Auth {
	public static function check() {
		global $myUser;
		return $myUser != null;
	}
	public static function id() {
		global $myUser;
		return $myUser['username'];
	}
	public static function user() {
		global $myUser;
		return $myUser;
	}
	public static function login($username, $remember = true) {
		if (!validateUsername($username)) {
			return;
		}
		$_SESSION['username'] = $username;
		if ($remember) {
			$remember_token = DB::selectFirst("select remember_token from user_info where username = '$username'")['remember_token'];
			if ($remember_token == '') {
				$remember_token = uojRandString(60);
				DB::update("update user_info set remember_token = '$remember_token' where username = '$username'");
			}

			$expire = time() + 60 * 60 * 24 * 15;
			Cookie::safeSet('uoj_username', $username, $expire, '/', array('httponly' => true));
			Cookie::safeSet('uoj_remember_token', $remember_token, $expire, '/', array('httponly' => true));
		}
		$now=time();
		setcookie('can_download',$now,$now+300,'/',UOJContext::cookieDomain(),false,true);
		setcookie('can_download_check',md5($now.$username),$now+300,'/',UOJContext::cookieDomain(),false,true);
	}
	public static function logout($all = false) {
		unset($_SESSION['username']);
		unset($_SESSION['last_visited']);
		Cookie::safeUnset('uoj_username', '/');
		Cookie::safeUnset('uoj_remember_token', '/');
		if($all){
			DB::update("update user_info set remember_token = '' where username = '".Auth::id()."'");
		}
	}

	private static function initMyUser() {
		global $myUser;
		$myUser = null;
		
		Cookie::safeCheck('uoj_username', '/');
		Cookie::safeCheck('uoj_remember_token', '/');
/*		
		注释该段以防止窃取session_id导致用户被盗
		
		if (isset($_SESSION['username'])) {
			if (!validateUsername($_SESSION['username'])) {
				return;
			}
			$myUser = queryUser($_SESSION['username']);
			return;
		}
*/
		$remember_token = Cookie::safeGet('uoj_remember_token', '/');
		if ($remember_token != null) {
			$username = Cookie::safeGet('uoj_username', '/');
			if (!validateUsername($username)) {
				return;
			}
			$myUser = queryUser($username);
			if ($myUser['remember_token'] !== $remember_token) {
				$myUser = null;
			}
			return;
		}
	}
	public static function init() {
		global $myUser;
		
		Auth::initMyUser();
		if ($myUser) {
			if ($myUser['usergroup'] == 'B') {
				$myUser = null;
			}
		}
		if ($myUser) {
			DB::update("update user_info set remote_addr = '".DB::escape($_SERVER['REMOTE_ADDR'])."', http_x_forwarded_for = '".DB::escape($_SERVER['HTTP_X_FORWARDED_FOR'])."' where username = '".DB::escape($myUser['username'])."'");
			$_SESSION['last_visited'] = time();
			$myUser['setting'] = json_decode($myUser['setting'], true);
		}
	}
}