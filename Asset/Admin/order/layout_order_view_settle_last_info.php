<?php
/**
 * 주문상세 - 최종 결제정보
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>
<table class="table table-cols table-toggle">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tr>
        <th>상품 판매금액</th>
        <td class="text-right">
            <strong>
                <?= gd_currency_display(gd_isset($data['totalGoodsPrice'])); ?>
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    (<?= gd_global_order_currency_display(gd_isset($data['totalGoodsPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                <?php } ?>
            </strong>
        </td>
    </tr>
    <tr>
        <th>
            <button type="button" class="btn btn-xs btn-link js-pay-toggle" data-number="<?= $data['totalDeliveryCharge']?>" data-target="toggleDelivery" <?php echo $styleDisplayNone; ?>>보기</button>
            총 배송비
        </th>
        <th class="th">
            <div class="text-primary">
                (+) <?= gd_currency_display(gd_isset($data['totalDeliveryCharge'])); ?>
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    (<?= gd_global_order_currency_display(gd_isset($data['totalDeliveryCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                <?php } ?>
            </div>
        </th>
    </tr>
    <tr id="toggleDelivery">
        <th style="display: none;"></th>
        <td class="th" style="display: none;">
            <ul class="list-unstyled">
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    <li>
                        <strong>총 무게</strong>
                        <span>
                                            <?= number_format(gd_isset($data['deliveryWeightInfo']['total']), 2); ?>kg
                                            (상품<?= number_format(gd_isset($data['deliveryWeightInfo']['goods']), 2); ?>kg +
                                             박스<?= number_format(gd_isset($data['deliveryWeightInfo']['box']), 2); ?>kg)
                                        </span>
                    </li>
                <?php } ?>
                <?php
                // 환불건의 경우 기본배송비, 지역별 배송비를 구분하지 않으므로 통일하여 보여준다.
                if((int)$data['claimGoods']['refundcnt']['orderGoodsCnt'] > 0) {
                    ?>
                    <li>
                        <strong>배송비</strong>
                        <span>
                            <?= gd_currency_display(gd_isset($data['totalDeliveryCharge'])); ?>
                            <?php if (empty($data['isDefaultMall']) === true) { ?>
                                (<?= gd_global_order_currency_display(gd_isset($data['totalDeliveryCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            <?php } ?>
                        </span>
                    </li>
                    <?php
                } else {
                    ?>
                    <li>
                        <strong>배송비</strong>
                        <span>
                            <?= gd_currency_display(gd_isset($data['totalDeliveryPolicyCharge'])); ?>
                            <?php if (empty($data['isDefaultMall']) === true) { ?>
                                (<?= gd_global_order_currency_display(gd_isset($data['totalDeliveryPolicyCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            <?php } ?>
                        </span>
                    </li>
                    <li>
                        <strong>지역별 배송비</strong>
                        <span>
                            <?= gd_currency_display(gd_isset($data['totalDeliveryAreaCharge'])); ?>
                            <?php if (empty($data['isDefaultMall']) === true) { ?>
                                (<?= gd_global_order_currency_display(gd_isset($data['totalDeliveryAreaCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            <?php } ?>
                        </span>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </td>
    </tr>
    <tr <?php echo $styleDisplayNone; ?>>
        <th>
            <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?= ($data['totalGoodsDcPrice'] + $data['totalMemberDcPrice'] + $data['totalMemberOverlapDcPrice'] + $data['totalCouponOrderDcPrice'] + $data['totalCouponGoodsDcPrice'] + $data['totalCouponDeliveryDcPrice'] + $data['totalMemberDeliveryDcPrice'] + $data['totalEnuriDcPrice'] + $data['totalMyappDcPrice'])?>" data-target="toggleDcPrice">보기</button>
            총 할인금액
        </th>
        <th class="th">
            <?php if ($data['totalCouponOrderDcPrice'] + $data['totalCouponGoodsDcPrice'] + $data['totalCouponDeliveryDcPrice'] + $data['totalCouponGoodsMileage'] + $data['totalCouponOrderMileage'] > 0) { ?>
                <div class="pull-left" style="margin-top:1px;">
                    <input type="button" value="쿠폰 정보 보기" class="btn btn-sm btn-gray js-order-coupon" />
                </div>
            <?php } ?>

            <?php if(($data['totalGoodsDcPrice'] + $data['totalMemberDcPrice'] + $data['totalMemberOverlapDcPrice'] + $data['totalCouponOrderDcPrice'] + $data['totalCouponGoodsDcPrice'] + $data['totalCouponDeliveryDcPrice'] + $data['totalMemberDeliveryDcPrice'] + $data['totalEnuriDcPrice'] + $data['totalMyappDcPrice']) < 0){ ?>
                <div class="text-primary">
                    (+)
            <?php } else { ?>
                <div class="text-danger">
                    (-)
            <?php } ?>

                 <?= gd_currency_display(abs($data['totalGoodsDcPrice'] + $data['totalMemberDcPrice'] + $data['totalMemberOverlapDcPrice'] + $data['totalCouponOrderDcPrice'] + $data['totalCouponGoodsDcPrice'] + $data['totalCouponDeliveryDcPrice'] + $data['totalMemberDeliveryDcPrice'] + $data['totalEnuriDcPrice'] + $data['totalMyappDcPrice'])); ?>
                <?php
                if (empty($data['isDefaultMall']) === true) {
                    $tmptotlaDcPrice = $data['totalGoodsDcPrice'] + $data['totalMemberDcPrice'] + $data['totalMemberOverlapDcPrice'] + $data['totalCouponOrderDcPrice'] + $data['totalCouponGoodsDcPrice'] + $data['totalCouponDeliveryDcPrice'] + $data['totalEnuriDcPrice'] + $data['totalMyappDcPrice'];
                    ?>
                    (<?= gd_global_order_currency_display(gd_isset($tmptotlaDcPrice), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                <?php } ?>
            </div>
        </th>
    </tr>
    <tr id="toggleDcPrice">
        <th style="display: none;"></th>
        <td class="th" style="display: none;">
            <ul class="list-unstyled">
                <li>
                    <strong>상품할인</strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['totalGoodsDcPrice'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['totalGoodsDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <?php if ($data['totalMyappDcPrice'] > 0) { ?>
                    <li>
                        <strong>상품할인(모바일앱)</strong>
                        <span>
                                            <?= gd_currency_display(gd_isset($data['totalMyappDcPrice'])); ?>
                            <?php if (empty($data['isDefaultMall']) === true) { ?>
                                (<?= gd_global_order_currency_display(gd_isset($data['totalMyappDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            <?php } ?>
                                        </span>
                    </li>
                <?php } ?>
                <li>
                    <strong>회원할인(상품)</strong>
                    <span>
                                            <?= gd_currency_display($data['totalMemberDcPrice'] + $data['totalMemberOverlapDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(($data['totalMemberDcPrice'] + $data['totalMemberOverlapDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>회원할인(배송비)</strong>
                    <span>
                                            <?= gd_currency_display($data['totalMemberDeliveryDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['totalMemberDeliveryDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>쿠폰할인(상품)</strong>
                    <span>
                                            <?= gd_currency_display($data['totalCouponGoodsDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['totalCouponGoodsDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>쿠폰할인(주문)</strong>
                    <span>
                                            <?= gd_currency_display($data['totalCouponOrderDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['totalCouponOrderDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>쿠폰할인(배송비)</strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['totalCouponDeliveryDcPrice'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['totalCouponDeliveryDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <?php if($data['totalEnuriDcPrice']){ ?>
                <li>
                    <strong>운영자 추가 할인</strong>
                    <span>
                        <?= gd_currency_display(gd_isset($data['totalEnuriDcPrice'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['totalEnuriDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                    </span>
                </li>
                <?php } ?>
            </ul>
        </td>
    </tr>
    <tr <?php echo $styleDisplayNone; ?>>
        <th>
            <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?=($data['useDeposit'] + $data['useMileage'])?>" data-target="toggleAddPrice">보기</button>
            총 부가결제금액
        </th>
        <th class="th">
            <div class="text-danger">
                (-) <?= gd_currency_display($data['useDeposit'] + $data['useMileage']); ?>
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    (<?= gd_global_order_currency_display(($data['useDeposit'] + $data['useMileage']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                <?php } ?>
            </div>
        </th>
    </tr>
    <tr id="toggleAddPrice">
        <th style="display: none;"></th>
        <td class="th" style="display: none;">
            <ul class="list-unstyled">
                <li>
                    <strong><?= $depositUse['name'] ?></strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['useDeposit'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['useDeposit']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong><?= $mileageUse['name'] ?></strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['useMileage'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['useMileage']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
            </ul>
        </td>
    </tr>

    <?php if($data['orderChannelFl'] == 'naverpay') {?>
        <tr>
            <th>네이버페이 할인금액</th>
            <td class="text-right">
                <?=$data['naverpay']['discountInfo']?>
            </td>
        </tr>
    <?php }?>

    <?php if (empty($data['isDefaultMall']) === true && $data['totalDeliveryInsuranceFee'] > 0) { ?>
        <tr>
            <th>해외배송 보험료</th>
            <td class="text-right">
                <div class="text-primary">
                    (+) <?= gd_currency_display(gd_isset($data['totalDeliveryInsuranceFee'])); ?>
                    (<?= gd_global_order_currency_display(gd_isset($data['totalDeliveryInsuranceFee']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                </div>
            </td>
        </tr>
    <?php } ?>

    <tr>
        <th>실 결제금액</th>
        <td class="text-right">
            <?php if($data['orderChannelFl'] == 'naverpay') {?>
                <?=$data['naverpay']['priceInfo']?><br>
                <strong><?= gd_currency_display(($data['checkoutData']['orderData']['GeneralPaymentAmount'])); ?></strong>
            <?php }
            else {?>
                <strong>
                    <?= gd_currency_display(gd_isset($data['totalRealSettlePrice'])); ?>
                    <?php if (empty($data['isDefaultMall']) === true) { ?>
                        (<?= gd_global_order_currency_display(gd_isset($data['totalRealSettlePrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                    <?php } ?>
                </strong>
            <?php }?>
        </td>
    </tr>

    <?php if (empty($data['isDefaultMall']) === true && substr($data['settleKind'], 0, 1) == 'o') { ?>
        <tr>
            <th>승인금액</th>
            <td class="text-right">
                <strong><?=$data['overseasSettleCurrency']?> <?= gd_isset($data['overseasSettlePrice']); ?></strong>
            </td>
        </tr>
    <?php } ?>

    <tr <?php echo $styleDisplayNone; ?>>
        <th>
            <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?= $data['totalMileage']?>" data-target="toggleSavingPrice">보기</button>
            총 적립금액
        </th>
        <td class="th">
            <div class="text-success"><?= number_format($data['totalMileage']); ?><?=$mileageUse['unit']?></div>
        </td>
    </tr>
    <tr id="toggleSavingPrice">
        <th style="display: none;"></th>
        <td class="th" style="display: none;">
            <ul class="list-unstyled">
                <?php if (gd_isset($data['totalGoodsMileage']) > 0) { ?>
                    <li><strong>상품 <?= $mileageUse['name'] ?></strong><span><?= number_format($data['totalGoodsMileage']); ?><?=$mileageUse['unit']?></span></li>
                <?php } ?>
                <?php if (gd_isset($data['totalMemberMileage']) > 0) { ?>
                    <li><strong>회원 <?= $mileageUse['name'] ?></strong><span><?= number_format($data['totalMemberMileage']); ?><?=$mileageUse['unit']?></span></li>
                <?php } ?>
                <?php if (gd_isset($data['totalCouponOrderMileage'], 0) + gd_isset($data['totalCouponGoodsMileage'], 0) > 0) { ?>
                    <li><strong>쿠폰 <?= $mileageUse['name'] ?></strong><span><?= number_format($data['totalCouponOrderMileage'] + $data['totalCouponGoodsMileage']); ?><?=$mileageUse['unit']?></span></li>
                <?php } ?>
                <?php if (gd_isset($data['mileageGiveExclude']) == 'n' && $data['useMileage'] > 0) { ?>
                    <div>※ <?= $mileageUse['name'] ?>을(를) 사용한 경우 적립이 되지 않습니다.</div>
                <?php } ?>
            </ul>
        </td>
    </tr>
</table>
