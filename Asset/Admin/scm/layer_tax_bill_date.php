<!-- 주의! 본사, 공급사 별도 수정 필요 -->
<div class="width100p pd10">
    <h4><?= $postData['chkNum']; ?>개의 선택된 정보의 세금계산서를 발행하시겠습니까?</h4>
    <table class="table table-cols">
        <colgroup>
            <col class="width-xs"/>
            <col class="width2p"/>
            <col/>
        </colgroup>
        <tr>
            <td>세금등급</td>
            <td>:</td>
            <td>과세</td>
        </tr>
        <tr class="form-inline">
            <td>발행일</td>
            <td>:</td>
            <td class="input-group js-datepicker"><input type="text" name="tmpTaxDate" value="" class="form-control width-xs"><span class="input-group-addon"><span class="btn-icon-calendar"></span></span></td>
        </tr>
    </table>
    <div class="center"><button type="button" class="btn btn-lg btn-black js-tax-bill-date">확인</button></div>
</div>
<script>
    $(document).ready(function () {
        $('.js-tax-bill-date').click(function () {
            if ($.trim($('input[name="tmpTaxDate"]').val())) {
                $('input[name="taxBillDate"]').val($('input[name="tmpTaxDate"]').val());
                layer_close();
                $('#frmScmAdjust').submit();
            } else {
                alert('발행일을 입력해 주세요.');
                return false;
            }
        });
    });
</script>
