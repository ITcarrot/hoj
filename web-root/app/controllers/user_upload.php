<?php
	requirePHPLib('form');
	
	if(Auth::id()==null){
		become403Page();
	}
	if(isContestUser(Auth::user())){
		becomeMsgPage('该功能不对比赛账户开放');
	}
	$siz=0;$max_siz=209715200;
	$path='/file/'.Auth::id().'/';
	$sys_path=$_SERVER['DOCUMENT_ROOT'];
	
	if(!is_dir($sys_path.$path)){
		mkdir($sys_path.$path);
	}
	
	$list=array();//(file1,file2,...)  file=>(filename, unix ctime, size, type)
	$dir=opendir($sys_path.$path);
	$finfo = finfo_open(FILEINFO_MIME);
	while($file=readdir($dir)){
		if($file!='.'&&$file!='..'){
			$file_siz = filesize($sys_path.$path.$file);
			$siz = $siz + $file_siz;
			$list[]=array($file, filectime($sys_path.$path.$file), (int)($file_siz/1024), finfo_file($finfo,$sys_path.$path.$file));
		}
	}
	closedir($dir);
	finfo_close($finfo);
	usort($list,function($l,$r){
		return $l[1]<$r[1];
	});
	
	if($_POST['problem_data_file_submit']=='submit'){
		if ($_FILES["problem_data_file"]["error"] > 0)
  		{
  			$errmsg = "Error: ".$_FILES["problem_data_file"]["error"];
			becomeMsgPage('<div>' . $errmsg . '</div><a href="/upload">返回</a>');
  		}
		else
  		{
			do{
				$up_filename=$sys_path.$path.mt_Rand(100000,999999);
			}while(file_exists($up_filename));
			move_uploaded_file($_FILES["problem_data_file"]["tmp_name"], $up_filename);
			if(filesize($up_filename)+$siz>$max_siz){
				unlink($up_filename);
				becomeMsgPage('<div>文件过大</div><a href="/user/upload">返回</a>');
			}
			header("location: /user/upload");
			die();
  		}
	}
	
	if($_POST['del']&&validateUInt($_POST['del'])&&file_exists($sys_path.$path.$_POST['del'])){
		unlink($sys_path.$path.$_POST['del']);
		header("location: /user/upload");
		die();
	}
?>
<?php echoUOJPageHeader('上传文件') ?>

<div class="modal fade" id="UploadDataModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
   		<div class="modal-content">
   			<div class="modal-header">
   				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
       			<h4 class="modal-title" id="myModalLabel">上传文件</h4>
			</div>
      		<div class="modal-body">
        		<form action="" method="post" enctype="multipart/form-data" role="form" id="upload">
  					<div class="form-group" id="div-upload">
    						<label for="exampleInputFile">文件</label>
    						<input type="file" name="problem_data_file" id="problem_data_file">
							<span class="help-block" id="help-upload"></span>
  					</div>
					<input type="hidden" name="problem_data_file_submit" value="submit">
  					<button type="submit" class="btn btn-success">上传</button>
				</form>
			</div>
			<div class="modal-footer">
   				<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
$('#upload').submit(function(e) {
	var ok = true;
	$(this).find("input[type='file']").each(function() {
		for (var i = 0; i < this.files.length; i++) {
			if (this.files[i].size > <?= $max_siz-$siz ?>) {
				$('#div-upload').addClass('has-error');
				$('#help-upload').text('文件大小不能超过<?= (int)(($max_siz-$siz)/1024) ?>Kb');
				ok = false;
			} else {
				$('#div-upload').removeClass('has-error');
				$('#help-upload').text('');
			}
		}
	});
	return ok;
});
</script>

<div class="row">
	<div class="col-lg-2">
		<h4>注意，此处上传的文件是完全公开的</h4>
		<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#UploadDataModal">上传文件</button>
	</div>
	<div class="col-lg-10">
		<h4>已用空间：<?=(int)($siz/1024)?>Kb / <?=$max_siz/1024?>Kb</h4>
		<div class="progress">
			<div class="progress-bar progress-bar-success" style="width: <?= $siz*100/$max_siz?>%; min-width: 20px;"><?= (int)($siz*100/$max_siz)?>%</div>
		</div>
		<?php foreach($list as $file): ?>
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="row">
						<div class="col-sm-3">
							<strong>地址：<a href="<?= $path.$file[0] ?>"><?= $path.$file[0] ?></a></strong>
						</div>
						<div class="col-sm-2">
							文件大小：<?= $file[2] ?>Kb
						</div>
						<div class="col-sm-3">
							文件类型：<?= $file[3] ?>
						</div>
						<div class="col-sm-3">
							上传时间：<?= date('Y-m-d H:i:s', $file[1]) ?>
						</div>
						<div class="pull-right">
							<form method="post">
								<input type="hidden" name="del" value="<?= $file[0] ?>">
								<input class="btn btn-danger" type="submit" onclick="return confirm('确认删除吗');" value="删除">
							</form>
						</div>
					</div>
				</div>
				<div class="panel-body">
					<?php if(preg_match('/^text\//',$file[3])): ?>
						<pre style="max-height:720px;overflow-x:hidden;overflow-y:auto;"><?= htmlspecialchars(uojFilePreview($sys_path.$path.$file[0], 3000)) ?></pre>
					<?php else :?>
						<div class="text-center">
							<img src="<?= $path.$file[0] ?>" alt="无法预览该文件" style="max-width:100%;max-height:720px;">
						</div>
					<?php endif ?>
				</div>
			</div>
		<?php endforeach ?>
	</div>
</div>
<?php echoUOJPageFooter() ?>