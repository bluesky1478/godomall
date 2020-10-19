<script type="text/javascript">
<!--
	$(document).ready(function() {
		// Form Process
//		$("#frmForbidden").formProcess();
	});
//-->
</script>
<form id="frmForbidden" action="board_ps.php" method="post" target="ifrmProcess">
<input type="hidden" name="mode" value="forbidden" />

	<div class="page-header js-affix">
		<h3><?php echo end($naviMenu->location);?> <small>게시판 금칙어 관리</small></h3>
		<input type="submit" value="저장" class="btn btn-red" />
	</div>

	<div class="table-title gd-help-manual">게시판 금칙어</div>

	<div style="padding:0px 5px 0px 5px;">
		<textarea name="word" style="width:98%; height:200px;" class="form-control" placeholder="예) 대출, 바카라, 로또"><?php echo gd_isset($forbidden)?></textarea>
	</div>

</form>

<div class="one-line"></div>

