// locale
uojLocaleData = {
	"username": {
		"en": "Username",
		"zh-cn": "用户名"
	},
	"contests::total score": {
		"en": "Score",
		"zh-cn": "总分"
	},
	"contests::n participants": {
		"en": function(n) {
			return n + " participant" + (n <= 1 ? '' : 's');
		},
		"zh-cn": function(n) {
			return "共 " + n + " 名参赛者";
		}
	},
	"click-zan::good": {
		"en": "Good",
		"zh-cn": "好评"
	},
	"click-zan::bad": {
		"en": "Bad",
		"zh-cn": "差评"
	},
	"editor::use advanced editor": {
		"en": "use advanced editor",
		"zh-cn": "使用高级编辑器"
	},
	"editor::language": {
		"en": "Language",
		"zh-cn": "语言"
	},
	"editor::browse": {
		"en": "Browse",
		"zh-cn": "浏览"
	},
	"editor::upload by editor": {
		"en": "Upload by editor",
		"zh-cn": "使用编辑器上传"
	},
	"editor::upload from local": {
		"en": "Upload from local",
		"zh-cn": "从本地文件上传"
	}
};

function uojLocale(name) {
	locale = $.cookie('uoj_locale');
	if (uojLocaleData[name] === undefined) {
		return '';
	}
	if (uojLocaleData[name][locale] === undefined) {
		locale = 'zh-cn';
	}
	val = uojLocaleData[name][locale];
	if (!$.isFunction(val)) {
		return val;
	} else {
		var args = [];
		for (var i = 1; i < arguments.length; i++) {
			args.push(arguments[i]);
		}
		return val.apply(this, args);
	}
}

// utility
function strToDate(str) {
	var a = str.split(/[^0-9]/);
	return new Date(
		parseInt(a[0]),
		parseInt(a[1]) - 1,
		parseInt(a[2]),
		parseInt(a[3]),
		parseInt(a[4]),
		parseInt(a[5]),
		0);
}
function dateToStr(date) {
	return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
}
function toFilledStr(o, f, l) {
	var s = o.toString();
	while (s.length < l) {
		s = f.toString() + s;
	}
	return s;
}
function getPenaltyTimeStr(x) {
	var ss = toFilledStr(x % 60, '0', 2);
	x = Math.floor(x / 60);
	var mm = toFilledStr(x % 60, '0', 2);
	x = Math.floor(x / 60);
	var hh = x.toString();
	return hh + ':' + mm + ':' + ss;
}

function htmlspecialchars(str)
{
	var s = "";
	if (str.length == 0) return "";
	s = str.replace(/&/g, "&amp;");
	s = s.replace(/</g, "&lt;");
	s = s.replace(/>/g, "&gt;");
	s = s.replace(/"/g, "&quot;");
	return s;
}

function getColOfRating(rating) {
	if (rating < 1500) {
		var H = 300 - (1500 - 850) * 300 / 1650, S = 30 + (1500 - 850) * 70 / 1650, V = 50 + (1500 - 850) * 50 / 1650;
		if (rating < 300) rating = 300;
		var k = (rating - 300) / 1200;
		return ColorConverter.toStr(ColorConverter.toRGB(new HSV(H + (300 - H) * (1 - k), 30 + (S - 30) * k, 50 + (V - 50) * k)));
	}
	if (rating > 2500) {
		rating = 2500;
	}
	return ColorConverter.toStr(ColorConverter.toRGB(new HSV(300 - (rating - 850) * 300 / 1650, 30 + (rating - 850) * 70 / 1650, 50 + (rating - 850) * 50 / 1650)));
}
function getColOfScore(score) {
	if (score == 0) {
		return ColorConverter.toStr(ColorConverter.toRGB(new HSV(352, 74, 85)));
	} else if (score == 100) {
		return ColorConverter.toStr(ColorConverter.toRGB(new HSV(119, 84, 75)));
	} else {
		return ColorConverter.toStr(ColorConverter.toRGB(new HSV(score*119/100, 74+score/10, 75+score/10)));
	}
}

function getUserLink(username, rating, email, isSuper) {
	if (!username) {
		return '';
	}
	var text = username;
	if (username.charAt(0) == '@') {
		username = username.substr(1);
	}
	if (isSuper == 1){
		return '<nobr><span class="hoj-super-border"><a target="_blank" class="uoj-username" href="' + uojHome + '/user/profile/' + username + '" style="color:' + getColOfRating(rating) + '">' + text + '</a>&nbsp;<span class="hoj-super-tag hidden-xs">&nbsp;管理员&nbsp;</span></span></nobr>';
	}
	if (!email || email == '@'){
		email= text;
	}
	var link_class = username + String(Math.random()).substr(2), ret="";
	ret += '<nobr><a target="_blank" class="uoj-username ' + link_class + '" href="' + uojHome + '/user/profile/' + username + '" style="color:' + getColOfRating(rating) + '">' + text + '</a></nobr>';
	ret += '<script>';
	ret += "$('." + link_class + "').css('min-width',$('." + link_class + "').css('width'));";
	ret += "$('." + link_class + "').mouseover(function(){";
	ret += "$(this).html('" + email + "');";
	ret += "});";
	ret += "$('." + link_class + "').mouseout(function(){";
	ret += "$(this).html('" + text + "');";
	ret += "});";
	ret += "</script>";
	return ret;
}
function getUserSpan(username, rating) {
	if (!username) {
		return '';
	}
	var text = username;
	if (username.charAt(0) == '@') {
		username = username.substr(1);
	}
	return '<span class="uoj-username" style="color:' + getColOfRating(rating) + '">' + text + '</span>';
}

function replaceWithHighlightUsername() {
	var username = $(this).text();
	var rating = $(this).data("rating");
	if (isNaN(rating)) {
		return;
	}
	if ($(this).data("link") != 0) {
		$(this).replaceWith(getUserLink(username, rating, $(this).data("email"), $(this).data("super")));
	} else {
		$(this).replaceWith(getUserSpan(username, rating));
	}
}

$.fn.uoj_honor = function() {
	return this.each(function() {
		var honor = $(this).text();
		var rating = $(this).data("rating");
		if (isNaN(rating)) {
			return;
		}
		$(this).css("color", getColOfRating(rating)).html(honor);
	});
}

function showErrorHelp(name, err) {
	if (err) {
		$('#div-' + name).addClass('has-error');
		$('#help-' + name).text(err);
		return false;
	} else {
		$('#div-' + name).removeClass('has-error');
		$('#help-' + name).text('');
		return true;
	}
}
function getFormErrorAndShowHelp(name, val) {
	var err = val($('#input-' + name).val());
	return showErrorHelp(name, err);
}

function validateSettingPassword(str) {
	if (str.length < 6) {
		return '密码长度不应小于6。';
	} else if (! /^[!-~]+$/.test(str)) {
		return '密码应只包含可见ASCII字符。';
	} else if (str != $('#input-confirm_password').val()) {
		return '两次输入的密码不一致。';
	} else {
		return '';
	}
}
function validatePassword(str) {
	if (str.length < 6) {
		return '密码长度不应小于6。';
	} else if (! /^[!-~]+$/.test(str)) {
		return '密码应只包含可见ASCII字符。';
	} else {
		return '';
	}
}
function validateEmail(str) {
	if (str.length > 50) {
		return '电子邮箱地址太长。';
	} else if (! /^(.+)@(.+)$/.test(str)) {
		return '电子邮箱地址非法。';
	} else {
		return '';
	}
}
function validateUsername(str) {
	if (str.length == 0) {
		return '用户名不能为空。';
	} else if (! /^[a-zA-Z0-9_]+$/.test(str)) {
		return '用户名应只包含大小写英文字母、数字和下划线。';
	} else {
		return '';
	}
}
function validateQQ(str) {
	if (str.length < 5) {
		return 'QQ的长度不应小于5。';
	} else if (str.length > 15) {
		return 'QQ的长度不应大于15。';
	} else if (/\D/.test(str)) {
		return 'QQ应只包含0~9的数字。';
	} else {
		return '';
	}
}
function validateMotto(str) {
	if (str.length > 50) {
		return '不能超过50字';
	} else {
		return '';
	}
}

// tags
$.fn.uoj_problem_tag = function() {
	return this.each(function() {
		$(this).attr('href', uojHome + '/problems?tag=' + encodeURIComponent($(this).text()));
	});
}
$.fn.uoj_blog_tag = function() {
	return this.each(function() {
		$(this).attr('href', '/archive?tag=' + encodeURIComponent($(this).text()));
	});
}

// click zan
function click_zan(zan_id, zan_type, zan_delta, node) {
	var loading_node = $('<div class="text-muted">loading...</div>');
	$(node).replaceWith(loading_node);
	$.post('/click-zan', {
		id : zan_id,
		delta : zan_delta,
		type : zan_type
	}, function(ret) {
		$(loading_node).replaceWith($(ret).click_zan_block());
	}).fail(function() {
		$(loading_node).replaceWith('<div class="text-danger">failed</div>');
	});
}

$.fn.click_zan_block = function() {
    return this.each(function() {
        var id = $(this).data('id');
        var type = $(this).data('type');
        var val = parseInt($(this).data('val'));
        var cnt = parseInt($(this).data('cnt'));
        if (isNaN(cnt)) {
            return;
        }
        if (val == 1) {
            $(this).addClass('uoj-click-zan-block-cur-up');
        } else if (val == 0) {
            $(this).addClass('uoj-click-zan-block-cur-zero');
        } else if (val == -1) {
            $(this).addClass('uoj-click-zan-block-cur-down');
        } else {
            return;
        }
        if (cnt > 0) {
            $(this).addClass('uoj-click-zan-block-positive');
        } else if (cnt == 0) {
            $(this).addClass('uoj-click-zan-block-neutral');
        } else {
            $(this).addClass('uoj-click-zan-block-negative');
        }

        var node = this;
        var up_node = $('<a href="#" class="uoj-click-zan-up"><span class="glyphicon glyphicon-thumbs-up"></span>'+'</a>').click(function(e) {
            e.preventDefault();
            click_zan(id, type, 1, node);
        });
        var down_node = $('<a href="#" class="uoj-click-zan-down"><span class="glyphicon glyphicon-thumbs-down"></span>'+'</a>').click(function(e) {
            e.preventDefault();
            click_zan(id, type, -1, node);
        });

        $(this)
            .append(up_node)
            .append($('<span class="uoj-click-zan-cnt">&nbsp;<strong>' + (cnt > 0 ? '+' + cnt : cnt) + '</strong>&nbsp;</span>'))
            .append(down_node);
    });
}


// count down
function getCountdownStr(t) {
	var x = Math.floor(t);
	var ss = toFilledStr(x % 60, '0', 2);
	x = Math.floor(x / 60);
	var mm = toFilledStr(x % 60, '0', 2);
	x = Math.floor(x / 60);
	var hh = x.toString();
	
	var res = '<span style="font-size:30px">';
	res += '<span style="color:' + getColOfScore(Math.min(t / 10800 * 100, 100)) + '">' + hh + '</span>';
	res += ':';
	res += '<span style="color:' + getColOfScore(mm / 60 * 100) + '">' + mm + '</span>';
	res += ':';
	res += '<span style="color:' + getColOfScore(ss / 60 * 100) + '">' + ss + '</span>';
	res += '</span>'
	return res;
}

$.fn.countdown = function(rest, callback) {
	return this.each(function() {
		var start = new Date().getTime();
		var cur_rest = rest != undefined ? rest : parseInt($(this).data('rest'));
		var cur = this;
		var countdown = function() {
			var passed = Math.floor((new Date().getTime() - start) / 1000);
			if (passed >= cur_rest) {
				$(cur).html(getCountdownStr(0));
				if (callback != undefined) {
					callback();
				}
			} else {
				$(cur).html(getCountdownStr(cur_rest - passed));
				setTimeout(countdown, 1000);
			}
		}
		countdown();
	});
};

// update_judgement_status
update_judgement_status_list = []
function update_judgement_status_details(id) {
	update_judgement_status_list.push(id);
};

$(document).ready(function() {
	function update() {
		$.get("/submission-status-details", {
				get: update_judgement_status_list
			},
			function(data) {
				for (var i = 0; i < update_judgement_status_list.length; i++) {
					$("#status_details_" + update_judgement_status_list[i]).html(data[i].html);
					if (data[i].judged) {
						location.reload();
					}
				}
			}, 'json').always(
			function() {
    			setTimeout(update, 500);
	    	}
	    );
	}
	if (update_judgement_status_list.length > 0) {
		setTimeout(update, 500);
	}
});

// highlight
$.fn.uoj_highlight = function() {
	return $(this).each(function() {
		$(this).find("span.uoj-username").each(replaceWithHighlightUsername);
		$(this).find(".uoj-honor").uoj_honor();
		$(this).find(".uoj-score").each(function() {
			var score = parseInt($(this).text());
			var maxscore = parseInt($(this).data('max'));
			if (isNaN(score)) {
				return;
			}
			if (isNaN(maxscore)) {
				$(this).css("color", getColOfScore(score));
			} else {
				$(this).css("color", getColOfScore(score / maxscore * 100));
			}
		});
		$(this).find(".uoj-status").each(function() {
			var success = parseInt($(this).data("success"));
			if(isNaN(success)){
				return;
			}
			if (success == 1) {
				$(this).css("color", ColorConverter.toStr(ColorConverter.toRGB(new HSV(120, 100, 80))));
			}
			else {
				$(this).css("color", ColorConverter.toStr(ColorConverter.toRGB(new HSV(0, 100, 100))));
			}
		});
		$(this).find(".uoj-problem-tag").uoj_problem_tag();
		$(this).find(".uoj-blog-tag").uoj_blog_tag();
		$(this).find(".uoj-click-zan-block").click_zan_block();
		$(this).find(".countdown").countdown();
	});
};

$(document).ready(function() {
	$('body').uoj_highlight();
});

// contest notice
function checkContestNotice(id, lastTime) {
	$.post('/contest/' + id.toString(), {
			check_notice : '',
			last_time : lastTime
		},
		function(data) {
			setTimeout(function() {
				checkContestNotice(id, data.time);
			}, 60000);
			if (data.msg != undefined) {
				alert(data.msg);
			}
		},
		'json'
	).fail(function() {
		setTimeout(function() {
			checkContestNotice(id, lastTime);
		}, 60000);
	});
}

// long table
$.fn.long_table = function(data, cur_page, header_row, get_row_str, config) {
	return this.each(function() {
		var table_div = this;
		
		$(table_div).html('');
		
		var page_len = config.page_len != undefined ? config.page_len : 10;
		
		if (!config.echo_full) {
			var n_rows = data.length;
			var n_pages = Math.max(Math.ceil(n_rows / page_len), 1);
			if (cur_page == undefined) {
				cur_page = 1;
			}
			if (cur_page < 1) {
				cur_page = 1;
			} else if (cur_page > n_pages) {
				cur_page = n_pages;
			}
			var cur_start = (cur_page - 1) * page_len;
		} else {
			var n_rows = data.length;
			var n_pages = 1;
			cur_page = 1;
			var cur_start = (cur_page - 1) * page_len;
		}
		
		var div_classes = config.div_classes != undefined ? config.div_classes : ['table-responsive'];
		var table_classes = config.table_classes != undefined ? config.table_classes : ['table', 'table-bordered', 'table-hover', 'table-striped', 'table-text-center'];
		
		var now_cnt = 0;
		var tbody = $('<tbody />')
		for (var i = 0; i < page_len && cur_start + i < n_rows; i++) {
			now_cnt++;
			if (config.get_row_index) {
				tbody.append(get_row_str(data[cur_start + i], cur_start + i));
			} else {
				tbody.append(get_row_str(data[cur_start + i]));
			}
		}
		if (now_cnt == 0) {
			tbody.append('<tr><td colspan="233">无</td></tr>');
		}
		
		$(table_div).append(
			$('<div class="' + div_classes.join(' ') + '" />').append(
				$('<table class="' + table_classes.join(' ') + '" />').append(
					$('<thead>' + header_row + '</thead>')
				).append(
					tbody
				)
			)
		);
		
		if (config.print_after_table != undefined) {
			$(table_div).append(config.print_after_table());
		}
		
		var get_page_li = function(p, h) {
			if (p == -1) {
				return $('<li></li>').addClass('disabled').append($('<a></a>').append(h));
			}
			
			var li = $('<li></li>');
			if (p == cur_page) {
				li.addClass('active');
			}
			li.append(
				$('<a></a>').attr('href', '#' + table_div.id).append(h).click(function(e) {
					if (config.prevent_focus_on_click) {
						e.preventDefault();
					}
					$(table_div).long_table(data, p, header_row, get_row_str, config);
				})
			);
			return li;
		};
		
		if (n_pages > 1) {
			var pagination = $('<ul class="pagination top-buffer-no bot-buffer-sm"></ul>');
			if (cur_page > 1) {
				pagination.append(get_page_li(1, '<span class="glyphicon glyphicon glyphicon-fast-backward"></span>'));
				pagination.append(get_page_li(cur_page - 1, '<span class="glyphicon glyphicon glyphicon-backward"></span>'));
			} else {
				pagination.append(get_page_li(-1, '<span class="glyphicon glyphicon glyphicon-fast-backward"></span>'));
				pagination.append(get_page_li(-1, '<span class="glyphicon glyphicon glyphicon-backward"></span>'));
			}
			var max_extend = config.max_extend != undefined ? config.max_extend : 5;
			for (var i = Math.max(cur_page - max_extend, 1); i <= Math.min(cur_page + max_extend, n_pages); i++) {
				pagination.append(get_page_li(i, i.toString()));
			}
			if (cur_page < n_pages) {
				pagination.append(get_page_li(cur_page + 1, '<span class="glyphicon glyphicon glyphicon-forward"></span>'));
				pagination.append(get_page_li(n_pages, '<span class="glyphicon glyphicon glyphicon-fast-forward"></span>'));
			} else {
				pagination.append(get_page_li(-1, '<span class="glyphicon glyphicon glyphicon-forward"></span>'));
				pagination.append(get_page_li(-1, '<span class="glyphicon glyphicon glyphicon-fast-forward"></span>'));
			}
			var page_jump = $('<form style="display:inline-block;vertical-align:top;position: relative;"></form>');
			page_jump.append('<input type="text" class="form-control input-sm" id="input-page-jump" style="display: inline;width: 55px;">&nbsp;页&nbsp;');
			page_jump.append('<button type="submit" class="btn btn-primary glyphicon glyphicon-share-alt" style="vertical-align:baseline"></button>');
			page_jump.submit(function(e){
				e.preventDefault();
				if(!isNaN($('#input-page-jump').val())){
					$(document).scrollTop(0);
					$(table_div).long_table(data, Number($('#input-page-jump').val()), header_row, get_row_str, config);
				}
			});
			pagination.append(page_jump);
			$(table_div).append($('<div class="text-center"></div>').append(pagination));
		}
	});
};

// comment
function showCommentReplies(id, replies) {
	var toggleFormReply = function(from, text) {
		if (text == undefined) {
			text = '';
		}
		
		var p = '#comment-body-' + id;
		var q = '#div-form-reply';
		var r = '#input-reply_comment';
		var t = '#input-reply_id';
		if ($(q).data('from') != from) {
			$(q).data('from', from);
			$(q).hide('fast', function() {
				$(this).appendTo(p).show('fast', function() {
					$(t).val(id);
					$(r).val(text).focus();
				});
			});

		} else if ($(q).css('display') != 'none') {
			$(q).appendTo(p).hide('fast');
		} else {
			$(q).appendTo(p).show('fast', function() {
				$(t).val(id);
				$(r).val(text).focus();
			});
		}
	}

	$('#reply-to-' + id).click(function(e) {
		e.preventDefault();
		toggleFormReply(id);
	});
	
	if (replies.length == 0) {
		return;
	}
	
	$("#replies-" + id).long_table(
		replies,
		1,
		'<tr>' +
			'<th>评论回复</th>' +
		'</tr>',
		function(reply) {
			return $('<tr id="' + 'comment-' + reply.id + '" />').append(
				$('<td />').append(
					$('<div class="comtbox6">' + getUserLink(reply.poster, reply.poster_rating, reply.poster_email, reply.poster_super) + '：' + reply.content + '</div>')
				).append(
					$('<ul class="text-right list-inline bot-buffer-no" />').append(
						'<li>' + '<small class="text-muted">' + reply.post_time + '</small>' + '</li>'
					).append(
						$('<li />').append(
							$('<a href="#">回复</a>').click(function (e) {
								e.preventDefault();
								toggleFormReply(reply.id, '回复 @' + reply.poster + '：');
							})
						)
					)
				)
			).uoj_highlight();
		}, {
			table_classes: ['table', 'table-condensed'],
			page_len: 5,
			prevent_focus_on_click: true
		}
	);
}

// standings
function showStandings() {
	$("#standings").long_table(standings,1,
		'<tr>' +
			'<th style="width:5em">#</th>' +
			'<th style="width:14em">'+uojLocale('username')+'</th>' +
			'<th style="width:5em">'+uojLocale('contests::total score')+'</th>' +
			$.map(problems, function(col, idx) {
				if(col > 0){
					return '<th style="width:8em;">' + '<a href="/contest/' + contest_id + '/problem/' + col + '">' + String.fromCharCode('A'.charCodeAt(0) + idx) + '</a>' + '</th>';
				}else{
					return '<th style="width:8em;">' + String.fromCharCode('A'.charCodeAt(0) + idx) + '</th>';
				}
			}).join('') +
		'</tr>',
		function(row) {
			var col_tr = '<tr>';
			col_tr += '<td>' + row[3] + '</td>';
			col_tr += '<td>' + getUserLink(row[2][0], row[2][1], row[2][2], row[2][3]) + '</td>';
			col_tr += '<td>' + '<div><span class="uoj-score" data-max="' + problems.length * 100 + '" style="color:' + getColOfScore(row[0] / problems.length) + '">' + row[0] + '</span></div>' + '<div>';
			if (standings_version < 2) {
				col_tr += getPenaltyTimeStr(row[1]);
			}else{
				col_tr += row[1] + 'ms';
			}
			col_tr += '</div></td>';
			for (var i = 0; i < problems.length; i++) {
				col_tr += '<td>';
				col = score[row[2][0]][i];
				if (col != undefined) {
					if(problems[i] > 0){
						col_tr += '<div><a href="/submission/' + col[2] + '" class="uoj-score" style="color:' + getColOfScore(col[0]) + '">' + col[0] + '</a></div>';
					}else{
						col_tr += '<div><span class="uoj-score" style="color:' + getColOfScore(col[0]) + '">' + col[0] + '</span></div>';
					}
					if (standings_version < 2) {
						col_tr += '<div>' + getPenaltyTimeStr(col[1]) + '</div>';
					} else {
						col_tr += '<div>' + col[1] + 'ms</div>';
					}
				}
				col_tr += '</td>';
			}
			col_tr += '</tr>';
			return col_tr;
		}, {
			table_classes: ['table', 'table-bordered', 'table-striped', 'table-text-center', 'table-vertical-middle', 'table-condensed'],
			page_len: 50,
			print_after_table: function() {
				return '<div class="text-right text-muted">' + uojLocale("contests::n participants", standings.length) + '</div>';
			}
		}
	);
}

function showExerciseStandings() {
	$("#standings").long_table(standings,1,
		'<tr>' +
			'<th style="width:14em">'+uojLocale('username')+'</th>' +
			'<th style="width:4em">'+uojLocale('contests::total score')+'</th>' +
			'<th style="width:8em;">完成时间</th>' +
			$.map(problems, function(col, idx) {
				if(col>0){
					return '<th style="width:4em;">' + '<a href="/problem/' + col + '">' + String.fromCharCode('A'.charCodeAt(0) + idx) + '</a>' + '</th>';
				}else{
					return '<th style="width:4em;">' + '<span>' + String.fromCharCode('A'.charCodeAt(0) + idx) + '</span>' + '</th>';
				}
			}).join('') +
		'</tr>',
		function(row) {
			var col_tr = '<tr>';
			col_tr += '<td>' + getUserLink(row[2][0], row[2][1], row[2][2], row[2][3]) + '</td>';
			col_tr += '<td>' + '<span class="uoj-score" data-max="' + problems.length * 100 + '" style="color:' + getColOfScore(row[0] / problems.length) + '">' + row[0] + '</span>' + '</td>';
			col_tr += '<td>' + row[1] + '</td>';
			for (var i = 0; i < problems.length; i++) {
				col_tr += '<td>';
				if (row[i+3] != -1) {
					if(problems[i] > 0) {
						col_tr += '<a href="/submissions?submitter=' + row[2][0] + '&problem_id=' + problems[i] + '">';
					}
					col_tr += '<span class="uoj-score" style="color:' + getColOfScore(row[i+3]) + '">' + row[i+3] + '</span>';
					if(problems[i] > 0) {
						col_tr += '</a>';
					}
				}
				col_tr += '</td>';
			}
			col_tr += '</tr>';
			return col_tr;
		}, {
			table_classes: ['table', 'table-bordered', 'table-striped', 'table-text-center', 'table-vertical-middle', 'table-condensed'],
			page_len: 50
		}
	);
}

//Autocomplete username
function load_user_list() {
	if(window.user_list == undefined){
		window.user_list = [];
		$.post('/user/list',function(data){
			window.user_list=data;
		},'json');
	}
}
function update_user_list(input, list) {
	if(window.user_list.length == 0) {
		list.html('<li>正在加载用户列表……</li>');
		return;
	}
	var val = eval('/^' + input.val() + '/i'), li;
	list.empty();
	for(var i=0;i<window.user_list.length;i++)
		if(val.test(window.user_list[i][0])){
			li = $('<li></li>');
			li.html(getUserSpan(window.user_list[i][0],window.user_list[i][1]));
			li.click(function(){
				input.val($(this).children('span').text());
				update_user_list(input, list);
			});
			list.append(li);
		}
}
$.fn.autouser = function() {
	$(this).each(function() {
		load_user_list();
		
		var show_list = $('<ul></ul>');
		show_list.addClass('autocomplete');
		show_list.css('display','none');
		show_list.css('width',$(this).css('width'));
		
		$(this).attr('autocomplete','off');
		$(this).after(show_list);
		$(this).focus(function(){
			update_user_list($(this), show_list);
			show_list.fadeIn('fast');
		});
		$(this).blur(function(){
			show_list.fadeOut('fast');
		});
		$(this).on('input',function(){
			update_user_list($(this), show_list);
		});
		var myself = $(this);
		$(window).resize(function() {
			show_list.css('width',myself.css('width'));
		});
	});
};
