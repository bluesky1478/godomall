<div class="well well-sm">
    현재 고객이 보유하고 있는 마일리지가 부족합니다.<br>
    마일리지 차감 방법을 선택해주세요.
</div>

<table class="table table-rows no-title-line">
    <tbody>
    <tr>
        <td class="text-center">
            <label class="radio-inline">
                <input type="radio" name="refundMinusMileage" value="y" <?=$checked['refundMinusMileage']['y']?>>
                마이너스 차감 후 환불
            </label>
            <label class="radio-inline">
                <input type="radio" name="refundMinusMileage" value="n" <?=$checked['refundMinusMileage']['n']?>>
                0원 처리 후 환불
            </label>
        </td>
    </tr>
    </tbody>
</table>

<div class="text-center">
    <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
    <button type="submit" class="btn btn-lg btn-black js-mileage-apply">확인</button>
</div>

<script type="text/javascript">
    $(function () {
        // 적용 할 경우
        $('.js-mileage-apply').bind('click', function (e) {
            $('input[name="returnStock[]"]').each(function (idx) {
                // 환불접수 건만 처리
                var handleSno = $(this).val();
                if (handleSno > 0) {
                    $('#frmRefund input[name="refund[' + handleSno + '][returnStock]"]').val((e.canceled ? 'n' : 'y'));
                }
            });

            // parent 창이 필드에 값 입력
            $('input[name="tmp[refundMinusMileage]"]').val($('input[name=refundMinusMileage]:checked').val());

            // parent 창에서 선언한 변수로 창이 한번 열렸음을 체크한다.
            isOpendOnce = true;

            // 레이어 닫기
            layer_close();
        });
    });
</script>
