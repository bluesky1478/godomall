<script type="text/javascript">
<!--
$(document).ready(function(){
});
//-->
</script>

<form name="frmEscrowDelivery" method="post" action="<?php echo PATH_INICIS;?>frame_escrow_ps.php">

	<div class="table-title gd-help-manual">
		에스크로 배송 등록
	</div>
	<table class="table table-cols">
		<colgroup>
			<col class="width-sm" />
			<col />
		</colgroup>
		<tr>
			<th>PG 업체</th>
			<td>
				<?php
				if (empty($pgConf['pgName'])) {
					echo '전자결제 서비스 업체와 계약이 필요합니다.';
				} else {
					echo $pgConf['pgNm'];

				}
				?>
			</td>
		</tr>
		<tr>
			<th>주문번호</th>
			<td class="font-num"><?php echo $orderData['orderNo'];?></td>
		</tr>
		<tr>
			<th>거래번호(TID)</th>
			<td><?php echo $orderData['pgTid'];?></td>
		</tr>
		<tr>
			<th>에스크로 타입</th>
			<td class="form-inline">
				<?php
				$escrowType	= array('I' => '배송등록', 'U' => '배송수정');
				echo gd_select_box(null,'EscrowType',$escrowType,null,$escrowTypeVal,'=선택하십시오=');
				?>
			</td>
		</tr>
		<tr>
			<th>배송비 지급방법</th>
			<td class="form-inline">
				<?php
				$dlvCharge	= array('SH' => '판매자부담', 'BH' => '구매자부담');
				echo gd_select_box(null,'dlv_charge',$dlvCharge,null,$dlvChargeVal,'=선택하십시오=');
				?>
			</td>
		</tr>
		<tr>
			<th>택배사 선택</th>
			<td class="form-inline">
				<select name="dlv_exCode" onchange="delivery_code_changer();" class="form-control">
					<option value="">선택하십시오</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>운송장 번호</th>
			<td class="form-inline">
				<input type="text" name="invoice" value="<?php echo $orderData['escrowInvoiceNo'];?>" class="form-control width80p" />
			</td>
		</tr>
		<tr>
			<th>배송등록 확인일시</th>
			<td><?php echo $orderData['escrowDeliveryDt'];?></td>
		</tr>
		<tr>
			<th>PG 로그</th>
			<td>
				<div class="width-2xl boxScroll">
					<?php echo nl2br(gd_htmlspecialchars_decode($orderData['orderPGLog']));?>
				</div>
			</td>
		</tr>
	</table>
	<div class="text-center">
		<input type="submit" value="에스크로 배송 등록" class="btn btn-red">
	</div>
</form>
