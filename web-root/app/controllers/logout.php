<?php
	crsf_defend();
	Auth::logout($_GET['all']==1?true:false);
?>

<script type="text/javascript">
window.location.href = '/';
</script>

