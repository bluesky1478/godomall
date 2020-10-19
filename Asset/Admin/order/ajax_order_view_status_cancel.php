<!-- 결제금액 정보 -->
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
        <col/>
        <col/>
    </colgroup>
    <tr>
        <th>상품 금액</th>
        <td colspan="3" id="cancelGoods"><?= gd_currency_display($cancelData['cancelGoodsPriceSum']); ?></td>
    </tr>
    <tr>
        <th>상품 할인혜택 금액
            <span class="flo-right" style="font-size: 10px; color: #117ef9;">
            <button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button>
        </span>
        </th>
        <td colspan="3" id="cancelGoodsDc"><?= gd_currency_display($cancelData['totalCancelGoodsDcPriceSum']); ?></td>
    </tr>
    <tr class="display-none js-detail-display">
        <td colspan="4">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg"/>
                    <col/>
                    <col/>
                    <col/>
                </colgroup>
                <tr>
                    <th>상품할인 금액</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="cancelGoodsDc" class="form-control width-md js-number" value="<?= $cancelData['cancelGoodsDcPriceSum']; ?>" disabled="disabled"/> 원
                        </div>
                    </td>
                    <td colspan="2" rowspan="5" style="color: #999999;">
                        취소 상품에 적용된 할인 금액이므로 전체 취소만 가능합니다.
                    </td>
                </tr>
                <?php if ($cancelData['cancelMyappDcPriceSum'] > 0) { ?>
                <tr>
                    <th>상품할인(모바일앱) 금액</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="cancelMyappDc" class="form-control width-md js-number" value="<?= $cancelData['cancelMyappDcPriceSum']; ?>" disabled="disabled"/> 원
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <th>회원 추가할인 금액</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="cancelMemberDc" class="form-control width-md js-number" value="<?= $cancelData['cancelMemberDcPriceSum']; ?>" disabled="disabled"/> 원
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>회원 중복할인 금액</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="cancelMemberOverlapDc" class="form-control width-md js-number" value="<?= $cancelData['cancelMemberOverlapDcPriceSum']; ?>" disabled="disabled"/> 원
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>상품쿠폰 금액</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="cancelGoodsCouponDc" class="form-control width-md js-number" value="<?= $cancelData['cancelGoodsCouponDcPriceSum']; ?>" disabled="disabled"/> 원
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>운영자할인 금액</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="cancelGoodsEnuri" class="form-control width-md js-number" value="<?= $cancelData['cancelGoodsEnuriPriceSum']; ?>" disabled="disabled"/> 원
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>주문쿠폰 할인 조정</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="totalCancelOrderCouponDc" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelOrderCouponDcPrice'], false); ?>"/> 원
                        </div>
                    </td>
                    <td colspan="2">
                        <label class="radio-inline">
                            <input type="radio" name="cancel3" value="y" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelOrderCouponDcPrice'], false); ?>"/> 주문쿠폰 할인 취소
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="cancel3" value="n"/> 취소안함
                        </label>

                        <span class="mgl10" style="color: #999999; font-size: 11px;">(최대 취소 가능 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelOrderCouponDcPrice']); ?></span>원)</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <th>총 취소 상품 금액</th>
        <th colspan="3" id="totalCancelGoodsPrice"><?= gd_currency_display($cancelData['totalCancelGoods']); ?></th>
    </tr>

    <?php foreach ($cancelData['totalCancelDeliveryPrice'] as $deliverySno => $val) { ?>
        <tr>
            <th>
                <?= $cancelData['totalCancelDeliveryGoodsName'][$deliverySno]; ?><br/>의 배송비 금액
                <span class="flo-right" style="font-size: 10px; color: #117ef9;">
                <button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button>
            </span>
            </th>
            <td colspan="3" id="cancelDelivery<?= $deliverySno; ?>"><?= gd_currency_display($cancelData['totalCancelDeliveryPriceSum'][$deliverySno]); ?></td>
        </tr>
        <tr class="display-none js-detail-display">
            <td colspan="4">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-lg"/>
                        <col/>
                        <col/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>배송비</th>
                        <td>
                            <div class="form-inline">
                                <input type="text" name="totalCancelDeliveryPrice[<?= $deliverySno; ?>]" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelDeliveryPrice'][$deliverySno], false); ?>" data-price="<?= (int)$cancelData['totalCancelDeliveryPrice'][$deliverySno]; ?>" data-sno="<?= $deliverySno; ?>"/> 원
                            </div>
                        </td>
                        <td colspan="2">
                            <label class="radio-inline">
                                <input type="radio" name="cancelD1<?= $deliverySno; ?>" value="y" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelDeliveryPrice'][$deliverySno], false); ?>"/> 배송비 취소
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="cancelD1<?= $deliverySno; ?>" value="n"/> 취소안함
                            </label>

                            <span class="mgl10" style="color: #999999; font-size: 11px;">(최대 취소 가능 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelDeliveryPrice'][$deliverySno]); ?></span>원)</span>
                        </td>
                    </tr>
                    <tr>
                        <th>지역별 배송비</th>
                        <td>
                            <div class="form-inline">
                                <input type="text" name="totalCancelAreaDeliveryPrice[<?= $deliverySno; ?>]" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelAreaDeliveryPrice'][$deliverySno], false); ?>" data-price="<?= (int)$cancelData['totalCancelAreaDeliveryPrice'][$deliverySno]; ?>" data-sno="<?= $deliverySno; ?>"/> 원
                            </div>
                        </td>
                        <td colspan="2">
                            <label class="radio-inline">
                                <input type="radio" name="cancelD2<?= $deliverySno; ?>" value="y" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelAreaDeliveryPrice'][$deliverySno], false); ?>"/> 지역별 배송비 취소
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="cancelD2<?= $deliverySno; ?>" value="n"/> 취소안함
                            </label>

                            <span class="mgl10" style="color: #999999; font-size: 11px;">(최대 취소 가능 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelAreaDeliveryPrice'][$deliverySno]); ?></span>원)</span>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>해외배송 보험료</th>
                        <td>
                            <div class="form-inline">
                                <input type="text" name="totalCancelOverseaDeliveryPrice[<?= $deliverySno; ?>]" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelOverseaDeliveryPrice'][$deliverySno], false); ?>" data-price="<?= (int)$cancelData['totalCancelOverseaDeliveryPrice'][$deliverySno]; ?>" data-sno="<?= $deliverySno; ?>"/> 원
                            </div>
                        </td>
                        <td colspan="2">
                            <label class="radio-inline">
                                <input type="radio" name="cancelD3<?= $deliverySno; ?>" value="y" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelOverseaDeliveryPrice'][$deliverySno], false); ?>"/> 해외배송 보험료 취소
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="cancelD3<?= $deliverySno; ?>" value="n"/> 취소안함
                            </label>

                            <span class="mgl10" style="color: #999999; font-size: 11px;">(최대 취소 가능 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelOverseaDeliveryPrice'][$deliverySno]); ?></span>원)</span>
                        </td>
                    </tr>
                    <tr>
                        <th>배송비 추가</th>
                        <td>
                            <div class="form-inline">
                                <input type="text" name="totalCancelAddDeliveryPrice[<?= $deliverySno; ?>]" class="form-control width-md js-number" disabled="disabled" data-sno="<?= $deliverySno; ?>"/> 원
                            </div>
                        </td>
                        <td colspan="2">
                            <label class="radio-inline">
                                <input type="radio" name="cancelD4<?= $deliverySno; ?>" value="n" checked="checked"/> 추가안함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="cancelD4<?= $deliverySno; ?>" value="y" data-price="0"/> 배송비 추가
                            </label>

                            <span class="mgl10" style="color: #999999; font-size: 11px;">(입력된 금액만큼 결제 예정금액에 추가됩니다.)</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <th>
            배송비 할인혜택 금액
            <span class="flo-right" style="font-size: 10px; color: #117ef9;">
            <button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button>
        </span>
        </th>
        <td colspan="3" id="cancelDeliveryDc"><?= gd_currency_display($cancelData['totalCancelDeliveryDcPrice']); ?></td>
    </tr>
    <tr class="display-none js-detail-display">
        <td colspan="4">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg"/>
                    <col/>
                    <col/>
                    <col/>
                </colgroup>
                <tr>
                    <th>배송비쿠폰 할인 조정</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="totalCancelDeliveryCouponDc" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelDeliveryCouponDcPrice'], false); ?>"/> 원
                        </div>
                    </td>
                    <td colspan="2">
                        <label class="radio-inline">
                            <input type="radio" name="totalCancelDeliveryCouponDcFl" value="y" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelDeliveryCouponDcPrice'], false); ?>"/> 배송비쿠폰 할인 취소
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="totalCancelDeliveryCouponDcFl" value="n"/> 취소안함
                        </label>

                        <span class="mgl10" style="color: #999999; font-size: 11px;">(최대 취소 가능 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelDeliveryCouponDcPrice']); ?></span>원)</span>
                    </td>
                </tr>
                <tr>
                    <th>회원 배송비무료 조정</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="totalCancelDeliveryMemberDc" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelDeliveryMemberDcPrice'], false); ?>" disabled="disabled" readonly="readonly" data-price="<?= (int)$cancelData['totalCancelDeliveryMemberDcPrice']; ?>"/> 원
                        </div>
                    </td>
                    <td colspan="2">
                        <label class="radio-inline">
                            <input type="radio" name="cancelDeliveryMemberDcFl" value="a" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelDeliveryMemberDcPrice'], false); ?>"/> 회원 배송비무료 취소
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="cancelDeliveryMemberDcFl" value="x"/> 취소안함
                        </label>

                        <span class="mgl10" style="color: #999999; font-size: 11px;">(취소 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelDeliveryMemberDcPrice']); ?></span>원)</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <th>총 취소 배송비 금액</th>
        <th colspan="3" id="totalCancelDeliveryPrice"><?= gd_currency_display($cancelData['totalCancelDelivery']); ?></th>
    </tr>
    <tr>
        <th>사용 예치금 환불 금액</th>
        <td>
            <div class="form-inline">
                <input type="text" name="totalCancelDeposit" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelDepositPrice'], false); ?>"/> 원
            </div>
        </td>
        <td colspan="2">
            <label class="radio-inline">
                <input type="radio" name="cancel10" value="y" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelDepositPrice'], false); ?>"/> 환불함
            </label>
            <label class="radio-inline">
                <input type="radio" name="cancel10" value="n"/> 환불안함
            </label>

            <span class="mgl10" style="color: #999999; font-size: 11px;">(최대 환불 가능 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelDepositPrice']); ?></span>원)
        </span>
        </td>
    </tr>
    <tr>
        <th>사용 마일리지 환불 금액</th>
        <td>
            <div class="form-inline">
                <input type="text" name="totalCancelMileage" class="form-control width-md js-number" value="<?= gd_money_format($cancelData['totalCancelMileagePrice'], false); ?>"/> 원
            </div>
        </td>
        <td colspan="2">
            <label class="radio-inline">
                <input type="radio" name="cancel12" value="y" checked="checked" data-price="<?= gd_money_format($cancelData['totalCancelMileagePrice'], false); ?>"/> 환불함
            </label>
            <label class="radio-inline">
                <input type="radio" name="cancel12" value="n"/> 환불안함
            </label>
            <!--            <label class="radio-inline">-->
            <!--                <input type="radio" name="cancel12" value="a" data-price="--><? //= (int)$cancelData['totalCancelMileagePrice']; ?><!--"/> 전체환불-->
            <!--            </label>-->

            <span class="mgl10" style="color: #999999; font-size: 11px;">(최대 환불 가능 금액 : <span style="color: red;"><?= gd_money_format($cancelData['totalCancelMileagePrice']); ?></span><?= $mileageUse['unit'] ?>)
        </span>
        </td>
    </tr>
</table>
    <!--    <tr>-->
    <!--        <th>예치금 환불금액</th>-->
    <!--        <th colspan="3" id="totalCancelDepositPrice">0--><?//= $depositUse['unit'] ?><!--</th>-->
    <!--    </tr>-->
    <!--    <tr>-->
    <!--        <th>마일리지 환불금액</th>-->
    <!--        <th colspan="3" id="totalCancelMileagePrice">0--><?//= $mileageUse['unit'] ?><!--</th>-->
    <!--    </tr>-->
<div class="table-title">
    <span class="gd-help-manual mgt30">취소 후 남은 결제 금액</span>
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
        <col/>
        <col/>
    </colgroup>
    <tr>
        <th>취소 전 주문 결제 금액</th>
        <th colspan="3"><?= gd_currency_display($cancelData['settlePrice']); ?></th>
    </tr>
    <tr>
        <th>총 취소 금액</th>
        <th colspan="3" id="totalCancelPrice"><?= gd_currency_display($cancelData['settlePrice']); ?></th>
        <input type="hidden" name="cancelPriceBySmsSend" value="<?= (int)$cancelData['cancelPriceBySmsSend']?>">
    </tr>
    <tr>
        <th>취소 후 주문 결제 예정금액</th>
        <th colspan="3" id="totalSettlePrice"><?= gd_currency_display($cancelData['totalSettlePrice']); ?></th>
        <input type="hidden" name="appointmentSettlePrice" value="<?= (int)$cancelData['totalSettlePrice']; ?>">
    </tr>
</table>
<!-- 결제금액 정보 -->
<script>
    $(document).ready(function () {
        var cancelGoodsPrice = parseInt('<?= $cancelData['cancelGoodsPriceSum']; ?>');// 상품 취소 금액
        var cancelGoodsDcPrice = parseInt('<?= $cancelData['totalCancelGoodsDcPriceSum']; ?>');// 상품 할인혜택 취소 금액(주문쿠폰 제외)
        var cancelOrderCoupon = 0;// 주문쿠폰 취소 금액
        var cancelDepositPrice = 0;// 예치금 환불 금액
        var cancelMileagePrice = 0;// 마일리지 환불 금액
        var cancelDeliveryPrice = new Object();// 배송비 취소 금액
        var cancelDeliveryCouponDcPrice = 0;// 배송비 쿠폰 취소 금액
        var cancelDeliveryMemberDcPrice = 0;// 배송비 회원할인 취소 금액
        $(document).on("click", "input[type='radio']", function () {
            var inputTextEl = $(this).closest("tr").find("input[type='text']");

            if ($(this).val() === 'n') { // n 은 y 랑
                inputTextEl.attr("disabled", true);
                inputTextEl.val(0);
                inputTextEl.trigger('blur');
            } else if ($(this).val() === 'y') {
                inputTextEl.attr("disabled", false);
                inputTextEl.val($(this).data('price'));
                inputTextEl.trigger('blur');
            } else if ($(this).val() === 'a') { // a 는 x 랑
                inputTextEl.attr("disabled", false);
                inputTextEl.val($(this).data('price'));
                inputTextEl.trigger('blur');
                setCancelAllPrice(inputTextEl);
            } else if ($(this).val() === 'x') {
                inputTextEl.attr("disabled", true);
                inputTextEl.val(0);
                inputTextEl.trigger('blur');
                setCancelAllPrice(inputTextEl);
            }
        });

        $('.js-pay-toggle').click(function () {
            $(this).closest('tr').next('tr').toggleClass("display-none");
            $(this).toggleClass('active');
//
//            // 한개만 열림
//            var displayChk = $(this).hasClass('active');
//            $('.js-pay-toggle').removeClass('active');
//            $('.js-detail-display').addClass('display-none');
//            if (displayChk) {
//                $(this).removeClass('active');
//                $(this).closest('tr').next('tr').addClass('display-none');
//            } else {
//                $(this).addClass('active');
//                $(this).closest('tr').next('tr').removeClass('display-none');
//            }
        });

//        $('#viewStatusCancelDetail').prop("disabled", true);

        $(document).on("keyup blur", "input[name='totalCancelOrderCouponDc']", function () {
            if ($(this).val() == '') {
                cancelOrderCoupon = 0;
            }
            if (parseInt($(this).val()) >= 0) {
                cancelOrderCoupon = parseInt($(this).val());
            }
            if (cancelOrderCoupon > <?= $cancelData['totalCancelOrderCouponDcPrice']; ?>) {
                $(this).val(<?= $cancelData['totalCancelOrderCouponDcPrice']; ?>);
                cancelOrderCoupon = parseInt($(this).val());
                alert('최대 취소 가능 금액을 초과할 수 없습니다.');
            }
            if (cancelOrderCoupon < 0) {
                $(this).val(0);
                cancelOrderCoupon = parseInt($(this).val());
                alert('최소 취소 가능 금액은 0보다 작을 수 없습니다.');
            }
            $('#cancelGoodsDc').text(numeral(cancelGoodsDcPrice + cancelOrderCoupon).format() + '<?= gd_currency_string(); ?>');
            setCancelPrice();
        });
        $("input[name='totalCancelOrderCouponDc']").trigger('blur');

        $(document).on("keyup blur", "input[name='totalCancelDeposit']", function () {
            if ($(this).val() == '') {
                cancelDepositPrice = 0;
            }
            if (parseInt($(this).val()) >= 0) {
                cancelDepositPrice = parseInt($(this).val());
            }
            if (cancelDepositPrice > parseInt('<?= $cancelData['totalCancelDepositPrice']; ?>')) {
                $(this).val(parseInt('<?= $cancelData['totalCancelDepositPrice']; ?>'));
                cancelDepositPrice = parseInt($(this).val());
                alert('최대 환불 가능 금액을 초과할 수 없습니다.');
            }
            if (cancelDepositPrice < 0) {
                $(this).val(0);
                cancelDepositPrice = parseInt($(this).val());
                alert('최소 환불 가능 금액은 0보다 작을 수 없습니다.');
            }
            setCancelPrice();
        });

        $(document).on("keyup blur", "input[name='totalCancelMileage']", function () {
            if ($(this).val() == '') {
                cancelMileagePrice = 0;
            }
            if (parseInt($(this).val()) >= 0) {
                cancelMileagePrice = parseInt($(this).val());
            }
            if (cancelMileagePrice > parseInt('<?= $cancelData['totalCancelMileagePrice']; ?>')) {
                $(this).val(parseInt('<?= $cancelData['totalCancelMileagePrice']; ?>'));
                cancelMileagePrice = parseInt($(this).val());
                alert('최대 환불 가능 금액을 초과할 수 없습니다.');
            }
            if (cancelMileagePrice < 0) {
                $(this).val(0);
                cancelMileagePrice = parseInt($(this).val());
                alert('최소 환불 가능 금액은 0보다 작을 수 없습니다.');
            }
            setCancelPrice();
        });

        $(document).on("keyup blur", "input[name*='totalCancelDeliveryPrice[']", function () {
            setDeliveryPrice($(this), 'Basic');
        });

        $(document).on("keyup blur", "input[name*='totalCancelAreaDeliveryPrice[']", function () {
            setDeliveryPrice($(this), 'Area');
        });

        $(document).on("keyup blur", "input[name*='totalCancelOverseaDeliveryPrice[']", function () {
            setDeliveryPrice($(this), 'Oversea');
        });

        $(document).on("keyup blur", "input[name*='totalCancelAddDeliveryPrice[']", function () {
            setDeliveryPrice($(this), 'Add');
        });

        $(document).on("keyup blur", "input[name='totalCancelDeliveryCouponDc']", function () {
            if (parseInt($(this).val()) >= 0) {
                cancelDeliveryCouponDcPrice = parseInt($(this).val());
            }
            if (cancelDeliveryCouponDcPrice > parseInt('<?= $cancelData['totalCancelDeliveryCouponDcPrice']; ?>')) {
                $(this).val(parseInt('<?= $cancelData['totalCancelDeliveryCouponDcPrice']; ?>'));
                cancelDeliveryCouponDcPrice = parseInt($(this).val());
                alert('최대 취소 가능 금액을 초과할 수 없습니다.');
            }
            if (cancelDeliveryCouponDcPrice < 0) {
                $(this).val(0);
                cancelDeliveryCouponDcPrice = parseInt($(this).val());
                alert('최소 취소 가능 금액은 0보다 작을 수 없습니다.');
            }
            $('#cancelDeliveryDc').text(numeral(cancelDeliveryCouponDcPrice + cancelDeliveryMemberDcPrice).format() + '<?= gd_currency_string(); ?>');
            setCancelPrice();
        });

        function setCancelAllPrice(obj) {
            if (obj.attr('name') == 'totalCancelDeliveryMemberDc') {
                cancelDeliveryMemberDcPrice = parseInt(obj.val());
                $('#cancelDeliveryDc').text(numeral(cancelDeliveryCouponDcPrice + cancelDeliveryMemberDcPrice).format() + '<?= gd_currency_string(); ?>');
            }
            setCancelPrice();
        }

        function setDeliveryPrice(obj, keyCode) {
            var deliverySno = obj.data('sno');
            var delivery = new Object();
            delivery['Basic'] = 0;
            delivery['Area'] = 0;
            delivery['Oversea'] = 0;
            delivery['Add'] = 0;

            if (parseInt($('input[name="totalCancelDeliveryPrice[' + deliverySno + ']"]').val()) > 0) {
                delivery['Basic'] = parseInt($('input[name="totalCancelDeliveryPrice[' + deliverySno + ']"]').val());
            }
            if (parseInt($('input[name="totalCancelAreaDeliveryPrice[' + deliverySno + ']"]').val()) > 0) {
                delivery['Area'] = parseInt($('input[name="totalCancelAreaDeliveryPrice[' + deliverySno + ']"]').val());
            }
            if (parseInt($('input[name="totalCancelOverseaDeliveryPrice[' + deliverySno + ']"]').val()) > 0) {
                delivery['Oversea'] = parseInt($('input[name="totalCancelOverseaDeliveryPrice[' + deliverySno + ']"]').val());
            }
            if (parseInt($('input[name="totalCancelAddDeliveryPrice[' + deliverySno + ']"]').val()) > 0) {
                delivery['Add'] = parseInt($('input[name="totalCancelAddDeliveryPrice[' + deliverySno + ']"]').val());
            }

            if (keyCode != 'Add') {
                if (delivery[keyCode] > parseInt(obj.data('price'))) {
                    obj.val(obj.data('price'));
                    delivery[keyCode] = parseInt(obj.data('price'));
                    alert('최대 취소 가능 금액을 초과할 수 없습니다.');
                }
            }
            if (delivery[keyCode] < 0) {
                obj.val(0);
                delivery[keyCode] = 0;
                alert('최소 취소 가능 금액은 0보다 작을 수 없습니다.');
            }

            var deliverySum = 0;
            $.each(delivery, function (k, v) {
                if (k == 'Add') {
                    deliverySum -= v;
                } else {
                    deliverySum += v;
                }
            });

            cancelDeliveryPrice[deliverySno] = deliverySum;

            var minusText = '';
            if (deliverySum < 0) {
                minusText = '(배송비 추가됨)';
            }
            $('#cancelDelivery' + deliverySno).text(numeral(deliverySum).format() + '<?= gd_currency_string(); ?> ' + minusText);
            setCancelPrice();
        }


        function setCancelPrice() {
            var totalCancelPrice = 0;
            var totalSettlePrice = parseInt('<?= $cancelData['settlePrice']; ?>');// 변경 전 결제 금액
            var totalCancelGoodsPrice = cancelGoodsPrice - (cancelGoodsDcPrice + cancelOrderCoupon);
            var totalCancelDepositMileagePrice = cancelDepositPrice + cancelMileagePrice;
            var totalCancelDeliveryPrice = 0;
            var cancelDeliveryMinusText = '';
            var totalCancelMinusText = '';

            totalSettlePrice = totalSettlePrice - totalCancelGoodsPrice;

            $.each(cancelDeliveryPrice, function (k, v) {
                totalCancelDeliveryPrice += v;
            });

            totalCancelDeliveryPrice = totalCancelDeliveryPrice - (cancelDeliveryCouponDcPrice + cancelDeliveryMemberDcPrice);
            if (totalCancelDeliveryPrice < 0) {
                cancelDeliveryMinusText = '(배송비 추가됨)';
            }

            totalSettlePrice = totalSettlePrice - totalCancelDeliveryPrice;
            totalSettlePrice = totalSettlePrice + totalCancelDepositMileagePrice;

            totalCancelPrice = (totalCancelGoodsPrice + totalCancelDeliveryPrice) - totalCancelDepositMileagePrice;

            if (totalSettlePrice < 0) {
                totalCancelMinusText = '(취소 후 주문 결제 예정금액이 0보다 작은 경우 취소처리가 불가합니다.)';
            }

            $('#totalCancelGoodsPrice').text(numeral(totalCancelGoodsPrice).format() + '<?= gd_currency_string(); ?>');
            //$('#totalCancelDepositPrice').text(numeral(cancelDepositPrice).format() + '<?//= $depositUse['unit'] ?>//');
            //$('#totalCancelMileagePrice').text(numeral(cancelMileagePrice).format() + '<?//= $mileageUse['unit'] ?>//');
            $('#totalCancelDeliveryPrice').text(numeral(totalCancelDeliveryPrice).format() + '<?= gd_currency_string(); ?> ' + cancelDeliveryMinusText);
            $('#totalCancelPrice').text(numeral(totalCancelPrice).format() + '<?= gd_currency_string(); ?>');
            $('#totalSettlePrice').text(numeral(totalSettlePrice).format() + '<?= gd_currency_string(); ?> ' + totalCancelMinusText);
            $('input[name="appointmentSettlePrice"]').val(totalSettlePrice);
            $('input[name="cancelPriceBySmsSend"]').val(totalCancelPrice);
        }
        $("input[name*='totalCancelDeliveryPrice[").trigger('blur');
        $("input[name='totalCancelDeposit']").trigger('blur');
        $("input[name='totalCancelMileage']").trigger('blur');
        // 배송비 할인혜택 초기 금액셋팅 및 계산
        $("input[name='totalCancelDeliveryCouponDcFl']:checked, input[name='cancelDeliveryMemberDcFl']:checked").each(function() {
            $(this).trigger('click');
        });
        setCancelPrice();
    });
</script>
