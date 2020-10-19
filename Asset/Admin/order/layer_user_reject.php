<div class="well well-sm">
	선택하신 <?=count($data['statusCheck'])?>건의 주문을 <?=$title?> 거절처리 하시겠습니까?
</div>

<form method="post" name="frmUserHandleAccept" id="frmUserHandleAccept" action="../order/order_ps.php" target="ifrmProcess">
	<input type="hidden" name="mode" value="user_handle_reject">
	<?php foreach ($statusCheck as $val) { ?>
		<input type="hidden" name="statusCheck[]" value="<?=$val?>">
	<?php } ?>
	<div class="table-title gd-help-manual"><?=$title?>거절 메모 등록</div>
	<textarea name="adminHandleReason" class="form-control"></textarea>

	<div class="text-center mgt10">
		<button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
		<button type="submit" class="btn btn-lg btn-black">확인</button>
	</div>
</form>

<script type="text/javascript">
<!--
$(document).ready(function(){
    // 폼 체크 후 전송
    $('#frmUserHandleAccept').validate({
        dialog: false,
        rules: {
            adminHandleReason: 'required'
        },
        messages: {
            adminHandleReason: '<?=$title?>거절 메모를 등록해주세요.'
        },
        submitHandler: function(form) {
            form.target = 'ifrmProcess';
            form.submit();
        }
    });
});
//-->
</script>
