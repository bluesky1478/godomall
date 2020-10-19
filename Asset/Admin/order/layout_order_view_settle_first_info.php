<?php
/**
 * 주문상세 - 최초 결제정보
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
                <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalGoodsPrice'])); ?>
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalGoodsPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                <?php } ?>
            </strong>
        </td>
    </tr>
    <tr>
        <th>
            <button type="button" class="btn btn-xs btn-link js-pay-toggle" data-number="<?= $data['viewOriginalPrice']['totalDeliveryCharge']?>" data-target="toggleDelivery" <?php echo $styleDisplayNone; ?>>보기</button>
            총 배송비
        </th>
        <th class="th">
            <div class="text-primary">
                (+) <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryCharge'])); ?>
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
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
                <li>
                    <strong>배송비</strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryPolicyCharge'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryPolicyCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>지역별 배송비</strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryAreaCharge'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryAreaCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
            </ul>
        </td>
    </tr>
    <tr <?php echo $styleDisplayNone; ?>>
        <th>
            <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?= ($data['viewOriginalPrice']['totalGoodsDcPrice'] + $data['viewOriginalPrice']['totalMemberDcPrice'] + $data['viewOriginalPrice']['totalMemberOverlapDcPrice'] + $data['viewOriginalPrice']['totalCouponOrderDcPrice'] + $data['viewOriginalPrice']['totalCouponGoodsDcPrice'] + $data['viewOriginalPrice']['totalCouponDeliveryDcPrice'] + $data['viewOriginalPrice']['totalMemberDeliveryDcPrice'] + $data['viewOriginalPrice']['totalEnuriDcPrice'] + $data['viewOriginalPrice']['totalMyappDcPrice'])?>" data-target="toggleDcPrice">보기</button>
            총 할인금액
        </th>
        <th class="th">
            <?php
            if ($data['viewOriginalPrice']['totalCouponOrderDcPrice'] + $data['viewOriginalPrice']['totalCouponGoodsDcPrice'] + $data['viewOriginalPrice']['totalCouponDeliveryDcPrice'] + $data['viewOriginalPrice']['totalCouponGoodsMileage'] + $data['viewOriginalPrice']['totalCouponOrderMileage'] > 0) {?>
                <div class="pull-left" style="margin-top:1px;">
                    <input type="button" value="쿠폰 정보 보기" class="btn btn-sm btn-gray js-order-coupon" />
                </div>
            <?php } ?>

            <?php if(($data['viewOriginalPrice']['totalGoodsDcPrice'] + $data['viewOriginalPrice']['totalMemberDcPrice'] + $data['viewOriginalPrice']['totalMemberOverlapDcPrice'] + $data['viewOriginalPrice']['totalCouponOrderDcPrice'] + $data['viewOriginalPrice']['totalCouponGoodsDcPrice'] + $data['viewOriginalPrice']['totalCouponDeliveryDcPrice'] + $data['viewOriginalPrice']['totalMemberDeliveryDcPrice'] + $data['viewOriginalPrice']['totalEnuriDcPrice'] + $data['viewOriginalPrice']['totalMyappDcPrice']) < 0){ ?>
            <div class="text-primary">
            (+)
            <?php } else { ?>
            <div class="text-danger">
            (-)
            <?php } ?>

            <?= gd_currency_display(abs($data['viewOriginalPrice']['totalGoodsDcPrice'] + $data['viewOriginalPrice']['totalMemberDcPrice'] + $data['viewOriginalPrice']['totalMemberOverlapDcPrice'] + $data['viewOriginalPrice']['totalCouponOrderDcPrice'] + $data['viewOriginalPrice']['totalCouponGoodsDcPrice'] + $data['viewOriginalPrice']['totalCouponDeliveryDcPrice'] + $data['viewOriginalPrice']['totalMemberDeliveryDcPrice'] + $data['viewOriginalPrice']['totalEnuriDcPrice'] + $data['viewOriginalPrice']['totalMyappDcPrice'])); ?>
                <?php
                if (empty($data['isDefaultMall']) === true) {
                    $tmptotlaDcPrice = $data['viewOriginalPrice']['totalGoodsDcPrice'] + $data['viewOriginalPrice']['totalMemberDcPrice'] + $data['viewOriginalPrice']['totalMemberOverlapDcPrice'] + $data['viewOriginalPrice']['totalCouponOrderDcPrice'] + $data['viewOriginalPrice']['totalCouponGoodsDcPrice'] + $data['viewOriginalPrice']['totalCouponDeliveryDcPrice'] + $data['viewOriginalPrice']['totalEnuriDcPrice'] + $data['viewOriginalPrice']['totalMyappDcPrice'];
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
                                            <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalGoodsDcPrice'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalGoodsDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <?php if ($data['viewOriginalPrice']['totalMyappDcPrice'] > 0) { ?>
                <li>
                    <strong>상품할인(모바일앱)</strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalMyappDcPrice'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalMyappDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <?php } ?>
                <li>
                    <strong>회원할인(상품)</strong>
                    <span>
                                            <?= gd_currency_display($data['viewOriginalPrice']['totalMemberDcPrice'] + $data['viewOriginalPrice']['totalMemberOverlapDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(($data['viewOriginalPrice']['totalMemberDcPrice'] + $data['viewOriginalPrice']['totalMemberOverlapDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>회원할인(배송비)</strong>
                    <span>
                                            <?= gd_currency_display($data['viewOriginalPrice']['totalMemberDeliveryDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalMemberDeliveryDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>쿠폰할인(상품)</strong>
                    <span>
                                            <?= gd_currency_display($data['viewOriginalPrice']['totalCouponGoodsDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalCouponGoodsDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>쿠폰할인(주문)</strong>
                    <span>
                                            <?= gd_currency_display($data['viewOriginalPrice']['totalCouponOrderDcPrice']); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalCouponOrderDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong>쿠폰할인(배송비)</strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalCouponDeliveryDcPrice'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalCouponDeliveryDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <?php if($data['totalEnuriDcPrice']){ ?>
                    <li>
                        <strong>운영자 추가 할인</strong>
                        <span>
                        <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalEnuriDcPrice'])); ?>
                            <?php if (empty($data['isDefaultMall']) === true) { ?>
                                (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalEnuriDcPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            <?php } ?>
                    </span>
                    </li>
                <?php } ?>
            </ul>
        </td>
    </tr>
    <tr <?php echo $styleDisplayNone; ?>>
        <th>
            <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?=($data['viewOriginalPrice']['useDeposit'] + $data['viewOriginalPrice']['useMileage'])?>" data-target="toggleAddPrice">보기</button>
            총 부가결제금액
        </th>
        <th class="th">
            <div class="text-danger">
                (-) <?= gd_currency_display($data['viewOriginalPrice']['useDeposit'] + $data['viewOriginalPrice']['useMileage']); ?>
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    (<?= gd_global_order_currency_display(($data['viewOriginalPrice']['useDeposit'] + $data['viewOriginalPrice']['useMileage']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
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
                                            <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['useDeposit'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['useDeposit']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                        <?php } ?>
                                        </span>
                </li>
                <li>
                    <strong><?= $mileageUse['name'] ?></strong>
                    <span>
                                            <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['useMileage'])); ?>
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['useMileage']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
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

    <?php if (empty($data['isDefaultMall']) === true && $data['viewOriginalPrice']['totalDeliveryInsuranceFee'] > 0) { ?>
        <tr>
            <th>해외배송 보험료</th>
            <td class="text-right">
                <div class="text-primary">
                    (+) <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryInsuranceFee'])); ?>
                    (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['totalDeliveryInsuranceFee']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                </div>
            </td>
        </tr>
    <?php } ?>

    <tr>
        <th>실 결제금액</th>
        <td class="text-right">
            <?php if($data['orderChannelFl'] == 'naverpay') {?>
                <?=$data['naverpay']['priceInfo']?><br>
                <strong><?= gd_currency_display(($data['viewOriginalPrice']['checkoutData']['orderData']['GeneralPaymentAmount'])); ?></strong>
            <?php } else {?>
                <strong>
                    <?= gd_currency_display(gd_isset($data['viewOriginalPrice']['settlePrice'])); ?>
                    <?php if (empty($data['isDefaultMall']) === true) { ?>
                        (<?= gd_global_order_currency_display(gd_isset($data['viewOriginalPrice']['settlePrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                    <?php } ?>
                </strong>
            <?php }?>
        </td>
    </tr>

    <?php if (empty($data['isDefaultMall']) === true && substr($data['viewOriginalPrice']['settleKind'], 0, 1) == 'o') { ?>
        <tr>
            <th>승인금액</th>
            <td class="text-right">
                <strong><?=$data['overseasSettleCurrency']?> <?= gd_isset($data['viewOriginalPrice']['overseasSettlePrice']); ?></strong>
            </td>
        </tr>
    <?php } ?>

    <tr <?php echo $styleDisplayNone; ?>>
        <th>
            <button type="button" class="btn btn-sm btn-link js-pay-toggle" data-number="<?= $data['totalMileage']?>" data-target="toggleSavingPrice">보기</button>
            총 적립금액
        </th>
        <td class="th">
            <div class="text-success"><?= number_format($data['viewOriginalPrice']['totalMileage']); ?><?=$mileageUse['unit']?></div>
        </td>
    </tr>
    <tr id="toggleSavingPrice">
        <th style="display: none;"></th>
        <td class="th" style="display: none;">
            <ul class="list-unstyled">
                <?php if (gd_isset($data['viewOriginalPrice']['totalGoodsMileage']) > 0) { ?>
                    <li><strong>상품 <?= $mileageUse['name'] ?></strong><span><?= number_format($data['viewOriginalPrice']['totalGoodsMileage']); ?><?=$mileageUse['unit']?></span></li>
                <?php } ?>
                <?php if (gd_isset($data['viewOriginalPrice']['totalMemberMileage']) > 0) { ?>
                    <li><strong>회원 <?= $mileageUse['name'] ?></strong><span><?= number_format($data['viewOriginalPrice']['totalMemberMileage']); ?><?=$mileageUse['unit']?></span></li>
                <?php } ?>
                <?php if (gd_isset($data['viewOriginalPrice']['totalCouponOrderMileage'], 0) + gd_isset($data['viewOriginalPrice']['totalCouponGoodsMileage'], 0) > 0) { ?>
                    <li><strong>쿠폰 <?= $mileageUse['name'] ?></strong><span><?= number_format($data['viewOriginalPrice']['totalCouponOrderMileage'] + $data['viewOriginalPrice']['totalCouponGoodsMileage']); ?><?=$mileageUse['unit']?></span></li>
                <?php } ?>
                <?php if (gd_isset($data['viewOriginalPrice']['mileageGiveExclude']) == 'n' && $data['viewOriginalPrice']['useMileage'] > 0) { ?>
                    <div>※ <?= $mileageUse['name'] ?>을(를) 사용한 경우 적립이 되지 않습니다.</div>
                <?php } ?>
            </ul>
        </td>
    </tr>
</table>
