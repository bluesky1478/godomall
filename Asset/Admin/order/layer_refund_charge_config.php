<div>
	<div class="phead_wrap mgt10"><div class="phead">
		<h2>환불 수수료 설정</h2>
		<div class="scroll_save" style="width:200px">
			<input type="button" value="" onclick="charge_config();" class="save" />
		</div>
	</div></div>

	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th>기본 환불 수수료</th>
			<td><input type="text" name="configRefundChargePrice" value="<?= $data['refundCharge'];?>" class="input_int_l" />원</td>
		</tr>
		</table>
	</div>
</div>

<script type="text/javascript">
	<!--
	$(document).ready(function(){
		$('input[name=\'refundCharge\']').number_only();
	});

	/**
	 * 설정 값 저장
	 */
	function charge_config()
	{
		var refundCharge	= $('input[name=\'configRefundChargePrice\']').val();

		var parameters		= {
			'mode'			: 'config_refund_charge',
			'refundCharge'	: refundCharge
		};

		// 저장
		$.post('order_ps.php',parameters, function(data){
			// 페이지 reload
			location.reload();
		});
	}
	//-->
</script>
