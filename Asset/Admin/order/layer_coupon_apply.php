<div class="self-order-coupon-apply">
    <div class="box coupon-down-layer apply-layer">
        <div class="view">
            <p>선택한 상품에 적용가능한 총 <strong><?=count($memberCouponArrData)?>개 의 보유쿠폰이 있습니다</strong></p>
            <div class="scroll-box">
                <form name="frmCouponApply" id="frmCouponApply" method="post">
                    <input type="hidden" name="memNo" value="<?=$memNo?>"/>
                    <input type="hidden" name="cart[cartSno]" value=""/>
                    <input type="hidden" name="cart[goodsNo]" value=""/>
                    <input type="hidden" name="cart[goodsCnt]" value=""/>
                    <input type="hidden" name="cart[addGoodsNo]" value=""/>
                    <input type="hidden" name="cart[addGoodsCnt]" value=""/>
                    <input type="hidden" name="cart[couponApplyNo]" value=""/>
                    <input type="hidden" name="cart[couponNo]" value=""/>
                    <input type="hidden" name="memberCartAddTypeCouponNo" value="<?=$memberCartAddTypeCouponNo?>"/>

                    <div class="table1">
                        <table class="table table-cols">
                            <colgroup>
                                <col style="width:60px"/>
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
                            <?php
                            foreach($cartCouponArrData as $key => $value){
                            ?>
                            <tr>
                                <td class="ta-c">
                                    <span class="form-element">
                                        <?php if($convertCartCouponPriceArrData['memberCouponAlertMsg'][$value['memberCouponNo']] == 'LIMIT_MIN_PRICE'){ ?>
                                            <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="memberCouponNo[]" class="checkbox" checked="checked" disabled="disabled">
                                        <?php } else { ?>
                                            <?php if($value['couponKindType'] == 'sale'){ ?>
                                            <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="memberCouponNo[]" class="checkbox" checked="checked" data-price="<?=$convertCartCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                            <?php } else if($value['couponKindType'] == 'add'){ ?>
                                            <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="memberCouponNo[]" class="checkbox" checked="checked" data-price="<?=$convertCartCouponPriceArrData['memberCouponAddMileage'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                            <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                            <input type="checkbox" id="check<?=$value['memberCouponNo']?>" name="memberCouponNo[]" class="checkbox" checked="checked" data-price="<?=$convertCartCouponPriceArrData['memberCouponDeliveryPrice'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" value="<?=$value['memberCouponNo']?>">
                                            <?php } ?>
                                        <?php } ?>
                                    </span>
                                </td>
                                <td class="guide2">
                                    <label for="check<?=$value['memberCouponNo']?>">
                                        <b><?=gd_currency_symbol()?></b>
                                        <?php if($value['couponKindType'] == 'sale'){ ?>
                                            <strong><?=gd_money_format($convertCartCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'add'){ ?>
                                            <strong><?=gd_money_format($convertCartCouponPriceArrData['memberCouponAddMileage'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                            <strong><?=gd_money_format($convertCartCouponPriceArrData['memberCouponDeliveryPrice'][$value['memberCouponNo']])?></strong>
                                        <?php } ?>
                                        <b><?=gd_currency_string()?></b>
                                        <span><?=$convertCartCouponArrData[$key]['couponKindType']?></span>
                                        <em><?=$value['couponNm']?></em>
                                    </label>
                                </td>
                                <td>
                                    <div class="msg">
                                        <?php if($convertCartCouponArrData[$key]['couponMaxBenefit']){ ?>
                                            <span>- <?=$convertCartCouponArrData[$key]['couponMaxBenefit']?></span>
                                        <?php } ?>
                                        <?php if($convertCartCouponArrData[$key]['couponMinOrderPrice']){ ?>
                                            <span>- <?=$convertCartCouponArrData[$key]['couponMinOrderPrice']?></span>
                                        <?php } ?>
                                        <span>- <?=$convertCartCouponArrData[$key]['couponApplyDuplicateType']?></span>
                                    </div>
                                </td>
                                <td class="ta-c date">
                                    <?=$value['memberCouponEndDate']?>
                                </td>
                            </tr>
                            <?php
                            }
                            ?>
                            <?php
                            $idx = 0;
                            foreach($memberCouponArrData as $key => $value){
                            ?>
                            <tr>
                                <td class="ta-c">
                                <span class="form-element">
                                    <?php if($convertMemberCouponPriceArrData['memberCouponAlertMsg'][$value['memberCouponNo']] == 'LIMIT_MIN_PRICE'){ ?>
                                    <input type="checkbox" id="check<?=$idx?>" class="checkbox" disabled="disabled">
                                    <?php } else { ?>
                                        <?php if($value['couponKindType'] == 'sale'){ ?>
                                            <input type="checkbox" id="check<?=$idx?>" name="memberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-useprice="<?=$cartUseMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-write-useprice="<?=$writeCartUseMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>"  data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" data-couponstate="<?=$value['memberCouponState']?>"  data-write-couponstate="<?=$value['orderWriteCouponState']?>" data-couponno="<?=$value['couponNo']?>" value="<?=$value['memberCouponNo']?>">
                                        <?php } else if($value['couponKindType'] == 'add'){ ?>
                                            <input type="checkbox" id="check<?=$idx?>" name="memberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['memberCouponAddMileage'][$value['memberCouponNo']]?>" data-useprice="<?=$cartUseMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-write-useprice="<?=$writeCartUseMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" data-couponstate="<?=$value['memberCouponState']?>" data-write-couponstate="<?=$value['orderWriteCouponState']?>"  data-couponno="<?=$value['couponNo']?>" value="<?=$value['memberCouponNo']?>">
                                        <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                            <input type="checkbox" id="check<?=$idx?>" name="memberCouponNo[]" class="checkbox" data-paytype="<?=$value['couponUseAblePaymentType']?>" data-price="<?=$convertMemberCouponPriceArrData['memberCouponDeliveryPrice'][$value['memberCouponNo']]?>" data-useprice="<?=$cartUseMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>" data-write-useprice="<?=$writeCartUseMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']]?>"  data-type="<?=$value['couponKindType']?>" data-duplicate="<?=$value['couponApplyDuplicateType']?>" data-couponstate="<?=$value['memberCouponState']?>" data-write-couponstate="<?=$value['orderWriteCouponState']?>"  data-couponno="<?=$value['couponNo']?>" value="<?=$value['memberCouponNo']?>">
                                        <?php } ?>
                                    <?php } ?>
                                </span>
                                </td>
                                <td class="guide2">
                                    <label for="check<?=$idx?>">
                                        <b><?=gd_currency_symbol()?></b>
                                        <?php if($value['couponKindType'] == 'sale'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['memberCouponSalePrice'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'add'){ ?>
                                        <strong><?=gd_money_format($convertMemberCouponPriceArrData['memberCouponAddMileage'][$value['memberCouponNo']])?></strong>
                                        <?php } else if($value['couponKindType'] == 'delivery'){ ?>
                                            <strong><?=gd_money_format($convertMemberCouponPriceArrData['memberCouponDeliveryPrice'][$value['memberCouponNo']])?></strong>
                                        <?php } ?>
                                        <b><?=gd_currency_string()?></b>
                                        <span><?=$convertMemberCouponArrData[$key]['couponKindType']?></span>
                                        <em><?=$value['couponNm']?></em>
                                    </label>
                                </td>
                                <td>
                                    <div class="msg">
                                        <?php if($convertMemberCouponArrData[$key]['couponMaxBenefit']){ ?>
                                        <span>- <?=$convertMemberCouponArrData[$key]['couponMaxBenefit']?></span>
                                        <?php } ?>
                                        <?php if($convertMemberCouponArrData[$key]['couponMinOrderPrice']){ ?>
                                        <span>- <?=$convertMemberCouponArrData[$key]['couponMinOrderPrice']?></span>
                                        <?php } ?>
                                        <span>- <?=$convertMemberCouponArrData[$key]['couponApplyDuplicateType']?></span>
                                    </div>
                                </td>
                                <td class="ta-c date">
                                    <?=$value['memberCouponEndDate']?>
                                </td>
                            </tr>
                            <?php
                                $idx++;
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="benefits">
                <div class="detail">
                    <div>
                        <span>총 할인금액</span> <strong><?=gd_currency_symbol()?><b id="couponSalePrice">0</b><?=gd_currency_string()?></strong>
                    </div>
                    <div>
                        <span>총 적립금액</span> <strong><?=gd_currency_symbol()?><b id="couponAddPrice">0</b><?=gd_currency_string()?></strong>
                    </div>
                </div>
            </div>
            <div class="text-center mgt20">
                <button class="skinbtn point1 layer-close btn-close"><em>취소</em></button>
                <button class="skinbtn point2 lca-couponapply" id="btnCouponApply"><em>쿠폰 적용</em></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('input:checkbox[name="memberCouponNo[]"]').click(function (e) {
            if (($(this).prop('checked') == true) && ($(this).data('duplicate') == 'n')) {
                $('input:checkbox[name="memberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).attr("checked", false);
                    $(this).next('label').removeClass('on');
                    $(this).attr('disabled', 'disabled');
                });
            } else if (($(this).prop('checked') == false) && ($(this).data('duplicate') == 'n')) {
                $('input:checkbox[name="memberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).removeAttr('disabled', 'disabled');
                });
            }

            // 장바구니 사용 상태의 쿠폰 선택시 사용여부 레이어
            if (($(this).prop('checked') == true) && ($(this).data('couponstate') == 'cart' || $(this).data('write-couponstate') == 'cart')) {
                var couponPrice = ($(this).data('couponstate') == 'cart') ? numeral($(this).data('useprice')).format() : numeral($(this).data('write-useprice')).format();
                var couponKindType = $(this).data('type');
                var couponKindTypeName = '';
                var couponCheckId = $(this).attr('id');

                switch (couponKindType) {
                    case 'sale':
                        couponKindTypeName = '할인';
                        break;
                    case 'add':
                        couponKindTypeName = '적립';
                        break;
                    case 'delivery':
                        couponKindTypeName = '배송비할인';
                        break;
                    case 'deposit':
                        couponKindTypeName = '예치금지급';
                        break;
                }

                var couponApplyNoArr = [];
                $('input:checkbox[name="memberCouponNo[]"]:checked').each(function (index) {
                    couponApplyNoArr[index] = $(this).val();
                });
                var couponApplyNoString = couponApplyNoArr.join('<?=$int_division?>');
                // 장바구니 구분 (일반 : cart / 수기 : write)
                if ($(this).data('couponstate') == 'cart') {
                    var cartUseType = 'cart';
                }

                if ($(this).data('write-couponstate') == 'cart') {
                    var cartUseType = 'write';
                }

                var html = '<div class="center pdb10">장바구니에 담긴 상품에 <b>' + couponPrice + '원 ' + couponKindTypeName + '</b> 으로 이미 적용되어있는 쿠폰입니다.</div>';
                html += '<div class="center pdb10">취소 후 이 상품에 적용하시겠습니까?</div>';
                dialog_confirm(html, function (result) {
                    if (result) {
                        $.ajax({
                            method: "POST",
                            cache: false,
                            url: "../order/order_ps.php",
                            data: {
                                'mode': 'UserCartCouponDel',
                                'memberCouponNo': couponApplyNoString,
                                'memNo': $("#frmCouponApply>input[name='memNo']").val(),
                                'cartUseType' : cartUseType
                            },
                        }).success(function (data) {
                            // alert(data);
                        }).error(function (e) {
                            // alert(e.responseText);
                            return false;
                        });
                    } else {
                        $('#'+couponCheckId).attr("checked", false);
                        $('#'+couponCheckId).next('label').removeClass('on');
                        couponPriceSum();
                    }
                });
            }
            couponPriceSum();
        });

        $('.btn-close').click(function () {
            layer_close();
        });

        $('#btnCouponApply').click(function (e) {
            var couponApplyNoArr = [];
            $('input:checkbox[name="memberCouponNo[]"]:checked').each(function (index) {
                couponApplyNoArr[index] = $(this).val();
            });
            var couponApplyNoString = couponApplyNoArr.join('<?=$int_division?>');
            $('[name="cart[cartSno]"]').val('<?=$cartSno?>');
            $('[name="cart[couponApplyNo]"]').val(couponApplyNoString);

            $('#frmCouponApply input[name=\'mode\']').val('order_write_goods_coupon_apply');
            var params = $( "#frmCouponApply" ).serialize();

            var ajaxUrl = "../order/order_ps.php?mode=order_write_goods_coupon_apply";

            $.ajax({
                method: "get",
                cache: false,
                url: ajaxUrl,
                data: params,
                dataType: 'json',
                success: function (returnData) {
                    if (!_.isUndefined(returnData.code) && returnData.code == 0) {
                        alert(returnData.message);
                        setTimeout(function () {
                            layer_close();
                        }, 2000);
                        return false;
                    }
                    //사용처리된 쿠폰 번호. (실제 cart 에서 삭제되어야 할 쿠폰번호 array 형태)
                    if($.trim($("input[name='memberCartAddTypeCouponNo']").val()) !== ''){
                        parent.updateMemberCartSnoCookie($('[name="cart[cartSno]"]').val(), returnData);
                    }

                    parent.set_goods('y');

                    layer_close();
                },
                error: function (data) {
                    alert(data.message);

                }
            });

            return false;
        });
        couponApplySetting();
        couponPriceSum();
    });

    // 쿠폰 적용 내용 초기화 (설정)
    function couponApplySetting() {
        $.each($('input:checkbox[name="memberCouponNo[]"]:checked'), function (index){
            if ($(this).data('duplicate') == 'n') {
                $('input:checkbox[name="memberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).attr("checked", false);
                    $(this).next('label').removeClass('on');
                    $(this).attr('disabled', 'disabled');
                });
            } else if ($(this).data('duplicate') == 'n') {
                $('input:checkbox[name="memberCouponNo[]"]').not($(this)).each(function (index) {
                    $(this).removeAttr('disabled', 'disabled');
                });
            }
        });
    }

    // 선택 쿠폰 금액 계산
    function couponPriceSum() {
        var salePrice = 0;
        var addPrice = 0;
        $('input:checkbox[name="memberCouponNo[]"]:checked').each(function (index) {
            if ($(this).data('type') == 'sale') {
                salePrice += parseFloat($(this).data('price'));
            } else if ($(this).data('type') == 'add') {
                addPrice += parseFloat($(this).data('price'));
            }
        });

        $('#couponSalePrice').text(numeral(salePrice).format());
        $('#couponAddPrice').text(numeral(addPrice).format());
    }
    //-->
</script>
