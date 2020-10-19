<!-- 옵션변경 레이어 -->
<div class="box self-order-goods-optioin" >
    <form name=frmViewLayer id='frmViewLayer' method=post>
        <input type="hidden" name="mode" value="order_write_change_option" />
        <input type="hidden" name="scmNo" value="<?=$goodsView['scmNo']?>" />
        <input type="hidden" name="cartSno" value="<?=$cartSno?>" />
        <input type="hidden" name="cartMode" value="" />
        <input type="hidden" name="set_goods_price"  id="set_goods_price" value="<?=gd_money_format(gd_isset($goodsView['goodsPrice'],0),false)?>" />
        <input type="hidden" name="set_goods_fixedPrice"  id="set_goods_fixedPrice" value="<?=gd_isset($goodsView['fixedPrice'],0)?>" />
        <input type="hidden" name="set_goods_mileage" value="<?=gd_isset($goodsView['goodsMileageBasic'],0)?>" />
        <input type="hidden" name="set_goods_stock" value="<?=gd_isset($goodsView['stockCnt'],0)?>" />
        <input type="hidden" name="set_coupon_dc_price" value="<?=gd_isset($goodsView['goodsPrice'],0)?>" />

        <input type="hidden" name="set_goods_total_price" id="set_goods_total_price" value="0" />
        <input type="hidden" name="set_option_price"  id="set_option_price" value="0" />
        <input type="hidden" name="set_option_text_price" id="set_option_text_price" value="0" />
        <input type="hidden" name="set_add_goods_price"  id="set_add_goods_price" value="0" />
        <input type="hidden" name="set_total_price" id="set_total_price" value="0" />


        <input type="hidden" name="mileageFl" value="<?=$goodsView['mileageFl']?>" />
        <input type="hidden" name="mileageGoods" value="<?=$goodsView['mileageGoods']?>" />
        <input type="hidden" name="mileageGoodsUnit" value="<?=$goodsView['mileageGoodsUnit']?>" />
        <input type="hidden" name="goodsDiscountFl" value="<?=$goodsView['goodsDiscountFl']?>" />
        <input type="hidden" name="goodsDiscount" value="<?=$goodsView['goodsDiscount']?>" />
        <input type="hidden" name="goodsDiscountUnit" value="<?=$goodsView['goodsDiscountUnit']?>" />

        <input type="hidden" name="taxFreeFl" value="<?=$goodsView['taxFreeFl']?>" />
        <input type="hidden" name="taxPercent" value="<?=$goodsView['taxPercent']?>" />

        <input type="hidden" name="scmNo" value="<?=$goodsView['scmNo']?>" />
        <input type="hidden" name="brandCd" value="<?=$goodsView['brandCd']?>" />
        <input type="hidden" name="cateCd" value="<?=$goodsView['cateCd']?>" />
        <input type="hidden" name="selectGoodsFl" value="<?=$selectGoodsFl?>" />

        <input type="hidden" id="set_dc_price" value="0" />

        <input type="hidden" id="goodsOptionCnt" value="1" />
        <input type="hidden" name="goodsDeliveryFl" value="<?=$goodsView['delivery']['basic']['goodsDeliveryFl']?>" />
        <input type="hidden" name="sameGoodsDeliveryFl" value="<?=$goodsView['delivery']['basic']['sameGoodsDeliveryFl']?>" />
        <div class="view">
            <div class="scroll-box">
                <div class="goods-layout1">
                    <div class="img-section">
                        <?=$goodsView['image']['detail']['thumb'][0]?>
                    </div>
                    <div class="content-section">
                        <div class="goods-name" style="width:430px;">
                            <h3 class="goods-name-t">
                                <?=gd_isset($goodsView['goodsNmDetail'])?>
                                <?php if(gd_isset($goodsView['shortDescription'])){ ?>
                                    <p><?=$goodsView['shortDescription']?></p>
                                <?php } ?>
                            </h3>

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
                                <select name="deliveryCollectFl" style="width:155px !important">
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
                                            <select name="deliveryMethodFl">
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
                        <?php if($goodsView['optionFl'] == 'y'){ ?>
                            <?php if($goodsView['optionDisplayFl'] == 's'){ ?>
                                <div class="ag-choice choice">
                                    <div class="list">
                                        <strong>
                                            <?php
                                            if($goodsView['optionEachCntFl'] == 'one' && empty(goodsView['optionName']) === false){
                                                echo $goodsView['optionName'];
                                            }
                                            else {
                                                echo "옵션 선택";
                                            }
                                            ?>
                                        </strong>
                                        <div>
                                            <select name="optionSnoInput" class="tune" style="width:100%" onchange="goodsViewLayerController.option_price_display(this);">
                                                <option value="">=
                                                    <?php if($goodsView['optionEachCntFl'] == 'many' && empty($goodsView['optionName']) === false){ ?>
                                                        <?=$goodsView['optionName']?>
                                                    <?php } else { ?>
                                                        옵션
                                                    <?php } ?>
                                                    : 가격
                                                    <?php if(in_array('optionStock', $displayAddField)){ ?>: 재고<?php } ?>
                                                    =
                                                </option>
                                                <?php foreach($goodsView['option'] as $key => $value){ ?>
                                                    <?php if($value['optionViewFl'] == 'y'){ ?>
                                                    <option <?php if($goodsView['optionIcon']['goodsImage']){ ?><?php if($value['optionImage']){ ?>data-img-src="<?=$value['optionImage']?>"  <?php } else { ?>data-img-src="blank"<?php } ?> <?php } ?> value="<?=$value['sno'].$int_division.gd_money_format($value['optionPrice'],false).$int_division.$value['mileage'].$int_division.$value['stockCnt'].$str_division.$value['optionValue']?><?php if($goodsView['stockFl'] == 'y' && $value['optionSellFl']){?><?=$optionSoldOutCode[$val['optionSellCode']]?><?php } ?><?php if($value['optionDeliveryFl'] == 't' && $optionDeliveryDelayCode != '') {?> <?=$optionDeliveryDelayCode[$value['optionDeliveryCode']]?><?php } ?>" <?php if(($goodsView['stockFl'] == 'y' && $value['stockCnt'] <  $goodsView['minOrderCnt']) || $value['optionSellFl'] =='n' || $value['optionSellFl'] =='t'){ ?>disabled="disabled" <?php } ?> <?php if(gd_isset($optionInfo['optionSno']) && $optionInfo['optionSno'] == $value['sno']){ ?> selected='selected' <?php } ?>>
                                                            <?=$value['optionValue']?>
                                                            <?php if(gd_isset($value['optionPrice']) != '0'){ ?>  : <?=gd_currency_symbol()?><?php if(gd_isset($value['optionPrice']) > 0){ ?>+<?php } ?><?=gd_money_format($value['optionPrice'])?><?=gd_currency_string()?> <?php } ?>
                                                            <?php if($goodsView['stockFl'] == 'y' && $value['optionSellFl'] == 't'){ ?><?php echo $optionSoldOutCode[$value['optionSellCode']]?>
                                                            <?php } else if(($goodsView['stockFl'] == 'y' && $value['stockCnt'] <  $goodsView['minOrderCnt']) || $value['optionSellFl'] =='n') { ?><?php echo $optionSoldOutCode['n']?>
                                                            <?php } else { ?><?php if(in_array('optionStock',$displayAddField)  && $goodsView['stockFl'] == 'y'){ ?> : <?=number_format($value['stockCnt'])?>개
                                                        <?php } ?><?php } ?>
                                                        <?php if($value['optionDeliveryFl'] == 't' && $value['optionDeliveryCode'] != ''){ echo '['.$optionDeliveryDelayCode[$value['optionDeliveryCode']].']'; } ?>
                                                    </option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php } else if($goodsView['optionDisplayFl'] == 'd'){ ?>
                                <?php
                                $idx = 0;
                                foreach($goodsView['optionName'] as $key => $value){
                                ?>
                                    <?php if($idx == 0){ ?>
                                        <input type="hidden" name="optionSnoInput" value="<?php if($optionInfo['optionSnoText']){ echo $optionInfo['optionSnoText']; } ?>" />
                                        <input type="hidden" name="optionCntInput" value="<?=count($goodsView['optionName'])?>" />
                                    <?php } ?>
                                <div class="ag-choice choice">
                                    <div class="list">
                                        <strong><?=$value?></strong>
                                        <div>
                                            <select name="optionNo_<?=$idx?>" class="tune" style="width:100%;"  onchange="goodsViewLayerController.option_select(this,'<?=$idx?>', '<?=gd_isset($goodsView['optionName'][($idx + 1)])?>','<?php if(in_array('optionStock',$displayAddField)){ ?>y<?php } else { ?>n<?php } ?>');"
                                            <?php if($idx > 0){ ?> disabled="disabled"
                                            <?php } ?>>
                                            <option value="">=
                                                <?php if($idx == 0){ ?><?=$value?> 선택
                                                <?php } else { ?><?=$goodsView['optionName'][($idx - 1)]?>을 먼저 선택해 주세요
                                                <?php } ?> =
                                            </option>
                                            <?php if($idx == 0){ ?>
                                                <?php foreach($goodsView['optionDivision'] as $key2 => $value2){ ?>
                                                    <option   <?php if($goodsView['optionIcon']['goodsImage']){ ?><?php if($goodsView['optionIcon']['goodsImage'][$value2]){ ?> data-img-src="<?=$goodsView['optionIcon']['goodsImage'][$value2]?>" <?php } else { ?>data-img-src="blank" <?php } ?><?php } ?> value="<?=$value2?>" <?php if(($goodsView['stockFl'] == 'y' && $goodsView['stockCnt'] < $goodsView['minOrderCnt']) || ($goodsView['stockFl'] == 'y' && $goodsView['fixedOrderCnt'] == 'option' && isset($goodsView['optionDivisionStock']) && $goodsView['optionDivisionStock'][$key2]['stockCnt'] < $goodsView['minOrderCnt']) || ($goodsView['stockFl'] == 'y' && $goodsView['optionDivisionStock'][$key2]['stockCnt'] == '0') || $goodsView['optionDivisionStock'][$key2]['optionSellFl'] ==  'n' || $goodsView['optionDivisionStock'][$key2]['optionSellFl'] ==  't'){?> disabled="disabled"<?php }?>>
                                                        <?=$value2?>
                                                        <?php if($goodsView['optionDivisionStock'][$key2]['optionSellFl']=='t'){?>[<?=$optionSoldOutCode[$goodsView['optionDivisionStock'][$key2]['optionSellCode']]?>]
                                                        <?php }else if(($goodsView['stockFl']=='y'&&$goodsView['optionDivisionStock'][$key2]['stockCnt']< 1)||$goodsView['optionDivisionStock'][$key2]['optionSellFl']=='n'){?>[<?=$optionSoldOutCode['n']?>]
                                                        <?php }?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>
                                            </select></div>
                                    </div>
                                </div>
                                <div id="iconImage_<?=$idx?>" class="option_icon"></div>
                                <?php
                                    $idx++;
                                }
                                ?>
                            <?php } ?>
                        <?php } ?>
                        <!--  옵션 끝 -->

                        <!-- 추가 옵션 입력형 시작-->
                        <?php if($goodsView['optionTextFl'] == 'y'){ ?>
                            <?php
                            $idx=0;
                            foreach($goodsView['optionText'] as $key => $value){
                            ?>
                                <div class="ag-choice choice">
                                    <div class="list">
                                        <?php if($idx == 0){ ?>
                                        <input type="hidden" id="optionTextCnt" value="<?=count($goodsView['optionText'])?>" />
                                        <?php } ?>
                                        <strong><span class="optionTextNm_<?=$idx?>"><?=$value['optionName']?><?php if($value['mustFl'] == 'y'){ ?><em>(필수)</em><?php } ?></span> <input type="hidden" name="optionTextMust_<?=$idx?>" value="<?=$value['mustFl']?>" /> <input type="hidden" name="optionTextLimit_<?=$idx?>" value="<?=$value['inputLimit']?>" /></strong>

                                        <div class="option_input optionTextDisplay<?=$value['sno']?>">
                            <span class="txt-field">
                            <input type="hidden" name="optionTextSno_<?=$idx?>" value="<?=$value['sno']?>"/>
                            <input type="text" name="optionTextInput_<?=$idx?>" value="" size="25" class="text" data-sno="<?=$value['sno']?>" onchange="goodsViewLayerController.option_text_select(this)" placeholder="<?=$value['inputLimit']?>글자를 입력하세요." maxlength="<?=$value['inputLimit']?>" />
                            <input type="hidden"  value="<?=$value['addPrice']?>"/>
                            </span>
                                            <?php if($value['addPrice'] != 0){ ?>
                                                <span class="msg">※ 작성시 <?=gd_currency_symbol()?><?=gd_money_format($value['addPrice'])?><?=gd_currency_string()?> 추가</span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php
                                $idx++;
                            }
                            ?>
                        <?php } ?>
                        <!-- 추가 옵션 입력형 종료 -->



                        <?php if($goodsView['addGoods']){ ?>
                            <?php foreach($goodsView['addGoods'] as $key => $value){ ?>
                                <div class="ag-choice choice <?php if($key == '0'){?>add  <?php } ?>">
                                    <div class="list">
                                        <strong><?=$value['title']?>  <?php if($value['mustFl'] == 'y'){ ?><em>(필수)</em><input type="hidden" name="addGoodsInputMustFl[]" value="<?=$key?>"><?php } ?></strong>
                                        <div>
                                            <select name="addGoodsInput<?=$key?>"   data-key="<?=$key?>" class="tune" style="width:100%;"  onchange="goodsViewLayerController.add_goods_select(this)">
                                                <option value="">추가상품</option>
                                                <?php foreach($value['addGoodsList'] as $key2 => $value2){ ?>
                                                <option <?php if($value['addGoodsImageFl'] == 'y'){ ?><?php if($value2['imageSrc']){ ?>data-img-src="<?=$value2['imageSrc']?>"<?php } else { ?>data-img-src="blank" <?php } ?><?php } ?> value="<?=$value2['addGoodsNo'].$int_division.$value2['goodsPrice'].$str_division.$value2['goodsNm'].'('.$value2['optionNm'].')'.$str_division.rawurlencode(gd_html_add_goods_image($value2['addGoodsNo'], $value2['imageNm'], $value2['imagePath'], $value2['imageStorage'], 30, $value2['goodsNm'], '_blank')).$str_division.$key.$str_division.$value2['stockUseFl'].$str_division.$value2['stockCnt']?>" <?php if($value2['soldOutFl'] =='y' || ($value2['stockUseFl'] =='1' && $value2['stockCnt'] == '0')){ ?> disabled="disabled" <?php } ?>>
                                                    <?=$value2['goodsNm']?> (<?=$value2['optionNm']?> <?php if(gd_isset($value2['goodsPrice']) != '0'){ ?> / <?=gd_currency_symbol()?><?php if(gd_isset($value2['goodsPrice']) > 0){ ?>+<?php } ?><?=gd_money_format($value2['goodsPrice'])?><?=gd_currency_string()?> <?php } ?> <?php if($value2['soldOutFl'] =='y' || ($value2['stockUseFl'] =='1' && $value2['stockCnt'] == '0')){ ?> / 품절 <?php } ?>)</option>
                                            <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>


                    </div>

                    <?php if($goodsView['optionFl'] == 'y'){ ?>
                    <div class="option_total_display_area" style="display:none">
                        <div class="order-goods option_display_area" ></div>
                    </div>
                    <?php } else { ?>
                    <div class="order-goods option_display_area">
                        <div class="option_display_item_0" id="option_display_item_0">
                            <input type="hidden" name="goodsNo[]" value="<?=$goodsView['goodsNo']?>">
                            <input type="hidden" name="optionSno[]" value="<?=gd_isset($goodsView['option'][0]['sno'])?>"/>
                            <input type="hidden" name="goodsPriceSum[]" value="0">
                            <input type="hidden" name="addGoodsPriceSum[]" value="0">
                            <div class="check optionKey_0">
                            <span class="name"><strong><?=gd_isset($goodsView['goodsNmDetail'])?></strong>
                                <span id="option_text_display_0"></span></span>

                                <div class="price">
                                        <span class="count">
                                                         <input type="text" class="text goodsCnt_0" title="수량" name="goodsCnt[]"  value="<?=gd_isset($goodsView['goodsCnt'])?>" data-value="<?=gd_isset($goodsView['goodsCnt'])?>"  data-stock="<?=gd_isset($goodsView['totalStock'])?>" data-key="0" onchange="goodsViewLayerController.input_count_change(this,'1');return false;">

                                            <span>
                                                <button type="button" class="up goods-cnt" title="증가"  value="up<?=$str_division?>0" style="cursor: pointer">증가</button>
                                                <button type="button" class="down goods-cnt" title="감소" value="dn<?=$str_division?>0" style="cursor: pointer">감소</button>
                                            </span>
                                        </span>

                                    <em><input type="hidden" value="<?=gd_isset($goodsView['option'][0]['optionPrice'],0)?>" name="optionPriceSum[]"> <input type="hidden" value="<?=gd_isset($goodsView['option'][0]['optionPrice'])?>" name="option_price_0"><?=gd_currency_symbol()?><strong class="option_price_display_0"><?=gd_money_format(gd_isset($goodsView['option'][0]['optionPrice'],0),false)?></strong><?=gd_currency_string()?></em>

                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <div class="text-center mgt30">
                <button type="button" class="skinbtn point1 layer-close btn-close"><em>취소</em></button>
                <button type="button" class="skinbtn point2 layerboard-save" id="btnToCart"><em>확인</em></button>
            </div>
        </div>
    </form>
</div>

<link type="text/css" rel="stylesheet" href="/admin/gd_share/script/jquery/chosen/chosen.css" />
<script type="text/javascript" src="/admin/gd_share/script/jquery/chosen/chosen.jquery.min.js"></script>
<script type="text/javascript" src="/admin/gd_share/script/orderWrite/orderWrite_goodsOption.js?ts=<?=time()?>"></script>
<script type="text/javascript" src="/admin/gd_share/script/jquery/chosen-imageselect/src/ImageSelect.jquery.js"></script>
<link type="text/css" rel="stylesheet" href="/admin/gd_share/script/jquery/chosen-imageselect/src/ImageSelect.css" />

<!-- //옵션변경 레이어 -->
<script type="text/javascript">
    <!--
    $(".modal-content").css("width", "627px");

    // 금액 표시 방법
    var gdCurrencySymbol = '<?php echo gd_currency_symbol()?>';
    var gdCurrencyString = '<?php echo gd_currency_string()?>';
    var goodsViewLayerController = new gd_goods_view();

    $(document).ready(function(){
        $("select.tune").chosen({
            disable_search_threshold: 10,
            no_results_text: '검색결과가 없습니다.'
        });

        $("select.multiple-select").chosen({
            disable_search_threshold: 10,
            no_results_text: '검색결과가 없습니다.'
        });

        var parameters		= {
            'setTemplate' : 'Layer',
            'setControllerName' : goodsViewLayerController,
            'setOptionFl' : '<?=$goodsView['optionFl']?>',
            'setOptionTextFl'	: '<?=$goodsView['optionTextFl']?>',
            'setOptionDisplayFl'	: '<?=$goodsView['optionDisplayFl']?>',
            'setAddGoodsFl'	: '<?php if(is_array($goodsView['addGoods'])){?>y<?php } else { ?>n<?php } ?>',
                'setIntDivision'	: '<?=$int_division?>',
            'setStrDivision'	: '<?=$str_division?>',
            'setMileageUseFl'	: '<?=$mileageData['useFl']?>',
            'setCouponUseFl'	: '<?=$couponUse?>',
            'setMinOrderCnt'	: '<?=$goodsView['minOrderCnt']?>',
            'setMaxOrderCnt'	: '<?=$goodsView['maxOrderCnt']?>',
            'setStockFl'	: '<?=gd_isset($goodsView['stockFl'])?>',
            'setSalesUnit' : '<?=gd_isset($goodsView['salesUnit'],1)?>',
            'setDecimal' : '<?=$currency['decimal']?>',
            'setGoodsPrice' : '<?=gd_isset($goodsView['goodsPrice'],0)?>',
            'setGoodsNo' : '<?=$goodsView['goodsNo']?>',
            'setMileageFl' : '<?=$goodsView['mileageFl']?>',
            'setFixedSales' : '<?=$goodsView['fixedSales']?>',
            'setFixedOrderCnt' : '<?=$goodsView['fixedOrderCnt']?>'
        };

        goodsViewLayerController.init(parameters);

        if('<?=$goodsView['optionFl']?>' == 'n'){
            goodsViewLayerController.goods_calculate('#frmViewLayer',1,0,'<?=$goodsView['minOrderCnt']?>');
        }

        $('button.goods-cnt').on('click', function(e){
            goodsViewLayerController.count_change(this,1);
        });

        $('button.add-goods-cnt').on('click', function(e){
            goodsViewLayerController.count_change(this);
        });

        //취소
        $('.btn-close').on('click', function(){
            layer_close();
        });

        $('#btnToCart').on('click', function(e){
            if('<?=$goodsView['optionFl']?>' == 'y') {
                var goodsInfo = $('#frmViewLayer input[name*=\'optionSno[]\']').length;
            }
            else {
                var goodsInfo		= $('#frmViewLayer input[name="optionSnoInput"]').val();
            }
            if (goodsInfo == '') {
                alert("가격 정보가 없거나 옵션이 선택되지 않았습니다!");
                return false;
            }

            if('<?=gd_isset($goodsView['optionTextFl'])?>' == 'y'){
                if(!goodsViewLayerController.option_text_valid("#frmViewLayer"))
                {
                    alert("입력 옵션을 확인해주세요.");
                    return false;
                }
            }

            if('<?=$goodsView['addGoods']?>'){
                //추가상품
                if(!goodsViewLayerController.add_goods_valid("#frmViewLayer"))
                {
                    alert("필수 추가 상품을 확인해주세요.");
                    return false;
                }
            }

            $('#optionViewLayer').find("input[name='goodsNo[]']").val('<?=$goodsView['goodsNo']?>');

            var parameters = $("#frmViewLayer").serialize();

            $.ajax({
                method: 'POST',
                cache: false,
                url: './order_ps.php',
                data: parameters,
            }).success(function () {
                parent.set_goods('y');

                layer_close();
            }).error(function (e) {
                alert(e.responseText);
            });
        });

        if('<?=$optionInfo['optionSno']?>'){
            if('<?=$goodsView['optionFl']?>' == 'n'){
                var optionKey = $('#optionViewLayer').find("div[id*='option_display_item_0']");
            }
            else {
                goodsViewLayerController.option_price_display("#frmViewLayer");
                var optionKey = $('#optionViewLayer').find("div[id*='option_display_item_<?=$optionInfo['optionSno']?>']");
            }

            if($(optionKey).attr('id')) {
                optionKey = $(optionKey).attr('id').replace("option_display_item_", "");
                $("#frmViewLayer .goodsCnt_"+optionKey).val('<?=$optionInfo['goodsCnt']?>');

                if('<?=count($optionInfo['optionTextSno'])?>' > 0){
                    <?php
                        $idx=0;
                        foreach($optionInfo['optionTextStr'] as $key => $value){
                    ?>
                        var optionText = $("#frmViewLayer .optionTextDisplay" + '<?=$key?>').find("input[name*='optionTextInput_']");
                        $(optionText).val('<?=$value?>');
                    <?php
                        $idx++;
                        }
                    ?>

                    goodsViewLayerController.option_text_select($("#frmViewLayer input[name*='optionTextInput_']"));

                    $("#frmViewLayer input[name*='optionTextInput_']").val('');
                }

                var duplicationAddOption = [];
                <?php
                $idx = 0;
                foreach($optionInfo['addGoodsNo'] as $key => $value){
                ?>
                    $("#frmViewLayer select[name*='addGoodsInput']").each(function (key) {
                        if($.inArray('<?=$value?>', duplicationAddOption) < 0 && $(this).find("option[value*='<?=$value?>']").length) {
                            duplicationAddOption[<?=$idx?>] = '<?=$value?>';
                            $(this).find("option[value*='<?=$value?>']").attr("selected", "selected");
                            goodsViewLayerController.add_goods_select($("#frmViewLayer select[name='"+$(this).attr('name')+"']"));
                            $("#frmViewLayer .addGoodsCnt_"+optionKey + '_<?=$value?>').val('<?=$optionInfo['addGoodsCnt'][$key]?>');
                            goodsViewLayerController.goods_calculate("#frmViewLayer",'',optionKey + '<?=$int_division?><?=$value?>','<?=$optionInfo['addGoodsCnt'][$key]?>');

                        }
                    });
                <?php
                    $idx++;
                }
                ?>

                <?php if(!$optionInfo['optionTextSno'] && !$optionInfo['addGoodsNo']){ ?>
                    goodsViewLayerController.goods_calculate("#frmViewLayer",1, optionKey,'<?=$optionInfo['goodsCnt']?>');
                <?php } ?>
            }
        }
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

        var totalGoodsPrice = (goodsTotalPrice + setOptionPrice + setOptionTextPrice + setAddGoodsPrice).toFixed('<?=$currency['decimal']?>');
        $('#frmViewLayer input[name="set_total_price"]').val(totalGoodsPrice);

    }


    //-->
</script>


<script type="text/html" id="optionTemplateLayer">
    <div id="option_display_item_<%=displayOptionkey%>" style="border-top:1px solid #dbdbdb;">
        <div class="check optionKey_<%=optionSno%>">
            <input type="hidden" name="goodsNo[]" value="<?=$goodsView['goodsNo']?>">
            <input type="hidden" name="optionSno[]" value="<%=optionSno%>">
            <input type="hidden" name="goodsPriceSum[]" value="0">
            <input type="hidden" name="addGoodsPriceSum[]" value="0">
            <input type="hidden" name="displayOptionkey[]" value="<%=displayOptionkey%>">
            <span class="name"><strong><%=optionName%></strong>
                <span id="option_text_display_<%=displayOptionkey%>"></span></span>

            <div class="price">
                <span class="count">
                <input type="text" class="text goodsCnt_<%=displayOptionkey%>" title="수량" name="goodsCnt[]" value="<?=$goodsView['goodsCnt']?>" data-value="<?=$goodsView['goodsCnt']?>"  data-stock="<%=optionStock%>" data-key="<%=displayOptionkey%>" onchange="goodsViewLayerController.input_count_change(this,'1');return false;">
                <span>
                <button type="button" class="up goods-cnt" title="증가"  value="up<?=$str_division?><%=displayOptionkey%>">증가</button>
                <button type="button" class="down goods-cnt" title="감소"  value="dn<?=$str_division?><%=displayOptionkey%>">감소</button>
                </span>
                </span>
                <em><input type="hidden" value="<%=optionPrice%>" name="option_price_<%=displayOptionkey%>"><input type="hidden" value="0" name="optionPriceSum[]" ><?=gd_currency_symbol()?><strong class="option_price_display_<%=displayOptionkey%>"><%=optionPrice%></strong><?=gd_currency_string()?></em>
            </div>
            <div class="del">
                <button type="button" class="delete-goods" title="삭제" data-key="option_display_item_<%=displayOptionkey%>" >삭제</button>
            </div>
        </div>
    </div>
</script>

<script type="text/html" id="addGoodsTemplateLayer">
    <div id="add_goods_display_item_<%=displayOptionkey%>_<%=displayAddGoodsKey%>" class="check" >
    <span class="name"><%=addGoodsimge%>
    <input type="hidden" name="addGoodsNo[<%=optionIndex%>][]" value="<%=optionSno%>" data-group="<%=addGoodsGroup%>">
    <%=addGoodsName%>
    </span>
        <div class="price">
    <span class="count">
    <input type="text" class="text addGoodsCnt_<%=displayOptionkey%>_<%=displayAddGoodsKey%>" title="수량" name="addGoodsCnt[<%=optionIndex%>][]" value="1"  data-key="<%=displayOptionkey%><?=$int_division?><%=displayAddGoodsKey%>" data-stock-fl="<%=addGoodsStockFl%>"  data-stock="<%=addGoodsStock%>"  onchange="goodsViewLayerController.input_count_change(this);return false;">
    <span>
    <button type="button" class="up add-goods-cnt" title="증가"  value="up<?=$str_division?><%=displayOptionkey%><?=$int_division?><%=displayAddGoodsKey%>">증가</button>
    <button type="button" class="down add-goods-cnt" title="감소"   value="dn<?=$str_division?><%=displayOptionkey%><?=$int_division?><%=displayAddGoodsKey%>">감소</button>
    </span>
    </span>
            <em><input type="hidden" value="<%=addGoodsPrice%>" name="add_goods_price_<%=displayOptionkey%>_<%=displayAddGoodsKey%>"><input type="hidden" name="add_goods_total_price[<%=optionIndex%>][]" value="" ><?=gd_currency_symbol()?><strong class="add_goods_price_display_<%=displayOptionkey%>_<%=displayAddGoodsKey%>"></strong><?=gd_currency_string()?></em>
        </div>
        <div class="del">
            <button type="button" class="delete-add-goods" title="삭제" data-key="<%=displayOptionkey%>-<%=displayAddGoodsKey%>">삭제</button>
        </div>
    </div>
</script>
