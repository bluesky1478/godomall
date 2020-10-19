<div class="table-title gd-help-manual">
	<?=$data['goodsNm']?>
</div>

<form method="post" name="frmUserHandleAccept" id="frmUserHandleAccept" action="../order/order_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="user_handle_update">
    <input type="hidden" name="sno" value="<?=$data['userHandleSno']?>">

    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>작성일시</th>
            <td><?=$data['userHandleRegDt']?></td>
            <th>작성자</th>
            <td>
                <?=$data['managerNm']?> (<?=$data['managerId']?>)
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <?php if ($data['adminHandleReason']) { ?>
                    <textarea name="adminHandleReason" class="form-control" style="height: 102px;"><?=$data['adminHandleReason']?></textarea>
                <?php } else { ?>
                    -
                <?php } ?>
            </td>
        </tr>
        </tbody>
    </table>

	<div class="text-center mgt10">
		<button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
		<button type="submit" class="btn btn-lg btn-black js-layer-close">적용</button>
	</div>
</form>

<script type="text/javascript">
	$(function(){
		// 수정버튼 토글 처리
		$('.js-memo-toggle').click(function(e) {
			if ($('#memo-toggle').hasClass('display-none')) {
				$('#memo-toggle').removeClass('display-none');
			} else {
				$('#memo-toggle').addClass('display-none');
			}
		});

		// 폼 체크 후 전송
		$('#frmUserHandleAccept').validate({
			rules: {
				adminHandleReason: 'required'
			},
			messages: {
				adminHandleReason: '반품승인 메모를 등록해주세요.'
			},
			submitHandler: function(form) {
				form.target = 'ifrmProcess';
				form.submit();
				layer_close();
			}
		});
	});
</script>
