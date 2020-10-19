<table class="table table-rows">
    <thead>
    <tr>
        <th>쿠폰유형</th>
        <th>쿠폰명</th>
        <th>혜택구분</th>
        <th>할인 금액</th>
        <th>적립 금액</th>
        <th>상품명</th>
        <th>비고</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($orderCoupon) === false) {
        $sumCouponPrice = 0;
        $sumCouponMileage = 0;
        foreach ($orderCoupon as $key => $val) {
            $addDescription = '';
            $sumCouponPrice = $sumCouponPrice + $val['couponPrice'];
            $sumCouponMileage = $sumCouponMileage + $val['couponMileage'];
            switch ($val['couponUseType']) {
                case 'product':
                    $couponUseType = '상품쿠폰';
                    break;
                case 'order':
                    $couponUseType = '주문쿠폰';
                    break;
                case 'delivery':
                    $couponUseType = '배송쿠폰';
                    break;
            }
            switch ($val['couponKindType']) {
                case 'sale':
                    $couponKindType = '상품할인';
                    break;
                case 'add':
                    $couponKindType = '마일리지적립';
                    if ($val['plusCouponFl'] === 'n') {
                        $addDescription = '<span class="text-primary" title="구매확정시 적립완료됩니다.">(적립예정)</span>';
                    } else {
                        $addDescription = '<span class="text-danger">(적립완료)</span>';
                    }
                    break;
                case 'delivery':
                    $couponKindType = '배송비할인';
                    break;
            }

            ?>
            <tr class="text-center">
                <td><?= $couponUseType; ?></td>
                <td><?= $val['couponNm']; ?></td>
                <td>
                    <?= $couponKindType; ?><br>
                    (<?= number_format($val['couponBenefit']); ?><?= $val['couponBenefitType'] == 'fix' ? gd_currency_string() : '%'; ?>)
                </td>
                <td><?= gd_currency_display($val['couponPrice']); ?></td>
                <td>
                    <?= number_format($val['couponMileage']); ?>
                    <?= $addDescription?>
                </td>
                <td>
                    <?php if ($val['goodsNo'] == 0) { ?>
                        -
                    <?php } else { ?>
                        <?php if ($val['goodsType'] === 'addGoods') { ?>
                            <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                            <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                               onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 30, '..'); ?></a>
                        <?php } else { ?>
                            <?php if($val['timeSaleFl'] =='y') { ?>
                                <img src='<?=PATH_ADMIN_GD_SHARE?>img/time-sale.png' alt='타임세일' />
                            <?php } ?>

                            <a href="javascript:void();" class="one-line bold mgb5" title="상품명"
                               onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 30, '..'); ?></a>
                        <?php } ?>
                        <?php
                        // 옵션 처리
                        if (empty($val['optionInfo']) === false) {
                            echo '<div class="option_info" title="상품 옵션">';
                            foreach (json_decode($val['optionInfo']) as $option) {
                                $tmpOption[] = $option[0] . ':' . $option[1];
                            }
                            echo implode(', ', $tmpOption);
                            echo '</div>';
                            unset($tmpOption);
                        }

                        // 텍스트 옵션 처리
                        if (empty($val['optionTextInfo']) === false) {
                            echo '<div class="option_info" title="텍스트 옵션">';
                            foreach (json_decode($val['optionTextInfo']) as $option) {
                                $tmpOption[] = $option[0] . ':' . $option[1];
                            }
                            echo implode(', ', $tmpOption);
                            echo '</div>';
                            unset($tmpOption);}
                        ?>
                    <?php } ?>
                </td>
                <td>-</td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="7" class="no-data">쿠폰이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
    <?php if (empty($orderCoupon) === false) { ?>
        <tfoot>
        <tr class="text-center">
            <th class="text-center">합계</th>
            <th colspan="2"></th>
            <th class="text-center"><?= number_format($sumCouponPrice); ?>원</th>
            <th class="text-center"><?= number_format($sumCouponMileage); ?></th>
            <th></th>
            <th></th>
        </tr>
        </tfoot>
    <?php } ?>
</table>
