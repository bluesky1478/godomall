<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
?>

<div class="self-order-member-cart">
    <!-- 상품내용 시작 -->
    <div class="self-order-contents">
        <div class="table1 type1">
            <?php foreach($list as $key => $valueArray){ ?>
                <?php if($cartScmCnt > 1 || $cartScmInfo[$key]['scmNo'] != 1) {?><h3 class="tit-supplier"><?=$cartScmInfo[$key]['companyNm']?> 배송상품</h3><?php } ?>
                <table>
                    <colgroup>
                        <col />
                        <col style="width:5%" />
                        <col style="width:5%" />
                        <col style="width:10%" />
                        <col style="width:10%" />
                        <col style="width:10%" />
                        <?php if($active['navSubTabs']['cart']) { ?>
                        <col style="width:15%" />
                        <?php } ?>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>상품/옵션 정보</th>
                        <th>수량</th>
                        <th>재고</th>
                        <th>상품금액</th>
                        <th>할인/적립</th>
                        <th>합계금액</th>
                        <?php if($active['navSubTabs']['cart']) { ?>
                        <th>배송비</th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($valueArray as $valueArrayKey => $valueArray2){ ?>
                        <?php foreach($valueArray2 as $valueArrayKey2 => $value){ ?>
                            <tr>
                                <!-- 상품/옵션 정보 -->
                                <td class="gi this-product">
                                    <span><a href="<?=URI_HOME?>goods/goods_view.php?goodsNo=<?=$value['goodsNo']?>" target="_blank"><?=$value['goodsImage']?></a></span>
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
                                                        <button type="button" class="target-impossible-layer sub_close" title="닫기">닫기</button>
                                                    </div>
                                                </div>
                                            </strong>
                                        <?php } ?>

                                        <?php if($value['duplicationGoods'] === 'y'){ ?>
                                            <strong class="caution-msg1">중복 상품</strong>
                                        <?php } ?>

                                        <a href="javascript:void(0);" onclick="goods_register_popup(<?=$value['goodsNo']?>);"><?=$value['goodsNm']?></a>

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
                                    <?=$value['goodsCnt']?>개
                                </td>
                                <!-- 수량 -->

                                <!-- 재고 -->
                                <td class="ta-c this-product">
                                    <?=($value['stockFl'] == 'n') ? '∞' : (is_numeric($value['stockCnt']) ? $value['stockCnt'].'개' : '-'); ?>
                                    <?=($value['optionSellFl'] == 'n' || $value['soldOutFl'] == 'y') ? '<br/>(수동품절)' : ''; ?>
                                </td>
                                <!-- 재고 -->

                                <!-- 상품금액 -->
                                <td class="ta-c this-product">
                                    <?php if(empty($value['goodsPriceString']) === false){ ?>
                                        <strong class="price"><?=$value['goodsPriceString']?></strong>
                                    <?php } else { ?>
                                        <?php if($active['navSubTabs']['cart']) { ?>
                                            <strong class="<?php if($value['timeSaleFl']){ echo "time-sale"; } else { echo "price"; } ?>"><?=gd_currency_display($value['price']['goodsPriceSum'] + $value['price']['optionPriceSum'] + $value['price']['optionTextPriceSum'])?></strong>
                                        <?php } else { ?>
                                            <strong><?=gd_global_currency_display($value['price']['goodsPriceSubtotal'])?></strong><?=gd_global_add_currency_display($value['price']['goodsPriceSubtotal'])?>
                                        <?php } ?>
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
                                                <dd>쿠폰 <strong>-<?=gd_currency_display($value['price']['couponGoodsDcPrice'])?></strong><strong class="caution-coupon"></strong></dd>
                                                <div class="nomal-layer coupon width-2xl display-none">
                                                    <div class="wrap">
                                                        <strong>쿠폰할인 내역</strong>
                                                        <div class="list">
                                                            <table cellspacing="0">
                                                                <colgroup>
                                                                    <col width="50%" />
                                                                    <col width="50%" />
                                                                </colgroup>
                                                                <thead>
                                                                <tr>
                                                                    <th>쿠폰</th>
                                                                    <th>사용조건</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td class="guide">
                                                                        <label>
                                                                            <b class="text-darkred"><?=$value['coupon'][$value['memberCouponNo']]['convertData']['couponBenefit']?></b>
                                                                            <?=$value['coupon'][$value['memberCouponNo']]['convertData']['couponKindType']?>
                                                                        </label>
                                                                        <em><?=$value['coupon'][$value['memberCouponNo']]['couponNm']?></em>
                                                                    </td>
                                                                    <td>
                                                                        <div class="msg">
                                                                            <?php if($value['coupon'][$value['memberCouponNo']]['convertData']['couponMaxBenefit']){ ?>
                                                                                <span>- <?=$value['coupon'][$value['memberCouponNo']]['convertData']['couponMaxBenefit']?></span>
                                                                            <?php } ?>
                                                                            <?php if($value['coupon'][$value['memberCouponNo']]['convertData']['couponMinOrderPrice']){ ?>
                                                                                <span>- <?=$value['coupon'][$value['memberCouponNo']]['convertData']['couponMinOrderPrice']?></span>
                                                                            <?php } ?>
                                                                            <span>- <?=$value['coupon'][$value['memberCouponNo']]['convertData']['couponApplyDuplicateType']?></span>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
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

                                <!-- 배송비 -->
                                <?php if($active['navSubTabs']['cart']) { ?>

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
                                                            <br/>상품수령 시 결제
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if(empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryPrice']) === true){ ?>
                                                            무료배송
                                                        <?php } else { ?>
                                                            <?php echo gd_currency_display($setDeliveryInfo[$valueArrayKey]['goodsDeliveryPrice']); ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                            <?php if(empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryMethodFlText']) == false) { ?>
                                                <br />
                                                (<?=$setDeliveryInfo[$valueArrayKey]['goodsDeliveryMethodFlText']?>)
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
                                                        <br/>상품수령 시 결제
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
                                        <?php if(empty($setDeliveryInfo[$valueArrayKey]['goodsDeliveryMethodFlText']) == false) { ?>
                                        <br />
                                        (<?=$setDeliveryInfo[$valueArrayKey]['goodsDeliveryMethodFlText']?>)
                                        <?php } ?>
                                    </span>
                                    </td>
                                <?php } ?>
                                <?php } ?>
                                <!-- 배송비 -->
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
                                            <?=$value['addGoods'][$i]['addGoodsCnt']?>개
                                        </td>

                                        <td class="ta-c">
                                            <?=($value['addGoods'][$i]['stockUseFl'] == 'n') ? '∞' : $value['addGoods'][$i]['stockCnt']; ?>
                                            <?=($value['addGoods'][$i]['soldOutFl'] == 'y') ? '<br/>(수동품절)' : ''; ?>
                                        </td>

                                        <td class="ta-c">
                                            <strong class="price"><?=gd_currency_display($value['addGoods'][$i]['addGoodsPrice']*$value['addGoods'][$i]['addGoodsCnt'])?></strong>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                            <tr class="op">
                                <td colspan="4">
                                    <div></div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    </tbody>

                    <?php if($active['navSubTabs']['cart']) { ?>
                    <?php if($cartScmCnt > 1){ ?>
                        <tfoot>
                        <tr>
                            <td colspan="8" class="supplier-total">
                                <strong class="flo-left">[<?=$cartScmInfo[$key]['companyNm']?> 배송상품]</strong>
                                <span>총 <strong><?=number_format($cartScmGoodsCnt[$key])?></strong>개의 상품금액 <?=gd_currency_symbol()?><br><strong><?=gd_money_format($totalScmGoodsPrice[$key])?></strong><?=gd_currency_string()?></span>
                                <?php if($totalScmGoodsDcPrice[$key] > 0){ ?>
                                    <img src="/admin/gd_share/img/self_order_member_cart/minus-s.png" alt="빼기" /><span>상품할인 <?=gd_currency_symbol()?><br><strong><?=gd_money_format($totalScmGoodsDcPrice[$key])?></strong><?=gd_currency_string()?></span>
                                <?php } ?>

                                <?php if($totalScmMemberDcPrice[$key] + $totalScmMemberOverlapDcPrice[$key] > 0){ ?>
                                    <img src="/admin/gd_share/img/self_order_member_cart/minus-s.png" alt="빼기" /><span>회원할인 <?=gd_currency_symbol()?><br><strong><?=gd_money_format($totalScmMemberDcPrice[$key]+$totalScmMemberOverlapDcPrice[$key])?></strong><?=gd_currency_string()?></span>
                                <?php } ?>

                                <?php if($totalScmCouponGoodsDcPrice[$key] > 0){ ?>
                                    <img src="/admin/gd_share/img/self_order_member_cart/minus-s.png" alt="빼기" /><span>쿠폰할인 <?=gd_currency_symbol()?><br><strong><?=gd_money_format($totalScmCouponGoodsDcPrice[$key])?></strong><?=gd_currency_string()?></span>
                                <?php } ?>
                                <img src="/admin/gd_share/img/self_order_member_cart/plus-s.png" alt="더하기" /><span>배송비 <?=gd_currency_symbol()?><br><strong><?=gd_money_format($totalScmGoodsDeliveryCharge[$key])?></strong><?=gd_currency_string()?></span>
                                <img src="/admin/gd_share/img/self_order_member_cart/total-s.png" alt="합계" /><span>합계 <?=gd_currency_symbol()?><br><strong class="total"><?=gd_money_format($totalScmGoodsPrice[$key]-$totalScmGoodsDcPrice[$key]-$totalScmMemberDcPrice[$key]-$totalScmMemberOverlapDcPrice[$key]-$totalScmCouponGoodsDcPrice[$key]+$totalScmGoodsDeliveryCharge[$key])?></strong><?=gd_currency_string()?></span>
                            </td>
                        </tr>
                        </tfoot>
                    <?php } ?>
                    <?php } ?>
                </table>
            <?php } ?>

            <?php if(empty($cnt) === true){ ?>
                <table>
                    <thead>
                    <tr>
                        <th>상품/옵션 정보</th>
                        <th>수량</th>
                        <th>재고</th>
                        <th>상품금액</th>
                        <th>할인/적립</th>
                        <th>합계금액</th>
                        <?php if($active['navSubTabs']['cart']) { ?>
                        <th>배송비</th>
                        <?php } ?>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="<?=$colspan?>" class="no-data">
                            등록된 상품이 없습니다.
                        </td>
                    </tr>
                    </tbody>
                </table>
            <?php } ?>
        </div>

    </div>
    <!-- 상품내용 끝 -->

    <?php if($active['navSubTabs']['cart']) { ?>
    <!-- 총 금액 정보 -->
    <div class="price-box">
        <div>
            <p>
                <span class="detail">
                    총 <em id="totalGoodsCnt"><?=number_format($cartCnt)?></em> 개의 상품금액<br> <?=gd_currency_symbol()?><strong id="totalGoodsPrice"><?=gd_money_format($totalGoodsPrice)?></strong><?=gd_currency_string()?>

                </span>
                <?php if($totalGoodsDcPrice > 0){ ?>
                    <img src="/admin/gd_share/img/self_order_member_cart/minus.png" alt="빼기" /><span>상품할인<br> <?=gd_currency_symbol()?><strong id="totalGoodsDcPrice"><?=gd_money_format($totalGoodsDcPrice)?></strong><?=gd_currency_string()?></span>
                <?php } ?>
                <?php if($totalSumMemberDcPrice > 0){ ?>
                    <img src="/admin/gd_share/img/self_order_member_cart/minus.png" alt="빼기" /><span>회원할인<br> <?=gd_currency_symbol()?><strong id="totalMinusMember"><?=gd_money_format($totalSumMemberDcPrice)?></strong><?=gd_currency_string()?></span>
                <?php } ?>
                <?php if($totalCouponGoodsDcPrice > 0){ ?>
                    <img src="/admin/gd_share/img/self_order_member_cart/minus.png" alt="빼기" /><span>쿠폰할인<br> <?=gd_currency_symbol()?><strong id="totalCouponGoodsDcPrice"><?=gd_money_format($totalCouponGoodsDcPrice)?></strong><?=gd_currency_string()?></span>
                <?php } ?>
                <img src="/admin/gd_share/img/self_order_member_cart/plus.png" alt="더하기" /><span id="deliveryCalculateNone">배송비<br> <?=gd_currency_symbol()?><strong id="totalDeliveryCharge"><?=gd_money_format($totalDeliveryCharge)?></strong><?=gd_currency_string()?></span>
                <img src="/admin/gd_share/img/self_order_member_cart/total.png" alt="합계" /><span class="total">합계 <br><?=gd_currency_symbol()?><strong id="totalSettlePrice"><?=gd_money_format($totalSettlePrice)?></strong><?=gd_currency_string()?></span>
            </p>
            <span id="deliveryChargeText"></span>
            <?php if($mileage['useFl'] === 'y'){ ?>
                <span>적립예정 <?=$mileage['name']?> : <span id="totalGoodsMileage"><?=gd_money_format($totalMileage)?></span> <?=$mileage['unit']?></span>
            <?php } ?>
        </div>
    </div>
    <!-- 총 금액 정보 -->
    <?php } ?>
</div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var cartCnt = '<?=number_format($cartCnt)?>';
        var wishCnt = '<?=number_format($wishCnt)?>';
        $("#cartCnt").text(cartCnt);
        $("#wishCnt").text(wishCnt);
        $(document).on("click",".target-impossible-layer",function() {
            $(".nomal-layer").addClass('display-none');
            if ($(".nomal-layer").is(":hidden")) {
                $(this).next(".nomal-layer").removeClass('display-none');
            }
        });

        $('.caution-coupon').hover(function(){
            $(".coupon").addClass('display-none');
            $(this).parent().next(".coupon").removeClass('display-none');
        }, function(){
            $(".coupon").addClass('display-none');
        });
    });
    //-->
</script>
