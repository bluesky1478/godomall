<?php /* Template_ 2.2.7 2016/03/08 10:26:07 D:\wamp\godo5\user\data\skin\front\food_story\goods\layer_option.html 000020334 */ ?>
<!-- 옵션변경 레이어 -->

<div class="box option-layer" >


    <form name=frmViewLayer id='frmViewLayer' method=post>
        <input type="hidden" name="mode" >
        <input type="hidden" name="scmNo" value="<?php echo $goodsView['scmNo']?>" />
        <input type="hidden" name="scmNm" value="<?php echo $goodsView['scmNm']?>" />
        <input type="hidden" name="cartMode" value="" />
        <input type="hidden" name="set_goods_price"  id="set_goods_price" value="<?php echo gd_money_format(gd_isset($goodsView['goodsPrice'], 0),false)?>" />
        <input type="hidden" name="set_goods_fixedPrice"  id="set_goods_fixedPrice" value="<?php echo gd_isset($goodsView['fixedPrice'], 0)?>" />
        <input type="hidden" name="set_goods_mileage" value="<?php echo gd_isset($goodsView['goodsMileageBasic'], 0)?>" />
        <input type="hidden" name="set_goods_stock" value="<?php echo gd_isset($goodsView['stockCnt'], 0)?>" />
        <input type="hidden" name="set_coupon_dc_price" value="<?php echo gd_isset($goodsView['goodsPrice'], 0)?>" />

        <input type="hidden" name="set_goods_total_price" id="set_goods_total_price" value="0" />
        <input type="hidden" name="set_option_price"  id="set_option_price" value="0" />
        <input type="hidden" name="set_option_text_price" id="set_option_text_price" value="0" />
        <input type="hidden" name="set_add_goods_price"  id="set_add_goods_price" value="0" />
        <input type="hidden" name="set_total_price" value="0" />


        <input type="hidden" name="mileageFl" value="<?php echo $goodsView['mileageFl']?>" />
        <input type="hidden" name="mileageGoods" value="<?php echo $goodsView['mileageGoods']?>" />
        <input type="hidden" name="mileageGoodsUnit" value="<?php echo $goodsView['mileageGoodsUnit']?>" />
        <input type="hidden" name="goodsDiscountFl" value="<?php echo $goodsView['goodsDiscountFl']?>" />
        <input type="hidden" name="goodsDiscount" value="<?php echo $goodsView['goodsDiscount']?>" />
        <input type="hidden" name="goodsDiscountUnit" value="<?php echo $goodsView['goodsDiscountUnit']?>" />

        <input type="hidden" name="taxFreeFl" value="<?php echo $goodsView['taxFreeFl']?>" />
        <input type="hidden" name="taxPercent" value="<?php echo $goodsView['taxPercent']?>" />

        <input type="hidden" name="scmNo" value="<?php echo $goodsView['scmNo']?>" />
        <input type="hidden" name="brandCd" value="<?php echo $goodsView['brandCd']?>" />
        <input type="hidden" name="cateCd" value="<?php echo $goodsView['cateCd']?>" />


        <input type="hidden" id="set_dc_price" value="0" />
        <input type="hidden" id="set_dc_goods_price" value="0" />
        <input type="hidden" id="set_dc_member_price" value="0" />
        <input type="hidden" id="set_dc_myapp_price" value="0" />

        <input type="hidden" id="optionFl" value="<?=$goodsView['optionFl']?>" />
        <input type="hidden" id="goodsOptionCnt" value="1" />
        <div class="view">
            <div class="scroll-box">
                <div class="goods-layout1">
                    <div class="img-section">
                        <?php echo $goodsView['image']['detail']['thumb'][ 0]?>

                    </div>
                    <div class="content-section">
                        <div class="goods-name" style="width:450px;">
                            <h3 class="goods-name-t">
                                <?php  if($goodsView['timeSaleFl']) {
                                    echo "<img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' />";
                                }
                                ?>
                                <?php echo gd_isset($goodsView['goodsNmDetail'])?>
                            </h3>
                            <div class="benefits" style="display:none" >
                                <strong>구매혜택</strong>
                                <div>
                                    <p class="sale">할인 : <strong class="total_benefit_price"></strong> <span class="benefit_price"></span></p>
                                    <p class="mileage">적립 <?php echo gd_display_mileage_name()?> : <strong class="total_benefit_mileage"></strong> <span class="benefit_mileage"></span></p>
                                </div>
                            </div>

                            <div>
                                <div class="form-inline">
                                    <strong>배송비</strong>
                                    <?php if ($goodsView['multipleDeliveryFl'] === true) { ?>
                                        <?php if(gd_isset($goodsView['delivery']['basic']['fixFl'])=='free'){?>
                                            <span>무료</span>
                                            <input type="hidden" name="deliveryPrice" value="0">
                                        <?php }elseif(gd_isset($goodsView['delivery']['basic']['fixFl'])=='fixed'){?>
                                            <span><?php echo gd_currency_symbol()?><?php echo gd_money_format($goodsView['delivery']['firstCharge']['0']['price'])?><?php echo gd_currency_string()?></span>
                                            <input type="hidden" name="deliveryPrice" value="<?=gd_isset($goodsView['delivery']['firstCharge']['0']['price'])?>">
                                        <?php }else{?>
                                            <span><?php echo gd_currency_symbol()?><?php echo gd_money_format($goodsView['delivery']['firstCharge']['0']['price'])?><?php echo gd_currency_string()?></span>
                                            <input type="hidden" name="deliveryPrice" value="<?=gd_isset($goodsView['delivery']['firstCharge']['0']['price'])?>">
                                            <a class="btn-db-bd target-delivery-add"><em>조건별배송</em></a>
                                        <?php }?>
                                    <?php } else { ?>
                                        <?php if(gd_isset($goodsView['delivery']['basic']['fixFl'])=='free'){?>
                                            <span>무료</span>
                                            <input type="hidden" name="deliveryPrice" value="0">
                                        <?php }elseif(gd_isset($goodsView['delivery']['basic']['fixFl'])=='fixed'){?>
                                            <span><?php echo gd_currency_symbol()?><?php echo gd_money_format($goodsView['delivery']['charge']['0']['price'])?><?php echo gd_currency_string()?></span>
                                            <input type="hidden" name="deliveryPrice" value="<?=gd_isset($goodsView['delivery']['charge']['0']['price'])?>">
                                        <?php }else{?>
                                            <span><?php echo gd_currency_symbol()?><?php echo gd_money_format($goodsView['delivery']['charge']['0']['price'])?><?php echo gd_currency_string()?></span>
                                            <input type="hidden" name="deliveryPrice" value="<?=gd_isset($goodsView['delivery']['charge']['0']['price'])?>">
                                            <a class="btn-db-bd target-delivery-add"><em>조건별배송</em></a>
                                        <?php }?>
                                    <?php }?>
                                    <?php if(gd_isset($goodsView['delivery']['basic']['fixFl'])!='free'){?>

                                        <?php if(gd_isset($goodsView['delivery']['basic']['collectFl'])=='pre'){?>
                                            <em><input type="hidden" name="deliveryCollectFl" value="pre" /></em>
                                            주문시결제(선결제)
                                        <?php }elseif(gd_isset($goodsView['delivery']['basic']['collectFl'])=='later'){?>
                                            <em><input type="hidden" name="deliveryCollectFl" value="later" /></em>
                                            상품수령시결제(착불)
                                        <?php }else{?>
                                            <span class="st-hs">
                                <select class="tune" name="deliveryCollectFl" style="width:155px !important">
                                    <option value="pre">주문시결제(선결제)</option>
                                    <option value="later">상품수령시결제(착불)</option>
                                </select>
                                </span>
                                        <?php }?>

                                    <?php }?>
                                </div>


                            </div>

                            <!-- 배송방식 -->
                            <?php if(gd_isset($goodsView['delivery']['basic']['fixFl']) != 'free' && count($goodsView['delivery']['basic']['deliveryMethodFlData']) > 0){ ?>
                                <div style="margin-top: 5px;">
                                    <div class="form-inline">
                                        <strong>배송방식</strong>

                                        <?php if(count($goodsView['delivery']['basic']['deliveryMethodFlData']) == 1){ ?>
                                            <input type="hidden" name="deliveryMethodFl" value="<?=$goodsView['delivery']['basic']['deliveryMethodFlFirst']['code']?>" />
                                            <span><?=$goodsView['delivery']['basic']['deliveryMethodFlFirst']['name']?></span>
                                        <?php } else { ?>
                                            <select class="tune" name="deliveryMethodFl">
                                                <?php foreach($goodsView['delivery']['basic']['deliveryMethodFlData'] as $key => $value){ ?>
                                                    <option value="<?=$key?>"><?=$value?></option>
                                                <?php } ?>
                                            </select>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <!-- 배송방식 -->
                        </div>

                        <!-- 옵션 있을때시작 -->
                        <?php if($goodsView['optionFl']=='y'){?>

                            <?php if($goodsView['optionDisplayFl']=='s'){?>
                                <div class="ag-choice choice">
                                    <div class="list">
                                        <strong><?php if($goodsView['optionEachCntFl']=='one'&&empty($goodsView['optionName'])===false){?><?php echo $goodsView['optionName']?><?php }else{?>옵션 선택<?php }?>  </strong>
                                        <div><?php
                                            ?>
                                            <select name="optionSnoInput" class="form-inline" style="width:100%" onchange="goodsViewLayerController.option_price_display(this);">
                                                <option value="">=
                                                    <?php if($goodsView['optionEachCntFl']=='many'&&empty($goodsView['optionName'])===false){?><?php echo $goodsView['optionName']?>

                                                    <?php }else{?>옵션
                                                    <?php }?>: 가격
                                                    : 재고
                                                    =
                                                </option>
                                                <?php foreach($goodsView['option'] as $val){?>
                                                    <?php if($val["optionViewFl"]=='y'){?>
                                                        <option value="<?php echo $val["sno"]?><?php echo INT_DIVISION?><?php echo gd_money_format($val["optionPrice"],false)?><?php echo INT_DIVISION?><?php echo $val["mileage"]?><?php echo INT_DIVISION?><?php echo $val["stockCnt"]?><?php echo STR_DIVISION?><?php echo $val["optionValue"]?><?php echo STR_DIVISION?><?php if($goodsView['stockFl'] == 'y' && $val['optionSellFl'] == 't'){?><?php echo $optionSoldOutCode[$val['optionSellCode']]?><?php } ?><?php echo STR_DIVISION?><?php if($val['optionDeliveryFl'] == 't' && !empty($optionDeliveryDelayCode[$val['optionDeliveryCode']])){?><?php echo $optionDeliveryDelayCode[$val['optionDeliveryCode']]?><?php } ?>"<?php if(($goodsView['stockFl']=='y'&&$goodsView['stockCnt'] <  $goodsView['minOrderCnt'])||($goodsView['stockFl']=='y'&&$goodsView['fixedOrderCnt'] == 'option' && $val['stockCnt'] < $goodsView['minOrderCnt']) || ($goodsView['stockFl']=='y'&&$val['stockCnt'] == '0') ||$val["optionSellFl"]=='n'||$val["optionSellFl"]=='t'){?>disabled="disabled"
                                                            <?php }?> <?php if(gd_isset($TPL_VAR["optionInfo"]['optionSno'])&&$TPL_VAR["optionInfo"]['optionSno']==$val["sno"]){?> selected='selected' <?php }?>> <?php echo $val["optionValue"]?>

                                                            : <?php echo gd_currency_symbol()?><?php echo gd_money_format($val["optionPrice"])?><?php echo gd_currency_string()?>

                                                            <?php if($val["optionSellFl"]=='t'){?>[<?=$optionSoldOutCode[$val["optionSellCode"]]?>]
                                                            <?php }else if(($goodsView['stockFl']=='y'&&$val["stockCnt"]< 1)||$val["optionSellFl"]=='n'){?>[<?=$optionSoldOutCode['n']?>]
                                                            <?php }else{?><?php if($goodsView['stockFl']=='y'){?> : <?php echo number_format($val["stockCnt"])?>개
                                                            <?php }?><?php }?>
                                                            <?php
                                                            if($val['optionDeliveryFl'] == 't' && !empty($val['optionDeliveryCode'])) {
                                                                echo '['.$optionDeliveryDelayCode[$val['optionDeliveryCode']].']';
                                                            }
                                                            ?>
                                                        </option>
                                                    <?php } } ?>

                                            </select>
                                        </div>
                                    </div>
                                </div>

                            <?php } elseif($goodsView['optionDisplayFl']=='d') { ?>

                                <?php foreach($goodsView['optionName'] as $k => $val){?>
                                    <?php if($k== 0){?>
                                        <input type="hidden" name="optionSnoInput" value="<?php echo gd_isset($TPL_VAR["optionInfo"]['optionSnoText'])?>" />
                                        <input type="hidden" name="optionCntInput" value="<?php echo count($goodsView['optionName']); ?>" />
                                    <?php }?>
                                    <div class="ag-choice choice">
                                        <div class="list">
                                            <strong><?php echo $val?></strong>
                                            <div>
                                                <select name="optionNo_<?php echo $k?>" class="tune" style="width:100%;"  onchange="goodsViewLayerController.option_select(this,'<?php echo $k?>', '<?php echo gd_isset($goodsView['optionName'][($k+ 1)])?>');"
                                                    <?php if($k> 0){?> disabled="disabled"
                                                    <?php }?>>
                                                    <option value="">=
                                                        <?php if($k== 0){?><?php echo $val?> 선택
                                                        <?php }else{?><?php echo $goodsView['optionName'][($k- 1)]?>을 먼저 선택해 주세요
                                                        <?php }?> =
                                                    </option>
                                                    <?php if($k== 0){?>
                                                        <?php foreach($goodsView['optionDivision'] as $key => $TPL_V2) {?>
                                                            <option value="<?php echo $TPL_V2?>" <?php if(($goodsView['stockFl'] == 'y' && $goodsView['stockCnt'] < $goodsView['minOrderCnt']) || ($goodsView['stockFl'] == 'y' && $goodsView['fixedOrderCnt'] == 'option' && isset($goodsView['optionDivisionStock']) && $goodsView['optionDivisionStock'][$key]['stockCnt'] < $goodsView['minOrderCnt']) || ($goodsView['stockFl'] == 'y' && $goodsView['optionDivisionStock'][$key]['stockCnt'] == '0') || $goodsView['optionDivisionStock'][$key]['optionSellFl'] ==  'n' || $goodsView['optionDivisionStock'][$key]['optionSellFl'] ==  't'){?> disabled="disabled"<?php }?> >
                                                                <?php echo $TPL_V2?>
                                                                <?php if($goodsView['optionDivisionStock'][$key]['optionSellFl']=='t'){?>[<?=$optionSoldOutCode[$goodsView['optionDivisionStock'][$key]['optionSellCode']]?>]
                                                                <?php }else if(($goodsView['stockFl']=='y'&&$goodsView['optionDivisionStock'][$key]['stockCnt']< 1)||$goodsView['optionDivisionStock'][$key]['optionSellFl']=='n'){?>[<?=$optionSoldOutCode['n']?>]
                                                                <?php }?>
                                                            </option>
                                                        <?php }?>
                                                    <?php }?>
                                                </select></div>
                                        </div>
                                    </div>
                                    <div id="iconImage_<?php echo $k?>" class="option_icon"></div>
                                <?php }?>
                            <?php }?>
                        <?php }?>
                        <!--  옵션 끝 -->


                        <?php if($goodsView['optionTextFl']=='y'){?>

                            <!-- 추가 옵션 입력형 시작-->
                            <?php foreach($goodsView['optionText'] as $k => $val){?>
                                <div class="ag-choice choice">
                                    <div class="list">
                                        <?php if($k== 0){?>
                                            <input type="hidden" id="optionTextCnt" value="<?php echo count($goodsView['optionText']); ?>" />
                                        <?php }?>
                                        <strong><span class="optionTextNm_<?php echo $k?>"><?php echo $val["optionName"]?><?php if($val["mustFl"]=='y'){?><em>(필수)</em><?php }?></span> <input type="hidden" name="optionTextMust_<?php echo $k?>" value="<?php echo $val["mustFl"]?>" /> <input type="hidden" name="optionTextLimit_<?php echo $k?>" value="<?php echo $val["inputLimit"]?>" /></strong>

                                        <div class="option_input optionTextDisplay<?php echo $val["sno"]?>">
                    <span class="txt-field">
                    <input type="hidden" name="optionTextSno_<?php echo $k?>" value="<?php echo $val["sno"]?>"/>
                    <input type="text" name="optionTextInput_<?php echo $k?>" value="" size="25" class="text" data-sno ="<?php echo $val["sno"]?>" onkeydown="goodsViewLayerController.enterKey(this)" onchange="goodsViewLayerController.option_text_select(this)" placeholder="<?php echo $val["inputLimit"]?>글자를 입력하세요." />
                    <input type="hidden"  value="<?php echo $val["addPrice"]?>"/>
                    </span>
                                            <?php if($val["addPrice"]!= 0){?>
                                                <span class="msg">※ 작성시 <?php echo gd_currency_symbol()?><?php echo gd_money_format($val["addPrice"])?><?php echo gd_currency_string()?> 추가</span>
                                            <?php }?>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                        <?php }?>
                        <!-- 추가 옵션 입력형 종료 -->

                        <?php if($goodsView['addGoods']){?>
                            <?php foreach($goodsView['addGoods'] as $k=>$val) {?>
                                <div class="ag-choice choice <?php if($k=='0'){?>add  <?php }?>">
                                    <div class="list">
                                        <strong><?php echo $val["title"]?>  <?php if($val["mustFl"]=='y'){?><em>(필수)</em><input type="hidden" name="addGoodsInputMustFl[]" value="<?php echo $k?>"><?php }?></strong>
                                        <div>
                                            <select name="addGoodsInput<?php echo $k?>"   data-key="<?php echo $k?>" class="tune" style="width:100%;"  onchange="goodsViewLayerController.add_goods_select(this)">
                                                <option value="">추가상품</option>
                                                <?php foreach($val["addGoodsList"] as $TPL_V2){?>
                                                    <option value="<?php echo $TPL_V2["addGoodsNo"]?><?php echo INT_DIVISION?><?php echo $TPL_V2["goodsPrice"]?><?php echo STR_DIVISION?><?php echo $TPL_V2["goodsNm"].'('.$TPL_V2["optionNm"].')'?><?php echo STR_DIVISION?><?php echo rawurlencode(gd_html_add_goods_image($TPL_V2["addGoodsNo"],$TPL_V2["imageNm"],$TPL_V2["imagePath"],$TPL_V2["imageStorage"], 30,$TPL_V2["goodsNm"],'_blank'))?><?php echo STR_DIVISION?><?php echo $k?><?=STR_DIVISION?><?=$TPL_V2["stockUseFl"]?><?=STR_DIVISION?><?=$TPL_V2["stockCnt"]?>" <?php if($TPL_V2["soldOutFl"]=='y'||($TPL_V2["stockUseFl"]=='1'&&$TPL_V2["stockCnt"]=='0')){?> disabled="disabled" <?php }?>><?php echo $TPL_V2["goodsNm"]?>  (<?php echo $TPL_V2["optionNm"]?> / <?php echo gd_currency_symbol()?><?php echo gd_money_format($TPL_V2["goodsPrice"])?><?php echo gd_currency_string()?> <?php if($TPL_V2["soldOutFl"]=='y'||($TPL_V2["stockUseFl"]=='1'&&$TPL_V2["stockCnt"]=='0')){?> / 품절 <?php }?>)</option>
                                                <?php }?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php }?>
                        <?php }?>

                    </div>

                    <?php if($goodsView['optionFl']=='y'){?>
                        <div class="option_total_display_area" style="display:none">
                            <div class="order-goods option_display_area" ></div>
                        </div>
                    <?php }else{?>
                        <div class="order-goods option_display_area">
                            <div class="option_display_item_0" id="option_display_item_0">

                                <div class="check optionKey_0">
                                    <input type="hidden" name="goodsNo[]" value="<?php echo $goodsView['goodsNo']?>">
                                    <input type="hidden" name="optionSno[]" value="<?php echo gd_isset($goodsView['option'][ 0]['sno'])?>"/>
                                    <input type="hidden" name="goodsPriceSum[]" value="0">
                                    <input type="hidden" name="addGoodsPriceSum[]" value="0">
                                    <span class="name"><strong><?php echo gd_isset($goodsView['goodsNmDetail'])?></strong>
                                <span class="option_text_display_0"></span></span>

                                    <div class="price">
                                        <span class="count">
                                            <input type="text" class="text goodsCnt_0" title="수량" name="goodsCnt[]"  value="<?php echo $goodsView['defaultGoodsCnt']; ?>" data-value="<?php echo $goodsView['defaultGoodsCnt']; ?>"  data-stock="<?=$goodsView['totalStock']?>" data-key="0" onchange="goodsViewLayerController.input_count_change(this,'1');return false;">

                                            <span>
                                                <button type="button" class="up goods-cnt" title="증가"  value="up<?php echo STR_DIVISION?>0" style="cursor: pointer">증가</button>
                                                <button type="button" class="down goods-cnt" title="감소" value="dn<?php echo STR_DIVISION?>0" style="cursor: pointer">감소</button>
                                            </span>
                                        </span>

                                        <em><input type="hidden" value="<?php echo gd_isset($goodsView['option'][ 0]['optionPrice'], 0)?>" name="optionPriceSum[]"> <input type="hidden" value="<?php echo gd_isset($goodsView['option'][ 0]['optionPrice'])?>" name="option_price_0"><?php echo gd_currency_symbol()?><strong class="option_price_display_0"><?php echo gd_money_format(gd_isset($goodsView['option'][ 0]['optionPrice'], 0),false)?></strong><?php echo gd_currency_string()?></em>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }?>
                    <div class="end-price" style="display:none">
                        <div class="pull-left">

                            <ul style="padding:10px;">
                                <li class="price">
                                    <span>총 상품금액</span>
                                    <strong class="goods_total_price"></strong>
                                </li>
                                <li class="discount">
                                    <span>총 할인금액</span>
                                    <strong class="total_benefit_price"></strong>
                                </li>
                                <li class="total">
                                    <span>총 합계금액</span>
                                    <strong class="total_price"></strong>
                                </li>
                            </ul>

                        </div>
                        <div class="pull-right" style="padding:10px;">
                            <input type="button" value="확인" class="btn btn-lg btn-black select-goods-btn" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>


<script type="text/javascript">
    var gd_goods_view = function () {
        var setOptionFl = "n";
        var setOptionTextFl = "n";
        var setOptionDisplayFl = "s";
        var setAddGoodsFl = "n";
        var setIntDivision = "";
        var setStrDivision = "";
        var setMileageUseFl = "n";
        var setCouponUseFl = "n";
        var setMinOrderCnt = 1;
        var setMaxOrderCnt = 0;
        var setStockFl = "n";
        var setSalesUnit = 1;
        var setDecimal= 0;
        var setGoodsPrice = 0;
        var setGoodsNo = '';
        var setMileageFl = '';
        var setControllerName = "";
        var setCartTabFl = "n";
        var setTemplate = "";
        var setFixedSales = "option";
        var setFixedOrderCnt = "option";
        var setStockCnt = 0;
        var setOriginalMinOrderCnt = 0;


        /**
         * 최소수량 체크
         *
         * @param string keyNo 상품 배열 키값
         */
        this.input_count_change = function(inputName,goodsFl)
        {
            var frmId = "#"+$(inputName).closest("form").attr('id');
            if($(inputName).val()=='') {
                $(inputName).val('0');
            }

            var itemNo = $(inputName).data('key');
            if(itemNo) {
                var optionDisplay =itemNo.split(setIntDivision);
                var optionDisplay = optionDisplay[0];
            } else {
                var optionDisplay = "0";
            }

            if($("#option_display_item_"+optionDisplay+" input[name='couponApplyNo[]']").val()) {
                alert('쿠폰이 적용된 상품은 수량변경을 할 수 없습니다.\n쿠폰취소 후 수량을 변경해주세요.');
                $(inputName).val($(inputName).data('value'));
                return false;
            }

            if(goodsFl) {

                var nowCnt	= parseInt($('input.goodsCnt_'+itemNo).val());

                var minCnt = parseInt(setMinOrderCnt);
                var maxCnt = parseInt(setMaxOrderCnt);

                var stockFl		= setStockFl;
                var setStock	= parseInt($(inputName).data('stock'));
                if (((setStock > 0 &&  maxCnt ==0) || (setStock <= maxCnt)) && stockFl == 'y') {
                    maxCnt = parseInt(setStock);
                }

                var salesUnit = parseInt(setSalesUnit);

            } else {

                var nowCnt	= parseInt($(inputName).val());
                var minCnt = 1;
                var salesUnit = 1;

                if($(inputName).data('stock-fl') =='1') var maxCnt =  parseInt($(inputName).data('stock'));
                else var maxCnt = 0 ;

            }

            if(parseInt( nowCnt % salesUnit) > 0 ) {
                alert(salesUnit+"개 단위로 묶음 주문 상품입니다.");
                $(inputName).val(minCnt);
            }

            if (nowCnt < minCnt && minCnt != 0 && minCnt != '' && typeof minCnt != 'undefined') {
                $(inputName).val(minCnt);
            }

            if (nowCnt > maxCnt && maxCnt != 0 && maxCnt != '' && typeof maxCnt != 'undefined') {

                if(parseInt( maxCnt % salesUnit) > 0 ) {
                    alert("최대 주문 가능 수량을 확인해주세요.");
                    $(inputName).val(minCnt);
                } else {
                    $(inputName).val(maxCnt);
                }
            }

            $(inputName).data('value', $(inputName).val());

            this.goods_calculate(frmId,goodsFl,itemNo, $(inputName).val());
        }


        /**
         * 수량 변경하기
         *
         * @param string inputName input box name
         * @param string modeStr up or dn
         * @param integer minCnt 최소수량
         * @param integer maxCnt 최대수량
         */
        this.count_change = function(inputName,goodsFl)
        {
            var frmId = "#"+$(inputName).closest("form").attr('id');

            var tmpStr =  $(inputName).val().split(setStrDivision);
            var modeStr =  tmpStr[0];
            var itemNo =  tmpStr[1];

            var optionDisplay =itemNo.split(setIntDivision);

            if($("#option_display_item_"+optionDisplay[0]+" input[name='couponApplyNo[]']").val()) {
                alert('쿠폰이 적용된 상품은 수량변경을 할 수 없습니다.\n쿠폰취소 후 수량을 변경해주세요.');
                return false;
            }

            var minCnt =parseInt(setMinOrderCnt);
            var maxCnt = parseInt(setMaxOrderCnt);

            var nowCnt	= parseInt($(inputName).closest('span.count').find('input').val());

            if(goodsFl) {

                var salesUnit = parseInt(setSalesUnit);

                // 최소 수량 체크
                if (minCnt == 0 || minCnt == '' || typeof minCnt == 'undefined') {
                    minCnt = 1;
                }
                if (nowCnt < minCnt) {
                    nowCnt = parseInt(minCnt);
                }

                // 최대 수량 체크
                if (maxCnt == 0 || maxCnt == '' || typeof maxCnt == 'undefined') {
                    var maxCntChk = false;
                } else {
                    var maxCntChk = true;
                    maxCnt = parseInt(maxCnt);
                }

                var stockFl		= setStockFl;
                var setStock	= parseInt($(inputName).closest('span.count').find('input').data('stock'));
                if (((setStock > 0 &&  maxCnt ==0) || (setStock <= maxCnt)) && stockFl == 'y') {
                    maxCnt = setStock;
                    var maxCntChk = true;
                }

            }
            else {
                var salesUnit = 1;
                minCnt = 1;

                if($(inputName).closest('span.count').find('input').data('stock-fl') =='1') {
                    var maxCnt =  parseInt($(inputName).closest('span.count').find('input').data('stock'));
                    var maxCntChk = true;
                }
                else var maxCnt = 0 ;

            }

            if (isNaN(nowCnt) === true) {
                var thisCnt	= minCnt;
            } else {
                if (modeStr == 'up') {
                    if (maxCntChk === true && (nowCnt+salesUnit) > maxCnt) {
                        var thisCnt	= nowCnt;
                    } else {
                        var thisCnt	= nowCnt + salesUnit;
                    }
                } else if (modeStr == 'dn') {
                    if (nowCnt > minCnt) {
                        var thisCnt	= nowCnt - salesUnit;
                    }
                    else{
                        var thisCnt = nowCnt;
                    }
                }
            }

            var goodsCntInput =  $(inputName).closest('span.count').find('input');
            $(goodsCntInput).val(thisCnt);
            $(goodsCntInput).focus();
            $(goodsCntInput).data('value',thisCnt);

            this.goods_calculate(frmId,goodsFl,itemNo,thisCnt);
        }


        /**
         * 옵션에 따른 가격 출력
         *
         * @param integer optionNo 상품 배열 키값 (기본 0)
         */
        this.option_price_display = function(inputName)
        {
            var frmId = "#"+$(inputName).closest("form").attr('id');

            if(setOptionTextFl =='y') {
                if(!this.option_text_valid(frmId))
                {
                    if(setOptionDisplayFl =='s') {
                        $(frmId+' select[name="optionSnoInput"]').val('');
                    } else {
                        $(frmId+' select[name*="optionNo_"]').val('');
                        $(frmId+' select[name*="optionNo_"]').trigger('chosen:updated');
                    }

                    alert('선택한 옵션의 필수 텍스트 옵션 내용을 먼저 입력해주세요.');

                    return false;
                }
                $(frmId+' input[name*="optionTextInput"]').val('');
            }

            if(setAddGoodsFl =='y') {
                if(!this.add_goods_valid(frmId))
                {
                    if(setOptionDisplayFl =='s') {
                        $(frmId+' select[name="optionSnoInput"]').val('');
                    } else {
                        $(frmId+' select[name*="optionNo_"]').val('');
                        $(frmId+' select[name*="optionNo_"]').trigger('chosen:updated');
                    }

                    alert('선택한 옵션의 필수 추가 상품 먼저 선택해주세요.');
                    return false;
                }
            }


            if($(frmId+" input[name='selectGoodsFl']").length && $(frmId+" input[name='selectGoodsFl']").val()) {
                $(frmId+" div.option_display_area").html('');
            }

            if(setOptionDisplayFl =='s') {
                if ($(frmId+' select[name="optionSnoInput"] option:selected').val() != '') {
                    var valTmp	= $(frmId+' select[name="optionSnoInput"] option:selected').val();
                    $(frmId+' select[name="optionSnoInput"]').val('');
                }
            } else if(setOptionDisplayFl =='d') {
                var valTmp		= $(frmId+' input[name="optionSnoInput"]').val();
                $(frmId+' select[name*="optionNo_"]').val('');
                $(frmId+' select[name*="optionNo_"]').not(':eq(0)').attr('disabled',true);
                $(frmId+' select[name*="optionNo_"]').trigger('chosen:updated');
            }

            if (typeof valTmp == 'undefined') return false;

            var arrTmp	= new Array();
            var arrTmp	= valTmp.split(setStrDivision);
            var optionName = arrTmp[1].trim();
            var optionInput = arrTmp[0];
            var optionSellCodeValue = arrTmp[2];
            var optionDeliveryCodeValue = arrTmp[3];

            var arrTmp	= optionInput.split(setIntDivision);

            if(optionSellCodeValue != "" && optionSellCodeValue != undefined){
                optionSellCodeValue = '[' + optionSellCodeValue + ']';
            }
            if(optionDeliveryCodeValue != "" && optionDeliveryCodeValue != undefined){
                optionDeliveryCodeValue = '[' + optionDeliveryCodeValue + ']';
            }

            if(setMileageUseFl =='y' && arrTmp[2]) {
                $(frmId+' input[name="set_goods_mileage"]').val(parseFloat(arrTmp[2].trim()));
            }


            if(arrTmp[3]) $(frmId+' input[name="set_goods_stock"]').val(parseFloat(arrTmp[3].trim()));

            var optionPrice = arrTmp[1].trim();
            var optionStock = parseFloat(arrTmp[3].trim());

            var displayOptionkey = arrTmp[0]+'_'+$.now();

            if($(frmId+" div.optionKey_"+arrTmp[0]).length) {
                alert("이미 선택된 옵션입니다");
                return false;
            }
            else
            {
                var addHtml = "";
                var complied = _.template($('#optionTemplate'+setTemplate).html());
                addHtml += complied({
                    displayOptionkey: displayOptionkey,
                    optionSno: arrTmp[0],
                    optionName:optionName,
                    optionPrice: optionPrice,
                    optionStock : optionStock,
                    optionSellCodeValue: optionSellCodeValue,
                    optionDeliveryCodeValue: optionDeliveryCodeValue
                });

                $(frmId+" div.option_display_area").append(addHtml);

                $(frmId+' div.optionKey_'+arrTmp[0] + '  .goods-cnt').on('click', function(e){
                    setControllerName.count_change(this,1);
                });

                $(frmId+' div.optionKey_'+arrTmp[0] + '  button.delete-goods').on('click', function(e){
                    setControllerName.remove_option($(this).data('key'));
                });

                this.goods_calculate(frmId,1, displayOptionkey, setMinOrderCnt);

                if(setCouponUseFl =='y') {
                    if (typeof bindBtnOpenLayer !== 'undefined' && $.isFunction(bindBtnOpenLayer)) bindBtnOpenLayer();
                }

                $(frmId+" div.option_total_display_area").show();




            }

        }

        this.option_select = function(inputName,thisKey, nextVal)
        {

            var frmId = "#"+$(inputName).closest("form").attr('id');

            // 무한정 판매 여부
            var stockFl				= setStockFl;

            // 재고 출력 여부
            var stockViewFl			= 'y';

            // 옵션의 갯수
            var optionCnt			= $(frmId+ ' input[name="optionCntInput"]').val();

            // 옵션 가격 출력 여부
            var optionPriceFl		= 'y';

            // 옵션 가격이 다른 경우 출력 여부
            var optionPriceDiffFl	= 'y';

            // 기본 상품 가격
            var defaultGoodsPrice	= parseFloat(setGoodsPrice);

            // 선택된 옵션
            var optionVal	= new Array();
            for (var i = 0; i <= thisKey; i++) {
                optionVal[i]	= $(frmId+' select[name="optionNo_'+i+'"]').val();
                // 선택값이 없는경우 disabled 처리
                if (optionVal[i] == '') {
                    for (var j = (i+1); j <= optionCnt; j++) {
                        if (j != 0) {
                            $(frmId+' select[name="optionNo_'+j+'"]').attr('disabled',true);
                        }
                    }

                    return true;
                }
            }

            $.post('../goods/goods_ps.php',{'mode' : 'option_select', 'optionVal' : optionVal, 'optionKey' : thisKey, 'goodsNo' : setGoodsNo, 'mileageFl' :setMileageFl}, function(data){

                if (typeof data.optionSno == 'string') {
                    //분리형 옵션 - 타임세일 할인율 적용된 옵션가격 설정.
                    var optionInfo = data.optionSno.split('||');
                    optionInfo[1] = data.optionPrice[0];
                    var optionSno = optionInfo.join("||");

                    $(frmId+' input[name=\'optionSnoInput\']').val(data.optionSno+setStrDivision+optionVal.join('/') + setStrDivision + data.stockCodeValue + setStrDivision + data.deliveryCodeValue);

                    setControllerName.option_price_display($(frmId+' input[name=\'optionSnoInput\']'));

                    return true;
                } else {
                    $(frmId+' input[name=\'optionSnoInput\']').val('');
                }

                for (var i = 0; i <= optionCnt; i++) {
                    if (i <= data.nextKey) {
                        $(frmId+' select[name="optionNo_'+i+'"]').attr('disabled',false);
                        if (i == data.nextKey) {
                            $(frmId+' select[name="optionNo_'+i+'"]').html('');
                            var addSelectOption		= '';
                            for (var j = 0; j < data.nextOption.length; j++) {
                                if(data.optionViewFl[j] =='y') {
                                    if(j == 0)
                                    {
                                        // 옵션 선택명
                                        addSelectOption	+= '<option value="">= '+nextVal+' 선택';

                                        // 마지막 옵션의 경우 가격, 재고 출력
                                        if ((parseInt(data.nextKey) + 1) == parseInt(optionCnt)) {
                                            if (optionPriceFl == 'y' && optionPriceDiffFl == 'y') {
                                                addSelectOption	+= ' : 가격';
                                            }
                                            if (stockFl == 'y' && stockViewFl == 'y') {
                                                addSelectOption	+= ' : 재고';
                                            }
                                        }

                                        addSelectOption	+= ' =</option>';
                                    }

                                    // 옵션값
                                    addSelectOption	+= '<option value="'+data.nextOption[j]+'"';

                                    // 재고 체크 (품절이면 disabled 처리)
                                    if (((stockFl == 'y' && setStockCnt <  setOriginalMinOrderCnt) || (stockFl == 'y' && setFixedOrderCnt == 'option' && data.stockCnt[j] < setMinOrderCnt) || (stockFl == 'y' && data.stockCnt[j] == 0) || data.optionSellFl[j] == 'n' || data.optionSellFl[j] == 't')) {
                                        addSelectOption	+= ' disabled="disabled"';
                                    }

                                    // 옵션값
                                    addSelectOption	+= '>'+data.nextOption[j];

                                    // 마지막 옵션의 경우 재고 체크 및 가격
                                    if ((parseInt(data.nextKey) + 1) == parseInt(optionCnt)) {
                                        // 가격 출력여부

                                        if(parseInt(data.optionPrice[j]) !='0') {
                                            if(data.optionPrice[j] > 0 ) var addPlus = "+";
                                            else var addPlus = "";
                                            if (optionPriceFl == 'y' && optionPriceDiffFl == 'y') {
                                                addSelectOption	+= ' : '+addPlus+numeral(data.optionPrice[j].toString()).format('0,0')+'';
                                            } else if (optionPriceFl == 'y' && optionPriceDiffFl == 'n' && defaultGoodsPrice != parseFloat(data.optionPrice[j])) {
                                                addSelectOption	+= ' ('+addPlus+numeral(data.optionPrice[j].toString()).format('0,0')+')';
                                            }
                                        }

                                        // 재고 체크
                                        if (data.optionSellFl[j] == 't' && data.stockCodeValue[j] != '' && data.stockCodeValue[j] != null) {
                                            addSelectOption	+= ' [' + data.stockCodeValue[j] + ']';
                                        } else if ((stockFl == 'y' && data.stockCnt == 0) || data.optionSellFl[j] == 'n' || (data.sellStopFl == 'y' && dta.sellStopStock >= data.stockCnt[j])) {
                                            addSelectOption	+= ' [' + data.stockCodeValue['n'] + ']';
                                        } else if (stockFl == 'y' && stockViewFl == 'y') {
                                            addSelectOption	+= ' : '+numeral(data.stockCnt[j]).format() + '개';
                                        }

                                        // 배송 상태 설정
                                        if (data.deliveryCodeValue[j] != '' && data.deliveryCodeValue[j] != null) {
                                            addSelectOption += '[' + data.deliveryCodeValue[j] + ']';
                                        }
                                    } else {
                                        // 재고 체크
                                        if (data.optionSellFl[j] == 't' && data.stockCodeValue[j] != '' && data.stockCodeValue[j] != null) {
                                            addSelectOption	+= ' [' + data.stockCodeValue[j] + ']';
                                        } else if ((stockFl == 'y' && data.stockCnt[j] == 0) || data.optionSellFl[j] == 'n') {
                                            addSelectOption	+= ' [' + data.stockCodeValue['n'] + ']';
                                        }
                                    }

                                    // 옵션값
                                    addSelectOption	+= '</option>';
                                }
                            }

                            $(frmId+' select[name="optionNo_'+i+'"]').html(addSelectOption);
                        }
                    } else {
                        if (i != 0) {
                            $(frmId+' select[name="optionNo_'+i+'"]').attr('disabled',true);
                        }
                    }

                    $(frmId+' select[name="optionNo_'+i+'"]').trigger('chosen:updated');
                }


            }, 'json');
        }


        /**
         * 옵션 삭제
         *
         * @param optionId 삭제 옵션 아이디
         */
        this.remove_option = function(optionId) {

            var frmId = "#"+$("#"+optionId).closest("form").attr('id');

            $("#"+optionId).remove();
            if (typeof total_calculate !== 'undefined' && $.isFunction(total_calculate)) total_calculate();

            var ontionCnt = $('div[id*=\'option_display_item_\']').length;
            if(ontionCnt == 0) $(frmId+" div.option_total_display_area").hide();

        }


        /**
         * 텍스트 옵션 선택
         */
        this.option_text_select = function(inputName) {

            var frmId = "#"+$(inputName).closest("form").attr('id');

            var optionText = '';
            var optionTextPrice = 0;
            var optionTextTotalPrice = 0;
            var optionTextSno = '';
            var optionTextKey = "";

            var displayOptionDisplay = $(frmId+' div[id*=\'option_display_item_\']').last().attr('id');

            if(displayOptionDisplay)
            {
                var checkKey = $(frmId+' #'+displayOptionDisplay+' div.check').attr('class').replace("check","").trim();
                var displayOptionkey = displayOptionDisplay.replace("option_display_item_", "");


                if(setOptionFl =='y') {
                    var optionItemNo = $(frmId+' div[id*=\'option_display_item_\']').length-1;
                } else {
                    var optionItemNo = 0;
                }
                if($(frmId+' .option_text_display_' + displayOptionkey).html() !== '') {
                    var optionTextInputCnt = $(frmId+' input[name*=\'optionTextInput_\']').length;
                    var emCnt = $(frmId+' div[id*=\'option_display_item_\']').last().find('em').length;
                    if(optionTextInputCnt < emCnt) {
                        var addHtml = "";
                        var complied = _.template($('#optionTemplate'+setTemplate).html());
                        var $lastDiv = $(frmId+' div[id*=\'option_display_item_\']').last();
                        var optionSno = $lastDiv.find('input[name=\'optionSno[]\']').val();
                        var optionName = $lastDiv.find('strong').first().text();
                        var optionPrice = $lastDiv.find('input[name*=\'option_price_\']').last().val();
                        var optionStock = $lastDiv.find('input[name*=\'goodsCnt\']').attr('data-stock');
                        displayOptionkey = optionItemNo+'_'+$.now();
                        addHtml += complied({
                            displayOptionkey: displayOptionkey,
                            optionSno: optionSno,
                            optionName: optionName,
                            optionPrice: optionPrice,
                            optionStock : optionStock
                        });
                        $(frmId+" div.option_display_area").append(addHtml);

                        $(frmId+' #option_display_item_'+displayOptionkey + '  .goods-cnt').on('click', function (e) {
                            setControllerName.count_change(this,1);
                        });

                        $(frmId+' #option_display_item_'+displayOptionkey + '  button.delete-goods').on('click', function (e) {
                            setControllerName.remove_option($(this).data('key'));
                        });
                    }
                }

                $(frmId+' input[name*=\'optionTextInput\']').each(function (key) {
                    if ($(this).val()) {

                        var optionTextLimit =  $(frmId+' input[name="optionTextLimit_'+key+'"]').val();
                        if($(this).val().length > optionTextLimit) {
                            $(this).val($(this).val().substring(0,optionTextLimit));
                        }

                        var optionValue = $(this).val();

                        optionTextPrice =parseFloat($(this).next('input').val());
                        optionTextSno += '<input type="hidden"  value="' + optionValue + '" name="optionText[' + optionItemNo + '][' + $(this).prev('input').val() + ']">';


                        var optionTextNm = $(frmId+' span.optionTextNm_'+key).text();
                        var optionDisplayText = optionTextNm+' : '+optionValue+' <b>('+optionTextPrice+')</b>';
                        optionTextSno += '<em>'+optionDisplayText+'</em>';


                        for (var i = 0, m = optionValue.length; i < m; i++) {
                            optionTextKey += optionValue.charCodeAt(i);
                        }

                    } else {
                        optionTextSno +='';
                        optionTextPrice = 0;
                    }

                    optionTextTotalPrice += optionTextPrice;

                });


                var tmpStr = displayOptionkey.split('_');


                if($(frmId+' div.optionKey_'+tmpStr[0]+optionTextKey).length) {
                    alert("이미 선택된 옵션입니다");
                    return false;
                } else {
                    if(optionTextKey)   $('#'+displayOptionDisplay+' div.'+checkKey +' .name strong').addClass('btm-txt');
                    else  $('#'+displayOptionDisplay+' div.'+checkKey +' .name strong').removeClass('btm-txt');

                    optionText = optionTextSno + '<input type="hidden" value="' + optionTextTotalPrice + '" name="optionTextPriceSum[]" ><input type="hidden" value="' + optionTextTotalPrice + '" name="option_text_price_' + displayOptionkey + '"></div>';

                    $(frmId+' .option_text_display_' + displayOptionkey).html(optionText);
                    $('#'+displayOptionDisplay+' div.'+checkKey).attr('class', 'check optionKey_' + tmpStr[0] + optionTextKey);


                    var goodsCnt = $(frmId+" input.goodsCnt_"+displayOptionkey).val();
                    this.goods_calculate(frmId,1, displayOptionkey, goodsCnt);
                }

            } else{
                alert("옵션을 먼저 선택해주세요");
                $(frmId+' input[name*=\'optionTextInput\']').val('');
                return false;
            }

        }

        /**
         * 텍스트 옵션 유효성체크
         */
        this.option_text_valid = function(frmId) {

            if($(frmId+' input[name="optionSno[]"]').length) {
                var returnFl = true;

                $(frmId+' input[name="optionSno[]"]').each(function (key) {

                    $(frmId +' input[name*=\'optionTextInput\']').each(function (textKey) {

                        var optionTextSno = $(frmId +' input[name="optionTextSno_'+textKey+'"]').val();
                        var optionText = $(frmId+' input[name="optionText[' + key + '][' + optionTextSno + ']"]');


                        if ( $(frmId +' input[name="optionTextMust_'+textKey+'"]').val() == 'y') {

                            if (optionText.length == '0') {
                                $(frmId +' input[name="optionTextInput_'+textKey+'"]').focus();
                                returnFl =  false;
                            }
                        }
                    });
                });
                return returnFl;

            }
            else return true;

        }

        this.add_goods_select = function(inputName) {

            var frmId = "#"+$(inputName).closest("form").attr('id');
            var selAddGoods = $(inputName).data('key');

            if ($(frmId+" select[name='addGoodsInput"+selAddGoods+"']").val() != '') {


                var displayOptionDisplay = $(frmId+' div[id*=\'option_display_item_\']').last().attr('id');

                if(displayOptionDisplay)
                {

                    var displayOptionkey = displayOptionDisplay.replace("option_display_item_", "");

                    if ($('#'+displayOptionDisplay + ' .add').length == 0) {
                        $('#'+displayOptionDisplay).append('<div class="add"></div>');
                    }

                    var addGoods = $(frmId+" select[name='addGoodsInput"+selAddGoods+"']");

                    var arrTmp = new Array();
                    var arrTmp = addGoods.val().split(setStrDivision);
                    var addGoodsName = arrTmp[1].trim();
                    var addGoodsimge = decodeURIComponent(arrTmp[2].trim());
                    var addGoodsGroup = arrTmp[3].trim();
                    var addGoodsValue = arrTmp[0];
                    var addGoodsStockFl = arrTmp[4];
                    var addGoodsStock = arrTmp[5];

                    var arrTmp = addGoodsValue.split(setIntDivision);


                    var displayAddGoodsKey = arrTmp[0];
                    var optionIndex = $(frmId+ ' #'+displayOptionDisplay).index();
                    if($(frmId+ ' #add_goods_display_item_' + displayOptionkey + '_' + displayAddGoodsKey).length) {
                        alert("이미 선택된 추가상품 입니다.");
                        return false;
                    } else {

                        var addHtml='';
                        var complied = _.template($('#addGoodsTemplate'+setTemplate).html());
                        addHtml += complied({
                            displayOptionkey: displayOptionkey,
                            displayAddGoodsKey: displayAddGoodsKey,
                            addGoodsimge:addGoodsimge,
                            addGoodsGroup:addGoodsGroup,
                            optionIndex: optionIndex,
                            optionSno: arrTmp[0],
                            addGoodsName: addGoodsName,
                            addGoodsStockFl: addGoodsStockFl,
                            addGoodsStock: addGoodsStock,
                            addGoodsPrice:  parseFloat(arrTmp[1].trim())
                        });


                        $(frmId+ ' #option_display_item_' + displayOptionkey + ' .add').append(addHtml);


                        $(frmId+ ' #add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey+' .add-goods-cnt').on('click', function(e){
                            setControllerName.count_change(this,0);
                        });

                        $(frmId+ ' #add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey+'  button.delete-add-goods').on('click', function(e){
                            setControllerName.remove_add_goods(displayOptionkey,displayAddGoodsKey);
                        });


                        var itemNo = displayOptionkey + setIntDivision + displayAddGoodsKey;

                        this.goods_calculate(frmId,0, itemNo, 1);

                        if(setCouponUseFl =='y') {
                            if (typeof bindBtnOpenLayer !== 'undefined' && $.isFunction(bindBtnOpenLayer)) bindBtnOpenLayer();
                        }

                        addGoods.val('');
                    }
                }
                else
                {
                    alert("옵션을 먼저 선택해주세요.");
                    $(frmId+' select[name*=\'addGoodsInput\']').val('');
                    return false;
                }
            }
        }


        /**
         * 추가상품 유효성검사
         */
        this.add_goods_valid = function(frmId) {
            if($(frmId+' input[name="addGoodsInputMustFl[]"]').length) {

                if($(frmId+' input[name="optionSno[]"]').length) {

                    var returnCnt = 0;
                    $(frmId+" div[id*='option_display_item_']").each(function (key) {

                        var mustFl = false;
                        $("#"+$(this).attr('id')+" input[name='addGoodsNo["+key+"][]']").each(function (textKey) {
                            var group = $(this).data('group');

                            $(frmId+' input[name="addGoodsInputMustFl[]"]').each(function () {
                                if($(this).val() == group) mustFl = true;
                            });
                        });

                        if(mustFl) returnCnt++;

                    });

                    if(returnCnt == $(frmId+" div[id*='option_display_item_']").length) return true;
                    else return false;
                }
                else return true;

            } else return true;

        }

        /**
         * 추가상품 삭제
         */
        this.remove_add_goods= function(optionId,addGoodsId) {
            $("#add_goods_display_item_"+optionId+"_"+addGoodsId).remove();
            if (typeof total_calculate !== 'undefined' && $.isFunction(total_calculate))   total_calculate();

            var addGoodsCnt = $('div[id=\'option_display_item_'+optionId+'\']').find('div[id*=\'add_goods_display_item_\']').length;

            if(addGoodsCnt == 0) $('div[id=\'option_display_item_'+optionId+'\']').find('.add').remove();

            var setAddGoodsPrice =  0;

            $("#option_display_item_"+optionId+" input[name*='add_goods_total_price']").each(function () {
                setAddGoodsPrice += parseFloat($(this).val());
            });

            $("#option_display_item_"+optionId+" input[name='addGoodsPriceSum[]']").val(setAddGoodsPrice);

        }


        /**
         * 상품 가격 계산
         * @param integer goodsFl 1: 상품 0:추가상품
         * @param integer itemNo 선택상품명
         * @param integer goodsCnt 상품 갯수
         */
        this.goods_calculate = function(frmId,goodsFl,itemNo,goodsCnt) {

            var goodsPrice = parseFloat($(frmId+' input[name="set_goods_price"]').val());

            if(goodsFl)
            {
                var optionTextPrice = 0;
                if(setOptionTextFl =='y') {
                    if($(frmId+' input[name="option_text_price_'+itemNo+'"]').length) optionTextPrice = parseFloat($(frmId+' input[name="option_text_price_'+itemNo+'"]').val());
                }

                var optionPrice = parseFloat($(frmId+' input[name="option_price_'+itemNo+'"]').val());

                var optionTotalPrice = (optionTextPrice)+(optionPrice);

                $(frmId+' .option_price_display_' + itemNo).html( numeral(((optionTotalPrice+goodsPrice)*goodsCnt).toFixed(setDecimal)).format());


                $('#option_display_item_' + itemNo + ' input[name="optionPriceSum[]"]').val((optionPrice*goodsCnt).toFixed(setDecimal));
                $('#option_display_item_' + itemNo + ' input[name="optionTextPriceSum[]"]').val((optionTextPrice*goodsCnt).toFixed(setDecimal));

                //var goodsPrice = parseFloat($('#set_goods_price').val());
                $("#option_display_item_"+itemNo+" input[name='goodsPriceSum[]']").val((goodsPrice*goodsCnt).toFixed(setDecimal));

            }
            else
            {
                var tmpStr = itemNo.split(setIntDivision);
                itemNo = tmpStr[0];
                var addGoodsItemNo =  tmpStr[1];
                var addGoodsPrice = parseFloat($(frmId+' input[name="add_goods_price_'+ itemNo+'_'+addGoodsItemNo+'"]').val());
                var addGoodsTotalPrice = parseFloat(addGoodsPrice*goodsCnt);

                $(frmId+' .add_goods_price_display_' + itemNo+'_'+addGoodsItemNo).html(numeral(addGoodsTotalPrice.toFixed(setDecimal)).format());

                $('#add_goods_display_item_'+ itemNo+'_'+addGoodsItemNo+' input[name*="add_goods_total_price"]').val(addGoodsTotalPrice);

                var setAddGoodsPrice =  0;

                $("#option_display_item_"+itemNo+" input[name*='add_goods_total_price']").each(function () {
                    setAddGoodsPrice += parseFloat($(this).val());
                });

                $("#option_display_item_"+itemNo+" input[name='addGoodsPriceSum[]']").val(setAddGoodsPrice);
            }

            if (typeof total_calculate !== 'undefined' && $.isFunction(total_calculate)) total_calculate();
        }

        this.enterKey = function(inputName) {
            if(event.keyCode == 13) {
                $(inputName).blur();
            }
        }


        this.init = function(param)
        {
            setOptionTextFl = param.setOptionTextFl;
            setOptionDisplayFl = param.setOptionDisplayFl;
            setAddGoodsFl =param.setAddGoodsFl;
            setIntDivision =param.setIntDivision;
            setStrDivision =param.setStrDivision;
            setMileageUseFl =param.setMileageUseFl;
            setCouponUseFl = param.setCouponUseFl;
            setStockFl = param.setStockFl;
            setDecimal = param.setDecimal;
            setOptionFl = param.setOptionFl;
            setGoodsPrice = param.setGoodsPrice;
            setGoodsNo = param.setGoodsNo;
            setMileageFl = param.setMileageFl;
            setControllerName = param.setControllerName;
            setFixedSales = param.setFixedSales;
            setFixedOrderCnt = param.setFixedOrderCnt;
            if(param.setTemplate) {
                setTemplate = param.setTemplate;
            }
            if (setFixedOrderCnt == 'option') {
                setMinOrderCnt = parseInt(param.setMinOrderCnt);
                setMaxOrderCnt = parseInt(param.setMaxOrderCnt);
            }
            if (setFixedSales != 'goods' || (setFixedSales == 'goods' && setOptionFl == 'n' && setOptionTextFl == 'n')) {
                setSalesUnit = parseInt(param.setSalesUnit);
                if (setSalesUnit > setMinOrderCnt) {
                    setMinOrderCnt = parseInt(param.setSalesUnit);
                }
            }


        }
        this.initCartTab = function(cartTabFl)
        {
            setCartTabFl = cartTabFl;
        }

    }

</script>



<script type="text/html" id="optionTemplate">
    <div id="option_display_item_<%=displayOptionkey%>" style="border-top:1px solid #dbdbdb;">
        <div class="check optionKey_<%=optionSno%>">
            <input type="hidden" name="goodsNo[]" value="<?php echo $goodsView['goodsNo']?>">
            <input type="hidden" name="optionSno[]" value="<%=optionSno%>">
            <input type="hidden" name="goodsPriceSum[]" value="0">
            <input type="hidden" name="addGoodsPriceSum[]" value="0">
            <input type="hidden" name="displayOptionkey[]" value="<%=displayOptionkey%>">

            <span class="name"><strong><%=optionName%></strong>
                <span><%=optionSellCodeValue%><%=optionDeliveryCodeValue%></span>

                <span class="option_text_display_<%=displayOptionkey%>"></span></span>

            <div class="price">
                <span class="count">
                <input type="text" class="text goodsCnt_<%=displayOptionkey%>" title="수량" name="goodsCnt[]" value="<?php echo $goodsView['defaultGoodsCnt']; ?>" data-value="<?php echo $goodsView['defaultGoodsCnt']; ?>" data-stock="<%=optionStock%>"   data-key="<%=displayOptionkey%>" onchange="goodsViewLayerController.input_count_change(this,'1');return false;">
                <span>
                <button type="button" class="up goods-cnt" title="증가"  value="up<?php echo STR_DIVISION?><%=displayOptionkey%>">증가</button>
                <button type="button" class="down goods-cnt" title="감소"  value="dn<?php echo STR_DIVISION?><%=displayOptionkey%>">감소</button>
                </span>
                </span>
                <em><input type="hidden" value="<%=optionPrice%>" name="option_price_<%=displayOptionkey%>"><input type="hidden" value="0" name="optionPriceSum[]" ><?php echo gd_currency_symbol()?><strong class="option_price_display_<%=displayOptionkey%>"><%=optionPrice%></strong><?php echo gd_currency_string()?></em>
            </div><div class="del">
                <button type="button" class="delete-goods" title="삭제" data-key="option_display_item_<%=displayOptionkey%>" >삭제</button>
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="addGoodsTemplate">
    <div id="add_goods_display_item_<%=displayOptionkey%>_<%=displayAddGoodsKey%>" class="check" >
    <span class="name"><%=addGoodsimge%>
    <input type="hidden" name="addGoodsNo[<%=optionIndex%>][]" value="<%=optionSno%>" data-group="<%=addGoodsGroup%>">
    <%=addGoodsName%>
    </span>
        <div class="price">
    <span class="count">
        <input type="text" class="text addGoodsCnt_<%=displayOptionkey%>_<%=displayAddGoodsKey%>" title="수량" name="addGoodsCnt[<%=optionIndex%>][]" value="1"  data-key="<%=displayOptionkey%><?php echo INT_DIVISION?><%=displayAddGoodsKey%>" data-stock-fl="<%=addGoodsStockFl%>"  data-stock="<%=addGoodsStock%>"  onchange="goodsViewLayerController.input_count_change(this);return false;">

    <span>
    <button type="button" class="up add-goods-cnt" title="증가"  value="up<?php echo STR_DIVISION?><%=displayOptionkey%><?php echo INT_DIVISION?><%=displayAddGoodsKey%>">증가</button>
    <button type="button" class="down add-goods-cnt" title="감소"   value="dn<?php echo STR_DIVISION?><%=displayOptionkey%><?php echo INT_DIVISION?><%=displayAddGoodsKey%>">감소</button>
    </span>
    </span>
            <em><input type="hidden" value="<%=addGoodsPrice%>" name="add_goods_price_<%=displayOptionkey%>_<%=displayAddGoodsKey%>"><input type="hidden" name="add_goods_total_price[<%=optionIndex%>][]" value="" ><?php echo gd_currency_symbol()?><strong class="add_goods_price_display_<%=displayOptionkey%>_<%=displayAddGoodsKey%>"></strong><?php echo gd_currency_string()?></em>
        </div>
        <div class="del">
            <button type="button" class="delete-add-goods" title="삭제" data-key="<%=displayOptionkey%>-<%=displayAddGoodsKey%>">삭제</button>
        </div>
    </div>
</script>



<script type="text/javascript">
    <!--

    var goodsViewLayerController = new gd_goods_view();

    $(document).ready(function(){

        var parameters		= {
            'setControllerName' : goodsViewLayerController,
            'setOptionFl' : '<?=$goodsView['optionFl']?>',
            'setOptionTextFl'	: '<?=$goodsView['optionTextFl']?>',
            'setOptionDisplayFl'	: '<?=$goodsView['optionDisplayFl']?>',
            'setAddGoodsFl'	: '<?php if($goodsView['addGoods']){?>y<?php } else { ?>n<?php } ?>',
            'setIntDivision'	: '<?=INT_DIVISION?>',
            'setStrDivision'	: '<?=STR_DIVISION?>',
            'setMileageUseFl'	: '<?=$mileageData['useFl']?>',
            'setCouponUseFl'	: 'n',
            'setMinOrderCnt'	: '<?=$goodsView['minOrderCnt']?>',
            'setMaxOrderCnt'	: '<?=$goodsView['maxOrderCnt']?>',
            'setStockFl'	: '<?=gd_isset($goodsView['stockFl'])?>',
            'setSalesUnit' : '<?=gd_isset($goodsView['salesUnit'],1)?>',
            'setDecimal' : '<?=$currency['decimal']?>',
            'setGoodsPrice' : '<?=gd_isset($goodsView['goodsPrice'],0)?>',
            'setGoodsNo' : '<?=$goodsView['goodsNo']?>',
            'setMileageFl' : ' <?=$goodsView['mileageFl']?>',
            'setFixedSales' : '<?=$goodsView['fixedSales']?>',
            'setFixedOrderCnt' : '<?=$goodsView['fixedOrderCnt']?>',
            'setOptionPriceFl' : '<?=$goodsView['optionPriceFl']?>',
            'setStockCnt' : '<?=$goodsView['stockCnt']?>'
        };

        goodsViewLayerController.init(parameters);



        $(".select-goods-btn").on('click', function(e){

            <?php if($goodsView['optionFl']=='y'){?>
            var goodsInfo		= $('#frmViewLayer input[name*=\'optionSno\']').length;
            <?php }else{?>
            var goodsInfo		= $('#frmViewLayer input[id="optionSnoInput"]').val();
            <?php }?>
            if (goodsInfo == '') {
                alert('가격 정보가 없거나 옵션이 선택되지 않았습니다!');
                return false;
            }

            <?php if(gd_isset($goodsView['optionTextFl'])=='y'){?>
            if(!goodsViewLayerController.option_text_valid("#frmViewLayer"))
            {
                alert('입력 옵션을 확인해주세요.');
                return false;
            }
            <?php }?>

            <?php if($goodsView['addGoods']){?>
            //추가상품
            if(!goodsViewLayerController.add_goods_valid("#frmViewLayer"))
            {
                alert('필수 추가 상품을 확인해주세요.');
                return false;
            }
            <?php }?>

            $('#optionViewLayer').find("input[name='goodsNo[]']").val('<?php echo $goodsView['goodsNo']?>');

            set_goods_option();
        });

        <?php if($goodsView['optionFl']=='n'){?>
        goodsViewLayerController.goods_calculate('#frmViewLayer',1,0,'<?php echo $goodsView['minOrderCnt']?>');
        <?php }?>

        $('button.goods-cnt').on('click', function(e){
            goodsViewLayerController.count_change(this,1);
        });

        $('button.add-goods-cnt').on('click', function(e){
            goodsViewLayerController.count_change(this);
        });


        <?php if($optionInfo['optionSno']){?>
        goodsViewLayerController.option_price_display("#frmViewLayer");

        var optionKey = $('#optionViewLayer').find("div[id*='option_display_item_<?php echo $optionInfo['optionSno']?>']");
        optionKey = $(optionKey).attr('id').replace("option_display_item_", "");
        $("#frmViewLayer .goodsCnt_"+optionKey).val('<?php echo $optionInfo['goodsCnt']?>');

        <?php if($optionInfo['optionTextSno']){?>
        <?php foreach($optionInfo['optionTextStr'] as $k=>$val){?>
        var optionText = $(".optionTextDisplay<?php echo $k?>").find("input[name*='optionTextInput_']");
        $(optionText).val("<?php echo $val?>");
        <?php }?>

        goodsViewLayerController.option_text_select($("#frmViewLayer input[name*='optionTextInput_']"));

        $("#frmViewLayer input[name*='optionTextInput_']").val('');
        <?php }?>

        <?php foreach($optionInfo['addGoodsNo'] as $k=>$val){?>
        $("#frmViewLayer select[name*='addGoodsInput']").each(function (key) {

            if($(this).find("option[value*='<?php echo $val?>']").length) {
                $(this).find("option[value*='<?php echo $val?>']").attr("selected", "selected");
                goodsViewLayerController.add_goods_select($("#frmViewLayer select[name='"+$(this).attr('name')+"']"));
                $("#frmViewLayer .addGoodsCnt_"+optionKey + '_<?php echo $val?>').val('<?php echo $optionInfo['addGoodsCnt'][$k]?>');
                goodsViewLayerController.goods_calculate("#frmViewLayer",'',optionKey + '<?php echo INT_DIVISION?><?php echo $val?>','<?php echo $optionInfo['addGoodsCnt'][$k]?>');
            }

        });
        <?php }?>

        <?php }?>

    });

    /**
     * 총 합산
     */
    function total_calculate()
    {
        var goodsPrice = parseFloat($('#frmViewLayer input[name="set_goods_price"]').val());

        //총합계 계산
        var goodsTotalCnt =  0;
        $('#frmViewLayer input[name*=\'goodsCnt[]\']').each(function () {
            goodsTotalCnt += parseFloat($(this).val());
        });
        var goodsTotalPrice = goodsPrice*goodsTotalCnt;


        var setOptionPrice =  0;


        $('#frmViewLayer input[name*="optionPriceSum[]"]').each(function () {
            setOptionPrice += parseFloat($(this).val());
        });

        var setOptionTextPrice =  0;
        $('#frmViewLayer input[name*="optionTextPriceSum[]"]').each(function () {
            setOptionTextPrice += parseFloat($(this).val());
        });


        var setAddGoodsPrice =  0;
        $('#frmViewLayer input[name*="add_goods_total_price["]').each(function () {
            setAddGoodsPrice += parseFloat($(this).val());
        });


        $('#set_option_price').val(setOptionPrice);
        $('#set_option_text_price').val(setOptionTextPrice);
        $('#set_add_goods_price').val(setAddGoodsPrice);


        var totalGoodsPrice = (goodsTotalPrice + setOptionPrice + setOptionTextPrice + setAddGoodsPrice).toFixed(<?=$currency['decimal']?>);
        $('#frmViewLayer input[name="set_total_price"]').val(totalGoodsPrice);
        $("#frmViewLayer .goods_total_price").html(" <?=gd_currency_symbol()?> "+numeral(totalGoodsPrice).format()+"<b><?=gd_currency_string()?></b>");

        benefit_calculation();
    }



    /*
     * 혜택
     */
    function benefit_calculation()
    {
        $('#frmViewLayer input[name="mode"]').val('order_write_benefit_goods');
        var parameters = $("#frmViewLayer").serialize();

        if($("#frmViewLayer input[name*='goodsNo']").length == 0)
        {
            parameters += "&goodsNo%5B%5D=<?php echo $goodsView['goodsNo']?>&goodsCnt%5B%5D=1&couponBenefitExcept=1";
        }

        $.post('../order/order_ps.php', parameters, function (data) {
            var getData = $.parseJSON(data);

            if(getData.totalDcPrice > 0 || getData.totalMileage > 0)
            {
                $(".benefits").show();

                if(getData.totalDcPrice > 0 )
                {
                    $(".benefits p.sale").show();
                    $(".end-price li.discount").show();
                    var tmp = new Array();
                    if(getData.goodsDcPrice)  {
                        tmp.push("상품 : " + " <?php echo gd_currency_symbol()?>"+numeral(getData.goodsDcPrice).format()+"<?php echo gd_currency_string()?>");

                        $("#set_dc_goods_price").val(getData.goodsDcPrice);

                    }
                    if(getData.memberDcPrice)  {
                        tmp.push("회원 : " + " <?php echo gd_currency_symbol()?>"+ numeral(getData.memberDcPrice).format()+"<?php echo gd_currency_string()?>");
                        $("#set_dc_member_price").val(getData.memberDcPrice);
                    }
                    if(getData.myappDcPrice)  {
                        tmp.push(" 모바일앱 : " + " <?php echo gd_currency_symbol()?>"+ numeral(getData.myappDcPrice).format()+"<?php echo gd_currency_string()?>");
                        $("#set_dc_myapp_price").val(getData.myappDcPrice);
                    }

                    $(".benefit_price").html("("+tmp.join()+")");

                    $(".total_benefit_price").html("-<?php echo gd_currency_symbol()?>"+numeral(getData.totalDcPrice).format()+"<b><?php echo gd_currency_string()?></b>");

                    $("#set_dc_price").val(getData.totalDcPrice);

                }
                else
                {
                    $("#set_dc_price").val('0');
                    $(".benefits p.sale").hide();
                    $(".end-price li.discount").hide();
                }

                if(getData.totalMileage > 0 )
                {
                    $(".benefits p.mileage").show();
                    var tmp =new Array();
                    if(getData.goodsMileage)  tmp.push("상품 : " + numeral(getData.goodsMileage).format()+"<?php echo $mileageData['unit']?>");
                    if(getData.memberMileage)  tmp.push("회원 : " + numeral(getData.memberMileage).format()+"<?php echo $mileageData['unit']?>");

                    $(".benefit_mileage").html("("+tmp.join()+")");

                    $(".total_benefit_mileage").html("+"+getData.totalMileage+"<?php echo $TPL_VAR["mileageData"]['unit']?>");
                }
                else  $(".benefits p.mileage").hide();

            } else {
                $("#set_dc_price").val('0');

                $(".benefits p.sale").hide();
                $(".end-price li.discount").hide();
            }

            var totalPrice = parseFloat($('#frmViewLayer input[name="set_total_price"]').val())-parseFloat(getData.totalDcPrice);
            $(".total_price").html(" <?php echo gd_currency_symbol()?> "+numeral(totalPrice).format()+"<b><?php echo gd_currency_string()?></b>");

            $(".end-price").show();

        });

    }

    //-->
</script>
<style>


    .ag-choice.add  {
        border-top:1px solid #e9e9e9;
    }

    .ag-choice .list strong  {
        display:table-cell;
        width:81px;
        color:#555;
        padding:7px 10px 0;
        vertical-align:top;
    }
    .ag-choice .list > div  {
        display:table-cell;
        width:330px;
    }

    .goods-layout1 .ag-choice {
        width: 100%;
        padding: 10px 0;
    }
    .goods-layout1 .ag-choice > strong {
        white-space: nowrap;
    }
    .goods-layout1 .goods-name-t {
        font-size: 14px;
        font-weight: bold;
        color: #000;
    }
    .goods-layout1 .goods-name {
        border-bottom: 1px solid #e6e6e6;
        padding: 0 0 15px;
    }
    .goods-layout1 .img-section {
        border: 1px solid #dbdbdb;
        float: left;
        margin: 0 5px 0 5px;
    }
    .goods-layout1 .content-section {
        float:right;
        /*overflow: hidden; */
        padding-right:0px;
        margin:0px;

    }
    .goods-layout1 .user-input {
        margin:0 0 9px;
    }

    .goods-layout1 .order-goods {
        clear:both;
    }

    .scroll-box {
        overflow-x:hidden;
        overflow-y:auto;
        height:480px;
        padding:0px;
        margin:0px;
    }


    .goods-layout1 .order-goods {
        clear:both;
    }

    .order-goods {
        background:#f5f5f5;
        border-top:none;
        border-bottom:2px solid #ff4c2e;
    }
    .order-goods .check {
        display:table;
        padding:13px 14px 13px 19px;
    }
    .order-goods > .check {
        border-top:1px solid #dbdbdb;
    }
    .order-goods .add {
        padding:10px 0 10px;
        border-top:1px solid #dbdbdb;
    }
    .order-goods .add .check {
        padding:0 14px 10px 19px;
    }
    .order-goods .add .check .name {
        padding:0 0 0 28px;
        background:url('../img/ap.png') no-repeat 7px 11px;
    }
    .order-goods .check .name > span {
        display:block;
        padding:5px 0 8px;
    }
    .order-goods .check .name > span em {
        display:block;
        color:#777;
    }
    .order-goods .check > * {
        vertical-align:middle;
    }
    .order-goods .check .name {
        display:table-cell;
        width:271px;
        padding:0 10px 0 0;
        color:#555;
    }
    .order-goods .check .name > strong {
        color:#111;
        font-size:13px;
    }
    .order-goods .check .name > strong.btm-txt {
        display:inline-block;
        padding-top:7px;
    }
    .order-goods .check .name > a img {
        margin:0 0 0 2px;
        vertical-align:-4px;
    }
    .order-goods .add .check .name img {
        width:31px;
        height:31px;
        margin:0 4px 0 0;
        border:1px solid #ccc;
        vertical-align:middle;
    }
    .order-goods .check .price {
        display:table-cell;
        width:210px;
        padding:0 13px;
        text-align:right;
    }
    .order-goods .check .price .count {
        display:inline-block;
        vertical-align:middle;
    }
    .order-goods .check .price .count .text {
        float:left;
        width:43px;
        height:31px;
        border:1px solid #ccc;
        color:#3f3f3f;
        line-height:31px;
        text-align:center;
    }
    .order-goods .check .price .count span {
        float:left;
        margin:0 0 0 -1px;
    }
    .order-goods .check .price .count span button {
        display:block;
        width:23px;
        height:17px;
        text-indent:-9999px;
    }
    .order-goods .check .price .count span button.up {
        border:0px;
        background:url('<?=PATH_ADMIN_GD_SHARE?>/img/count-up.png') no-repeat left top;
    }
    .order-goods .check .price .count span button.down {
        margin:-1px 0 0;
        background:url('<?=PATH_ADMIN_GD_SHARE?>/img/count-down.png') no-repeat left top;
        border:0px;
    }
    .order-goods .check .price em {
        display:inline-block;
        min-width:87px;
        padding:0 0 0 15px;
        color:#333;
        font-size:13px;
        text-align:right;
        vertical-align:middle;
    }
    .order-goods .check .price em strong {
        font-family:arial;
    }
    .order-goods .check .del {
        display:table-cell;
    }
    .order-goods .check .del button {
        width:16px;
        height:16px;
        background:url('<?=PATH_ADMIN_GD_SHARE?>/img/del.png') no-repeat left top;
        text-indent:-9999px;
        border:0px;
    }

    .end-price {
        clear:both;

    }



</style>
