<form method="post" class="form-horizontal" id="form-<?= $editor->name ?>" enctype="multipart/form-data">
<?= HTML::hiddenToken() ?>
<div class="row">
	<div class="col-sm-6">
		<?= HTML::div_vinput("{$editor->name}_title", 'text', $editor->label_text['title'], html_entity_decode($editor->cur_data['title'])) ?>
	</div>
	<div class="col-sm-6">
	<?php if ($editor->cur_data['is_exercise_editor']==NULL): ?>
		<?= HTML::div_vinput("{$editor->name}_tags", 'text', $editor->label_text['tags'], join(', ', $editor->cur_data['tags'])) ?>
	<?php else: ?>
		<input type="text" class="form-control" name="_tags" id="input-_tags" value="<?=join(', ', $editor->cur_data['tags'])?>" style="display: none;">
	<?php endif ?>
	</div>
</div>
<?= HTML::div_vtextarea("{$editor->name}_content_md", $editor->label_text['content'], $editor->cur_data['content_md']) ?>
<div class="row">
	<div class="col-sm-2">
		<?php if ($editor->blog_url): ?>
		<a id="a-<?= $editor->name ?>_view_blog" class="btn btn-info" href="<?= HTML::escape($editor->blog_url) ?>"><?= $editor->label_text['view blog'] ?></a>
		<?php else: ?>
		<a id="a-<?= $editor->name ?>_view_blog" class="btn btn-info" style="display: none;"><?= $editor->label_text['view blog'] ?></a>
		<?php endif ?>
	</div>
	<div class="col-sm-4">
		<?php
			if($editor->type == 'slide'){
				$themes=array('beige','black','blood','league','moon','night','serif','simple','sky','solarized','white');
				$theme = explode("\n" , $editor->cur_data['content'])[0];
				if(!in_array($theme,$themes)){
					$theme = 'moon';
				}
				
				echo <<<EOD
<div id="div-{$editor->name}_theme">
	<label for="input-{$editor->name}_theme" class="control-label">选择幻灯片模板</label>
	<select class="form-control" id="input-{$editor->name}_theme" name="{$editor->name}_theme">
EOD;
				foreach ($themes as $val) {
					if ($val != $theme) {
						echo '<option value="',$val,'">',$val,'</option>';
					} else {
						echo '<option value="',$val,'" selected="selected">',$val,'</option>';
					}
				}
				echo '</select></div>';
			}
		?>
	</div>
	<div class="col-sm-6 text-right">
		<?php if ($editor->cur_data['is_exercise_editor']==NULL): ?>
		<?= HTML::checkbox("{$editor->name}_is_hidden", $editor->cur_data['is_hidden']) ?>
		<?php endif ?>
	</div>
</div>
</form>
<script type="text/javascript">
<?php if ($editor->cur_data['is_exercise_editor']==NULL): ?>
$('#<?= "input-{$editor->name}_is_hidden" ?>').bootstrapSwitch({
	onText: <?= json_encode($editor->label_text['private']) ?>,
	onColor: 'danger',
	offText: <?= json_encode($editor->label_text['public']) ?>,
	offColor: 'primary',
	labelText: <?= json_encode($editor->label_text['blog visibility']) ?>,
	handleWidth: 100
});
<?php endif ?>
blog_editor_init("<?= $editor->name ?>", <?= json_encode(array('type' => $editor->type)) ?>);
</script>
