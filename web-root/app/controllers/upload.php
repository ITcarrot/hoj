<?php
	requirePHPLib('form');
	
	if (!isSuperUser($myUser)) {
		become403Page();
	}

	if($_POST['problem_data_file_submit']=='submit'){
		if ($_FILES["problem_data_file"]["error"] > 0)
  		{
  			$errmsg = "Error: ".$_FILES["problem_data_file"]["error"];
			becomeMsgPage('<div>' . $errmsg . '</div><a href="/upload">返回</a>');
  		}
		else
  		{
			$up_filename="/tmp/upload";
			unlink($up_filename);
			move_uploaded_file($_FILES["problem_data_file"]["tmp_name"], $up_filename);
			DB::manage_log('upload','upload a file');
  		}
	}
	
	if($_GET['picname']){
		$esc_name=EscapeShellCmd($_GET['picname']);
		$esc_name=preg_replace("/\./","_",$esc_name);
		exec('mv /tmp/upload '.$_SERVER['DOCUMENT_ROOT'].'/pictures/'.$esc_name);
		DB::manage_log('sync','sync last uploaded file as a picture named '.$esc_name);
		becomeMsgPage('<a href="/upload">返回</a><h3>如果下面出现了你所希望上传的图片，那么部署是成功的</h3><p>题面中的引用方式：![](/pictures/'.$esc_name.')</p><image src="/pictures/'.$esc_name.'" alt="图片部署失败！">');
	}
	
	if($_GET['name']){
		if(preg_replace('/\S{1,20}/','',urlencode($_GET['name']),1)!=''){
			becomeMsgPage('<p>文件名不合法</p><a href="/upload">返回</a>');
		}
		$esc_name=DB::escape($_GET['name']);
		$used=DB::fetch(DB::query("select name from files where name='$esc_name' limit 1"));
		if($used){
			becomeMsgPage('<p>文件名已被使用</p><a href="/upload">返回</a>');
		}
		do {
			$fileName =uojRandString(20);
		} while (file_exists("/var/uoj_data/web/".$fileName));
		exec('mv /tmp/upload /var/uoj_data/web/'.$fileName);
		if(!file_exists("/var/uoj_data/web/".$fileName)){
			becomeMsgPage('<p>文件部署失败</p><a href="/upload">返回</a>');
		}
		DB::query("insert into files(name,file) value('$esc_name','$fileName')");
		DB::manage_log('sync','sync last uploaded file as a file for download named '.$esc_name.$escsuf);
		header("Location: /upload");
		die();
	}
	
	$delpic = new UOJForm('delpic');
	$delpic -> addInput('delpic','text','输入图片名：','',
		function($str){
			global $myUser;
			if(!$_COOKIE['can_download']||time()-$_COOKIE['can_download']>300||$_COOKIE['can_download_check']!=md5($_COOKIE['can_download'].$myUser['username']))
				return '为保障数据的安全，请重新登录，并在登录后5分钟内进行操作！';
			if(!file_exists("{$_SERVER['DOCUMENT_ROOT']}/pictures/$str"))
				return '图片不存在';
			return '';
		}
		,null);
	$delpic -> handle = function () {
		unlink("{$_SERVER['DOCUMENT_ROOT']}/pictures/{$_POST['delpic']}");
		DB::manage_log('delete','delete a picture named '.$_POST['delpic']);
	};
	$delpic->submit_button_config['class_str'] = 'btn btn-danger';
	$delpic->submit_button_config['text'] = '删除图片';
	$delpic->submit_button_config['smart_confirm'] = '';
	$delpic->runAtServer();
	
	$piclist=array();
	exec("ls {$_SERVER['DOCUMENT_ROOT']}/pictures 2>&1",$piclist);
	
	$delfile=new UOJForm('delfile');
	$delfile->addInput('delname','text','输入文件名：','',
		function($str){
			global $myUser;
			if(!$_COOKIE['can_download']||time()-$_COOKIE['can_download']>300||$_COOKIE['can_download_check']!=md5($_COOKIE['can_download'].$myUser['username']))
				return '为保障数据的安全，请重新登录，并在登录后5分钟内进行操作！';
			$esc_del = DB::escape($str);
			if(!DB::selectFirst("select file from files where name='$esc_del' limit 1"))
				return '文件不存在';
			return '';
		}
		,null);
	$delfile->handle=function(){
		$esc_del=DB::escape($_POST['delname']);
		$fname=DB::selectFirst("select file from files where name='$esc_del' limit 1");
		unlink("/var/uoj_data/web/".$fname['file']);
		DB::query("delete from files where name='$esc_del'");
		DB::manage_log('delete','delete a file named '.$esc_del);
	};
	$delfile->submit_button_config['class_str'] = 'btn btn-danger';
	$delfile->submit_button_config['text'] = '删除文件';
	$delfile->submit_button_config['smart_confirm'] = '';
	$delfile->runAtServer();
?>
<?php
	$REQUIRE_LIB['dialog'] = '';
?>
<?php echoUOJPageHeader('上传文件') ?>

<div class="row">
	<?php //dhxh begin ?>
	<div class="modal fade" id="UploadDataModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title" id="myModalLabel">上传文件</h4>
  				</div>
  				<div class="modal-body">
					<form action="" method="post" enctype="multipart/form-data" role="form">
  						<div class="form-group">
							<label for="exampleInputFile">文件</label>
							<input type="file" name="problem_data_file" id="problem_data_file">
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
	<?php //dhxh end ?>
	<div class="row top-buffer-sm">
		<div class="col-md-4">
			<h3>第一步：上传文件</h3>
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#UploadDataModal">上传文件</button>
			<?php if(file_exists('/tmp/upload')):?>
				<?php
					$finfo = finfo_open(FILEINFO_MIME);
					$mimetype = finfo_file($finfo,'/tmp/upload');
					finfo_close($finfo);
				?>
				<h4>已上传文件！</h4>
				<p>文件类型：<?= $mimetype ?></p>
				<p>文件大小：<?= preg_replace('/(?<=\d)(?=(\d\d\d)+$)/',',',filesize('/tmp/upload')) ?>字节</p>
				<p>文件上传时间：<?= date('Y-m-d H:i:s',filectime('/tmp/upload')) ?></p>
			<?php else: ?>
				<h4>尚未上传任何文件！</h4>
			<?php endif ?>
		</div>
		<div class="col-md-4">
			<h3>第2A步：将文件部署成图片</h3>
			<form>
				<p>文件名（请不要使用后缀）：<input type="text" style="width:100%" name="picname" placeholder="格式“题号_编号”（例3_2）" class="form-control input-sm" style="display:inline;width:200px;"></p>
				<p><input type="submit" value="部署" class="btn btn-primary"></p>
			</form>
		</div>
		<div class="col-md-4">
			<h3>第2B步：让文件开放下载/开放预览</h3>
			<form>
				<p>文件引用（下载）名：<input type="text" name="name" class="form-control input-sm" style="display:inline;width:200px;"></p>
				<p>一般命名规则：</p>
				<p>第2题文件1：P2_1.pdf<p>
				<p>练习3文件2：E3_2.zip</p>
				<p><strong style="color:red">需要带文件后缀，不能出现中文</strong></p>
				<p><input type="submit" value="部署" class="btn btn-primary"></p>
			</form>
			<strong>部署成功后可以用 [点击查看](/files/文件名) 引用文件</strong>
		</div>
	</div>
	<h3>删除图片：</h3>
		<?php $delpic->printHTML(); ?>
	<div class="text-center">
		<h3>图片列表（预览）</h3>
		<p><img id="preview" style="height:250px;min-height:250px;"></p>
		<?php
			foreach($piclist as $row){
				echo '<span class="preview_src">',$row,'</span>';
			}
		?>
		<style>
		.preview_src{
			display:inline-block;
			padding:5px 20px;
			cursor: pointer;
		}
		</style>
		<script>
		$('.preview_src').mouseover(function(){
			$('#preview').attr('src','/pictures/'+$(this).html());
		});
		$('.preview_src').mouseout(function(){
			$('#preview').attr('src','');
		});
		</script>
	</div>
	<hr>
	<h3>删除文件：</h3>
		<?php $delfile->printHTML(); ?>
	<h3>文件列表</h3>
		<?php $lft=0 ?>
		<?php echoLongTable(array('*'), 'files', '1', '', '',
			function($row)use(&$lft){
				if($lft%3==0){
					echo '<tr>';
				}
				echo '<td>',$row['name'],'</td>';
				echo '<td><a href="/files/',$row['name'],'">点击查看</a></td>';
				if($lft%3==2){
					echo '</tr>';
				}
				$lft++;
			},array('page_len' => 45))?>
</div>
<?php echoUOJPageFooter() ?>