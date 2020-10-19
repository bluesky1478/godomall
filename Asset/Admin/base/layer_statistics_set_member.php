<form id="frmSet" name="frmSet" action="../base/statistics_ps.php" method="post">
<input type="hidden" name="mode" value="setMember" />
	<div class="page-header js-affix">
	    <h3>유입통계설정 <small></small></h3>
	</div>

	<div style="padding:8px 5px 8px 0;">
		<table class="table table-cols no-title-line">
		<tr>
			<th>기간</th>
			<td>
			<label><input type="radio" name="term" value="3" <?php echo gd_isset($checked['term']['3']);?>/>최근 3일</label>
			<label><input type="radio" name="term" value="5" <?php echo gd_isset($checked['term']['5']);?>/>최근 5일</label>
			<label><input type="radio" name="term" value="7" <?php echo gd_isset($checked['term']['7']);?>/>최근 7일</label>
			</td>
		</tr>
		<tr>
			<th>비교기간</th>
			<td>
			<input type="text" class="datepicker" maxlength="10" name="compareDtStart" value="<?php echo gd_isset($getData['compareDtStart']);?>" />
			~ <input type="text" class="datepicker" maxlength="10" name="compareDtEnd" value="<?php echo gd_isset($getData['compareDtEnd']);?>" />
			<span class="snote num">(예:<?php echo date('Y-m-d');?>)</span>
			</td>
		</tr>
		</table>
		<div style="text-align:center; margin-top:5px;">
			<input type="image" src="<?=PATH_ADMIN_GD_SHARE?>image/main/btn_m_set_ok.gif" value="확인" class="vtop" />
			<img src="<?=PATH_ADMIN_GD_SHARE?>image/main/btn_m_set_cancel.gif" alt="취소" class="layer_close" />
		</div>
	</div>
</form>

<script type="text/javascript">
<!--
$(document).ready(function(){
	$('#frmSet').formProcess('alert',[
		{'inputName':'term','name':'기간','required':true,'requireMsg':'기간을 입력해 주세요.'}
		, {'callback':function(){
			gap = between_date($('input[name=\'compareDtStart\']').val(),$('input[name=\'compareDtEnd\']').val());
			if (gap >= 7) {
				alert('비교 기간은 최대 7일까지만 설정할 수 있습니다.');
				return false;
			}
			return true;
		}}
	],false);
	$('.layer_close').click(function(){
		parent.$.unblockUI();
	}).css('cursor','pointer');
});
//-->
</script>
