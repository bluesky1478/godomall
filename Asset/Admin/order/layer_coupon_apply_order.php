<div class="self-order-coupon-apply">
    <div class="box coupon-down-layer my-coupon-layer">
        <div class="view">
            <p>가능한 쿠폰만 노출됩니다. 실제 보유한 쿠폰과 차이가 날 수 있습니다.</p>
            <div class="scroll-box">
                <h3>주문쿠폰</h3>

                <div class="table1">
                    <table class="table table-cols">
                        <colgroup>
                            <col style="width:80px"/>
                            <col/>
                            <col/>
                            <col/>
                        </colgroup>
                        <thead>
                        <tr>
                            <th></th>
                            <th>쿠폰</th>
                            <th>사용조건</th>
                            <th>사용기한</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(count($memberCouponArrData['order']) > 0){ ?>
                            <?php foreach($memberCouponArrData['order'] as $key => $value){ ?>
                            <tr>
                                <td class="ta-c">
                                        <span class="form-element">
                                            <?php if($convertMemberCouponPriceArrData['order']['memberCouponAlertMsg'][$value['memberCouponNo']] == 'LIMIT_MIN_PRICE'){ ?>
                                                <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="orderMemberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>">
                                            <?php } else { ?>
                                                <?php if($value['couponKindType'] == 'sale'){ ?>
                                                    <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="orderMemberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['order']['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                                <?php } else if($value['couponKindType'] == 'add'){ ?>
                                                    <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="orderMemberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['order']['memberCouponAddMileage'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                                <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                                    <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="orderMemberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['order']['memberCouponDeliveryPrice'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                                <?php } ?>
                                            <?php } ?>
                                        </span>
                                </td>
                                <td class="guide2">
                                    <label for="check<?=$value['memberCouponNo']?>">
                                        <b><?=gd_currency_symbol()?></b>
                                        <?php if($value['couponKindType'] == 'sale'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['order']['memberCouponSalePrice'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'add'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['order']['memberCouponAddMileage'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['order']['memberCouponDeliveryPrice'][$value['memberCouponNo']])?></strong>
                                        <?php } ?>
                                        <b><?=gd_currency_string()?></b>
                                        <span><?=$convertMemberCouponArrData['order'][$key]['couponKindType']?></span>
                                        <em><?=$value['couponNm']?></em>
                                    </label>
                                </td>
                                <td>
                                    <div class="msg">
                                        <?php if($convertMemberCouponArrData['order'][$key]['couponMaxBenefit']){ ?>
                                        <span>- <?=$convertMemberCouponArrData['order'][$key]['couponMaxBenefit']?></span>
                                        <?php } ?>
                                        <?php if($convertMemberCouponArrData['order'][$key]['couponMinOrderPrice']){ ?>
                                        <span>- <?=$convertMemberCouponArrData['order'][$key]['couponMinOrderPrice']?></span>
                                        <?php } ?>
                                        <span>- <?=$convertMemberCouponArrData['order'][$key]['couponApplyDuplicateType']?></span>
                                    </div>
                                </td>
                                <td class="ta-c date">
                                    <?=$value['memberCouponEndDate']?>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } else { ?>
                        <tr>
                            <td class="no-data" colspan="10">쿠폰이 없습니다.</td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <h3>배송비쿠폰</h3>

                <div class="table1">
                    <table class="table table-cols">
                        <colgroup>
                            <col style="width:80px"/>
                            <col/>
                            <col/>
                            <col/>
                        </colgroup>
                        <thead>
                        <tr>
                            <th></th>
                            <th>쿠폰</th>
                            <th>사용조건</th>
                            <th>사용기한</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(count($memberCouponArrData['delivery']) > 0){ ?>
                            <?php foreach($memberCouponArrData['delivery'] as $key => $value){ ?>
                            <tr>
                                <td class="ta-c">
                                        <span class="form-element">
                                            <?php if($convertMemberCouponPriceArrData['delivery']['memberCouponAlertMsg'][$value['memberCouponNo']]  == 'LIMIT_MIN_PRICE'){ ?>
                                                <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="deliveryMemberCouponNo[]" class="checkbox coupon-checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>">
                                            <?php } else { ?>
                                                <?php if($value['couponKindType'] == 'sale'){ ?>
                                                    <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="deliveryMemberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['delivery']['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                                <?php } else if($value['couponKindType'] == 'add'){ ?>
                                                    <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="deliveryMemberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['delivery']['memberCouponAddMileage'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                                <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                                    <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="deliveryMemberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['delivery']['memberCouponDeliveryPrice'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                                <?php } ?>
                                            <?php } ?>
                                        </span>
                                </td>
                                <td class="guide2">
                                    <label for="check<?=$value['memberCouponNo']?>">
                                        <b><?=gd_currency_symbol()?></b>
                                        <?php if($value['couponKindType'] == 'sale'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['delivery']['memberCouponSalePrice'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'add'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['delivery']['memberCouponAddMileage'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['delivery']['memberCouponDeliveryPrice'][$value['memberCouponNo']])?></strong>
                                        <?php } ?>
                                        <b><?=gd_currency_string()?></b>
                                        <span><?=$convertMemberCouponArrData['delivery'][$key]['couponKindType']?></span>
                                        <em><?=$value['couponNm']?></em>
                                    </label>
                                </td>
                                <td>
                                    <div class="msg">
                                        <?php if($convertMemberCouponArrData['delivery'][$key]['couponMaxBenefit']){ ?>
                                        <span>- <?=$convertMemberCouponArrData['delivery'][$key]['couponMaxBenefit']?></span>
                                        <?php } ?>
                                        <?php if($convertMemberCouponArrData['delivery'][$key]['couponMinOrderPrice']){ ?>
                                        <span>- <?=$convertMemberCouponArrData['delivery'][$key]['couponMinOrderPrice']?></span>
                                        <?php } ?>
                                        <span>- <?=$convertMemberCouponArrData['delivery'][$key]['couponApplyDuplicateType']?></span>
                                    </div>
                                </td>
                                <td class="ta-c date">
                                    <?=$value['memberCouponEndDate']?>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } else { ?>
                        <tr>
                            <td class="no-data" colspan="10">쿠폰이 없습니다.</td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="benefits">
                <div class="detail">
                    <div>
                        <span>총 할인금액</span> <strong><?=gd_currency_symbol()?><b id="couponSalePrice">0</b><?=gd_currency_string()?></strong>
                    </div>
                    <div>
                        <span>총 적립금액</span> <strong><?=gd_currency_symbol()?><b id="couponAddPrice">0</b><?=gd_currency_string()?></strong>
                    </div>
                    <div>
                        <span>배송비 할인금액</span> <strong><?=gd_currency_symbol()?><b id="couponDeliveryPrice">0</b><?=gd_currency_string()?></strong>
                    </div>
                </div>
            </div>
            <div class="text-center mgt20">
                <button class="skinbtn point1 layerboard-close btn-close"><em>취소</em></button>
                <button class="skinbtn point2 lca-couponapply" id="btnCouponApply"><em>쿠폰 적용</em></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('input:checkbox[name="orderMemberCouponNo[]"]').click(function (e) {
            if (($(this).prop('checked') == true) && ($(this).data('duplicate') == 'n')) {
                $('input:checkbox[name="orderMemberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).attr("checked", false);
                    $(this).next('label').removeClass('on');
                    $(this).attr('disabled', 'disabled');
                });
            } else if (($(this).prop('checked') == false) && ($(this).data('duplicate') == 'n')) {
                $('input:checkbox[name="orderMemberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).removeAttr('disabled', 'disabled');
                });
            }
            var objCouponPrice = couponPriceSum();
            $('#couponSalePrice').text(numeral(objCouponPrice.saleDc).format());
            $('#couponAddPrice').text(numeral(objCouponPrice.add).format());
        });
        $('input:checkbox[name="deliveryMemberCouponNo[]"]').click(function (e) {
            if (($(this).prop('checked') == true) && ($(this).data('duplicate') == 'n')) {
                $('input:checkbox[name="deliveryMemberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).attr("checked", false);
                    $(this).next('label').removeClass('on');
                    $(this).attr('disabled', 'disabled');
                });
            } else if (($(this).prop('checked') == false) && ($(this).data('duplicate') == 'n')) {
                $('input:checkbox[name="deliveryMemberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).removeAttr('disabled', 'disabled');
                });
            }
            var objCouponPrice = couponPriceSum();
            $('#couponDeliveryPrice').text(numeral(objCouponPrice.deliveryDc).format());
        });
        $('.btn-close').click(function () {
            layer_close();
        });
        $('#btnCouponApply').click(function (e) {
            var couponApplyNoArr = new Array;
            var couponPaymentTypeCheck = 'n';
            $('input:checkbox[name="orderMemberCouponNo[]"]:checked').each(function (index) {
                couponApplyNoArr.push($(this).val());
                // 결제방식제한쿠폰체크
                if ($(this).attr('data-paytype') == 'bank') {
                    couponPaymentTypeCheck = 'y';
                }
            });
            $('input:checkbox[name="deliveryMemberCouponNo[]"]:checked').each(function (index) {
                couponApplyNoArr.push($(this).val());
                // 결제방식제한쿠폰체크
                if ($(this).attr('data-paytype') == 'bank') {
                    couponPaymentTypeCheck = 'y';
                }
            });
            if (couponApplyNoArr.length > 0) {
                var couponApplyNoString = couponApplyNoArr.join('<?=$int_division?>');
                var objCouponPrice = couponPriceSum();
                $('input:hidden[name="couponApplyOrderNo"]').val(couponApplyNoString);
                $('input:hidden[name="totalCouponOrderDcPrice"]').val(objCouponPrice.saleDc);
                $('input:hidden[name="totalCouponOrderPrice"]').val(objCouponPrice.sale);
                $('input:hidden[name="totalCouponOrderMileage"]').val(objCouponPrice.add);
                $('input:hidden[name="totalCouponDeliveryDcPrice"]').val(objCouponPrice.deliveryDc);
                $('input:hidden[name="totalCouponDeliveryPrice"]').val(objCouponPrice.delivery);
                $('#useDisplayCouponDcPrice').text(numeral(objCouponPrice.saleDc).format());
                $('#useDisplayCouponMileage').text(numeral(objCouponPrice.add).format());
                $('#useDisplayCouponDelivery').text(numeral(objCouponPrice.deliveryDc).format());
                $('.order-coupon-benefits').removeClass('display-none');
                if ($('input[name="chooseCouponMemberUseType"]').val() == 'coupon') {
                    $('#sale-default').addClass('display-none');
                    $('#sale-without-member').removeClass('display-none');
                    $('#mileage-default').addClass('display-none');
                    $('#mileage-without-member').removeClass('display-none');
                }
            } else {
                $('input:hidden[name="couponApplyOrderNo"]').val('');
                $('input:hidden[name="totalCouponOrderDcPrice"]').val('');
                $('input:hidden[name="totalCouponOrderPrice"]').val('');
                $('input:hidden[name="totalCouponOrderMileage"]').val('');
                $('input:hidden[name="totalCouponDeliveryDcPrice"]').val('');
                $('input:hidden[name="totalCouponDeliveryPrice"]').val('');
                $('#useDisplayCouponDcPrice').text(0);
                $('#useDisplayCouponMileage').text(0);
                $('#useDisplayCouponDelivery').text(0);
                $('.order-coupon-benefits').addClass('display-none');
                if ($('input[name="chooseCouponMemberUseType"]').val() == 'coupon') {
                    $('#sale-default').removeClass('display-none');
                    $('#sale-without-member').addClass('display-none');
                    $('#mileage-default').removeClass('display-none');
                    $('#mileage-without-member').addClass('display-none');
                }
            }

            if (couponPaymentTypeCheck == 'y') {
                if ($('#settlekind_escrow').length) {
                    $('#settlekind_escrow').addClass('display-none');
                }
                if ($('#settlekind_overseas').length) {
                    $('#settlekind_overseas').addClass('display-none');
                }
                if ($('#settlekind_payco').length) {
                    $('#settlekind_payco').addClass('display-none');
                }

                $('#settleKind_gb').trigger('click');
                $('label[for="settleKind_gb"]').addClass('on');

                $('[id^="settlekind_type_"]').each(function (index) {
                    if ($(this).attr('id') != 'settlekind_type_gb') {
                        var tempId = $(this).attr('id');
                        var sId = tempId.replace('settlekind_type_', '');
                        $(this).children('radio[id="settleKind_' + sId + '"]').prop('checked', false);
                        $(this).children('label[for="settleKind_' + sId + '"]').removeClass('on');
                        $(this).addClass('display-none');
                    }
                });
            } else {
                if ($('#settlekind_escrow').length) {
                    $('#settlekind_escrow').removeClass('display-none');
                }
                if ($('#settlekind_overseas').length) {
                    $('#settlekind_overseas').removeClass('display-none');
                }
                if ($('#settlekind_payco').length) {
                    $('#settlekind_payco').removeClass('display-none');
                }
                $('[id^="settlekind_type_"]').each(function (index) {
                    if ($(this).attr('id') != 'settlekind_type_gb') {
                        $(this).removeClass('display-none');
                    }
                });
            }

            // 마일리지, 예치금 초기화
            if ($('input:text[name="useDeposit"]').length) {
                $('input:text[name="useDeposit"]').val(0);
                $("#selfOrderUseDepositAll").prop("checked", false);
            }
            if ($('input:text[name="useMileage"]').length) {
                $("#selfOrderUseMileageAll").prop("checked", false);
                $('input:text[name="useMileage"]').val(0);
            }

            var address = $('input[name="receiverAddress"]').val() + $('input[name="receiverAddressSub"]').val();
            var memNo = $('input[name="memNo"]').val();

            //주문쿠폰 적용시 재계산
            var cartPrice = set_recalculation();
            set_real_settle_price(cartPrice, 'y');

            // 마일리지 사용에 관한 text 문구
            if($("input[name='memNo']").val() && $("input[name='memNo']").val() !== '0') {
                mileage_use_check();
            }

            displayBankArea();

            layer_close([]);
        });
        couponApplySetting();
    });

    // 쿠폰 적용 내용 초기화 (설정)
    function couponApplySetting() {
        var couponApplyNoString = '<?=$couponApplyOrderNo?>';
        var couponApplyNoArr = new Array();
        if (couponApplyNoString) {
            var couponApplyNoArr = couponApplyNoString.split('<?=$int_division?>');
        }
        $.each(couponApplyNoArr, function (index) {
            $('input:checkbox[name="orderMemberCouponNo[]"][value="' + couponApplyNoArr[index] + '"]').trigger('click');
            $('input:checkbox[name="deliveryMemberCouponNo[]"][value="' + couponApplyNoArr[index] + '"]').trigger('click');
        });
        var objCouponPrice = couponPriceSum();
        $('#couponSalePrice').text(numeral(objCouponPrice.saleDc).format());
        $('#couponAddPrice').text(numeral(objCouponPrice.add).format());
        $('#couponDeliveryPrice').text(numeral(objCouponPrice.deliveryDc).format());
    }

    // 선택 쿠폰 금액 계산
    function couponPriceSum() {
        var salePrice = 0;
        var saleDcPrice = 0;
        var originSalePrice = parseInt($('#totalGoodsPrice').text().replace(/[^\d]+/g, ''));
        var addPrice = 0;
        var deliveryPrice = 0;
        var deliveryDcPrice = 0;
        var originDeliveryCharge = parseInt($('#totalDeliveryCharge').text().replace(/[^\d]+/g, '')) + parseInt($('#deliveryAreaCharge').text().replace(/[^\d]+/g, ''));
        $('input:checkbox[name="orderMemberCouponNo[]"]:checked').each(function (index) {
            if ($(this).data('type') == 'sale') {
                salePrice += parseFloat($(this).data('price'));
            } else if ($(this).data('type') == 'add') {
                addPrice += parseFloat($(this).data('price'));
            }
        });
        $('input:checkbox[name="deliveryMemberCouponNo[]"]:checked').each(function (index) {
            if ($(this).data('type') == 'delivery') {
                deliveryPrice += parseFloat($(this).data('price'));
            }
        });
        if (!_.isUndefined(originSalePrice) && salePrice > originSalePrice) {
            saleDcPrice = originSalePrice;
        } else {
            saleDcPrice = salePrice;
        }
        if (!_.isUndefined(originDeliveryCharge) && deliveryPrice > originDeliveryCharge) {
            deliveryDcPrice = originDeliveryCharge;
        } else {
            deliveryDcPrice = deliveryPrice;
        }

        var couponPrice = {
            'sale': salePrice,
            'saleDc': saleDcPrice,
            'add': addPrice,
            'delivery': deliveryPrice, // 쿠폰 자체의 할인가
            'deliveryDc': deliveryDcPrice, // 배송비 금액에 따른 실제 할인가
        };

        return couponPrice;
    }
    //-->
</script>
