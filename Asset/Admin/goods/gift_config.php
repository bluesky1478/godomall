<form id="frmGiftConfig" name="frmGiftConfig" action="./gift_ps.php" method="post" target="ifrmProcess">
<input type="hidden" name="mode" value="gift_config" />
	<div class="page-header js-affix">
		<h3><?=end($naviMenu->location);?></h3>
		<div class="btn-group">
			<input type="submit" value="저장" class="btn btn-red">

		</div>
	</div>

	<div class="table-title ">
		<?=end($naviMenu->location);?>
	</div>
	<table class="table table-cols">
		<colgroup><col class="width-md" /><col/></colgroup>
		<tr>
			<th>사은품 지급</th>
			<td>
				<label class="radio-inline" title="사은품 설정된 것이 있는 경우, 구매고객에게 각 조건에 따라 사은품 증정을 진행합니다."><input type="radio" name="giftFl" value="y" <?=gd_isset($checked['giftFl']['y']);?> />사용함</label>
				<label class="radio-inline" title="사은품 설정과 상관없이 사은품 정책을 사용하지 않습니다."><input type="radio" name="giftFl" value="n" <?=gd_isset($checked['giftFl']['n']);?> />사용안함</label>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">


</script>
