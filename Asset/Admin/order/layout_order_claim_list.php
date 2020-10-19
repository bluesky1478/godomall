<?php
/**
 * 주문상세에서 사용하는 클래임 접수 가능상품 리스트
 *
 * @param string $claimMode 해당 클래임의 접수가능 상품목록
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
?>

<table class="table table-rows">
    <thead>
    <th><input type="checkbox" class="js-checkall" data-target-name="<?=$claimMode?>[statusCheck]"/></th>
    <th>번호</th>
    <th>상품<br/>주문번호</th>
    <th>이미지</th>
    <th>주문상품</th>
    <th>수량</th>
    <th>판매가</th>
    <th>
        <?php
        switch ($claimMode) {
            case 'cancel':
                echo '취소';
                break;
            case 'refund':
                echo '환불';
                break;
            case 'exchange':
                echo '교환';
                break;
            case 'back':
                echo '반품';
                break;
        }
        ?>
        수량
    </th>
    <?php if (!$isProvider) { ?>
        <th>상품할인</th>
        <th>회원할인</th>
        <th>쿠폰할인</th>
        <th>사용 <?= $depositUse['name'] ?></th>
        <th>사용 <?= $mileageUse['name'] ?></th>
        <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) { ?>    <th>매입처</th> <?php } ?>
        <th>공급사</th>
        <th>배송비<br>사용 <?= $depositUse['name'] ?></th>
        <th>배송비<br>사용 <?= $mileageUse['name'] ?></th>
    <?php } ?>
    <th>실 배송비</th>
    <th>처리상태</th>
    </thead>
    <tbody>
    <?php
    // 주문 처리가 주문기준으로 되어야 할 주문 단계의 경우 체크가 동일 처리되게 or 해외몰인 경우도 부분취소가 안되므로 무조건 전체 취소되도록 처리
    if (in_array(gd_isset($data['statusMode']), $order->statusListCombine) || empty($data['isDefaultMall']) === true) {
        $onclickAction = 'js-checkall';
    }
    if($data['orderChannelFl'] == 'naverpay') {
        $cntDisabled = ' disabled';
    }
    $rowAll = 0;
    $sortNo = $data['claimGoods'][$claimMode . 'cnt']['goods']['all'];// 번호 설정
    foreach ($data['claimGoods'][$claimMode] as $sKey => $sVal) {
        $rowScm = 0;
        foreach ($sVal as $dKey => $dVal) {
            $rowDelivery = 0;
            foreach ($dVal as $key => $val) {
                $statusMode = substr($val['orderStatus'], 0, 1);
                $addGoodsCnt = empty($val['addGoods']) === false ? count($val['addGoods']) : 0;

                // rowspan 처리
                $orderAddGoodsRowSpan = $addGoodsCnt > 0 ? 'rowspan="' . ($addGoodsCnt + 1) . '"' : '';
                $orderScmRowSpan = ' rowspan="' . ($data['claimGoods'][$claimMode . 'cnt']['scm'][$sKey]) . '"';
                $orderDeliveryRowSpan = ' rowspan="' . ($data['claimGoods'][$claimMode . 'cnt']['delivery'][$dKey]) . '"';
                if ($val['totalMemberDeliveryDcPrice'] > 0) {
                    $val['realDeliveryCharge'] -= $val['deliveryPolicyCharge'];
                }
                ?>
                <tr class="text-center">
                    <td <?= $orderAddGoodsRowSpan ?>>
                        <input type="checkbox" name="<?= $claimMode ?>[statusCheck][<?= $val['sno'] ?>]" value="<?= $val['sno']; ?>" class="<?= gd_isset($onclickAction); ?>" />
                        <input type="hidden" name="<?= $claimMode ?>[statusMode][<?= $val['sno'] ?>]" value="<?= $val['orderStatus']; ?>"/>
                        <input type="hidden" name="<?= $claimMode ?>[goodsType][<?= $val['sno'] ?>]" value="<?= $val['goodsType']; ?>"/>
                    </td>
                    <td <?= $orderAddGoodsRowSpan ?>><?= $sortNo ?></td>
                    <td <?= $orderAddGoodsRowSpan ?>><?= $val['sno'] ?></td>
                    <td>
                        <?php if ($val['goodsType'] === 'addGoods') { ?>
                            <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                        <?php } else { ?>
                            <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                        <?php } ?>
                    </td>
                    <td class="text-left">
                        <?php if ($val['goodsType'] === 'addGoods') { ?>
                        <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                            <a href="javascript:void()" class="text-primary" title="추가상품명" onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>');"><?= strip_tags(htmlspecialchars_decode($val['goodsNm'])); ?></a>
                        <?php } else { ?>
                            <a href="javascript:void()" class="text-primary" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>');"><?= strip_tags(htmlspecialchars_decode($val['goodsNm'])); ?></a>
                        <?php } ?>

                        <div class="info">
                            <?php
                            // 상품 코드
                            if (empty($val['goodsCd']) === false) {
                                echo '<div class="font-kor" title="상품코드">[' . $val['goodsCd'] . ']</div>';
                            }

                            // 옵션 처리
                            if (empty($val['optionInfo']) === false) {
                                foreach ($val['optionInfo'] as $oKey => $oVal) {
                                    echo '<dl class="dl-horizontal" title="옵션명">';
                                    echo '<dt>' . $oVal['optionName'] . ' :</dt>';
                                    echo '<dd>' . $oVal['optionValue'] . '</dd>';
                                    echo '</dl>';
                                }
                            }

                            // 텍스트 옵션 처리
                            if (empty($val['optionTextInfo']) === false) {
                                foreach ($val['optionTextInfo'] as $oKey => $oVal) {
                                    echo '<ul class="list-unstyled" title="텍스트 옵션명">';
                                    echo '<li>' . $oVal['optionName'] . ' :</li>';
                                    echo '<li>' . $oVal['optionValue'] . ' ';
                                    if ($oVal['optionTextPrice'] > 0) {
                                        echo '<span>(추가금 ' . gd_currency_symbol() . gd_money_format($oVal['addPrice']) . gd_currency_string() . ')</span>';
                                    }
                                    echo '</li>';
                                    echo '</ul>';
                                }
                            }
                            ?>
                        </div>
                    </td>
                    <td><?= $val['goodsCnt']; ?></td>
                    <td class="text-right"><?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']); ?></td>
                    <td <?= $orderAddGoodsRowSpan ?>>
                        <?php if ($val['addGoodsCnt'] > 0 || empty($data['isDefaultMall']) === true) { ?>
                            <?= $val['goodsCnt'] ?>
                            <input type="hidden" name="<?= $claimMode ?>[goodsOriginCnt][<?= $val['sno'] ?>]" value="<?= $val['goodsCnt'] ?>"/>
                            <input type="hidden" name="<?= $claimMode ?>[goodsCnt][<?= $val['sno'] ?>]" value="<?= $val['goodsCnt'] ?>"/>
                        <?php } else { ?>
                            <input type="hidden" name="<?= $claimMode ?>[goodsOriginCnt][<?= $val['sno'] ?>]" value="<?= $val['goodsCnt'] ?>"/>

                            <input type="text" name="<?= $claimMode ?>[goodsCnt][<?= $val['sno'] ?>]" class="form-control width-2xs input-sm mgauto text-center" <?=$cntDisabled?> value="<?= $val['goodsCnt'] ?>"/>
                            <?php if($cntDisabled) {?>
                                <input type="hidden" name="<?= $claimMode ?>[goodsCnt][<?= $val['sno'] ?>]" class="form-control width-2xs input-sm mgauto text-center"  value="<?= $val['goodsCnt'] ?>"/>
                            <?php }?>
                        <?php } ?>
                    </td>
                    <?php if (!$isProvider) { ?>
                        <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['goodsDcPrice']); ?></td>
                        <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice']); ?></td>
                        <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalCouponGoodsDcPrice'] + $val['totalDivisionCouponOrderDcPrice']); ?></td>
                        <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['divisionUseDeposit']); ?></td>
                        <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['divisionUseMileage']); ?></td>
                        <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) { ?><td><?= $val['purchaseNm']; ?></td><?php } ?>
                        <?php if ($rowScm == 0) { ?>
                            <td <?= $orderScmRowSpan ?> class="text-center"><?= $val['companyNm']; ?></td>
                        <?php } ?>
                    <?php } ?>
                    <?php if (($rowDelivery == 0 && $data['mallSno'] == DEFAULT_MALL_NUMBER) || ($rowAll == 0 && $data['mallSno'] > DEFAULT_MALL_NUMBER)) { ?>
                        <?php if (!$isProvider) { ?>
                            <td <?= $orderDeliveryRowSpan ?> class="text-right"><?= gd_currency_display($val['divisionDeliveryUseDeposit']); ?></td>
                            <td <?= $orderDeliveryRowSpan ?> class="text-right"><?= gd_currency_display($val['divisionDeliveryUseMileage']); ?></td>
                        <?php } ?>
                        <td <?= $orderDeliveryRowSpan ?> class="text-right"><?= gd_currency_display($val['realDeliveryCharge']); ?></td>
                    <?php } ?>
                    <td <?= $orderAddGoodsRowSpan ?>><?= $val['orderStatusStr']; ?></td>
                </tr>
                <?php
                if ($val['addGoodsCnt'] > 0) {
                    foreach ($val['addGoods'] as $aVal) {
                        ?>
                        <tr class="text-center add-goods">
                            <td class="text-center"><span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span></td>
                            <td class="text-left">
                                <?= gd_html_add_goods_image($aVal['addGoodsNo'], $aVal['imageNm'], $aVal['imagePath'], $aVal['imageStorage'], 30, $aVal['goodsNm'], '_blank'); ?>
                                <a href="javascript:void()" class="one-line" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo']; ?>');"><?= gd_html_cut($aVal['goodsNm'], 46, '..'); ?>
                                    <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..'); ?>)</small>
                                </a>
                            </td>
                            <td class="goods_cnt"><span class="option_info bold" title="상품 주문 수량"><?= number_format($aVal['goodsCnt']); ?></span></td>
                            <td class="text-right"><span title="상품 금액 : (상품단가+옵션금액) x 수량"><?= gd_currency_display($aVal['goodsPrice'] * $aVal['goodsCnt']); ?></span></td>
                        </tr>
                        <?php
                    }
                }
                $sortNo--;
                $rowScm++;
                $rowDelivery++;
                $rowAll++;
            }
        }
    } ?>
    </tbody>
</table>
