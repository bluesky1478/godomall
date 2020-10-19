<div class="table-title gd-help-manual">
	<?=$data['goodsNm']?>
</div>

<table class="table table-cols no-title-line">
	<colgroup>
		<col class="width-sm"/>
		<col/>
	</colgroup>
	<tr>
		<th>메모 내용</th>
		<td>
			<?php if ($data['userHandleDetailReason']) { ?>
                <div class="boxScroll_small">
				    <?=nl2br($data['userHandleDetailReason'])?>
                </div>
			<?php } else { ?>
				-
			<?php } ?>
		</td>
	</tr>
	<?php if ($statusMode != 'e') { ?>
	<tr>
		<th>환불 계좌정보</th>
		<td>
			<?php if ($data['userRefundBankName']) { ?>
			<?=$data['userRefundBankName']?> / <?=$data['userRefundAccountNumber']?> / <?=$data['userRefundDepositor']?>
			<?php } else { ?>
			-
			<?php } ?>
		</td>
	</tr>
	<?php } ?>
</table>

<div class="text-center">
	<button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
</div>
