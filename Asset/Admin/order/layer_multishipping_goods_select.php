<div class="self-order-multishipping-goods-select">
    <div class="table1">
        <table class="table table-cols">
            <colgroup>
                <col class="width60p" />
                <col class="width20p" />
                <col class="width20p" />
            </colgroup>
            <thead>
            <tr>
                <th> 상품 / 옵션정보 </th>
                <th> 수량 </th>
                <th> 배송방법 </th>
            </tr>
            </thead>
            <tbody>
            <?php
            if(count($cartInfo) > 0){
                foreach ($cartInfo as $sKey => $sVal) {
                    foreach ($sVal as $dKey => $dVal) {
                        foreach ($dVal as $gKey => $gVal) {
                            $goodsDeliveryMethodFl = gd_isset($nowData['deliveryMethodFl'][$shippingNo][$gVal['goodsNo']][$gVal['sno']], $gVal['goodsDeliveryMethodFl']);
                            $deliveryCollectFl = gd_isset($nowData['deliveryCollectFl'][$shippingNo][$gVal['goodsNo']][$gVal['sno']], $gVal['deliveryCollectFl']);
            ?>
                            <input type="hidden" name="multiShippingCartSno[]" data-scm-no="<?=$gVal['scmNo']?>" data-delivery-sno="<?=$gVal['deliverySno']?>" value="<?=$gVal['sno']?>" />
                            <tr>
                                <!-- 상품정보 -->
                                <td>
                                    <div class="goods-info" style="display: table;">
                                        <div class="pdr10" style="display: table-cell; vertical-align: middle;">
                                            <?=$gVal['goodsImage']?>
                                        </div>
                                        <div style="display: table-cell;">
                                            <?=$gVal['goodsNm']?>

                                            <?php
                                            if(count($gVal['option']) > 0){
                                                foreach($gVal['option'] as $optKey => $optVal){
                                                    $optionName = "<div style='color: #a9a9a9;'>";
                                                    $optionName .= $optVal['optionName'] . ' : ' . $optVal['optionValue'];
                                                    if($optVal['optionPrice'] > 0){
                                                        $optionName .= ' (+' . $optVal['optionPrice'] . ')';
                                                    }
                                                    $optionName .= "</div>";

                                                    echo $optionName;
                                                }
                                            }

                                            if(count($gVal['optionText']) > 0){
                                                foreach($gVal['optionText'] as $optTextKey => $optTextVal){
                                                    $optionName = "<div style='color: #a9a9a9;'>";
                                                    $optionName .= $optTextVal['optionName'] . ' : ' . $optTextVal['optionValue'];
                                                    if($optTextVal['optionPrice'] > 0){
                                                        $optionName .= ' (+' . $optTextVal['optionPrice'] . ')';
                                                    }
                                                    $optionName .= "</div>";

                                                    echo $optionName;
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <!-- 상품정보-->

                                <!-- 수량 -->
                                <td>
                                    <div class="price">
                                        <span class="count">
                                            <div class="display-inline-block">
                                                <input type="text" class="text" title="수량" name="multiShippingGoodsCnt[]" value="<?=gd_isset($nowData['goods'][$gVal['goodsNo']][$gVal['sno']], 0)?>" data-max-cnt="<?=$gVal['goodsCnt']-$setData['goods'][$gVal['goodsNo']][$gVal['sno']]?>" data-cart-sno="<?=$gVal['sno']?>" data-goods-no="<?=$gVal['goodsNo']?>" data-fixed-sales="<?=$gVal['fixedSales']?>" data-fixed-order-cnt="<?=$gVal['fixedOrderCnt']?>" data-min-order-cnt="<?=$gVal['minOrderCnt']?>" data-goods-key="<?=$gVal['goodsKey']?>" data-sales-unit="<?php if($gVal['fixedSales'] == 'option'){ echo $gVal['salesUnit']; } else { echo 1; }?>" data-real-sales-unit="<?=$gVal['salesUnit']?>" data-goods-nm="<?=gd_remove_only_tag($gVal['goodsNm'])?>" data-option-nm="<?=$gVal['optionNm']?>" data-parent-cart-sno="<?=$gVal['parentCartSno']?>" data-goods-delivery-fl="<?=$gVal['goodsDeliveryFl']?>" data-same-goods-delivery-fl="<?=$gVal['sameGoodsDeliveryFl']?>">
                                                <span>
                                                    <button type="button" class="up goods-cnt hand" title="증가">증가</button>
                                                    <button type="button" class="down goods-cnt hand" title="감소">감소</button>
                                                </span>
                                            </div>

                                            <div class="display-inline-block">&nbsp;/&nbsp;<?=$gVal['goodsCnt']-$setData['goods'][$gVal['goodsNo']][$gVal['sno']]?></div>
                                        </span>
                                    </div>
                                </td>
                                <!-- 수량 -->

                                <?php
                                if ($gVal['goodsDeliveryFl'] == 'y') {
                                    if ($gKey == '0') {
                                    ?>
                                        <!-- 배송방법 -->
                                        <td align="center" rowspan="<?=(($setDeliveryInfo[$dKey]['goodsLineCnt']) + $setDeliveryInfo[$dKey]['addGoodsLineCnt']); ?>">
                                            <select name="multiShippingDeliveryMethodFl[]" data-cart-sno="<?=$gVal['sno']?>" style="max-width:100px;">
                                                <?php foreach ($deliveryBasicInfo[$gVal['deliverySno']]['deliveryMethodFl'] as $deliveryMethodFl) { ?>
                                                    <option value="<?=$deliveryMethodFl; ?>" <?=$deliveryMethodFl == $goodsDeliveryMethodFl ? 'selected="selected"' : ''; ?>><?=$deliveryMethodFl == 'etc' ? $deliveryMethodEtc : $deliveryBasicName[$deliveryMethodFl]; ?></option>
                                                <?php } ?>
                                            </select><br />
                                            <select class="mgt5" name="multiShippingDeliveryCollectFl[]" data-cart-sno="<?=$gVal['sno']?>">
                                                <?php foreach ($deliveryCollect[$deliveryBasicInfo[$gVal['deliverySno']]['collectFl']] as $key => $collectFl) { ?>
                                                    <option value="<?=$key?>" <?=$key == $deliveryCollectFl ? 'selected="selected"' : ''; ?>><?=$collectFl?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <!-- 배송방법 -->
                                <?php
                                    }
                                } else {
                                    if ($gVal['sameGoodsDeliveryFl'] == 'y') {
                                        if ($gVal['equalGoodsNo'] === true) {
                                            ?>
                                            <!-- 배송방법 -->
                                            <td align="center"
                                                rowspan="<?= (($setDeliveryInfo[$dKey][$gVal['goodsNo']]['goodsLineCnt']) + $setDeliveryInfo[$dKey][$gVal['goodsNo']]['addGoodsLineCnt']); ?>">
                                                <select name="multiShippingDeliveryMethodFl[]" data-cart-sno="<?= $gVal['sno'] ?>" style="max-width:100px;">
                                                    <?php foreach ($deliveryBasicInfo[$gVal['deliverySno']]['deliveryMethodFl'] as $deliveryMethodFl) { ?>
                                                        <option value="<?= $deliveryMethodFl; ?>" <?= $deliveryMethodFl == $goodsDeliveryMethodFl ? 'selected="selected"' : ''; ?>><?= $deliveryMethodFl == 'etc' ? $deliveryMethodEtc : $deliveryBasicName[$deliveryMethodFl]; ?></option>
                                                    <?php } ?>
                                                </select><br/>
                                                <select class="mgt5" name="multiShippingDeliveryCollectFl[]" data-cart-sno="<?= $gVal['sno'] ?>">
                                                    <?php foreach ($deliveryCollect[$deliveryBasicInfo[$gVal['deliverySno']]['collectFl']] as $key => $collectFl) { ?>
                                                        <option value="<?= $key ?>" <?= $key == $deliveryCollectFl ? 'selected="selected"' : ''; ?>><?= $collectFl ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                            <!-- 배송방법 -->
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <!-- 배송방법 -->
                                        <td align="center"
                                            rowspan="<?= empty($gVal['addGoods']) === false ? (count($gVal['addGoods']) + 1) : 1; ?>">
                                            <select name="multiShippingDeliveryMethodFl[]" data-cart-sno="<?= $gVal['sno'] ?>" style="max-width:100px;">
                                                <?php foreach ($deliveryBasicInfo[$gVal['deliverySno']]['deliveryMethodFl'] as $deliveryMethodFl) { ?>
                                                    <option value="<?= $deliveryMethodFl; ?>" <?= $deliveryMethodFl == $goodsDeliveryMethodFl ? 'selected="selected"' : ''; ?>><?= $deliveryMethodFl == 'etc' ? $deliveryMethodEtc : $deliveryBasicName[$deliveryMethodFl]; ?></option>
                                                <?php } ?>
                                            </select><br/>
                                            <select class="mgt5" name="multiShippingDeliveryCollectFl[]" data-cart-sno="<?= $gVal['sno'] ?>">
                                                <?php foreach ($deliveryCollect[$deliveryBasicInfo[$gVal['deliverySno']]['collectFl']] as $key => $collectFl) { ?>
                                                    <option value="<?= $key ?>" <?= $key == $deliveryCollectFl ? 'selected="selected"' : ''; ?>><?= $collectFl ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <!-- 배송방법 -->
                                        <?php
                                    }
                                } ?>
                            </tr>

                            <?php
                            if(count($gVal['addGoods']) > 0){
                                foreach($gVal['addGoods'] as $agKey => $agVal) {
                            ?>
                            <tr>
                                <!-- 상품정보 -->
                                <td>
                                    <div class="goods-info" style="display: table;">
                                        <div style="display: table-cell; vertical-align: middle;">
                                            <span class="label label-default">추가</span>
                                        </div>
                                        <div class="pdl5 pdr10" style="display: table-cell; vertical-align: middle;">
                                            <?=$agVal['addGoodsImage']?>
                                        </div>
                                        <div style="display: table-cell;">
                                            <?=$agVal['addGoodsNm']?>

                                            <?php
                                            if($agVal['optionNm']){
                                                echo "<div style='color: #a9a9a9;'>".$agVal['optionNm']."</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </td>
                                <!-- 상품정보 -->

                                <!-- 수량 -->
                                <td>
                                    <?php if($agVal['addGoodsMustFl'] === 'y'){ ?><div class="ta-c">(필수)</div><?php } ?>
                                    <div class="price">
                                        <span class="count">
                                            <div class="display-inline-block">
                                                <input type="text" class="text" title="수량" name="multiShippingAddGoodsCnt[]" value="<?=gd_isset($nowData['addGoods'][$agVal['addGoodsNo']][$gVal['sno']], 0)?>" data-max-cnt="<?=$agVal['addGoodsCnt']-$setData['addGoods'][$agVal['addGoodsNo']][$gVal['sno']]?>" data-cart-sno="<?=$gVal['sno']?>" data-parent-goods-no="<?=$gVal['goodsNo']?>" data-add-goods-no="<?=$agVal['addGoodsNo']?>" data-must-fl="<?=$agVal['addGoodsMustFl']?>" data-sales-unit="1" data-real-sales-unit="1" data-goods-nm="<?=gd_remove_only_tag($agVal['addGoodsNm'])?>">
                                                <span>
                                                    <button type="button" class="up goods-cnt hand" title="증가">증가</button>
                                                    <button type="button" class="down goods-cnt hand" title="감소">감소</button>
                                                </span>
                                            </div>
                                            <div class="display-inline-block">&nbsp;/&nbsp;<?=$agVal['addGoodsCnt']-$setData['addGoods'][$agVal['addGoodsNo']][$gVal['sno']]?></div>
                                        </span>
                                    </div>
                                </td>
                                <!-- 수량 -->
                            </tr>
                            <?php
                                }
                            }
                            ?>
            <?php
                        }
                    }
                }
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="ta-c">
        <button type="button" class="btn btn-lg btn-black shipping-save-btn">확인</button>
    </div>
</div>

<script type="text/html" class="template" id="selectGoodsRow">
    <tr class="add-select-goods-tr">
        <th class="nobold"><%=goodsInfo%></th>
        <th class="nobold"><%=goodsCnt%></th>
        <%=deliveryInfo%>
        <th>
            <div class="js-delete-multiShipping-select-goods">
                <button type="button" data-cart-sno="<%=cartSno%>" data-goods-no="<%=goodsNo%>" data-parent-goods-no="<%=parentGoodsNo%>" data-must-fl="<%=mustFl%>" data-type="<%=goodsType%>" data-parent-cart-sno="<%=parentCartSno%>" class="btn-icon-delete btn" style="padding: 5px 12px 4px !important;" data-toggle="delete"></button>
            </div>
        </th>
    </tr>
</script>
<script type="text/javascript">
    var shippingNo = '<?=$shippingNo?>';

    $(function(){
        $('.goods-cnt').click(function(){
            var $target = $(this).closest('span.count').find('input[type="text"]');
            var param = {
                nowCnt: parseInt($target.val()),
                maxCnt: parseInt($target.attr('data-max-cnt')),
                salesUnit: parseInt($target.attr('data-sales-unit'))
            }

            var mode = 'up';
            if ($(this).hasClass('down') === true) {
                mode = 'down';
            }

            var cnt = '';
            switch (mode) {
                case 'up':
                    if (param.nowCnt >= param.maxCnt) {
                        return;
                    }
                    cnt = param.nowCnt + param.salesUnit;
                    break;
                case 'down':
                    if (param.nowCnt <= 0) {
                        return;
                    }
                    cnt = param.nowCnt - param.salesUnit;
                    break;
            }
            $target.val(cnt);
        });

        $('input[name="multiShippingGoodsCnt[]"], input[name="multiShippingAddGoodsCnt[]"]').blur(function(){
            var param = {
                nowCnt: parseInt($(this).val()),
                maxCnt: parseInt($(this).attr('data-max-cnt')),
                salesUnit: parseInt($(this).attr('data-sales-unit')),
            }

            $(this).number_only("d");

            if (param.nowCnt > param.maxCnt) {
                alert('구매수량(' + param.maxCnt + '개)를 초과하였습니다.');
                $(this).val(param.maxCnt);
                return;
            }
            if (param.nowCnt < 0) {
                alert('구매수량은 0개 이상이어야 합니다.');
                $(this).val(0);
                return;
            }
            if (param.nowCnt > 0 && param.nowCnt % param.salesUnit > 0) {
                alert(param.salesUnit + '개 단위로 묶음 주문 상품입니다. 배송지 별로 묶음 주문 수량을 설정하셔야만 배송지 설정이 가능합니다.');
                $(this).val(param.nowCnt - (param.nowCnt % param.salesUnit));
                return;
            }

        });

        $('.shipping-save-btn').click(function(){
            getMultiShippingDeliveryCharge();
        });
    });

    function getMultiShippingDeliveryCharge() {
        var msg = '';
        var totalGoodsCnt = 0;
        var setData = [];
        var goodsCntData = [];

        $('input[name="multiShippingCartSno[]"]').each(function(index){
            var cartData = {
                sno: $(this).val(),
                scmNo: $(this).attr('data-scm-no'),
                deliverySno: $(this).attr('data-delivery-sno'),
                goodsInfo: '',
                goodsData: '',
                addGoodsTotalCnt: 0,
                addGoodsNo: [],
                addGoodsCnt: [],
                addGoodsInfo: [],
                addGoodsData: [],
            };

            $('input[name="multiShippingGoodsCnt[]"][data-cart-sno="' + cartData.sno + '"], input[name="multiShippingAddGoodsCnt[]"][data-cart-sno="' + cartData.sno + '"]').each(function(){
                var name = this.name;

                if (name == 'multiShippingGoodsCnt[]') {
                    var goodsKey = $(this).attr('data-goods-key');
                    var parentCartSno = cartData.sno;
                    totalGoodsCnt += parseInt($(this).val());
                    if (goodsCntData[goodsKey]) {
                        goodsCntData[goodsKey] += parseInt($(this).val());
                    } else {
                        goodsCntData[goodsKey] = parseInt($(this).val());
                    }
                    if ($(this).attr('data-goods-delivery-fl') == 'y' || ($(this).attr('data-goods-delivery-fl') != 'y' && $(this).attr('data-same-goods-delivery-fl') == 'y')) {
                        parentCartSno = $(this).attr('data-parent-cart-sno');
                    }

                    cartData.goodsNo = $(this).attr('data-goods-no');
                    cartData.goodsCnt = $(this).val();
                    cartData.goodsInfo = $(this).closest('tr').find('.goods-info').html();
                    cartData.goodsData = $(this);
                    cartData.deliveryMethodFl = $('select[name="multiShippingDeliveryMethodFl[]"][data-cart-sno="' + parentCartSno + '"]').val();
                    cartData.deliveryCollectFl = $('select[name="multiShippingDeliveryCollectFl[]"][data-cart-sno="' + parentCartSno + '"]').val();
                    cartData.goodsDeliveryFl = $(this).attr('data-goods-delivery-fl');
                    cartData.sameGoodsDeliveryFl = $(this).attr('data-same-goods-delivery-fl');
                    cartData.visitAddressUseFl = $('tr.self-order-goods-layout[data-goodsno="' + cartData.goodsNo + '"]').find('.visit-address-use-fl').val();
                    cartData.deliveryMethodVisitArea = $('tr.self-order-goods-layout[data-goodsno="' + cartData.goodsNo + '"]').find('.delivery-method-visit-area').val();
                    cartData.parentCartSno = parentCartSno;
                } else {
                    cartData.addGoodsTotalCnt += parseInt($(this).val());
                    cartData.addGoodsNo.push($(this).attr('data-add-goods-no'));
                    cartData.addGoodsCnt.push($(this).val());
                    cartData.addGoodsInfo.push($(this).closest('tr').find('.goods-info').html());
                    cartData.addGoodsData.push($(this));

                    if (cartData.goodsCnt > 0) {
                        if ($(this).attr('data-must-fl') == 'y' && $(this).val() <= 0) {
                            msg = '추가상품이 필수 선택인 상품이 있습니다. 추가상품도 함께 선택하셔야 배송지 선택이 가능합니다.';
                        }
                    } else {
                        if ($(this).val() > 0) {
                            msg = '추가상품만 단독으로 배송지 선택은 불가합니다.';
                        }
                    }
                }
            });
            setData.push(cartData);
        });

        $.each(goodsCntData, function(index, value){
            if (!value) {
                return true;
            }

            var $data = $('input[name="multiShippingGoodsCnt[]"][data-goods-key="' + index + '"]');
            var goodsNm = $data.data('goods-nm');
            if ($data.data('option-nm')) {
                goodsNm += ' - ' + $data.data('option-nm');
            }

            if ($data.attr('data-fixed-sales') == 'goods') {
                if (value > 0 && value % $data.data('real-sales-unit') > 0) {
                    msg = '[' + goodsNm + '] ' + $data.data('real-sales-unit') + '개 단위로 묶음 주문 상품입니다. 배송지 별로 묶음 주문 수량을 설정하셔야만 배송지 설정이 가능합니다.';
                    return false;
                }
            } else {
                $.each($data, function(){
                    if ($(this).val() > 0 && $(this).val() % $(this).attr('data-sales-unit') > 0) {
                        msg = '[' + goodsNm + '] ' + $(this).attr('data-sales-unit') + '개 단위로 묶음 주문 상품입니다. 배송지 별로 묶음 주문 수량을 설정하셔야만 배송지 설정이 가능합니다.';
                        return false;
                    }
                });
            }
            if ($data.attr('data-fixed-order-cnt') == 'goods') {
                if (value > 0 && $data.attr('data-min-order-cnt') > 1 && $data.attr('data-min-order-cnt') > value) {
                    msg = '[' + goodsNm + '] 최소 ' + $data.attr('data-min-order-cnt') + '개 이상 선택하셔야합니다.';
                    return false;
                }
            } else {
                $.each($data, function(){
                    if ($(this).val() > 0 && $(this).attr('data-min-order-cnt') > 1 && $(this).attr('data-min-order-cnt') > $(this).val()) {
                        msg = '[' + goodsNm + '] 최소 ' + $(this).attr('data-min-order-cnt') + '개 이상 선택하셔야합니다.';
                    }
                });
            }
        });
        if (msg) {
            alert(msg);
            return false;
        }

        var data = JSON.stringify(setData);

        $.ajax({
            method: "POST",
            url: "../order/order_ps.php",
            data: {mode: 'multi_shipping_delivery', selectGoods: data, address: '<?=$address?>', useDeliveryInfo: 'y'}
        }).success(function (getData) {
            var totalDeliveryCharge = 0;
            if (totalGoodsCnt > 0) {
                $('.select-goods-area:eq(' + shippingNo + ')').removeClass('display-none').find('table>tbody').empty();

                var parentCartSno = '';
                setData.forEach(function (goodsEle, goodsIdx) {
                    if (goodsEle.goodsCnt > 0) {
                        var deliverInfo = '';
                        if (getData.deliveryInfo[goodsEle.sno]) {
                            deliverInfo += '<th class="nobold delivery-info" style="text-align:center;" rowspan="' + (getData.deliveryInfo[goodsEle.sno]['rowspan']) + '" data-parent-cart-sno="' + goodsEle.sno + '">';
                            deliverInfo += getData.deliveryInfo[goodsEle.sno]['goodsDeliveryMethod']  + '<br />';
                            deliverInfo += currencySymbol + '<span class="shipping-delivery-price">' + numeral(getData.deliveryInfo[goodsEle.sno]['deliveryPrice']).format() + '</span>' + currencyString + '<br />';
                            deliverInfo += '(' + getData.deliveryInfo[goodsEle.sno]['deliveryMethodFl'] + '-' + getData.deliveryInfo[goodsEle.sno]['goodsDeliveryCollectFl'] + ')';
                            deliverInfo += '</th>';
                            parentCartSno = goodsEle.sno;
                        }
                        var content = {
                            goodsInfo: goodsEle.goodsInfo,
                            goodsCnt: goodsEle.goodsCnt,
                            cartSno: goodsEle.sno,
                            goodsNo: goodsEle.goodsNo,
                            parentGoodsNo: 0,
                            mustFl: 'n',
                            goodsType: 'goods',
                            parentCartSno: parentCartSno,
                            deliveryInfo: deliverInfo
                        };
                        var compiled = _.template($('#selectGoodsRow').html());
                        compiled = compiled(content);
                        $('.select-goods-area:eq(' + shippingNo + ')').find('table>tbody').append(compiled);

                        goodsEle.addGoodsInfo.forEach(function (addGoodsInfo, addGoodsIdx) {
                            if (goodsEle.addGoodsCnt[addGoodsIdx] > 0) {
                                var content = {
                                    goodsInfo: addGoodsInfo,
                                    goodsCnt: goodsEle.addGoodsCnt[addGoodsIdx],
                                    cartSno: goodsEle.sno,
                                    goodsNo: goodsEle.addGoodsNo[addGoodsIdx],
                                    parentGoodsNo: goodsEle.goodsNo,
                                    mustFl: $(goodsEle.addGoodsData[addGoodsIdx]).attr('data-must-fl'),
                                    goodsType: 'addGoods',
                                    parentCartSno: parentCartSno,
                                    deliveryInfo: ''
                                };

                                var compiled = _.template($('#selectGoodsRow').html());
                                compiled = compiled(content);
                                $('.select-goods-area:eq(' + shippingNo + ')').find('table>tbody').append(compiled);
                            }
                        });
                    }
                });

                totalDeliveryCharge = getData.totalDeliveryCharge;
                $('input[name="multiDelivery[' + shippingNo + ']"]').val(getData.totalDeliveryCharge);
                $('input[name="multiAreaDelivery[' + shippingNo + ']"]').val(getData.totalDeliveryAreaCharge);
                $('input[name="multiPolicyDelivery[' + shippingNo + ']"]').val(getData.totalGoodsDeliveryPolicyCharge);
                $('input[name="selectGoods[' + shippingNo + ']"]').val(data);
            }
            else {
                $('.select-goods-area:eq(' + shippingNo + ')').addClass('display-none');
                $('input[name="multiDelivery[' + shippingNo + ']"]').val(0);
                $('input[name="multiAreaDelivery[' + shippingNo + ']"]').val(0);
                $('input[name="multiPolicyDelivery[' + shippingNo + ']"]').val(0);
                $('input[name="selectGoods[' + shippingNo + ']"]').val('');
                $('.select-goods-area:eq(' + shippingNo + ')').find('table>tbody').empty();
            }
            alert('배송비가 ' + currencyDisplayOrderWrite(totalDeliveryCharge) + ' 추가되었습니다.');

            set_real_settle_price([], 'n');

            // 복수배송지 사용시 마일리지 재셋팅
            setMultiShippingMileage();

            set_shipping_delivery_visit(shippingNo);

            $('.close').trigger('click');
        });
    }
</script>
