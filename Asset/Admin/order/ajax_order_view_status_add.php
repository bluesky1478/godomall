<!-- 추가금액 설정 -->
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
        <col/>
        <col/>
    </colgroup>
    <tr>
        <th>상품 금액</th>
        <td><?= gd_currency_display($addData['totalGoodsPrice']) ?></td>
    </tr>
    <tr>
        <th>상품 할인 금액</th>
        <td><?= gd_currency_display($addData['totalGoodsDcPrice']) ?></td>
    </tr>
    <tr>
        <th>운영자 추가 할인금액</th>
        <td id="enuriSumPrice"><?= gd_currency_display($addData['enuriSumPrice']); ?></td>
    </tr>
    <tr>
        <th>상품 추가 결제금액</th>
        <th id="totalAddGoodsPrice"><?= gd_currency_display($addData['totalGoodsPrice'] - $addData['totalGoodsDcPrice']); ?></th>
    </tr>
    <tr>
        <th>
            배송비 금액
            <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
        </th>
        <td id="totalDeliveryCharge"><?= gd_currency_display($addData['totalDeliveryCharge']) ?></td>
    </tr>
    <tr class="display-none js-detail-display">
        <td colspan="2">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg"/>
                    <col/>
                </colgroup>
                <!-- $key는 복수배송지 사용시 에는 ordeInfoCd, 일반 주문건에는 deliverySno로 변환된다. -->
                <?php foreach ($addData['totalDelivery'] as $key => $val) { ?>
                <tr>
                    <th><?= $val['subject']; ?><br/>의 배송비</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="addDeliveryFl[<?= $key; ?>]" value="y" checked="checked" data-price="<?= $val['deliveryCharge']; ?>" class="js-each-delivery"/> 추가함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="addDeliveryFl[<?= $key; ?>]" value="n" data-price="<?= $val['deliveryCharge']; ?>" class="js-each-delivery"/> 추가안함
                        </label>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </td>
    </tr>
    <tr>
        <th>배송비 추가 결제금액</th>
        <th id="totalAddDeliveryPrice"><?= gd_currency_display($addData['totalDeliveryCharge']); ?></th>
    </tr>
    <tr>
        <th>총 추가 결제금액</th>
        <th id="totalSettlePrice"><?= gd_currency_display($addData['totalSettlePrice']); ?></th>
        <input type="hidden" name="appointmentSettlePrice" value="<?= (int)$addData['totalSettlePrice']; ?>">
    </tr>
</table>
<!-- 결제금액 정보 -->

<?php
if ($addData['settleStatus']) { // 주문이 입금 후 라면 추가 결제 정보 제공
    ?>
    <!-- 결제 수단 정보 -->
    <div id="addAddPriceArea" class="display-none">
        <div class="table-title">
            <span class="gd-help-manual mgt30">결제 수단 정보</span>
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>결제수단</th>
                <td>무통장입금</td>
            </tr>
            <tr>
                <th>입금자명</th>
                <td>
                    <input type="text" name="ehSettleName" value="" class="form-control width-md"/>
                </td>
            </tr>
            <tr>
                <th>입금계좌</th>
                <td>
                    <?php if (empty($bankData) === false) { ?>
                        <?= gd_select_box('ehSettleBankAccountInfo', 'ehSettleBankAccountInfo', $bankData, null, null, '=입금 계좌 선택='); ?>
                    <?php } else { ?>
                        <span class="notice-danger">기본설정 > 결제 정책 > 무통장 입금 은행 관리등록을 해주세요.</span>
                        <a href="../policy/settle_bank.php" target="_blank" class="btn btn-white btn-sm">무통장 입금 은행 관리 등록하기</a>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
    <!-- 결제 수단 정보 -->
    <?php
}
?>
<script>
    $(document).ready(function () {
        $('.js-pay-toggle').click(function () {
            var displayChk = $(this).hasClass('active');
            $('.js-pay-toggle').removeClass('active');
            $('.js-detail-display').addClass('display-none');
            if (displayChk) {
                $(this).removeClass('active');
                $(this).closest('tr').next('tr').addClass('display-none');
            } else {
                $(this).addClass('active');
                $(this).closest('tr').next('tr').removeClass('display-none');
            }
        });
        $('.js-each-delivery').click(function () {
            var totalDeliveryPrice = 0;
            $('input:radio[name*="addDeliveryFl["]:checked').each(function () {
                if ($(this).val() == 'y') {
                    totalDeliveryPrice = totalDeliveryPrice + parseInt($(this).data('price'));
                }
            });
            $("#totalDeliveryCharge").text(numeral(totalDeliveryPrice).format() + '<?= gd_currency_string(); ?>');
            $("#totalAddDeliveryPrice").text(numeral(totalDeliveryPrice).format() + '<?= gd_currency_string(); ?>');
            setCancelPrice();
        });
        function setCancelPrice() {
            var totalAddGoodsPrice = parseInt('<?= $addData['totalGoodsPrice']; ?>');
            var totalAddGoodsDcPrice = parseInt('<?= $addData['totalGoodsDcPrice']; ?>');
            var enuriSumPrice = parseInt('<?= $addData['enuriSumPrice']; ?>');
            var totalAddDeliveryPrice = 0;
            var totalGoodsPrice = 0;
            var totalSettlePrice = 0;

            $('input:radio[name*="addDeliveryFl["]:checked').each(function () {
                if ($(this).val() == 'y') {
                    totalAddDeliveryPrice = totalAddDeliveryPrice + parseInt($(this).data('price'));
                }
            });
            totalGoodsPrice = totalAddGoodsPrice - totalAddGoodsDcPrice - enuriSumPrice;
            totalSettlePrice = totalGoodsPrice + totalAddDeliveryPrice;

            $('#totalAddGoodsPrice').text(numeral(totalGoodsPrice).format() + '<?= gd_currency_string(); ?>');
            $('#totalSettlePrice').text(numeral(totalSettlePrice).format() + '<?= gd_currency_string(); ?>');
            $('input[name="appointmentSettlePrice"]').val(totalSettlePrice);
        }
        setCancelPrice();
    });
</script>
