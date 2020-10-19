<form id="frmGoodsToday" name="frmGoodsToday" action="./goods_ps.php" method="post" target="ifrmProcess">
<input type="hidden" name="mode" value="goods_today" />
	<div class="page-header js-affix">
		<h3><?=end($naviMenu->location);?> </h3>
		<div class="btn-group">
			<input type="submit" value="저장" class="btn btn-red">
		</div>
	</div>

	<div class="table-title gd-help-manual">
		최근 본 상품 설정
	</div>
	<table class="table table-cols">
		<colgroup><col class="width-md" /><col/></colgroup>
		<tr>
			<th>시간 설정</th>
			<td>  <div class="form-inline">
				<label title="최근 본 상품에 대한 유지시간을 설정합니다. 시간으로 작성을 하시면되고 1일인 경우 24를 설정하시면 됩니다.!"><input type="text" name="todayHour" value="<?=$data['todayHour'];?>" class="form-control" /> 시간</label>
			</div></td>
		</tr>
		<tr>
			<th>최대 수량</th>
			<td>  <div class="form-inline">
				<label title="최근 본 상품의 최대 수량입니다. 최대 <?=DEFAULT_LIMIT_TODAY_CNT?>개 까지만 가능합니다."><input type="text" name="todayCnt" value="<?=$data['todayCnt'];?>" class="form-control" /> 개 상품</label>
				</div></td>
		</tr>
	</table>
</form>

<script type="text/javascript">
	<!--
	$(document).ready(function(){

		$('input[name=\'todayHour\']').number_only();
		$('input[name=\'todayCnt\']').number_only(3,<?=DEFAULT_LIMIT_TODAY_CNT;?>,<?=DEFAULT_LIMIT_TODAY_CNT;?>);
	});
	//-->
</script>
