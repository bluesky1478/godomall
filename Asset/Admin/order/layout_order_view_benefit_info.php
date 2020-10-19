<?php
/**
 * 주문상세 - 쿠폰/할인/혜택 정보
 *
 * @author <tomi@godo.co.kr>
 */
if(empty($orderAllData) == false) { // 환불 상세 페이지는 다른 변수로 setData
    $data = $orderAllData;
}
$arrMemberPolicyFixedOrderType = array('option' => '옵션별', 'goods' => '상품별', 'order' => '주문별', 'brand' => '브랜드별');
$arrDiscountGroup = array('member' => '| 회원전용 |', 'group' => '| 특정회원등급 |');
$arrNewGoodsReg = array('regDt' => '등록일', 'modDt' => '수정일');
$arrNewGoodsDate = array('day' => '일', 'hour' => '시간');
$goodsDcFieldArray = ['goodsDcPrice', 'couponGoodsDcPrice', 'memberDcPrice', 'memberOverlapDcPrice', 'myappDcPrice']; // 상품할인 필드배열
$addFieldArray = ['goodsMileage', 'memberMileage', 'divisionCouponOrderMileage']; // 적립필드배열
// 총 상품 금액 할인 혜택 범위금액
$benefitTotalGoodsDcPrice = $data['orderViewBenefitPrice']['totalGoodsDc'];
// 총 상품 금액 할인 혜택 범위금액 + 운영자 할인
if($data['totalEnuriDcPrice'] != 0) {
    $benefitTotalGoodsDcPrice = $benefitTotalGoodsDcPrice + $data['totalEnuriDcPrice'];
}
// 총 상품 금액 할인 혜택 범위금액 + 운영자 할인 + 마이앱 할인
if($data['totalMyappDcPrice'] != 0) {
    $benefitTotalGoodsDcPrice = $benefitTotalGoodsDcPrice + $data['totalMyappDcPrice'];
}
// 총 주문 할인 혜택 범위금액
$benefitTotalOrderDcPrice = $data['orderViewBenefitPrice']['totalOrderDc'];
// 총 배송비 할인 혜택 범위금액
$benefitTotalDeliveryDcPrice = $data['orderViewBenefitPrice']['totalDeliveryDc'];
// 총 마일리지 적립 혜택 범위금액
$benefitTotalMileage = $data['orderViewBenefitPrice']['totalMileageAdd'];

$dotHtmlTag = '<div style="border-top: 1px dotted #A9A9A9;margin:3px;"></div>';

?>
<table class="table table-cols table-toggle table-benefit-info">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <?php
    // 총 상품할인금액, 총 주문 할인금액, 총 배송비할인금액, 총 적립금액, 총 운영자할인할증 이 있을 경우
    if($benefitTotalGoodsDcPrice > 0 || $benefitTotalOrderDcPrice > 0 || $benefitTotalDeliveryDcPrice > 0 || $benefitTotalMileage > 0 || $data['totalEnuriDcPrice'] != 0) {
        ?>
        <tr>
            <th>
                <button type="button" class="btn btn-xs btn-link js-pay-toggle" data-number="<?= $benefitTotalGoodsDcPrice ?>" data-target="toggleBenefitGoodsDc">보기</button>
                총 상품 할인금액
            </th>
            <th class="th">
                <?php
                $plusMinusText = '(-)';
                $statusApplyCancelFontColor = 'text-danger';
                $benefitTotalGoodsDcPrice = $data['orderViewBenefitPrice']['goodsDc'];
                if($data['statusMode'] == 'c') $benefitTotalGoodsDcPrice = 0;
                if($benefitTotalGoodsDcPrice < 0 && $benefitTotalGoodsDcPrice != 0) { // 할증인 경우
                    $benefitTotalGoodsDcPrice = str_replace('-', '', $benefitTotalGoodsDcPrice);
                    $statusApplyCancelFontColor = 'text-primary';
                    $plusMinusText = '(+)';
                }
                ?>
                <div class="<?=$statusApplyCancelFontColor;?>" style="text-align:left;">
                    <?=$plusMinusText;?> <?= gd_global_order_currency_display($benefitTotalGoodsDcPrice, $data['exchangeRate'], $data['currencyPolicy']); ?>
                </div>
            </th>
        </tr>
        <tr id="toggleBenefitGoodsDc">
            <th style="display: none;"></th>
            <td class="th" style="display: none;">
                <ul class="list">
                    <?php
                    $myappDiscount = '모바일앱 추가 혜택 : ';
                    if ($data['totalMyappDcPrice'] > 0 && empty($data['myappPolicy']) === false && $myappPolicy['isUsing'] == true) {
                        $myappDiscount .= $myappPolicy['benefit']['discountValue'];
                        $myappDiscount .= $myappPolicy['benefit']['discountType'] == 'won' ? gd_currency_string() : '%';
                        $myappDiscount .= '할인 - ';
                    }
                    foreach($data['orderViewBenefitGoods'] as $key => $val) {
                        // 주문상품 주문상태
                        if($val['orderCancelFl'] == true) {
                            $statusApplyCancelString = "<적용취소> ";
                            $statusApplyCancelCss = " style='color:#AEAEAE;'";
                            $statusApplyCancelFontColor = '';
                        } else {
                            $statusApplyCancelString = $statusApplyCancelCss = "";
                            $statusApplyCancelFontColor = 'text-danger';
                        }
                        if($data['totalGoodsDcPrice'] > 0 && $val['goodsDcPrice'] > 0) { // 상품할인
                            $benefitNm = '개별설정';
                            if(empty($val['goodsDiscountInfo']['benefitNm']) == false && $val['goodsDiscountInfo'] != null) {
                                $benefitNm = $val['goodsDiscountInfo']['benefitNm'];
                            }
                            $divisionText = ''; // 기간할인 구분자 초기화
                            if(!$arrDiscountGroup[$val['goodsDiscountInfo']['goodsDiscountGroup']]) $divisionText = " |" ;  // 배열에 값이 없을 경우 기간할인 구분자 삽입
                            if($val['goodsDiscountInfo']['goodsDiscountUnit'] == 'price') $goodsDiscountPricePrint = gd_currency_symbol() . gd_money_format($val['goodsDiscountInfo']['goodsDiscount']) . gd_currency_string();
                            else $goodsDiscountPricePrint = $val['goodsDiscountInfo']['goodsDiscount'] . '%';
                            if($val['goodsDiscountInfo']['benefitUseType'] == 'nonLimit') { // 제한 없음
                                $benefitPeriod = '';
                            } else if($val['goodsDiscountInfo']['benefitUseType'] == 'newGoodsDiscount') { // 등록일 기준
                                $benefitPeriod = $divisionText . ' 상품' . $arrNewGoodsReg[$val['goodsDiscountInfo']['newGoodsRegFl']] . '부터 ' . $val['goodsDiscountInfo']['newGoodsDate'] . $arrNewGoodsDate[$val['goodsDiscountInfo']['newGoodsDateFl']] . '까지';
                            } else { // 기간 제한
                                $benefitPeriod = $divisionText . " " . gd_date_format("Y-m-d H:i", $val['goodsDiscountInfo']['periodDiscountStart']) . ' ~ ' . gd_date_format("Y-m-d H:i", $val['goodsDiscountInfo']['periodDiscountEnd']);
                            }
                            if(empty($val['goodsDiscountInfo']) === false || $val['goodsDiscountInfo'] != null) {
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $benefitNm; ?> : <?= $goodsDiscountPricePrint; ?>
                                    할인 <?= $arrDiscountGroup[$val['goodsDiscountInfo']['goodsDiscountGroup']]; ?><?= $benefitPeriod; ?> - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['goodsDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                                </li>
                                <?php
                            } else {
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] 상품 할인 - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['goodsDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                                </li>
                                <?php
                            }
                        }
                        if($data['totalCouponGoodsDcPrice'] > 0 && $val['couponGoodsDcPrice'] > 0) { // 상품적용 쿠폰
                            $couponGoodsAddOverCheckArr = []; // 상품쿠폰 중복체크 배열
                            $couponGoodsDcPrintArr = []; // 상품쿠폰 병합
                            foreach($val['orderGoodsCouponData'] as $couponKey => $couponVal) {
                                if($couponVal['couponKindType'] != 'sale' || $couponVal['couponUseType'] != 'product') continue;
                                if($couponVal['orderCd'] != 0) { // 상품쿠폰의 경우 중복 체크를 위해 조건추가
                                    if(in_array($couponVal['couponNm'], $couponGoodsAddOverCheckArr) == true) continue;
                                    $couponGoodsAddOverCheckArr[$couponKey] = $couponVal['couponNm'];
                                }
                                if($couponVal['couponBenefitType'] == 'fix') { $couponBenefitType = gd_currency_default(); } else { $couponBenefitType =  '%'; }
                                if($couponVal['couponUseType'] == 'product') { // 상품적용 쿠폰인 경우
                                    $couponGoodsDcPrintArr[] =  $couponVal['couponNm'] . " : " . gd_money_format($couponVal['couponBenefit']) . $couponBenefitType . " 할인";
                                }
                                ?>
                                <?php
                            }
                            ?>
                            <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= implode('<span class="bold" style="font-size: 13px;"> + </span>', $couponGoodsDcPrintArr);?>
                                : <?= gd_money_format($couponVal['couponBenefit']); ?><?php if($couponVal['couponBenefitType'] == 'fix') { ?><?= gd_currency_default(); ?><?php } else { ?>%<?php } ?>
                                할인 - (<span class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['couponGoodsDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                            </li>
                            <?php
                        }
                        if(($data['totalMemberDcPrice'] > 0 && $val['memberDcPrice'] > 0) && ($orderMemberPolicy['fixedOrderTypeDc'] == 'goods' || $orderMemberPolicy['fixedOrderTypeDc'] == 'option' || $orderMemberPolicy['fixedOrderTypeDc'] == 'brand' || empty($orderMemberPolicy['fixedOrderTypeDc']) === true)) { // 회원 등급 추가 할인
                            if ($orderMemberPolicy['fixedOrderTypeDc'] == 'brand') {
                                //회원등급 > 브랜드별 추가할인 상품 브랜드 정보
                                if (in_array($val['brandCd'], $orderMemberPolicy['dcBrandInfo']['cateCd'])) {
                                    $goodsBrandInfo[$val['goodsNo']][$val['brandCd']] = $val['brandCd'];
                                } else {
                                    if ($val['brandCd']) {
                                        $goodsBrandInfo[$val['goodsNo']]['allBrand'] = $val['brandCd'];
                                    } else {
                                        $goodsBrandInfo[$val['goodsNo']]['noBrand'] = $val['brandCd'];
                                    }
                                }

                                // 무통장결제 중복 할인 설정 체크에 따른 할인율
                                foreach ($goodsBrandInfo[$val['goodsNo']] as $gKey => $gVal) {
                                    foreach ($orderMemberPolicy['dcBrandInfo']['cateCd'] AS $mKey => $mVal) {
                                        if ($gKey == $mVal) {
                                            $orderMemberPolicy['dcPercent'] = ($orderMemberPolicy['dcBrandInfo']['goodsDiscount'][$mKey]);
                                        }
                                    }
                                }
                            }
                            if(empty($orderMemberPolicy) == false) { // 주문당시회원등급별 정책에 값이 없을 경우
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $orderMemberPolicy['groupNm']; ?>
                                    : <?= $arrMemberPolicyFixedOrderType[$orderMemberPolicy['fixedOrderTypeDc']]; ?> 구매금액 <?= gd_currency_display($orderMemberPolicy['dcLine']); ?> 이상
                                    | <?= $orderMemberPolicy['dcPercent'] ?>% 추가 할인 - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['memberDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                                </li>
                                <?php
                            } else {
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] 회원 추가 할인 - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['memberDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                                </li>

                                <?php
                            }
                        }
                        if($data['totalMemberOverlapDcPrice'] > 0 && $val['memberOverlapDcPrice'] > 0 && ($orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'goods' || $orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'option' || empty($orderMemberPolicy['fixedOrderTypeOverlapDc']) === true)) { // 회원 등급 중복 할인
                            if(empty($orderMemberPolicy) == false) { // 주문당시회원등급별 정책에 값이 없을 경우
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $orderMemberPolicy['groupNm']; ?>
                                    : <?= $arrMemberPolicyFixedOrderType[$orderMemberPolicy['fixedOrderTypeOverlapDc']]; ?> 구매금액 <?= gd_currency_display($orderMemberPolicy['overlapDcLine']); ?> 이상
                                    | <?= $orderMemberPolicy['overlapDcPercent']; ?>% 중복 할인 - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['memberOverlapDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                                </li>
                                <?php
                            } else {
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] 회원 중복 할인 - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['memberOverlapDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                                </li>
                                <?php

                            }
                        }

                        if($val['enuri'] !=0 ) { // 운영자 추가 할인
                            if($val['enuri'] < 0 ) { // 할증
                                $val['enuri'] = str_replace('-', '', $val['enuri']);
                                if($val['orderCancelFl'] == false) {
                                    $statusApplyCancelFontColor = 'text-primary';
                                }
                                $statusApplyUnitText = '할증';
                            } else if($val['enuri'] > 0) { // 할인
                                $statusApplyUnitText = '할인';
                            }
                            ?>
                            <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] 운영자 추가 <?=$statusApplyUnitText;?> - (<span
                                        class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['enuri']); ?></span><?=gd_currency_default();?> <?=$statusApplyUnitText;?>)
                            </li>
                            <?php
                        }
                        // 마이앱 추가 혜택
                        if ($data['totalMyappDcPrice'] > 0 && $val['myappDcPrice'] > 0) { ?>
                            <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>">
                                <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $myappDiscount; ?>(<span class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['myappDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </td>
        </tr>
        <tr>
            <th>
                <button type="button" class="btn btn-sm btn-link js-pay-toggle"
                        data-number="<?= $benefitTotalOrderDcPrice ?>" data-target="toggleBenefitOrderDc">보기
                </button>
                총 주문 할인금액
            </th>
            <th class="th">
                <?php
                $plusMinusText = '(-)';
                $statusApplyCancelFontColor = 'text-danger';
                $benefitTotalOrderDcPrice = $data['orderViewBenefitPrice']['orderDc'];
                if($data['statusMode'] == 'c') $benefitTotalOrderDcPrice = 0;
                if($benefitTotalOrderDcPrice < 0 && $benefitTotalOrderDcPrice != 0) { // 할증인 경우
                    $benefitTotalOrderDcPrice = str_replace('-', '', $benefitTotalOrderDcPrice);
                    $statusApplyCancelFontColor = 'text-primary';
                    $plusMinusText = '(+)';
                }
                ?>
                <div class="<?=$statusApplyCancelFontColor;?>" style="text-align:left;">
                    <?=$plusMinusText;?> <?= gd_global_order_currency_display($benefitTotalOrderDcPrice, $data['exchangeRate'], $data['currencyPolicy']); ?>
                </div>
            </th>
        </tr>
        <tr id="toggleBenefitOrderDc">
            <th style="display: none;"></th>
            <td class="th" style="display: none;">
                <ul class="list">
                    <?php
                    // 주문상태
                    if($data['statusMode'] == 'c' || $data['statusMode'] == 'e' || $data['statusMode'] == 'r') {
                        $statusApplyCancelString = "<적용취소> ";
                        $statusApplyCancelCss = " style='color:#AEAEAE;'";
                        $statusApplyCancelFontColor = '';
                    } else {
                        $statusApplyCancelString = $statusApplyCancelCss = "";
                        $statusApplyCancelFontColor = 'text-danger';
                    }

                    foreach($data['orderViewBenefitGoods'] as $key => $val) {
                        // 주문상품 주문상태
                        if($val['orderCancelFl'] == true) {
                            $statusApplyCancelString = "<적용취소> ";
                            $statusApplyCancelCss = " style='color:#AEAEAE;'";
                            $statusApplyCancelFontColor = '';
                        } else {
                            $statusApplyCancelString = $statusApplyCancelCss = "";
                            $statusApplyCancelFontColor = 'text-danger';
                        }
                        if($data['totalCouponOrderDcPrice'] > 0) { // 주문적용 쿠폰
                            $couponAddOverCheckArr = []; // 주문쿠폰 중복체크 배열
                            $couponOrderDcPrintArr = []; // 주문쿠폰 병합
                            foreach($data['orderCouponBenefitData']['orderCouponData'] as $couponKey => $couponVal) {
                                if($couponVal['couponKindType'] != 'sale' || $couponVal['couponUseType'] != 'order') continue;
                                if($couponVal['orderCd'] == 0) { // 주문쿠폰의 경우 중복 체크를 위해 조건추가
                                    if(in_array($couponVal['couponNm'], $couponAddOverCheckArr) == true) continue;
                                    $couponAddOverCheckArr[$couponKey] = $couponVal['couponNm'];
                                }
                                if($couponVal['couponBenefitType'] == 'fix') { $couponBenefitType = gd_currency_default(); } else { $couponBenefitType =  '%'; }
                                if($couponVal['couponUseType'] == 'order') { // 주문적용 쿠폰인 경우
                                    $couponOrderDcPrintArr[] =  $couponVal['couponNm'] . " : " . gd_money_format($couponVal['couponBenefit']) . $couponBenefitType . " 할인";
                                }
                            }
                            if($val['divisionCouponOrderDcPrice'] > 0 ) {
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= implode('<span class="bold" style="font-size: 13px;"> + </span>', $couponOrderDcPrintArr);?>
                                    - (<span class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['divisionCouponOrderDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                                </li>
                                <?php
                            }
                        }
                        if(($data['totalMemberDcPrice'] > 0 && $val['memberDcPrice'] > 0) && ($orderMemberPolicy['fixedOrderTypeDc'] == 'order')) { // 회원 등급 추가 할인
                            ?>
                            <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $orderMemberPolicy['groupNm']; ?>
                                : <?= $arrMemberPolicyFixedOrderType[$orderMemberPolicy['fixedOrderTypeDc']]; ?> 구매금액 <?= gd_currency_display($orderMemberPolicy['dcLine']); ?> 이상 | <?= $orderMemberPolicy['dcPercent']; ?>% 추가할인 -
                                (<span class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['memberDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                            </li>
                            <?php
                        }
                        if($data['totalMemberOverlapDcPrice'] > 0 && $val['memberOverlapDcPrice'] > 0 && ($orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'order')) { // 회원 등급 중복
                            ?>
                            <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $orderMemberPolicy['groupNm']; ?>
                                : <?= $arrMemberPolicyFixedOrderType[$orderMemberPolicy['fixedOrderTypeOverlapDc']]; ?> 구매금액 <?= gd_currency_display($orderMemberPolicy['overlapDcLine']); ?> 이상
                                | <?= $orderMemberPolicy['overlapDcPercent']; ?>% 중복 할인 - (<span
                                        class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['memberOverlapDcPrice']); ?></span><?=gd_currency_default();?> 할인)
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
            </td>
        </tr>
        <tr>
            <th>
                <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?= $benefitTotalDeliveryDcPrice ?>"
                        data-target="toggleBenefitDeliveryDc">보기
                </button>
                총 배송비 할인금액
            </th>
            <th class="th">
                <?php
                $plusMinusText = '(-)';
                $statusApplyCancelFontColor = 'text-danger';
                if($data['statusMode'] == 'c') $benefitTotalDeliveryDcPrice = 0;
                if($benefitTotalDeliveryDcPrice < 0 && $benefitTotalDeliveryDcPrice != 0) { // 할증인 경우
                    $benefitTotalDeliveryDcPrice = str_replace('-', '', $benefitTotalDeliveryDcPrice);
                    $statusApplyCancelFontColor = 'text-primary';
                    $plusMinusText = '(+)';
                }
                ?>
                <div class="<?=$statusApplyCancelFontColor;?>" style="text-align:left;">
                    <?=$plusMinusText;?>  <?= gd_global_order_currency_display($benefitTotalDeliveryDcPrice, $data['exchangeRate'], $data['currencyPolicy']); ?>
                </div>
            </th>
        </tr>
        <tr id="toggleBenefitDeliveryDc">
            <th style="display: none;"></th>
            <td class="th" style="display: none;">
                <ul class="list">
                    <?php
                    if($data['statusMode'] == 'c' || $data['statusMode'] == 'e' || $data['statusMode'] == 'r') {
                        $statusApplyCancelString = "<적용취소> ";
                        $statusApplyCancelCss = " style='color:#AEAEAE;'";
                        $statusApplyCancelFontColor = '';
                    } else {
                        $statusApplyCancelString = $statusApplyCancelCss = "";
                        $statusApplyCancelFontColor = 'text-danger';
                    }
                    if($data['totalCouponDeliveryDcPrice'] > 0) { // 배송비적용 쿠폰
                        foreach($data['orderCouponBenefitData']['orderCouponData'] as $couponKey => $couponVal) {
                            if($couponVal['couponKindType'] != 'delivery' && $couponVal['couponUseType'] != 'delivery') continue;
                            ?>
                            <li <?= $statusApplyCancelCss; ?>"> <?= $statusApplyCancelString; ?> <?= $couponVal['couponNm']; ?>
                                : <?= gd_money_format($couponVal['couponBenefit']); ?><?php if($couponVal['couponBenefitType'] == 'fix') { ?><?= gd_currency_default(); ?><?php } else { ?>%<?php } ?>
                                할인
                                - (<span class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($couponVal['couponPrice']); ?></span><?=gd_currency_default();?>)
                            </li>
                            <?php
                        }
                    }
                    if($data['totalMemberDeliveryDcPrice'] > 0) { // 회원 등급 배송비 할인
                        ?>
                        <li <?= $statusApplyCancelCss; ?>"> <?= $statusApplyCancelString; ?> <?= $orderMemberPolicy['groupNm']; ?> : 배송비 무료 - (<span
                                    class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_currency_display($data['totalMemberDeliveryDcPrice']); ?></span>)
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </td>
        </tr>
        <tr>
            <th>
                <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?= $benefitTotalMileage ?>" data-target="toggleBenefitAdd">보기</button>
                총 적립금액
            </th>
            <th class="th">
                <?php
                $plusMinusText = '(+)';
                $statusApplyCancelFontColor = 'text-primary';
                $benefitTotalMileage = $data['orderViewBenefitPrice']['mileageAdd'];
                if($data['statusMode'] == 'c') $benefitTotalMileage = 0;
                if($benefitTotalDeliveryDcPrice < 0 && $benefitTotalDeliveryDcPrice != 0) { // 할증인 경우
                    $benefitTotalDeliveryDcPrice = str_replace('-', '', $benefitTotalDeliveryDcPrice);
                    $statusApplyCancelFontColor = 'text-danger';
                    $plusMinusText = '(-)';
                }
                ?>
                <div class="<?=$statusApplyCancelFontColor;?>" style="text-align:left;">
                    <?=$plusMinusText;?> <?= number_format($benefitTotalMileage); ?><?= $mileageUse['unit'] ?>
                </div>
            </th>
        </tr>
        <tr id="toggleBenefitAdd">
            <th style="display: none;"></th>
            <td class="th" style="display: none;">
                <ul class="list">
                    <?php
                    foreach($data['orderViewBenefitGoods'] as $key => $val) {
                        // 주문상품 주문상태
                        if($val['orderCancelFl'] == true) {
                            $statusApplyCancelString = "<적용취소> ";
                            $statusApplyCancelCss = " style='color:#AEAEAE;'";
                            $statusApplyCancelFontColor = '';
                            foreach($addFieldArray as $addKey => $addVal) {
                                // 취소 상품에 적립 있을 경우
                                if($data['orderViewBenefitOriginalGoods'][$val['orderCd']][$addVal] > 0) {
                                    $val[$addVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$addVal];
                                }
                            }
                        } else {
                            $statusApplyCancelString = $statusApplyCancelCss = "";
                            $statusApplyCancelFontColor = 'text-primary';
                        }
                        if($data['totalGoodsMileage'] > 0 && $val['goodsMileage'] > 0) { // 상품적립
                            if($val['goodsMileageAddInfo']['mileageFl'] == 'c') { // 통합 적립설정
                                if($orderMileagePolicy['give']['giveType'] == 'price') { // 가격별
                                    $mileAgeAddText = "구매금액의 " . $orderMileagePolicy['give']['goods'] . "%를 " . $orderMileagePolicy['basic']['name'] . "로 적립";
                                } else if($orderMileagePolicy['give']['giveType'] == 'priceUnit') { // 금액별
                                    $mileAgeAddText = "구매금액으로 " . number_format($orderMileagePolicy['give']['goodsPriceUnit']) . $orderMileagePolicy['basic']['unit'] . " 단위로 " . number_format($orderMileagePolicy['give']['goodsMileage']) . " ". $orderMileagePolicy['basic']['name'] . "로 적립";
                                } else if($orderMileagePolicy['give']['giveType'] == 'cntUnit') { // 갯수별
                                    $mileAgeAddText = "구매금액과 상관없이 구매상품 1개 단위로 " . number_format($orderMileagePolicy['give']['cntMileage']) . " " . $orderMileagePolicy['basic']['name'] . "로 적립";
                                }
                            } else { // 상품 개별 적립설정
                                $mileAgeAddText = "구매수량별 ";
                                if($val['goodsMileageAddInfo']['mileageGoodsUnit'] == 'percent') { // 정률 할인
                                    $mileAgeAddText = $mileAgeAddText. $val['goodsMileageAddInfo']['mileageGoods'] . "% 를 " . $orderMileagePolicy['basic']['name'] . "로 적립";
                                } else { // 정액 할인
                                    $mileAgeAddText = $mileAgeAddText . gd_money_format($val['goodsMileageAddInfo']['mileageGoods'], false) . $orderMileagePolicy['basic']['unit'] . "을 ". $orderMileagePolicy['basic']['name'] . "로 적립";
                                }
                            }
                            if(empty($orderMileagePolicy) === true || empty($val['goodsMileageAddInfo']) === true || $val['goodsMileageAddInfo'] == null) { // 기존 데이터 레거시 보장
                                $mileAgeAddText = "상품 적립";
                            }
                            ?>
                            <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $mileAgeAddText; ?> - (<span
                                        class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['goodsMileage']); ?></span><?= $mileageUse['unit'] ?>)
                            </li>
                            <?php
                        }
                        $couponAddOverCheckArr = []; // 주문쿠폰 중복체크 배열
                        $couponOrderAddPrintArr = []; // 적립쿠폰 주문 병합
                        $couponProductAddPrintArr = []; // 적립쿠폰 상품 병합
                        if(($data['totalCouponOrderMileage'] > 0 || $data['totalCouponGoodsMileage'] > 0) && ($val['divisionCouponOrderMileage'] > 0 || $val['couponGoodsMileage'] > 0)) { // 적립 쿠폰
                            foreach($val['orderGoodsCouponData'] as $couponKey => $couponVal) {
                                if($couponVal['couponKindType'] != 'add') continue;
                                if($couponVal['orderCd'] == 0) { // 주문쿠폰의 경우 중복호출이 있어 조건 추가
                                    if(in_array($couponVal['couponNm'], $couponAddOverCheckArr) == true) continue;
                                    $couponAddOverCheckArr[$couponKey] = $couponVal['couponNm'];
                                }
                                if($couponVal['couponBenefitType'] == 'fix') { $couponBenefitType = $mileageUse['unit']; } else { $couponBenefitType =  '%'; }
                                if($couponVal['couponUseType'] == 'order') { // 주문적용 쿠폰인 경우
                                    $couponOrderAddPrintArr[] =  $couponVal['couponNm'] . " : " . gd_money_format($couponVal['couponBenefit']) . $couponBenefitType . " 적립";
                                } else if($couponVal['couponUseType'] == 'product') { // 상품적용 쿠폰인 경우
                                    $couponProductAddPrintArr[] =  $couponVal['couponNm'] . " : " . gd_money_format($couponVal['couponBenefit']) . $couponBenefitType . " 적립";
                                }
                            }
                            if(count($couponOrderAddPrintArr) > 0) { // 적립(주문) 쿠폰 체크
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= implode('<span class="bold" style="font-size: 13px;"> + </span>', $couponOrderAddPrintArr);?>
                                     - (<span class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['divisionCouponOrderMileage']); ?></span><?= $mileageUse['unit'] ?>)
                                </li>
                                <?php
                            }
                            if(count($couponProductAddPrintArr) > 0) { // 적립(상품) 쿠폰 체크
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= implode('<span class="bold" style="font-size: 13px;"> + </span>', $couponProductAddPrintArr);?>
                                     - (<span class="<?= $statusApplyCancelFontColor; ?> bold"><?= gd_money_format($val['couponGoodsMileage']); ?></span><?= $mileageUse['unit'] ?>)
                                </li>
                                <?php
                            }
                        }
                        if($data['totalMemberMileage'] > 0 && $val['memberMileage'] > 0 ) { // 회원 등급 혜택
                            if(empty($orderMemberPolicy) == false) { // 주문당시회원등급별 정책에 값이 없을 경우
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] <?= $orderMemberPolicy['groupNm']; ?>
                                    : <?= $arrMemberPolicyFixedOrderType[$orderMemberPolicy['fixedOrderTypeDc']]; ?> 구매금액 <?= gd_currency_display($orderMemberPolicy['mileageLine']); ?> 이상
                                    | <?= $orderMemberPolicy['mileagePercent']; ?>% 추가 적립 - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= number_format($val['memberMileage']); ?></span><?= $mileageUse['unit'] ?>)
                                </li>
                                <?php
                            } else {
                                ?>
                                <li <?= $statusApplyCancelCss; ?> data-no="<?= $val['sno']; ?>"> <?= $statusApplyCancelString; ?> [<?= $val['sno']; ?>] 회원 추가 적립 - (<span
                                            class="<?= $statusApplyCancelFontColor; ?> bold"><?= number_format($val['memberMileage']); ?></span><?= $mileageUse['unit'] ?>)
                                </li>
                                <?php
                            }
                        }
                    }
                    ?>
                </ul>
            </td>
        </tr>
        <?php
    } else {
        ?>
        <tr>
            <td class="no-data">쿠폰/할인/혜택 정보가 없습니다.</td>
        </tr>
        <?php
    }
    ?>
</table>
<script type="text/javascript">
    var dotHtmlTag = '<?=$dotHtmlTag?>';

    $(document).ready(function () {
        // 구분선처리 id 배열
        var toggleBenefitArray = ['toggleBenefitGoodsDc', 'toggleBenefitOrderDc', 'toggleBenefitAdd'];
        $.each(toggleBenefitArray, function (idKey, idVal) {
            var lastIndex = $('#' + idVal + ' ul li ').length-1; // 최종 값 구분선처리 체크
            $.each($('#' + idVal + ' ul li '), function (tagKey, tagVal) {
                var orderGoodsSno = $(tagVal).attr('data-no'); // 현재 상품주문번호
                var orderGoodsBefore = $($('#' + idVal + ' ul li ')).eq(tagKey + 1).attr('data-no'); // 이전 상품주문번호 추출
                if(lastIndex != tagKey) { // 최종 값과 다를 경우
                    if(orderGoodsSno != orderGoodsBefore) { // 주문상품번호가 이전 값과 다른 경우
                        $(tagVal).append(dotHtmlTag); // 구분선 추가
                    }
                }
            });
        });
    });
</script>