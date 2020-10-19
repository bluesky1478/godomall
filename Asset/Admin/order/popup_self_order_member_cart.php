<div class="self-order-member-cart">
    <form id="frmCart" name="frmCart" method="post">
    <input type="hidden" name="memNo" value="<?=$memNo?>" />
    <input type="hidden" name="reSetMemberCouponApply" value="<?=$reSetMemberCouponApply?>" />
    <!-- 상품내용 시작 -->
    <div class="self-order-contents">
    <div class="table1 type1">
    <?php foreach($cartInfo as $key => $valueArray){ ?>
        <?php if($cartScmCnt > 1) {?><h3 class="tit-supplier"><?=$cartScmInfo[$key]['companyNm']?> 배송상품</h3><?php } ?>
        <table>
            <thead>
            <tr>
                <th>
                    <span class="form-element">
                        <input type="checkbox" id="allCheck-<?=$key?>" class="checkbox js-checkall" data-target-id="cartSno<?=$key?>_" data-target-form="#frmCart" checked="checked">
                        <label for="allCheck-<?=$key?>" class="check-s on">전체선택</label>
                    </span>
                </th>
                <th>상품/옵션 정보</th>
                <th>수량</th>
                <th>상품금액</th>
                <th>할인/적립</th>
                <th>합계금액</th>
                <th>배송비</th>
            </tr>
            </thead>
            <tbody>
                <?php foreach($valueArray as $valueArrayKey => $valueArray2){ ?>
                    <?php foreach($valueArray2 as $valueArrayKey2 => $value){ ?>
                        <tr>
                            <td rowspan="<?php if(empty($value['addGoods']) === false){ echo count($value['addGoods']) + 2; } else { echo 2; } ?>" class="ta-c cb-array">
                                <span class="form-element">
                                    <input type="checkbox" name="cartSno[]" id="cartSno<?=$key?>_<?=$value['sno']?>" value="<?=$value['sno']?>" class="checkbox" checked="checked" data-price="<?=$value['price']['goodsPriceSubtotal']?>" data-mileage="<?=$value['mileage']['goodsMileage']+$value['mileage']['memberMileage']?>" data-goodsdc="<?=$value['price']['goodsDcPrice']?>" data-memberdc="<?=$value['price']['memberDcPrice']+$value['price']['memberOverlapDcPrice']?>" data-coupondc="<?=$value['price']['couponGoodsDcPrice']?>" data-possible="<?=$value['orderPossible']?>"/>
	<label for="cartSno<?=$key?>_<?=$value['sno']?>" class="check-s on">선택</label>
                                </span>
                            </td>

                            <!-- 상품/옵션 정보 -->
                            <td class="gi this-product">
                                <span><?=$value['goodsImage']?></span>
                                <div>
                                   <?php if($value['orderPossibleMessageList']){ ?>
                                       <strong class="caution-msg1 pos-r">구매 이용 조건 안내
                                           <a class="normal-btn small1 target-impossible-layer">
                                               <em >전체보기<img class="arrow" src="/admin/gd_share/img/self_order_member_cart/bl_arrow.png" alt="" /></em>
                                           </a>
                                           <div class="nomal-layer display-none">
                                               <div class="wrap">
                                                   <strong>결제 제한 조건 사유</strong>
                                                   <div class="list">
                                                       <table cellspacing="0">
                                                       <?php foreach($value['orderPossibleMessageList'] as $possibleKey => $possibleValue){ ?>
                                                           <tr>
                                                               <td class="strong"><?=$possibleValue?></td>
                                                           </tr>
                                                       <?php } ?>
                                                       </table>
                                                   </div>
                                                   <button type="button" class="close target-impossible-layer" title="닫기">닫기</button>
                                               </div>
                                           </div>
                                       </strong>
                                   <?php } ?>

                                    <?php if($value['duplicationGoods'] === 'y'){ ?>
                                        <strong class="caution-msg1">중복 상품</strong>
                                    <?php } ?>

                                    <?php if($couponUse === 'y' && $couponConfig['chooseCouponMemberUseType'] !== 'member' && $value['couponBenefitExcept'] === 'n'){ ?>
                                        <div id="coupon_apply_<?=$value['sno']?>">
                                            <?php if($value['memberCouponNo']){ ?>
                                                <img class="self-order-cancel-coupon" src="/admin/gd_share/img/self_order_member_cart/coupon-cancel.png" alt="쿠폰취소" />
                                                <img src="/admin/gd_share/img/self_order_member_cart/coupon-change.png" alt="쿠폰변경" />
                                            <?php } else { ?>
                                                <img src="/admin/gd_share/img/self_order_member_cart/coupon-apply.png" alt="쿠폰적용"/>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>

                                    <?=$value['goodsNm']?>

                                    <?php if($value['payLimitFl'] === 'y' && is_array($value['payLimit'])){ ?>
                                        <p>
                                            <?php foreach($value['payLimit'] as $payLimitKey => $payLimitKeyValue){ ?>
                                                <img src="/admin/gd_share/img/self_order_member_cart/settle-kind-<?=$payLimitKeyValue?>.png" alert="<?=$payLimitKeyValue?>" <?php if($payLimitKeyValue == 'pg'){ ?>class="hand js-pg-limit-view"<?php } ?>>
                                            <?php } ?>
                                        </p>
                                    <?php } ?>

                                    <?php for($i=0; $i<count($value['option']); $i++){ ?>
                                        <dl>
                                            <dt><?=$value['option'][$i]['optionName']?> :</dt>
                                            <dd><?=$value['option'][$i]['optionValue']?>
                                                <?php if((($i + 1) == count($value['option'])) && $value['option'][$i]['optionPrice'] != 0){ ?>
                                                (<?php if($value['option'][$i]['optionPrice'] > 0){ ?>+<?php } ?><?=gd_currency_display($value['option'][$i]['optionPrice'])?>)
                                                <?php } ?>
                                            </dd>
                                        </dl>
                                    <?php } ?>

                                    <?php foreach($value['optionText'] as $optionTextKey => $optionTextValue){ ?>
                                        <?php if($optionTextValue['optionValue']){ ?>
                                            <dl>
                                                <dt><?=$optionTextValue['optionName']?> :</dt>
                                                <dd><?=$optionTextValue['optionValue']?>
                                                    <?php if($optionTextValue['optionTextPrice'] != 0){ ?>
                                                        (<?php if($optionTextValue['optionTextPrice'] > 0){ ?>+<?php } ?><?=gd_currency_display($optionTextValue['optionTextPrice'])?>)
                                                    <?php } ?>
                                                </dd>
                                            </dl>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </td>
                            <!-- 상품/옵션 정보 -->

                            <!-- 수량 -->
                            <td class="ta-c count this-product">
                                <input type="text" name="goodsCnt[]" value="<?=$value['goodsCnt']?>" title="수량" class="text" data-stock-fl="<?=$value['stockFl']?>"  <?php if($value['optionFl'] === 'y'){ ?>data-total-stock="<?=$value['stockCnt']?>" <?php } else { ?>data-total-stock="<?=$value['totalStock']?>"  <?php } ?> data-min-order-cnt="<?=$value['minOrderCnt']?>"  data-max-order-cnt="<?=$value['maxOrderCnt']?>" data-sales-unit="<?=$value['salesUnit']?>" readonly="readonly" />
                            </td>
                            <!-- 수량 -->

                            <!-- 상품금액 -->
                            <td class="ta-c this-product">
                                <?php if(empty($value['goodsPriceString']) === false){ ?>
                                    <strong class="price"><?=$value['goodsPriceString']?></strong>
                                <?php } else { ?>
                                <strong class="<?php if($value['timeSaleFl']){ echo "time-sale"; } else { echo "price"; } ?>"><?=gd_currency_display($value['price']['goodsPriceSum'] + $value['price']['optionPriceSum'] + $value['price']['optionTextPriceSum'])?></strong>
                                <?php } ?>
                            </td>
                            <!-- 상품금액 -->

                            <!-- 할인/적립 -->
                            <td rowspan="<?php if(empty($value['addGoods']) === false){ echo count($value['addGoods']) + 2; } else { echo 2; } ?>" class="benefits">
                                <?php if(($value['price']['goodsDcPrice'] + $value['price']['memberDcPrice'] + $value['price']['memberOverlapDcPrice'] + $value['price']['couponGoodsDcPrice']) > 0){ ?>
                                <dl class="sale">
                                    <dt>할인</dt>
                                    <?php if($value['price']['goodsDcPrice'] > 0){ ?>
                                    <dd>상품 <strong>-<?php echo gd_currency_display($value['price']['goodsDcPrice']) ?></strong></dd>
                                    <?php } ?>
                                    <?php if(($value['price']['memberDcPrice'] + $value['price']['memberOverlapDcPrice']) > 0){ ?>
                                    <dd>회원 <strong>-<?=gd_currency_display($value['price']['memberDcPrice']+$value['price']['memberOverlapDcPrice'])?></strong></dd>
                                    <?php } ?>
                                    <?php if($value['price']['couponGoodsDcPrice'] > 0){ ?>
                                    <dd>쿠폰 <strong>-<?=gd_currency_display($value['price']['couponGoodsDcPrice'])?></strong></dd>
                                    <?php } ?>
                                </dl>
                                <?php } ?>

                                <?php if($mileage['useFl'] === 'y' && ($value['mileage']['goodsMileage'] + $value['mileage']['memberMileage'] + $value['mileage']['couponGoodsMileage']) > 0){ ?>
                                <dl class="mileage">
                                    <dt>적립</dt>
                                    <?php if($value['mileage']['goodsMileage'] > 0){ ?>
                                    <dd>상품 <strong>+<?php echo gd_money_format($value['mileage']['goodsMileage']);?><?php echo $mileage['unit']; ?></strong></dd>
                                    <?php } ?>
                                    <?php if($value['mileage']['memberMileage'] > 0){ ?>
                                    <dd>회원 <strong>+<?php echo gd_money_format($value['mileage']['memberMileage']);?><?php echo $mileage['unit']; ?></strong></dd>
                                    <?php } ?>
                                    <?php if($value['mileage']['couponGoodsMileage'] > 0){ ?>
                                    <dd>쿠폰 <strong>+<?php echo gd_money_format($value['mileage']['couponGoodsMileage']);?><?php echo $mileage['unit']; ?></strong></dd>
                                    <?php } ?>
                                </dl>
                                <?php } ?>
                            </td>
                            <!-- 할인/적립 -->

                            <!-- 합계금액 -->
                            <td rowspan="<?php if(empty($value['addGoods']) === false){ echo count($value['addGoods']) + 2; } else { echo 2; }?>" class="ta-c">
                                <strong class="price"><?php echo gd_currency_display($value['price']['goodsPriceSubtotal']); ?></strong>
                            </td>
                            <!-- 합계금액 -->

                            <?php if($value['goodsDeliveryFl'] === 'y'){ ?>
                                <?php if($valueArrayKey2 === 0){ ?>
                                    <td rowspan="<?php echo ($setDeliveryInfo[$valueArrayKey]['goodsLineCnt'] * 2) + $setDeliveryInfo[$valueArrayKey]['addGoodsLineCnt'];?>" class="ta-c">
                                        <span class="c-gray">
                                            <?=$setDeliveryInfo[$valueArrayKey]['goodsDeliveryMethod']?><br/>
                                            <?php if($setDeliveryInfo[$valueArrayKey]['fixFl'] === 'free'){ ?>
                                                무료배송
                                            <?php } else { ?>
                                                <?php if($setDeliveryInfo[$valueArrayKey]['goodsDeliveryWholeFreeFl'] === 'y'){ ?>
                                                    조건에 따른 배송비 무료
                                                    <?php if(empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryWholeFreePrice']) === false){ ?>
                                                        <?php echo gd_currency_display($setDeliveryInfo[$valueArrayKey]['goodsDeliveryWholeFreePrice']); ?>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <?php if($setDeliveryInfo[$valueArrayKey]['goodsDeliveryCollectFl'] === 'later'){ ?>
                                                        <?php if(empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryCollectPrice']) === false){ ?>
                                                            <?php echo gd_currency_display($setDeliveryInfo[$valueArrayKey]['goodsDeliveryCollectPrice']); ?>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if(empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryPrice']) === true){ ?>
                                                            무료배송
                                                        <?php } else { ?>
                                                            <?php echo $setDeliveryInfo[$valueArrayKey]['goodsDeliveryPrice']; ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if (empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryMethodFlText']) === false) { ?>
                                                <br /><?php echo $setDeliveryInfo[$valueArrayKey]['goodsDeliveryMethodFlText']; ?>-
                                                <?php if ($setDeliveryInfo[$valueArrayKey]['goodsDeliveryCollectFl'] == 'later' && empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryCollectPrice']) === false) { ?>
                                                    착불
                                                <?php } else { ?>
                                                    선결제
                                                <?php } ?>
                                            <?php } ?>
                                        </span>
                                    </td>
                                <?php } ?>
                            <?php } else { ?>

                                <?php if ($value['sameGoodsDeliveryFl'] == 'y') { ?>
                                    <?php if ($value['equalGoodsNo'] === true) { ?>
                                        <td rowspan="<?php echo ($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsLineCnt'] * 2) + $setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['addGoodsLineCnt'];?>" class="ta-c">
                                            <span class="c-gray">
                                                <?=$setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryMethod']?><br/>
                                                <?php if($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['fixFl'] === 'free'){ ?>
                                                    무료배송
                                                <?php } else { ?>
                                                    <?php if($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryWholeFreeFl'] === 'y'){ ?>
                                                        조건에 따른 배송비 무료
                                                        <?php if(empty($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryWholeFreePrice']) === false){ ?>
                                                            <?php echo gd_currency_display($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryWholeFreePrice']); ?>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryCollectFl'] === 'later'){ ?>
                                                            <?php if(empty($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryCollectPrice']) === false){ ?>
                                                                <?php echo gd_currency_display($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryCollectPrice']); ?>
                                                            <?php } ?>
                                                        <?php } else { ?>
                                                            <?php if(empty($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryPrice']) === true){ ?>
                                                                무료배송
                                                            <?php } else { ?>
                                                                <?php echo $setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryPrice']; ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                                <?php if (empty($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryMethodFlText']) === false) { ?>
                                                    <br /><?php echo $setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryMethodFlText']; ?>-
                                                    <?php if ($setDeliveryInfo[$valueArrayKey][$value['goodsNo']]['goodsDeliveryCollectFl'] == 'later' && empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryCollectPrice']) === false) { ?>
                                                        착불
                                                    <?php } else { ?>
                                                        선결제
                                                    <?php } ?>
                                                <?php } ?>
                                            </span>
                                        </td>
                                    <?php } ?>
                                <?php } else { ?>
                                    <td rowspan="<?php if(empty($value['addGoods']) === false){ echo count($value['addGoods']) + 2; } else { echo 2; }?>" class="ta-c">
                                        <span class="c-gray">
                                            <?=$value['goodsDeliveryMethod']?><br/>
                                            <?php if($value['goodsDeliveryFixFl'] === 'free'){ ?>
                                                무료배송
                                            <?php } else { ?>
                                                <?php if($value['goodsDeliveryWholeFreeFl'] === 'y'){ ?>
                                                    조건에 따른 배송비 무료
                                                    <?php if(empty($value['price']['goodsDeliveryWholeFreePrice']) === false){ ?>
                                                        <br/><?php echo gd_currency_display($value['price']['goodsDeliveryWholeFreePrice']); ?>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <?php if($value['goodsDeliveryCollectFl'] === 'later'){ ?>
                                                        <?php if(empty($value['price']['goodsDeliveryCollectPrice']) === false){ ?>
                                                            <?=gd_currency_display($value['price']['goodsDeliveryCollectPrice'])?>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if(empty($value['price']['goodsDeliveryPrice']) === true){ ?>
                                                            무료배송
                                                        <?php } else { ?>
                                                            <?=gd_currency_display($value['price']['goodsDeliveryPrice'])?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if (empty($value['goodsDeliveryMethodFlText']) === false) { ?>
                                                <br /><?php echo $value['goodsDeliveryMethodFlText']; ?>-
                                                <?php if ($value['goodsDeliveryCollectFl'] == 'later' && empty($value['price']['goodsDeliveryCollectPrice']) === false) { ?>
                                                    착불
                                                <?php } else { ?>
                                                    선결제
                                                <?php } ?>
                                            <?php } ?>
                                        </span>
                                    </td>
                                <?php } ?>
                            <?php } ?>
                        </tr>

                        <?php if(empty($value['addGoods']) === false){ ?>
                            <?php for($i=0; $i<count($value['addGoods']); $i++){ ?>
                                <tr class="add<?php if((int)$i === 0 && count($value['addGoods']) != 1){ echo 'fir'; } else if ((count($value['addGoods'])-1) === $i && count($value['addGoods']) > 1) { echo 'last'; } else if(count($value['addGoods']) === 1) { echo 'single';}?>">
                                    <td class="gi">
                                        <img src="/admin/gd_share/img/self_order_member_cart/icon-add.png" alt="추가" class="add-item-icon va-m">
                                        <span><?=$value['addGoods'][$i]['addGoodsImage']?></span>
                                        <div>
                                            <?=$value['addGoods'][$i]['addGoodsNm']?>
                                            <?php if($value['addGoods'][$i]['optionNm']){ ?>
                                            <br>
                                            <dl>
                                                <dt>옵션 :</dt>
                                                <dd><?=$value['addGoods'][$i]['optionNm']?></dd>
                                            </dl>
                                            <?php } ?>
                                        </div>
                                    </td>

                                    <td class="ta-c count">
                                        <input type="text" name="addGoodsCnt[]" value="<?=$value['addGoods'][$i]['addGoodsCnt']?>" sno="<?=$value['sno']?>" title="수량" class="text" data-stock-fl="<?=$value['addGoods'][$i]['stockUseFl']?>" data-total-stock="<?=$value['addGoods'][$i]['stockCnt']?>" data-min-order-cnt="1" data-max-order-cnt="0" data-sales-unit="1" readonly="readonly" />
                                    </td>

                                    <td class="ta-c">
                                        <strong class="price"><?=gd_currency_display($value['addGoods'][$i]['addGoodsPrice']*$value['addGoods'][$i]['addGoodsCnt'])?></strong>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        <tr class="op">
                            <td colspan="3">
                                <div></div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>

            <?php if($cartScmCnt > 1){ ?>
            <tfoot>
            <tr>
                <td colspan="8" class="supplier-total">
                    <strong>[<?=$cartScmInfo[$key]['companyNm']?> 배송상품]</strong>
                    <span>총 <strong><?=number_format($cartScmGoodsCnt[$key])?></strong>개의 상품금액 <?=gd_currency_symbol()?><strong><?=gd_money_format($totalScmGoodsPrice[$key])?></strong><?=gd_currency_string()?></span>
                    <?php if($totalScmGoodsDcPrice[$key] > 0){ ?>
                    <span><img src="/admin/gd_share/img/self_order_member_cart/minus-s.png" alt="빼기" />상품할인 <?=gd_currency_symbol()?><strong><?=gd_money_format($totalScmGoodsDcPrice[$key])?></strong><?=gd_currency_string()?></span>
                    <?php } ?>

                    <?php if($totalScmGoodsDcPrice[$key] + $totalScmMemberOverlapDcPrice[$key] > 0){ ?>
                    <span><img src="/admin/gd_share/img/self_order_member_cart/minus-s.png" alt="빼기" />회원할인 <?=gd_currency_symbol()?><strong><?=gd_money_format($totalScmMemberDcPrice[$key]+$totalScmMemberOverlapDcPrice[$key])?></strong><?=gd_currency_string()?></span>
                    <?php } ?>

                    <?php if($totalScmCouponGoodsDcPrice[$key] > 0){ ?>
                    <span><img src="/admin/gd_share/img/self_order_member_cart/minus-s.png" alt="빼기" />쿠폰할인 <?=gd_currency_symbol()?><strong><?=gd_money_format($totalScmCouponGoodsDcPrice[$key])?></strong><?=gd_currency_string()?></span>
                    <?php } ?>
                    <span><img src="/admin/gd_share/img/self_order_member_cart/plus-s.png" alt="더하기" />배송비 <?=gd_currency_symbol()?><strong><?=gd_money_format($totalScmGoodsDeliveryCharge[$key])?></strong><?=gd_currency_string()?></span>
                    <span><img src="/admin/gd_share/img/self_order_member_cart/total-s.png" alt="합계" /><?=gd_currency_symbol()?><strong class="total"><?=gd_money_format($totalScmGoodsPrice[$key]-$totalScmGoodsDcPrice[$key]-$totalScmMemberDcPrice[$key]-$totalScmMemberOverlapDcPrice[$key]-$totalScmCouponGoodsDcPrice[$key]+$totalScmGoodsDeliveryCharge[$key])?></strong><?=gd_currency_string()?></span>
                </td>
            </tr>
            </tfoot>
            <?php } ?>
        </table>
    <?php } ?>

    <?php if(empty($cartCnt) === true){ ?>
    <table>
        <tr>
            <td colspan="8" class="no-data">
                장바구니에 담겨있는 상품이 없습니다.
            </td>
        </tr>
        </tbody>
    </table>
    <?php } ?>
    </div>

    </div>
    <!-- 상품내용 끝 -->

    <div class="mgt10">
        <span class="notice-danger">
            주문상품 리스트에서만 "쿠폰적용/변경/취소"가 가능합니다. <br />
            <span style="margin-left: 15px;">상품의 묶음수량 및 최소/최대 구매수량이 변경된 경우, 변경된 수량이 적용되어 주문상품으로 추가됩니다.</span>
        </span>
    </div>

    <!-- 총 금액 정보 -->
    <div class="price-box">
        <div>
            <p>
                <span class="detail">
                    총 <em id="totalGoodsCnt"><?=number_format($cartCnt)?></em> 개의 상품금액 <?=gd_currency_symbol()?><strong id="totalGoodsPrice"><?=gd_money_format($totalGoodsPrice)?></strong><?=gd_currency_string()?>

                </span>
                <?php if($totalGoodsDcPrice > 0){ ?>
                <span><img src="/admin/gd_share/img/self_order_member_cart/minus.png" alt="빼기" />상품할인 <?=gd_currency_symbol()?><strong id="totalGoodsDcPrice"><?=gd_money_format($totalGoodsDcPrice)?></strong><?=gd_currency_string()?></span>
                <?php } ?>
                <?php if($totalSumMemberDcPrice > 0){ ?>
                <span><img src="/admin/gd_share/img/self_order_member_cart/minus.png" alt="빼기" />회원할인 <?=gd_currency_symbol()?><strong id="totalMinusMember"><?=gd_money_format($totalSumMemberDcPrice)?></strong><?=gd_currency_string()?></span>
                <?php } ?>
                <?php if($totalCouponGoodsDcPrice > 0){ ?>
                <span><img src="/admin/gd_share/img/self_order_member_cart/minus.png" alt="빼기" />쿠폰할인 <?=gd_currency_symbol()?><strong id="totalCouponGoodsDcPrice"><?=gd_money_format($totalCouponGoodsDcPrice)?></strong><?=gd_currency_string()?></span>
                <?php } ?>
                <span id="deliveryCalculateNone"><img src="/admin/gd_share/img/self_order_member_cart/plus.png" alt="더하기" />배송비 <?=gd_currency_symbol()?><strong id="totalDeliveryCharge"><?=gd_money_format($totalDeliveryCharge)?></strong><?=gd_currency_string()?></span>
                <span class="total"><img src="/admin/gd_share/img/self_order_member_cart/total.png" alt="합계" /><?=gd_currency_symbol()?><strong id="totalSettlePrice"><?=gd_money_format($totalSettlePrice)?></strong><?=gd_currency_string()?></span>
            </p>
            <span id="deliveryChargeText"></span>
            <?php if($mileage['useFl'] === 'y'){ ?>
            <span>적립예정 <?=$mileage['name']?> : <span id="totalGoodsMileage"><?=gd_money_format($totalMileage)?></span> <?=$mileage['unit']?></span>
            <?php } ?>
        </div>
    </div>
    <!-- 총 금액 정보 -->

    <div class="order-btn-area">
        <button type="button" class="skinbtn point1 cart-orderselect"><em>선택 상품 주문</em></button>
        <button type="button" class="skinbtn point2 cart-orderall"><em>전체 상품 주문</em></button>
    </div>
    </form>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 쿠폰 유효성 체크 추가
        if ($('input[name=reSetMemberCouponApply]').val()) {
            dialog_confirm('사용기간이 만료된 쿠폰이 포함되어 있어 제외 후 진행합니다.', function (result) {
                if (result) {
                    location.reload();
                } else {
                    self.close();
                }
            });
        }

        //선택 상품 주문
        $(".cart-orderselect").click(function(){
            if($("input[name='cartSno[]']:checked").length < 1){
                alert("주문할 상품을 선택해 주세요.");
                return false;
            }
            var cartSnoArr = $('#frmCart input:checkbox[name="cartSno[]"]:checked');
            adjustCartOrder(cartSnoArr);
        });
        //전체 상품 주문
        $(".cart-orderall").click(function(){
            $("#frmCart input[type='checkbox']").prop("checked", true);
            var cartSnoArr = $("#frmCart input:checkbox[name='cartSno[]']");
            adjustCartOrder(cartSnoArr);
        });

        //상품쿠폰 적용
        $(document).on("click",".self-order-apply-coupon",function() {
            var params = {
                mode: 'coupon_apply',
                cartSno: $(this).attr('data-cartsno'),
                memNo : $("input[name='memNo']").val(),
                selfOrderMemberCartFl : 'y',
                layerType : 'popup_self_order_member_cart'
            };
            $.ajax({
                method: "POST",
                cache: false,
                url: "../order/layer_coupon_apply.php",
                data: params,
                success: function (data) {
                    data = '<div id="layerSelfOrderWriteCouponApplyGoods">' + data + '</div>';
                    var layerForm = data;
                    BootstrapDialog.show({
                        name: "layer_coupon_apply_goods",
                        size: BootstrapDialog.SIZE_WIDE,
                        title: '상품 쿠폰 적용',
                        message: $(layerForm),
                        closable: true
                    });
                },
                error: function (data) {
                    alert(data);
                }
            });
        });

        //상품쿠폰 취소
        $(document).on("click",".self-order-cancel-coupon",function() {
            var memNo = $("input[name='memNo']").val();
            var parameter = {
                'mode': 'order_write_goods_coupon_cancel',
                'cartSno': $(this).attr('data-cartsno'),
                'memNo' : memNo,
                'selfOrderMemberCartFl' : 'y'
            };
            $.post('./order_ps.php', parameter, function () {
                window.location.href='../order/popup_self_order_member_cart.php?memNo=' + memNo;
            });
        });

        $(document).on("click",".target-impossible-layer",function() {
            $(".nomal-layer").addClass('display-none');
            if ($(".nomal-layer").is(":hidden")) {
                $(this).next(".nomal-layer").removeClass('display-none');
            }
        });
    });

    <?php if(empty($cartCnt) === false){ ?>
    var totalDeliveryCharge = $('#totalDeliveryCharge').text().replace(/[^\d]+/g, '');
    // 선택한 상품에 따른 금액 계산
    $('#frmCart input:checkbox[name="cartSno[]"]').click(function () {
        // 체크박스 전체 선택상태에 따른 체크박스 변경처리
        var checkedCount = 0;
        var $eachCheckBox = $(this).closest('table').find('tbody input[name="cartSno[]"]:checkbox');
        $eachCheckBox.each(function(idx){
            if ($(this).prop('checked') === true) {
                checkedCount++;
            }
        });
        if ($eachCheckBox.length == checkedCount) {
            $(this).closest('table').find('thead > tr > th:first-child input[id*=allCheck-]').prop('checked', true);
            $(this).closest('table').find('thead > tr > th:first-child label[for*=allCheck-]').addClass('on');
        } else {
            $(this).closest('table').find('thead > tr > th:first-child input[id*=allCheck-]').prop('checked', false);
            $(this).closest('table').find('thead > tr > th:first-child label[for*=allCheck-]').removeClass('on');
        }

        window.setTimeout(function(){
            $.ajax({
                method: "POST",
                cache: false,
                url: "../order/order_ps.php",
                data: "mode=order_write_cart_select_calculation&memNo=" + $("input[name='memNo']").val() + "&useBundleGoods=1&" +$('#frmCart input:checkbox[name="cartSno[]"]:checked').serialize(),
                dataType: 'json'
            }).success(function (data) {
                $('#totalGoodsCnt').html(numeral(data.cartCnt).format('0,0'));
                $('#totalGoodsPrice').html(numeral(data.totalGoodsPrice).format());
                $('#totalGoodsDcPrice').html(numeral(data.totalGoodsDcPrice).format());
                $('#totalMinusMember').html(numeral(data.totalMemberDcPrice).format());
                $('#totalCouponGoodsDcPrice').html(numeral(data.totalCouponGoodsDcPrice).format());
                $('#totalSettlePrice').html(numeral(data.totalSettlePrice).format());
                $('#totalSettlePriceAdd').html(numeral(data.totalSettlePrice).format());
                $('#totalGoodsMileage').html(numeral(data.totalMileage).format());
                $('#deliveryChargeText').html('');
                $('#totalDeliveryCharge').html(numeral(data.totalDeliveryCharge).format());
            }).error(function (e) {
                alert(e);
            });
        }, 200);
    });
    <?php } ?>

    function adjustCartOrder(cartSnoArr){
        var orderPossible = true;
        $.each(cartSnoArr, function () {
            if($(this).attr('data-possible') == 'n'){
                orderPossible = false;
                return false;
            }
        });
        if(orderPossible !== true){
            alert("구매불가 상품이 포함되어 있습니다.");
            return false;
        }

        $.ajax({
            method: 'POST',
            cache: false,
            url: './order_ps.php',
            data: 'mode=order_write_member_cart&memNo=' + $("input[name='memNo']").val() + '&useBundleGoods=1&' + cartSnoArr.serialize(),
        }).success(function (returnData) {
            if(returnData) {
                if(returnData.code === 0){
                    alert(returnData.message);
                    return;
                }
                if (Object.keys(returnData).length > 0) {
                    var owMemberCartSnoData = [];
                    var owMemberRealCartSnoData = [];
                    var owMemberCartCouponNoData = [];
                    var idx = 0;
                    $.each(returnData, function (key, value) {
                        if (key && value.preRealCartSno && value.memberCouponNo) {
                            owMemberCartSnoData[idx] = key;
                            owMemberRealCartSnoData[idx] = value.preRealCartSno;
                            owMemberCartCouponNoData[idx] = value.memberCouponNo;
                        }
                        idx++;
                    });

                    opener.parent.setMemberCartSnoCookie(owMemberCartSnoData, owMemberRealCartSnoData, owMemberCartCouponNoData);
                }
            }

            opener.parent.set_goods('y');
            self.close();
        }).error(function (e) {
            alert(e.responseText);
        });
    }
    //-->
</script>
