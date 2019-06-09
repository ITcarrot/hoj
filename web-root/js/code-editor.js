function get_codemirror_mode(lang) {
	switch (lang) {
		case 'C++':
			return 'text/x-c++src';
		case 'C':
			return 'text/x-csrc';
		case 'Python2.7':
		case 'Python3':
			return 'text/x-python';
		case 'Pascal':
			return 'text/x-pascal';
		default:
			return 'text/plain';
	}
};
// auto save
function autosave_locally(interval, name, target) {
	if (typeof(Storage) === "undefined") {
		console.log('autosave_locally: Sorry! No Web Storage support..');
		return;
	}
	var url = window.location.href;
	var hp = url.indexOf('#');
	var uri = hp == -1 ? url : url.substr(0, hp);
	var full_name = name + '@' + uri;

	target.val(localStorage.getItem(full_name));
	var save = function() {
		localStorage.setItem(full_name, target.val());
		setTimeout(save, interval);
	};
	setTimeout(save, interval);
}
// source code form group
$.fn.source_code_form_group = function(name, text, langs_options_html) {
	return this.each(function() {
		var input_language_id = 'input-' + name + '_language';
		var input_language_name = name + '_language';
		var input_optimized_id = 'input-' + name + '_optimized';
		var input_optimized_name = name + '_optimized';
		var input_cstandard_id = 'input-' + name + '_cstandard';
		var input_cstandard_name = name + '_cstandard';
		var input_upload_type_name = name + '_upload_type';
		var input_editor_id = 'input-' + name + '_editor';
		var input_editor_name = name + '_editor';
		var input_file_id = 'input-' + name + '_file';
		var input_file_name = name + '_file';

		var div_editor_id = 'div-' + name + '_editor';
		var div_file_id = 'div-' + name + '_file';

		var help_file_id = 'help-' + name + '_file';

		var input_language =
			$('<select id="' + input_language_id + '" name="' + input_language_name + '" class="form-control input-sm"/>')
				.html(langs_options_html);
		var input_optimized = $('<select id="' + input_optimized_id + '" name="' + input_optimized_name + '" class="form-control input-sm"/>');
		var input_cstandard = $('<select id="' + input_cstandard_id + '" name="' + input_cstandard_name + '" class="form-control input-sm"/>');
		var input_upload_type_editor = $('<input type="radio" name="' + input_upload_type_name + '" value="editor" />');
		var input_upload_type_file = $('<input type="radio" name="' + input_upload_type_name + '" value="file" />');
		var input_file = $('<input type="file" id="' + input_file_id + '" name="' + input_file_name + '" style="display: none" />');
		var input_file_path = $('<input class="form-control" type="text" readonly="readonly" />');
		var input_editor = $('<textarea class="form-control" id="' + input_editor_id + '" name="' + input_editor_name + '"></textarea>');
		var input_use_advanced_editor = $('<input type="checkbox">');

		var div_language = $('<div class="col-sm-2"/>')
			.append($('<label class="control-label" for="' + input_language_name + '">'+uojLocale('editor::language')+'</label>'))
			.append(input_language);
		var div_optimized = $('<div class="col-sm-2"/>')
			.append($('<label class="control-label" for="' + input_optimized_name + '">优化级别</label>'))
			.append(input_optimized);
		var div_cstandard = $('<div class="col-sm-2"/>')
			.append($('<label class="control-label" for="' + input_cstandard_name + '">语言标准</label>'))
			.append(input_cstandard);
		var div_editor =
			$('<div id="' + div_editor_id + '" class="col-sm-12 top-buffer-md"/>')
				.append(input_editor)
				.append($('<div class="checkbox text-right" />')
					.append($('<label />')
						.append(input_use_advanced_editor)
						.append(' ' + uojLocale('editor::use advanced editor'))
					)
				)
		var div_file =
			$('<div id="' + div_file_id + '" class="col-sm-12 top-buffer-md"/>')
				.append(input_file)
				.append($('<div class="input-group"/>')
					.append(input_file_path)
					.append($('<span class="input-group-btn"/>')
						.append($('<button type="button" class="btn btn-primary">'+'<span class="glyphicon glyphicon-folder-open"></span> '+uojLocale('editor::browse')+'</button>')
							.css('width', '100px')
							.click(function() {
								input_file.click();
							})
						)
					)
				)
				.append($('<span class="help-block" id="' + help_file_id + '"></span>'))

		var advanced_editor = null;
		var advanced_editor_init = function() {
			var mode = get_codemirror_mode(input_language.val());
			if (advanced_editor != null) {
				return;
			}
			advanced_editor = CodeMirror.fromTextArea(input_editor[0], {
				mode: mode,
				lineNumbers: true,
				lineWrapping: true,
				indentUnit: 4,
				indentWithTabs: true,
				theme: 'default',
				matchBrackets: true,
				autoCloseBrackets: true,
				foldGutter: true,
				gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
				extraKeys: {
					"Ctrl-/": "toggleComment",
					"F11": function(cm) {
					  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
					},
					"Esc": function(cm) {
					  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
					}
				},
				continueComments:true,
				styleActiveLine: true
			});
			advanced_editor.on('change', function() {
				advanced_editor.save();
			});
			$(advanced_editor.getWrapperElement()).css('box-shadow', '0 2px 10px rgba(0,0,0,0.2)');
			advanced_editor.focus();
		}

		autosave_locally(2000, name, input_editor);

		input_upload_type_editor[0].checked = true;
		div_file.css('display', 'none');
		input_use_advanced_editor[0].checked = true;

		var prefer_lang_option = $.cookie('hoj_prefer_lang_option') || '{"Pascal":["Default"],"C":["Default","Default"],"C++":["Default","Default"]}';
		prefer_lang_option = JSON.parse(prefer_lang_option);
		var show_lang_option = function() {
			if(/^Python/.test($(this).val())){
				div_cstandard.fadeTo('normal',0);
				div_optimized.fadeTo('normal',0);
			}else if($(this).val() == 'Pascal'){
				div_cstandard.fadeTo('normal',0);
				input_optimized.empty();
				input_optimized.append('<option>Default</option>')
					.append('<option>-O1</option>')
					.append('<option>-O2</option>')
					.append('<option>-O3</option>')
					.append('<option>-O4</option>');
				input_optimized.val(prefer_lang_option[$(this).val()][0]);
				div_optimized.fadeTo('normal',1);
			}else{
				input_optimized.empty();
				input_optimized.append('<option>Default</option>')
					.append('<option>-O1</option>')
					.append('<option>-O2</option>')
					.append('<option>-O3</option>')
					.append('<option>-Ofast</option>');
				input_optimized.val(prefer_lang_option[$(this).val()][0]);
				div_optimized.fadeTo('normal',1);
				if($(this).val() == 'C'){
					input_cstandard.empty();
					input_cstandard.append('<option>Default</option>')
						.append('<option>-std=c90</option>')
						.append('<option>-std=c99</option>')
						.append('<option>-std=c11</option>');
					div_cstandard.fadeTo('normal',1);
				}else{
					input_cstandard.empty();
					input_cstandard.append('<option>Default</option>')
						.append('<option>-std=c++98</option>')
						.append('<option>-std=c++03</option>')
						.append('<option>-std=c++11</option>')
						.append('<option>-std=c++14</option>')
						.append('<option>-std=c++17</option>');
					div_cstandard.fadeTo('normal',1);
				}
				input_cstandard.val(prefer_lang_option[$(this).val()][1]);
			}
		};
		input_language.each(show_lang_option);
		input_language.change(show_lang_option);
		input_language.change(function() {
			if (advanced_editor != null) {
				var mode = get_codemirror_mode(input_language.val());
				advanced_editor.setOption('mode', mode);
			}
		});
		input_optimized.change(function() {
			prefer_lang_option[input_language.val()][0] = $(this).val();
			$.cookie('hoj_prefer_lang_option', JSON.stringify(prefer_lang_option), { expires: 7, path: '/' });
		});
		input_cstandard.change(function() {
			prefer_lang_option[input_language.val()][1] = $(this).val();
			$.cookie('hoj_prefer_lang_option', JSON.stringify(prefer_lang_option), { expires: 7, path: '/' });
		});
		
		input_upload_type_editor.click(function() {
			div_editor.show('fast');
			div_file.hide('fast');
		});
		input_upload_type_file.click(function() {
			div_file.show('fast');
			div_editor.hide('fast');
		});
		input_file.change(function() {
			input_file_path.val(input_file.val());
		});
		input_use_advanced_editor.click(function() {
			if (this.checked) {
				advanced_editor_init();
			} else {
				if (advanced_editor != null) {
					advanced_editor.toTextArea();
					advanced_editor = null;
					input_editor.focus();
				}
			}
		});

		$(this)
			.append($('<label class="col-sm-2 control-label"><div class="text-left">' + text + '</div></label>'))
			.append(div_language)
			.append(div_optimized)
			.append(div_cstandard)
			.append($('<div class="col-sm-2 radio"/>')
				.append($('<label/>')
					.append(input_upload_type_editor)
					.append(' '+uojLocale('editor::upload by editor'))
				)
			)
			.append($('<div class="col-sm-2 radio"/>')
				.append($('<label/>')
					.append(input_upload_type_file)
					.append(' '+uojLocale('editor::upload from local'))
				)
			)
			.append(div_editor)
			.append(div_file);

		var check_advanced_init = function() {
			if (div_editor.is(':visible')) {
				advanced_editor_init();
			} else {
				setTimeout(check_advanced_init, 1);
			}
		}
		check_advanced_init();
	});
}

// text file form group
$.fn.text_file_form_group = function(name, text) {
	return this.each(function() {
		var input_upload_type_name = name + '_upload_type';
		var input_editor_id = 'input-' + name + '_editor';
		var input_editor_name = name + '_editor';
		var input_file_id = 'input-' + name + '_file';
		var input_file_name = name + '_file';

		var div_editor_id = 'div-' + name + '_editor';
		var div_file_id = 'div-' + name + '_file';

		var help_file_id = 'help-' + name + '_file';

		var input_upload_type_editor = $('<input type="radio" name="' + input_upload_type_name + '" value="editor" />');
		var input_upload_type_file = $('<input type="radio" name="' + input_upload_type_name + '" value="file" />');
		var input_file = $('<input type="file" id="' + input_file_id + '" name="' + input_file_name + '" style="display: none" />');
		var input_file_path = $('<input class="form-control" type="text" readonly="readonly" />');
		var input_editor = $('<textarea class="form-control" id="' + input_editor_id + '" name="' + input_editor_name + '"></textarea>');
		var input_use_advanced_editor = $('<input type="checkbox">');

		var div_editor =
			$('<div id="' + div_editor_id + '" class="col-sm-12"/>')
				.append(input_editor)
				.append($('<div class="checkbox text-right" />')
					.append($('<label />')
						.append(input_use_advanced_editor)
						.append(' ' + uojLocale('editor::use advanced editor'))
					)
				)
		var div_file =
			$('<div id="' + div_file_id + '" class="col-sm-12"/>')
				.append(input_file)
				.append($('<div class="input-group"/>')
					.append(input_file_path)
					.append($('<span class="input-group-btn"/>')
						.append($('<button type="button" class="btn btn-primary">'+'<span class="glyphicon glyphicon-folder-open"></span> '+uojLocale('editor::browse')+'</button>')
							.css('width', '100px')
							.click(function() {
								input_file.click();
							})
						)
					)
				)
				.append($('<span class="help-block" id="' + help_file_id + '"></span>'))

		var advanced_editor = null;
		var advanced_editor_init = function() {
			var mode = get_codemirror_mode('text');
			if (advanced_editor != null) {
				return;
			}
			advanced_editor = CodeMirror.fromTextArea(input_editor[0], {
				mode: mode,
				lineNumbers: true,
				lineWrapping: true,
				theme: 'default',
				extraKeys: {
					"F11": function(cm) {
					  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
					},
					"Esc": function(cm) {
					  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
					}
				},
				styleActiveLine: true
			});
			advanced_editor.on('change', function() {
				advanced_editor.save();
			});
			$(advanced_editor.getWrapperElement()).css('box-shadow', '0 2px 10px rgba(0,0,0,0.2)');
			advanced_editor.focus();
		}

		var save_prefer_upload_type = function(type) {
			$.cookie('uoj_text_file_form_group_preferred_upload_type', type, { expires: 7, path: '/' });
		};

		autosave_locally(2000, name, input_editor);

		var prefer_upload_type = $.cookie('uoj_text_file_form_group_preferred_upload_type');
		if (prefer_upload_type === null) {
			prefer_upload_type = 'editor';
		}
		if (prefer_upload_type == 'file') {
			input_upload_type_file[0].checked = true;
			div_editor.css('display', 'none');
		} else {
			input_upload_type_editor[0].checked = true;
			div_file.css('display', 'none');

			if (prefer_upload_type == 'advanced') {
				input_use_advanced_editor[0].checked = true;
			}
		}

		input_upload_type_editor.click(function() {
			div_editor.show('fast');
			div_file.hide('fast');
			save_prefer_upload_type('editor');
		});
		input_upload_type_file.click(function() {
			div_file.show('fast');
			div_editor.hide('fast');
			save_prefer_upload_type('file');
		});
		input_file.change(function() {
			input_file_path.val(input_file.val());
		});
		input_use_advanced_editor.click(function() {
			if (this.checked) {
				advanced_editor_init();
				save_prefer_upload_type('advanced');
			} else {
				if (advanced_editor != null) {
					advanced_editor.toTextArea();
					advanced_editor = null;
					input_editor.focus();
				}
				save_prefer_upload_type('editor');
			}
		});

		$(this)
			.append($('<label class="col-sm-2 control-label"><div class="text-left">' + text + '</div></label>'))
			.append($('<div class="top-buffer-sm" />'))
			.append($('<div class="col-sm-offset-6 col-sm-2 radio"/>')
				.append($('<label/>')
					.append(input_upload_type_editor)
					.append(' '+uojLocale('editor::upload by editor'))
				)
			)
			.append($('<div class="col-sm-2 radio"/>')
				.append($('<label/>')
					.append(input_upload_type_file)
					.append(' '+uojLocale('editor::upload from local'))
				)
			)
			.append(div_editor)
			.append(div_file);

		if (prefer_upload_type == 'advanced') {
			var check_advanced_init = function() {
				if (div_editor.is(':visible')) {
					advanced_editor_init();
				} else {
					setTimeout(check_advanced_init, 1);
				}
			}
			check_advanced_init();
		}
	});
}
