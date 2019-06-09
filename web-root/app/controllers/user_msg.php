<?php
	if ($myUser == null) {
		redirectToLogin();
	}

	function handleMsgPost() {
		global $myUser;
		if (!isset($_POST['receiver'])) {
			return 'fail';
		}
		if (!isset($_POST['message'])) {
			return 'fail';
		}
		if (0 > strlen($_POST['message']) || strlen($_POST['message']) > 65535) {
			return 'fail';
		}
		$receiver = $_POST['receiver'];
		$esc_message = DB::escape($_POST['message']);
		$sender = $myUser['username'];
		
		if (!validateUsername($receiver) || !queryUser($receiver)) {
			return 'fail';
		}

		DB::query("insert into user_msg (sender, receiver, message, send_time) values ('$sender', '$receiver', '$esc_message', now())");
		return "ok";
	}

	function getConversations() {
		global $myUser;
		$username = $myUser['username'];
		$result = DB::query( "select * from user_msg where sender = '$username' or receiver = '$username' order by send_time DESC" );
		$ret = array();
		while ($msg = DB::fetch($result)) {
			if ($msg['sender'] !== $username) {
				if (isset($ret[$msg['sender']])) {
					$ret[$msg['sender']][1] |= ($msg['read_time'] == null);
					continue;
				}
			$ret[$msg['sender']] = array($msg['send_time'], ($msg['read_time'] == null));
			} else {
				if (isset($ret[$msg['receiver']])) continue;
					$ret[$msg['receiver']] = array($msg['send_time'], 0);
			}
		}
		$res = [];
		foreach ($ret as $name => $con) {
			$res[] = [$con[0], $con[1], $name];
		}
		usort($res, function($a, $b) { return -strcmp($a[0], $b[0]); });
		return json_encode($res);
	}

	function getHistory() {
		global $myUser;
		$username = $myUser['username'];
		if (!isset($_GET['conversationName']) || !validateUsername($_GET['conversationName'])) {
			return '[]';
		}
		if (!isset($_GET['pageNumber']) || !validateUInt($_GET['pageNumber'])) {
			return '[]';
		}

		$conversationName = $_GET['conversationName'];
		$pageNumber = ($_GET['pageNumber'] - 1) * 10;
		DB::query("update user_msg set read_time = now() where sender = '$conversationName'  and  receiver = '$username' and read_time is null");

		$result = DB::query("select * from user_msg where (sender = '$username' and receiver = '$conversationName') or (sender = '$conversationName' and receiver = '$username')	order by send_time DESC limit $pageNumber, 11");
		$ret = array();
		while ($msg = DB::fetch($result)) {
			$ret[] = array($msg['message'], $msg['send_time'], $msg['read_time'], $msg['id'], ($msg['sender'] == $username));
		}
		return json_encode($ret);
	}

	if (isset($_POST['user_msg'])) {
		die(handleMsgPost());
	} elseif (isset($_GET['getConversations'])) {
		die(getConversations());
	} elseif (isset($_GET['getHistory'])) {
		die(getHistory());
	}
?>

<?php echoUOJPageHeader('私信') ?>

<h1 class="page-header">私信</h1>

<div class="row" style="margin-bottom: 20px;">
	<div class="col-xs-4"><label class="pull-right">打开会话：</label></div>
	<form>
		<div class="col-xs-3">
			<input type="text" class="form-control input-sm" placeholder="请输入用户名" name="enter" id="enter">
			<script>$('#enter').autouser();</script>
		</div>
		<div class="col-xs-4">
			<input type="submit" class="btn btn-default btn-sm" value="跳转">
		</div>
	</form>
</div>

<div id="conversations"></div>

<div id="history" style="display:none">
	<div class="panel panel-primary">
		<div class="panel-heading">
			<button type="button" id="goBack" class="btn btn-info btn-xs" style="position:absolute">返回</button>
			<div id="conversation-name" class="text-center"></div>
		</div>
		<div class="panel-body">
			<ul class="pager top-buffer-no">
				<li class="previous"><a href="#" id="pageLeft">&larr; 更早的消息</a></li>
				<li class="text-center" id="pageShow" style="line-height:32px"></li>
				<li class="next"><a href="#" id="pageRight">更新的消息 &rarr;</a></li>
			</ul>
			<div id="history-list" style="min-height: 200px;">
			</div>
			<ul class="pager bot-buffer-no">
				<li class="previous"><a href="#history" id="pageLeft2">&larr; 更早的消息</a></li>
				<li class="next"><a href="#history" id="pageRight2">更新的消息 &rarr;</a></li>
			</ul>
			<hr />
			<form id="form-message">
				<div class="form-group" id="form-group-message">
					<textarea id="input-message" class="form-control"></textarea>
					<span id="help-message" class="help-block"></span>
				</div>
				<div class="text-right">
					<button type="submit" id="message-submit" class="btn btn-info btn-md">发送</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="/min/js?rq=%7B%22user-msg%22%3A%22%22%7D&v=20190406"></script>
<script>
<?php if (isset($_GET['enter'])): ?>
	<?php if (!validateUsername($_GET['enter']) || !queryUser($_GET['enter'])): ?>
		alert("用户不存在");
	<?php else: ?>
		enterConversation("<?= $_GET['enter'] ?>");
	<?php endif ?>
<?php endif ?>
</script>
<?php echoUOJPageFooter() ?>