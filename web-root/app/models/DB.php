<?php

class DB {
	public static function init() {
		global $uojMySQL;
		@$uojMySQL = mysqli_connect(UOJConfig::$data['database']['host'], UOJConfig::$data['database']['username'], UOJConfig::$data['database']['password'],UOJConfig::$data['database']['database'],3306);
		if (!$uojMySQL) {
			echo 'There is something wrong with database >_<.... ' . mysqli_connect_error();
			die();
		}
	}
	public static function escape($str) {
		global $uojMySQL;
		return mysqli_real_escape_string($uojMySQL,$str);
	}
	public static function fetch($r, $opt = MYSQLI_BOTH) {
		if(!$r)
			return 0;
		return mysqli_fetch_array($r, $opt);
	}
	public static function query($q) {
		global $uojMySQL;
		return mysqli_query($uojMySQL,$q);
	}
	public static function update($q) {
		return DB::query($q);
	}
	public static function insert($q) {
		return DB::query($q);
	}
	public static function insert_id() {
		global $uojMySQL;
		return mysqli_insert_id($uojMySQL);
	}
	public static function delete($q) {
		return DB::query($q);
	}
	public static function select($q) {
		return DB::query($q);
	}
	public static function selectAll($q, $opt = MYSQLI_ASSOC) {
		$res = array();
		$qr = DB::query($q);
		while ($row = DB::fetch($qr, $opt)) {
			$res[] = $row;
		}
		return $res;
	}
	public static function selectFirst($q, $opt = MYSQLI_ASSOC) {
		return DB::fetch(DB::query($q), $opt);
	}
	public static function selectCount($q) {
		list($cnt) = DB::fetch(DB::query($q), MYSQLI_NUM);
		return $cnt;
	}
	public static function checkTableExists($name) {
		return DB::query("select 1 from $name") !== false;
	}
	public static function affected_rows() {
		global $uojMySQL;
		return mysqli_affected_rows($uojMySQL);
	}
	public static function manage_log($type,$detail) {
		global $myUser;
		$type=DB::escape($type);
		$detail=DB::escape($detail);
		DB::query("insert into manage_log (user, remote_addr,http_x_forwarded_for,type,detail)value('{$myUser['username']}','".DB::escape($_SERVER['REMOTE_ADDR'])."','".DB::escape($_SERVER['HTTP_X_FORWARDED_FOR'])."','$type','$detail')");
	}
}