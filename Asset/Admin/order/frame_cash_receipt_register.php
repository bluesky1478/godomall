<script type="text/javascript">
	<!--
	$(document).ready(function(){

		$("#frmCashReceipt").validate({
			dialog: false,
			submitHandler: function (form) {
				<?php if (empty($pgConf['pgName']) === false && $pgConf['cashReceiptFl'] === 'y') { ?>
				// 인증 체크
				var certObj = {'b':'사업자번호','c':'휴대폰번호'};
				var certFl = $('input[name=\'certFl\']:checked').val();
				var certCnt = $('input[name=\'certNo['+certFl+'][]\']').length
				for (var i = 0; i < certCnt; i++) {
					if ($('input[name=\'certNo['+certFl+'][]\']').eq(i).val() == '') {
						alert('인증 체크 : ' + certObj[certFl] + '를 입력해 주세요!');
						return false;
					}
				}
				BootstrapDialog.show({
					title: '현금영수증 발급 요청',
					message: '작성된 내용으로 현금영수증 발급 요청을 진행중입니다.',
					closable: true
				});
				form.target = 'ifrmProcess';
				form.submit();
				<?php } else {?>
				BootstrapDialog.show({
					title: '현금영수증 발급 불가',
					message: 'PG를 사용중이 아니거나, 현금영수증 미사용인경우 발급이 불가능합니다.',
					closable: true
				});
				return false;
				<?php }?>
			},
			rules: {
			},
			messages: {
			}
		});

		display_toggle('c');
	});

	/**
	 * 출력 여부
	 *
	 * @param string arrayID 해당 ID
	 * @param string modeStr 출력 여부 (show or hide)
	 */
	function display_toggle(thisID) {
		$('#certNo_b').hide();
		$('#certNo_c').hide();
		$('#certNo_'+thisID).show();

		// 사업자 번호는 지출 증빙용으로만
		if (thisID == 'b') {
			$('input[name=\'useFl\']').eq(0).prop('disabled',true);
			$('input[name=\'useFl\']').eq(1).prop('disabled',false);
			$('input[name=\'useFl\']').eq(1).prop('checked',true);
		} else {
			$('input[name=\'useFl\']').eq(0).prop('disabled',false);
			$('input[name=\'useFl\']').eq(1).prop('disabled',true);
			$('input[name=\'useFl\']').eq(0).prop('checked',true);
		}
	}
	//-->
</script>

<form id="frmCashReceipt" name="frmCashReceipt" action="cash_receipt_ps.php" method="post">
	<input type="hidden" name="mode" value="cash_receipt_register_order" />
	<input type="hidden" name="pgName" value="<?php echo $pgConf['pgName'];?>" />
	<input type="hidden" name="orderNo" value="<?php echo $orderData['orderNo'];?>" />
	<input type="hidden" name="requestNm" value="<?php echo $orderData['orderName'];?>" />
	<input type="hidden" name="requestGoodsNm" value="<?php echo $orderData['orderGoodsNm'];?>" />
	<input type="hidden" name="requestCellPhone" value="<?php echo $orderData['orderCellPhone'];?>" />
	<input type="hidden" name="requestEmail" value="<?php echo $orderData['orderEmail'];?>" />
	<input type="hidden" name="settlePrice" value="<?php echo $orderData['settlePrice'];?>" />
	<input type="hidden" name="supplyPrice" value="<?php echo $orderData['totalSupplyPrice'];?>" />
	<input type="hidden" name="taxPrice" value="<?php echo $orderData['totalVatPrice'];?>" />
	<input type="hidden" name="freePrice" value="<?php echo $orderData['totalFreePrice'];?>" />
	<input type="hidden" name="servicePrice" value="0" />

	<div class="table-title gd-help-manual">
		현금영수증 정보
	</div>
	<table class="table table-cols">
		<colgroup>
			<col class="width-sm" />
			<col />
		</colgroup>
		<tr>
			<th>PG 업체</th>
			<td class="form-inline">
				<?php
				if (empty($pgConf['pgName'])) {
					echo '전자결제 서비스 업체와 계약이 필요합니다.';
				} else {
					echo $pgConf['pgNm'];
				}

				if (empty($pgConf['pgName']) === false && $pgConf['cashReceiptFl'] == 'y') {
					echo ' <span class="text-blue">(신청 가능)</span>';
				} else {
					if (empty($pgConf['pgName'])) {
						echo ' <span class="text-red">(신청 불가)</span>';
					} else {
						echo ' <span class="text-red">(신청 불가</span> - &quot;'.$pgConf['pgNm'].'&quot;에서 현금영수증 신청을 해주시기 바랍니다.)';
					}
				}
				?>
			</td>
		</tr>
		<tr>
			<th>신청자명</th>
			<td class="form-inline"><?php echo $orderData['orderName'];?></td>
		</tr>
		<tr>
			<th>상품명</th>
			<td class="form-inline"><?php echo $orderData['orderGoodsNm'];?>
			</td>
		</tr>
		<tr>
			<th>신청 금액</th>
			<td class="form-inline">
				<div>
					발행액 : <span class="font-num"><?php echo gd_currency_display($orderData['settlePrice']);?></span>
				</div>
				<div class="mgt5">
					공급액 : <span class="font-num"><?php echo gd_currency_display($orderData['totalSupplyPrice']);?></span>
				</div>
				<div class="mgt5">
					부가세 : <span class="font-num"><?php echo gd_currency_display($orderData['totalVatPrice']);?></span>
				</div>
				<div class="mgt5">
					면세 : <span class="font-num"><?php echo gd_currency_display($orderData['totalFreePrice']);?></span>
				</div>
			</td>
		</tr>
		<tr>
			<th>발행 용도</th>
			<td>
				<label><input type="radio" name="useFl" value="d" checked="checked" /> 소득공제용</label>
				<label><input type="radio" name="useFl" value="e" /> 지출증빙용</label>
			</td>
		</tr>
		<tr>
			<th class="require">인증 종류</th>
			<td>
				<div>
					<label><input type="radio" name="certFl" value="b" onclick="display_toggle('b');" /> 사업자번호</label>
					<label><input type="radio" name="certFl" value="c" onclick="display_toggle('c');" checked="checked" /> 휴대폰번호</label>
				</div>
				<div id="certNo_c" class="form-inline">
					휴대폰번호 :
					<input type="text" name="certNo[c][]" value="<?=gd_remove_special_char($memberData['cellPhone']);?>" maxlength="12" class="form-control js-number-only width-md"/>
				</div>
				<div id="certNo_b" class="form-inline">
					사업자번호 :
					<input type="text" name="certNo[b][]" maxlength="3" value="<?=explode('-', $memberData['busiNo'])[0];?>" class="form-control width-2xs" /> -
					<input type="text" name="certNo[b][]" maxlength="2" value="<?=explode('-', $memberData['busiNo'])[1];?>" class="form-control width-3xs" /> -
					<input type="text" name="certNo[b][]" maxlength="5" value="<?=explode('-', $memberData['busiNo'])[2];?>" class="form-control width-2xs" />
				</div>
			</td>
		</tr>
		<tr>
			<th>관리자 메모</th>
			<td>
				<textarea name="adminMemo" rows="3" class="form-control"></textarea>
			</td>
		</tr>
	</table>

	<div class="text-center">
		<button type="button" class="btn btn-white js-layer-close">닫기</button>
		<input type="submit" value="저장" class="btn btn-black" />
	</div>
</form>
