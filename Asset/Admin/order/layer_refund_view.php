<form id="frmClaimRegister" name="frmClaimRegister" method="post" action="../order/order_ps.php">
    <input type="hidden" name="mode" value="modify_claim" />
    <input type="hidden" name="sno" value="<?=$handleData['handleSno']?>" />
    <div class="table-responsive">
        <table class="table table-rows">
            <thead>
            <tr>
                <th>공급사</th>
                <th>환불상품</th>
                <th>환불수량</th>
                <th>환불금액</th>
                <th>환불배송비</th>
                <th>환불수단</th>
                <th>환불일자</th>
                <th>환불계좌정보</th>
                <th>환불사유</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($handleData) === false) { ?>
                <tr class="text-center nowrap">
                    <td><?= $handleData['companyNm'] ?></td>
                    <td><?= $handleData['goodsNm'] ?></td>
                    <td><?= $handleData['goodsCnt']; ?></td>
                    <td><?= gd_currency_display((($handleData['goodsPrice'] + $handleData['optionPrice'] + $handleData['textOptionPrice']) * $handleData['goodsCnt']) + $handleData['addGoodsPrice']); ?></td>
                    <td><?= gd_currency_display($handleData['refundDeliveryCharge']); ?></td>
                    <td><?= gd_select_box(null, 'refundMethod', $refundMethod, null, $handleData['refundMethod'], null); ?></td>
                    <td>
                        <?= $handleData['handleDt'] ?>
                    </td>
                    <td><?= $handleData['refundBankName'] ?><br><?= $handleData['refundAccountNumber'] ?><br><?= $handleData['refundDepositor'] ?></td>
                    <td><?= gd_select_box(null, 'handleReason', $refundReason, null, $handleData['handleReason'], null); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="submit" class="btn btn-lg btn-black js-layer-close">확인</button>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(function(){
        $('#frmClaimRegister').validate({
            submitHandler: function(form) {
                form.target = 'ifrmProcess';
                form.submit();
            }
        });
    });
    //-->
</script>
