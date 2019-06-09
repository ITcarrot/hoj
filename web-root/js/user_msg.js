$.ajaxSetup({async:false});

function addButton(conversationName, send_time, type) {
	$("#conversations").append(
		'<div class="row top-buffer-sm">' +
			'<div class="col-sm-3">' +
				'<button type="button" class="btn btn-' + ( type ? 'warning' : 'primary' ) + ' btn-block" ' +
					'onclick="enterConversation(\'' + conversationName + '\')">' +
					conversationName +
				'</button>' +
			'</div>' +
			'<div class="col-sm-9" style="line-height:34px">' +
				'最后发送时间：' + send_time +
			'</div>' +
		'</div>'
	);
}

function addBubble(content, send_time, read_time, msgId, conversation, page, type) {
	$("#history-list").append(
			'<div style=' + (type ? "margin-left:40%;margin-right:0%;" : "margin-left:0%;margin-right:40%;") + '>' +
				'<div class="panel panel-info">' +
					'<div class="panel-body bg-info" style="word-break: break-all">' +
						'<div style="white-space:pre-wrap">' +
							htmlspecialchars(content) +
						'</div>' +
					'</div>' +
					'<div>' +
						'<div class="row">' +
							'<div class="col-sm-6">' +
								'发送时间：' + send_time +
							'</div>' +
							'<div class="col-sm-6 text-right">' +
								 '查看时间：' + (read_time == null ? '<strong>未查看</strong>' : read_time) +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>'
	);
}

function submitMessagePost(conversationName) {
		if ($('#input-message').val().length == 0  ||  $('#input-message').val().length >= 65536) {
				$('#help-message').text('私信长度必须在1~65535之间。');
				$('#form-group-message').addClass('has-error');
				return;
		}
		$('#help-message').text('');
		$('#form-group-message').removeClass('has-error');

		$.post('/user/msg', {
				user_msg : 1,
				receiver : conversationName,
				message : $('#input-message').val()
    }, function(msg) {
				$('#input-message').val("");
		});
}

function refreshHistory(conversation, page) {
		$("#history-list").empty();
		var ret = false;
		$('#conversation-name').text(conversation);
		$('#pageShow').text("第" + page.toString() + "页");
		$.get('/user/msg', {
				getHistory : '',
				conversationName : conversation,
				pageNumber : page
		}, function(msg) {
				var result = JSON.parse(msg);
				var cnt = 0, flag = 0, F = 0;
				if (result.length == 11) flag = 1, F = 1;
				result.reverse();
				for (msg in result) {
						if (flag) {flag = 0; continue;}
						var message = result[msg];
						addBubble(message[0], message[1], message[2], message[3], conversation, page, message[4]);
						if ((++cnt) + 1 == result.length  &&  F) break;
				}
				if (result.length == 11) ret = true;
		});
		return ret;
}

function refreshConversations() {
	$("#conversations").html('');
    $.get('/user/msg', {
			getConversations : ""
		}, function(msg) {
			var result = JSON.parse(msg);
			for (i in result) {
				var conversation = result[i];
				if (conversation[1] == 1) {
					addButton(conversation[2], conversation[0], conversation[1]);
				}
			}
			for (i in result) {
				var conversation = result[i];
				if (conversation[1] == 0) {
					addButton(conversation[2], conversation[0], conversation[1]);
				}
			}
		}
	);
}

function enterConversation(conversationName) {
	var slideTime = 300;
	var page = 1;
	$("#conversations").hide(slideTime);
    var changeAble = refreshHistory(conversationName, page);
	$("#history").slideDown(slideTime);
	$('#form-message').unbind("submit").submit(function() {
		submitMessagePost(conversationName);
		page = 1;
		changeAble = refreshHistory(conversationName, page);
		refreshConversations();
		return false;
	});
	$('#goBack').unbind("click").click(function() {
		refreshConversations();
		$("#history").slideUp(slideTime);
		$("#conversations").show(slideTime);
		return;
	});
	$('#pageLeft').unbind("click").click(function() {
		if (changeAble) page++;
		changeAble = refreshHistory(conversationName, page);
		return false;
	});
	$('#pageLeft2').unbind("click").click(function() {
		if (changeAble) page++;
		changeAble = refreshHistory(conversationName, page);
	});
	$('#pageRight').unbind("click").click(function() {
		if (page > 1) page--;
		changeAble = refreshHistory(conversationName, page);
		return false;
	});
	$('#pageRight2').unbind("click").click(function() {
		if (page > 1) page--;
		changeAble = refreshHistory(conversationName, page);
	});
}

refreshConversations();
