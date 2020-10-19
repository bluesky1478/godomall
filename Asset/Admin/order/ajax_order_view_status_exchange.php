<!-- 차액 설정 -->
<style>
    .exchangeMinusPrice { color: red; }
    .exchangePlusPrice { color: blue; }
</style>
<div id="exchangePriceHtml">
    <div class="table-title">
        <span class="gd-help-manual mgt30">차액 정보</span>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <?php if(!$isProvider){ ?>
            <tr>
                <th>취소 상품 결제금액</th>
                <td colspan="3">
                    <input type="hidden" name="totalGoodsSettlePrice" value="<?=$exchangeData['before']['totalGoodsSettlePrice']?>" />
                    <?=gd_currency_display($exchangeData['before']['totalGoodsSettlePrice'])?>

                    <span>(할인혜택 : -<?=gd_currency_display((int)$exchangeData['before']['totalGoodsDcPriceSum'])?>)</span>
                </td>
            </tr>
            <tr>
                <th>추가 상품 결제금액</th>
                <td colspan="3">
                    <input type="hidden" name="addGoodsSettlePrice" value="<?=$exchangeData['after']['addGoodsSettlePrice']?>" />
                    <?=gd_currency_display($exchangeData['after']['addGoodsSettlePrice'])?>

                    <span>(할인혜택 : -<?=gd_currency_display((int)$exchangeData['after']['addGoodsDcPrice'])?>)</span>
                </td>
            </tr>

            <tr>
                <th>
                    취소 배송비
                    <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
                </th>
                <td colspan="3">
                    <div class="form-inline" id="cancelDeliveryPriceArea">
                        <input type="hidden" name="cancelDeliveryPrice" value="<?=$exchangeData['before']['cancelDeliveryPrice']?>" />
                        <input type="hidden" name="cancelDeliveryDcPrice" value="<?=$exchangeData['before']['cancelDeliveryDcPrice']?>" />

                        <span id="cancelDeliveryPriceAreaText"><?=gd_currency_display($exchangeData['before']['cancelDeliveryPrice'])?></span>
                        (할인혜택 : -<span id="cancelDeliveryDcPriceAreaText"><?=gd_currency_display((int)$exchangeData['before']['cancelDeliveryDcPrice'])?></span>)

                        <span class="mgl10" style="color: #999999; font-size: 11px;">(취소된 상품 기준 배송비 입니다.)</span>
                    </div>
                </td>
            </tr>
            <tr class="display-none js-detail-display">
                <td colspan="2">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-lg"/>
                            <col/>
                        </colgroup>
                        <?php foreach ($exchangeData['before']['cancelDeliveryList'] as $orderDeliverySno => $valueArr) { ?>
                            <tr>
                                <th><?= $valueArr['subject']; ?><br/>의 배송비</th>
                                <td>
                                    <label class="radio-inline">
                                        <input type="radio" name="cancelDeliveryFl[<?= $orderDeliverySno; ?>]" value="y" checked="checked" data-radio-type="cancel" data-delivery-price="<?= $valueArr['deliveryCharge']; ?>" data-delivery-dc-price="<?= $valueArr['deliveryDcPrice']; ?>" class="js-each-delivery"/> 취소함
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="cancelDeliveryFl[<?= $orderDeliverySno; ?>]" value="n" data-radio-type="cancel" data-delivery-price="<?= $valueArr['deliveryCharge']; ?>" data-delivery-dc-price="<?= $valueArr['deliveryDcPrice']; ?>" class="js-each-delivery"/> 취소안함
                                    </label>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>

            <tr>
                <th>
                    추가 배송비
                    <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
                </th>
                <td colspan="3">
                    <div class="form-inline" id="addDeliveryPriceArea">
                        <input type="hidden" name="addDeliveryPrice" value="<?=$exchangeData['after']['addDeliveryPrice']?>" />
                        <span id="addDeliveryPriceAreaText"><?=gd_currency_display($exchangeData['after']['addDeliveryPrice'])?></span>

                        <span class="mgl10" style="color: #999999; font-size: 11px;">(추가된 상품 기준 배송비 입니다.)</span>
                    </div>
                </td>
            </tr>
            <tr class="display-none js-detail-display">
                <td colspan="2">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-lg"/>
                            <col/>
                        </colgroup>
                        <?php foreach ($exchangeData['after']['addDeliveryList'] as $orderDeliverySno => $valueArr) { ?>
                            <tr>
                                <th><?= $valueArr['subject']; ?><br/>의 배송비</th>
                                <td>
                                    <label class="radio-inline">
                                        <input type="radio" name="addDeliveryFl[<?= $orderDeliverySno; ?>]" value="y" checked="checked" data-radio-type="add" data-delivery-price="<?= $valueArr['deliveryCharge']; ?>" class="js-each-delivery"/> 추가함
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="addDeliveryFl[<?= $orderDeliverySno; ?>]" value="n" data-radio-type="add" data-delivery-price="<?= $valueArr['deliveryCharge']; ?>" class="js-each-delivery"/> 추가안함
                                    </label>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>

            <tr>
                <th>취소 상품 부가결제금액</th>
                <td colspan="3">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-lg"/>
                            <col/>
                            <col/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>취소 마일리지</th>
                            <td colspan="3">
                                <input type="hidden" name="originalCancelDivisionUseMileage" value="<?= $exchangeData['before']['totalDivisionUseMileage']; ?>" />

                                <input type="text" name="cancelDivisionUseMileage" class="form-control width-md js-number" value="<?= $exchangeData['before']['totalDivisionUseMileage']; ?>" data-totalDivisionUseMileage="<?= $exchangeData['before']['totalDivisionUseMileage']; ?>" data-goodsDivisionUseMileage="<?= $exchangeData['before']['divisionUseMileage']; ?>" data-deliveryDivisionUseMileage="<?= $exchangeData['before']['divisionDeliveryUseMileage']; ?>" data-maxDivisionUseMileage="<?= $exchangeData['before']['totalDivisionUseMileage']; ?>" />

                                <div>
                                    최대 취소 가능 마일리지 : <span id="maxDivisionUseMileageText"><?= $exchangeData['before']['totalDivisionUseMileage']; ?></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>취소 예치금</th>
                            <td colspan="3">
                                <input type="hidden" name="originalCancelDivisionUseDeposit" value="<?= $exchangeData['before']['totalDivisionUseDeposit']; ?>" />

                                <input type="text" name="cancelDivisionUseDeposit" class="form-control width-md js-number" value="<?= $exchangeData['before']['totalDivisionUseDeposit']; ?>" data-totalDivisionUseDeposit="<?= $exchangeData['before']['totalDivisionUseDeposit']; ?>" data-goodsDivisionUseDeposit="<?= $exchangeData['before']['divisionUseDeposit']; ?>" data-deliveryDivisionUseDeposit="<?= $exchangeData['before']['divisionDeliveryUseDeposit']; ?>" data-maxDivisionUseDeposit="<?= $exchangeData['before']['totalDivisionUseDeposit']; ?>" />

                                <div>
                                    최대 취소 가능 예치금 : <span id="maxDivisionUseDepositText"><?= $exchangeData['before']['totalDivisionUseDeposit']; ?></span>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>부가결제 필수 취소 금액</th>
                            <td colspan="3">
                                <input type="hidden" name="requireCancelAddPayment" value="<?php echo $exchangeData['requireCancelAddPayment']; ?>" />
                                <span id="requireCancelAddPaymentText"><?php echo gd_isset($exchangeData['requireCancelAddPayment'], 0); ?></span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php } else { ?>
            <!-- 취소 배송비 -->
            <input type="hidden" name="cancelDeliveryPrice" value="<?=$exchangeData['before']['cancelDeliveryPrice']?>" />
            <input type="hidden" name="cancelDeliveryDcPrice" value="<?=$exchangeData['before']['cancelDeliveryDcPrice']?>" />
            <?php foreach ($exchangeData['before']['cancelDeliveryList'] as $orderDeliverySno => $valueArr) { ?>
                <input type="hidden" name="cancelDeliveryFl[<?= $orderDeliverySno; ?>]" value="y" />
            <?php } ?>

            <!-- 추가 배송비 -->
            <input type="hidden" name="addDeliveryPrice" value="<?=$exchangeData['after']['addDeliveryPrice']?>" />
            <?php foreach ($exchangeData['after']['addDeliveryList'] as $orderDeliverySno => $valueArr) { ?>
                <input type="hidden" name="addDeliveryFl[<?= $orderDeliverySno; ?>]" value="y" />
            <?php } ?>
        <?php } ?>

        <tr>
            <th>총 취소 결제금액</th>
            <th colspan="3">
                <div class="form-inline" id="totalCancelSettlePriceArea">
                    <input type="hidden" name="totalCancelSettlePrice" value="<?=$exchangeData['before']['totalGoodsSettlePrice']+$exchangeData['before']['cancelDeliveryPrice'] - ($exchangeData['before']['totalDivisionUseMileage'] + $exchangeData['before']['totalDivisionUseDeposit'])?>" />

                    <span id="totalCancelSettlePriceAreaText"><?=gd_currency_display($exchangeData['before']['totalGoodsSettlePrice']+$exchangeData['before']['cancelDeliveryPrice'] - ($exchangeData['before']['totalDivisionUseMileage'] + $exchangeData['before']['totalDivisionUseDeposit']))?></span>
                </div>
            </th>
        </tr>
        <tr>
            <th>총 추가 결제금액</th>
            <th colspan="3">
                <div class="form-inline" id="totalAddSettlePriceArea">
                    <input type="hidden" name="totalAddSettlePrice" value="<?=$exchangeData['after']['addGoodsSettlePrice']+$exchangeData['after']['addDeliveryPrice']?>" />
                    <span id="totalAddSettlePriceAreaText"><?=gd_currency_display($exchangeData['after']['addGoodsSettlePrice']+$exchangeData['after']['addDeliveryPrice'])?></span>
                </div>
            </th>
        </tr>
        <tr>
            <th>총 교환 결제 금액</th>
            <th colspan="3">
                <?php
                if($exchangeData['totalChangePrice'] < 0){
                    $addClass = " class='exchangePlusPrice'";
                }
                else {
                    $addClass = " class='exchangeMinusPrice'";
                }
                ?>
                <input type="hidden" name="totalChangePrice" value="<?=$exchangeData['totalChangePrice']?>" />
                <span id="totalChangePriceText" <?=$addClass?>>
                    <?php
                    if((float)$exchangeData['totalChangePrice']){
                        echo gd_currency_display($exchangeData['totalChangePrice']*-1);
                    }
                    else {
                        echo gd_currency_display(0);
                    }
                    ?>
                </span>
            </th>
        </tr>
    </table>
    <!-- 결제금액 정보 -->

    <!-- 결제 수단 정보 -->
    <div id="exchangeAddPriceArea" class="display-none">
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
                    <input type="text" name="ehSettleName" value="" class="form-control width-md" />
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

    <!-- 환불처리 상세정보 -->
    <div id="exchangeMinusPriceArea"  class="display-none">
        <div class="table-title">
            <span class="gd-help-manual mgt30">환불처리 상세정보</span>
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>환불수단</th>
                <td>
                    <?= gd_select_box('ehRefundMethod', 'ehRefundMethod', $ehRefundMethodArr, null, 'bank', '=환불 수단 선택='); ?>
                </td>
            </tr>
            <tr>
                <th>환불금액</th>
                <td>
                    <span id="exchangeRefundPrice"><?=gd_money_format(abs($exchangeData['totalChangePrice']))?>원</span>
                    <span class="notice-info mgl10">(운영자가 별도로 고객에게 지급해야 합니다.)</span>
                </td>
            </tr>
            <tr id="exchangeRefundBankInfoArea">
                <th>환불 계좌정보</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('ehRefundBankName', 'ehRefundBankName', $bankNm, null, '', '= 은행 선택 =') ?>
                        &nbsp;
                        계좌정보 : <input type="text" name="ehRefundBankAccountNumber" class="form-control width-md js-number" maxlength="20" />
                        &nbsp;
                        예금주 : <input type="text" name="ehRefundName" class="form-control width-md" maxlength="20">
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <!-- 환불처리 상세정보 -->
</div>
<script>
    var gd_currency_string = '<?= gd_currency_string(); ?>';
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

        //배송비 취소, 추가여부 - 최초 결제 금액에는 모두 포함되어 있다
        $('input:radio[name*="cancelDeliveryFl"], input:radio[name*="addDeliveryFl"]').change(function () {
            setExchangePrice();
        });

        //취소 상품 부가결제 금액
        $('input[name="cancelDivisionUseMileage"], input[name="cancelDivisionUseDeposit"]').change(function () {
            if($.trim($(this).val()) === ''){
                $(this).val(0);
            }
            setExchangePrice();
        });

        $(document).on("change", "#ehRefundMethod", function () {
            if($(this).val() === 'bank'){
                $("#exchangeRefundBankInfoArea").removeClass("display-none");
            }
            else {
                $("#exchangeRefundBankInfoArea").addClass("display-none");
            }
        });

        displayPartArea();
    });

    function displayPartArea()
    {
        if($("input[name='totalChangePrice']").val() > 0){
            $("#exchangeAddPriceArea").addClass("display-none");
            $("#exchangeMinusPriceArea").removeClass("display-none");
        }
        else if($("input[name='totalChangePrice']").val() == 0){
            $("#exchangeAddPriceArea").addClass("display-none");
            $("#exchangeMinusPriceArea").addClass("display-none");
        }
        else {
            $("#exchangeAddPriceArea").removeClass("display-none");
            $("#exchangeMinusPriceArea").addClass("display-none");
        }
    }

    function setAddPaymentText(requireCancelAddPayment)
    {
        // 취소 가능 마일리지
        $("#maxDivisionUseMileageText").html($("input[name='cancelDivisionUseMileage']").attr('data-maxDivisionUseMileage'));
        // 취소 가능 예치금
        $("#maxDivisionUseDepositText").html($("input[name='cancelDivisionUseDeposit']").attr('data-maxDivisionUseDeposit'));
        // 부가결제 필수 취소 금액
        $("#requireCancelAddPaymentText").html(requireCancelAddPayment);
    }

    function setExchangePrice()
    {
        //취소 상품 결제 금액
        var totalGoodsSettlePrice = $("input[name='totalGoodsSettlePrice']").val();
        //추가 상품 결제 금액
        var addGoodsSettlePrice = $("input[name='addGoodsSettlePrice']").val();
        //취소 배송비 금액
        var cancelDeliveryPriceObj = $("input[name='cancelDeliveryPrice']");
        var cancelDeliveryPrice = 0;
        //취소 배송비 할인 금액
        var cancelDeliveryDcPriceObj = $("input[name='cancelDeliveryDcPrice']");
        var cancelDeliveryDcPrice = 0;
        // 취소가능한 안분된 상품 마일리지
        var goodsDivisionUseMileage  = parseInt($("input[name='cancelDivisionUseMileage']").attr('data-goodsDivisionUseMileage'));
        // 취소가능한 안분된 상품 예치금
        var goodsDivisionUseDeposit  = parseInt($("input[name='cancelDivisionUseDeposit']").attr('data-goodsDivisionUseDeposit'));
        // 취소가능한 안분된 배송비 마일리지
        var deliveryDivisionUseMileage  = parseInt($("input[name='cancelDivisionUseMileage']").attr('data-deliveryDivisionUseMileage'));
        // 취소가능한 안분된 배송비 예치금
        var deliveryDivisionUseDeposit  = parseInt($("input[name='cancelDivisionUseDeposit']").attr('data-deliveryDivisionUseDeposit'));

        //추가 배송비 금액
        var addDeliveryPriceObj = $("input[name='addDeliveryPrice']");
        var addDeliveryPrice = 0;
        //총 취소 결제 금액
        var totalCancelSettlePriceObj = $("input[name='totalCancelSettlePrice']");
        //충 추가 결제 금액
        var totalAddSettlePriceObj = $("input[name='totalAddSettlePrice']");
        //최종 결제 금액
        var totalChangePriceObj = $("input[name='totalChangePrice']");

        var maxDivisionUseMileage = goodsDivisionUseMileage;
        var maxDivisionUseDeposit = goodsDivisionUseDeposit;

        $.each($('input:radio[name*="cancelDeliveryFl"]:checked'), function() {
            var thisValue = $(this).val();
            var deliveryPrice = $(this).attr('data-delivery-price');
            var deliveryDcPrice = $(this).attr('data-delivery-dc-price');

            if(thisValue === 'y'){
                //취소 배송비 취소함
                cancelDeliveryPrice = parseFloat(cancelDeliveryPrice) + (parseFloat(deliveryPrice) - parseFloat(deliveryDcPrice));
                cancelDeliveryDcPrice = parseFloat(cancelDeliveryDcPrice) + parseFloat(deliveryDcPrice);

                //최대 취소가능한 부가결제 금액
                maxDivisionUseMileage += deliveryDivisionUseMileage;
                maxDivisionUseDeposit += deliveryDivisionUseDeposit;
            }
            else {
                //취소 배송비 취소안함
                cancelDeliveryPrice += 0;
                cancelDeliveryDcPrice += 0;
            }
        });

        $.each($('input:radio[name*="addDeliveryFl"]:checked'), function() {
            var thisValue = $(this).val();
            var deliveryPrice = $(this).attr('data-delivery-price');

            if(thisValue === 'y'){
                //추가 배송비
                addDeliveryPrice = parseFloat(addDeliveryPrice) + parseFloat(deliveryPrice);
            }
            else {
                //추가 배송비
                addDeliveryPrice += 0;
            }
        });
        //총 추가 결제금액
        var totalAddSettlePrice = parseFloat(addGoodsSettlePrice) + parseFloat(addDeliveryPrice);

        // 부가결제 필수 환불금액
        var requireCancelAddPayment = (totalAddSettlePrice < (maxDivisionUseMileage+maxDivisionUseDeposit)) ? (maxDivisionUseMileage+maxDivisionUseDeposit) - totalAddSettlePrice : 0;
        $("input[name='requireCancelAddPayment']").val(requireCancelAddPayment);

        //취소가능한 부가결제금액 설정
        $("input[name='cancelDivisionUseMileage']").attr('data-maxDivisionUseMileage', maxDivisionUseMileage);
        $("input[name='cancelDivisionUseDeposit']").attr('data-maxDivisionUseDeposit', maxDivisionUseDeposit);
        if($("input[name='cancelDivisionUseMileage']").val() > maxDivisionUseMileage){
            $("input[name='cancelDivisionUseMileage']").val(maxDivisionUseMileage);
        }
        if($("input[name='cancelDivisionUseDeposit']").val() > maxDivisionUseDeposit){
            $("input[name='cancelDivisionUseDeposit']").val(maxDivisionUseDeposit);
        }
        $("input[name='originalCancelDivisionUseDeposit']").val(maxDivisionUseDeposit);
        $("input[name='originalCancelDivisionUseMileage']").val(maxDivisionUseMileage);

        //취소 배송비
        cancelDeliveryPriceObj.val(cancelDeliveryPrice);
        $("#cancelDeliveryPriceAreaText").html(numeral(cancelDeliveryPrice).format() + gd_currency_string);

        //취소 배송비 할인
        cancelDeliveryDcPriceObj.val(cancelDeliveryDcPrice);
        $("#cancelDeliveryDcPriceAreaText").html(numeral(cancelDeliveryDcPrice).format() + gd_currency_string);

        //취소될 부가결제 금액
        var cancelAddPaymentPrice = parseInt($("input[name='cancelDivisionUseMileage']").val()) + parseInt($("input[name='cancelDivisionUseDeposit']").val());

        //총 취소 결제금액
        var totalCancelSettlePrice = parseFloat(totalGoodsSettlePrice) + parseFloat(cancelDeliveryPrice) - parseFloat(cancelAddPaymentPrice);
        totalCancelSettlePriceObj.val(totalCancelSettlePrice);
        $("#totalCancelSettlePriceAreaText").html(numeral(totalCancelSettlePrice).format() + gd_currency_string);

        //추가 배송비
        addDeliveryPriceObj.val(addDeliveryPrice);
        $("#addDeliveryPriceAreaText").html(numeral(addDeliveryPrice).format() + gd_currency_string);

        //총 추가 결제금액
        totalAddSettlePriceObj.val(totalAddSettlePrice);
        $("#totalAddSettlePriceAreaText").html(numeral(totalAddSettlePrice).format() + gd_currency_string);

        var totalChangePrice = parseFloat(totalCancelSettlePrice) - parseFloat(totalAddSettlePrice);
        totalChangePriceObj.val(totalChangePrice);
        if(parseFloat(totalChangePrice)){
            $("#totalChangePriceText").html(numeral(totalChangePrice*-1).format() + gd_currency_string);
            if(parseFloat(totalChangePrice) < 0){
                $("#totalChangePriceText").removeClass('exchangeMinusPrice').addClass('exchangePlusPrice');
            }
            else {
                $("#totalChangePriceText").removeClass('exchangePlusPrice').addClass('exchangeMinusPrice');
            }
        }
        else {
            $("#totalChangePriceText").html(numeral(0).format() + gd_currency_string);
        }
        $("#exchangeRefundPrice").html(numeral(Math.abs(totalChangePrice)).format() + gd_currency_string);

        // 취소가능 부가결제 금액 텍스트 변경
        setAddPaymentText(requireCancelAddPayment);

        displayPartArea();
    }
</script>
