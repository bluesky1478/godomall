<!-- 프린트 출력을 위한 form -->
<form id="frmOrderPrint" name="frmOrderPrint" action="" method="post" class="display-none">
    <input type="checkbox" name="orderNo" value="<?=gd_isset($data['orderNo'])?>" checked="checked"/>
    <input type="hidden" name="orderPrintCode" value=""/>
    <input type="hidden" name="orderPrintMode" value=""/>
</form>
<!-- // 프린트 출력을 위한 form -->


<div class="page-header js-affix">
    <h3><?= end($naviMenu->location) ?> <small></small></h3>
    <?php if ($statusFl) { ?>
        <input type="button" value="환불하기" class="btn btn-red js-refund-form">
    <?php } ?>
</div>

<form id="frmRefundStatus" method="post" action="./order_ps.php">
    <input type="hidden" name="mode" value="refund_rollback"/>
    <input type="hidden" name="orderNo" value="<?=gd_isset($data['orderNo'])?>"/>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-4 pdt3">
                    <span class="flag flag-16 flag-<?= $data['domainFl']; ?>"></span>
                    <?= $data['mallName']; ?>
                    <?= str_repeat('&nbsp', 6); ?>

                    주문번호 : <span><?= $data['orderNo'] ?></span>
                </div>
                <div class="col-xs-4 pdt3 text-center">
                    해당 주문 상품 <strong><?= $data['orderGoodsCnt'] ?></strong>개 중 <strong class="text-red"><?= $data['cnt']['goods']['goods'] ?></strong>개의 상품 환불
                </div>
                <div class="col-xs-4 text-right">
                    <div class="form-inline">
                        <?= gd_select_box('orderPrintMode', null, ['report' => '주문내역서', 'customerReport' => '주문내역서 (고객용)', 'reception' => '간이영수증', 'particular' => '거래명세서', 'taxInvoice' => '세금계산서'], null, null, '=인쇄 선택=', null, 'form-control input-sm') ?>
                        <input type="button" onclick="order_print_popup($('#orderPrintMode').val(), 'frmOrderPrint', 'frmOrderPrint', 'orderNo', <?=$isProvider ? 'true' : 'false'?>);" value="프린트" class="btn btn-sm btn-white"/>
                    </div>
                </div>
            </div>


            <div class="pull-right">

            </div>
        </div>
    </div>

    <div class="table-title gd-help-manual">환불 상품 정보</div>
    <div class="table-responsive">
        <table class="table table-rows">
            <thead>
            <tr>
                <?php if ($statusFl) { ?>
                    <th class="width3p" rowspan="2">
                        <input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="bundle[statusCheck]"/>
                    </th>
                <?php } ?>
                <th class="width3p" rowspan="2">번호</th>
                <th class="width5p" rowspan="2">접수일자</th>
                <th class="width5p" rowspan="2">사유</th>
                <th class="width5p" rowspan="2">상품<br/>주문번호</th>
                <th class="width-3xs" rowspan="2">이미지</th>
                <th rowspan="2">주문상품</th>
                <th class="width3p" rowspan="2">수량</th>
                <th class="width5p" rowspan="2">판매가</th>
                <th class="width5p" rowspan="2">매입가</th>
                <th class="width5p" rowspan="2">상품할인</th>
                <th class="width5p" rowspan="2">상품할인(모바일앱)</th>
                <th class="width5p" rowspan="2">회원할인(상품)</th>
                <th class="width5p" rowspan="2">회원할인(배송비)</th>
                <th class="width12p" colspan="3">쿠폰할인</th>
                <th class="width5p" rowspan="2">사용<?=$depositUse['name']?></th>
                <th class="width5p" rowspan="2">사용<?=$mileageUse['name']?></th>
                <th class="width5p" rowspan="2">결제금액</th>
                <th class="width5p" rowspan="2">해외배송<br>보험료</th>
                <th class="width5p" rowspan="2">배송비<br>실 결제금액</th>
                <th class="width5p" rowspan="2">적립<?=$mileageUse['name']?></th>
                <th class="width5p" rowspan="2">공급사</th>
                <th class="width7p" rowspan="2">처리상태</th>
            </tr>
            <tr class="text-center nowrap">
                <th class="width4p">상품</th>
                <th class="width4p">주문</th>
                <th class="width4p">배송비</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (isset($data['goods']) === true) {
                $rowAll = 0;
                $sortNo = $data['cnt']['goods']['goods'];// 번호 설정
                $settlePrice = 0;// 결제금액
                $totalSettlePrice = 0; // 전체 결제금액 (결제금액 + 배송비)
                foreach ($data['goods'] as $sKey => $sVal) {
                    $rowScm = 0;
                    foreach ($sVal as $dKey => $dVal) {
                        $rowDelivery = 0;
                        foreach ($dVal as $key => $val) {
                            // 결제금액 (추가상품 분리 작업이후 addGoodsPrice는 0원으로 들어가짐)
                            $settlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['totalGoodsDivisionUseDeposit'] - $val['totalGoodsDivisionUseMileage'] - $val['totalDivisionCouponOrderDcPrice'] - $val['totalMyappDcPrice'];

                            $totalSettlePrice += $settlePrice;
                            $statusMode = substr($val['orderStatus'], 0, 1);

                            // rowspan 처리
                            $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';
                            $orderScmRowSpan = ' rowspan="' . ($data['cnt']['scm'][$sKey]) . '"';
                            $orderDeliveryRowSpan = ' rowspan="' . ($data['cnt']['delivery'][$dKey]) . '"';

                            // 기본 배송업체 설정
                            if (empty($val['deliverySno']) === true) {
                                $val['orderDeliverySno'] = $deliverySno;
                            }
                            ?>
                            <tr id="statusCheck_<?= $statusMode ?>_<?= $val['sno'] ?>" class="text-center nowrap">
                                <?php if ($statusFl) { ?>
                                    <td <?= $orderAddGoodsRowSpan ?> class="center">
                                        <div class="display-block">
                                            <input type="checkbox" id="checkBox_<?= $statusMode ?>_<?= $val['sno'] ?>" name="bundle[statusCheck][<?= $val['sno'] ?>]" value="<?= $val['sno'] ?>"/>
                                            <input type="hidden" name="bundle[handleSno][<?= $val['sno']; ?>]" value="<?= $val['handleSno']; ?>"/>
                                            <input type="hidden" name="bundle[orderStatus][<?= $val['sno']; ?>]" value="<?= $val['orderStatus']; ?>"/>
                                            <input type="hidden" name="bundle[beforeStatus][<?= $val['sno']; ?>]" value="<?= $val['beforeStatus']; ?>"/>
                                            <input type="hidden" name="bundle[orderCd][<?= $val['sno']; ?>]" value="<?= $val['orderCd']; ?>"/>
                                        </div>
                                    </td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?> class="font-num"><?= $sortNo ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="font-date"><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleRegDt'])); ?></td>
                                <td <?= $orderAddGoodsRowSpan ?>><?= $val['handleReason'] ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="font-num"><?= $val['sno'] ?></td>
                                <td>
                                    <?php if ($val['goodsType'] === 'addGoods') { ?>
                                        <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                    <?php } else { ?>
                                        <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank') ?>
                                    <?php } ?>
                                </td>
                                <td class="text-left">
                                    <?php if ($val['goodsType'] === 'addGoods') { ?>
                                        <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                        <a title="추가 상품명" onclick="addgoods_register_popup('<?= $val['goodsNo'] ?>');"><strong><?= gd_html_cut($val['goodsNm'], 46, '..') ?></strong></a>
                                    <?php } else { ?>
                                        <a href="#" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo'] ?>');"><strong><?= $val['goodsNm'] ?></strong></a>
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
                                                    echo '<span>(추가금 ' . gd_currency_display($oVal['optionTextPrice']) . ')</span>';
                                                }
                                                echo '</li>';
                                                echo '</ul>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center"><?= number_format($val['goodsCnt']) ?></td>
                                <td class="text-right"><?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) ?></td>
                                <td class="text-right"><?= gd_currency_display(($val['costPrice'] + $val['optionCostPrice']) * $val['goodsCnt']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['goodsDcPrice']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['myappDcPrice']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice']) ?></td>
                                <?php if (($rowDelivery == 0 && $data['mallSno'] == DEFAULT_MALL_NUMBER) || ($rowAll == 0 && $data['mallSno'] > DEFAULT_MALL_NUMBER)) { ?>
                                    <td <?= $orderDeliveryRowSpan ?> class="text-right">
                                        <?php
                                        $memberDeliveryDcPrice = 0;
                                        if ($val['totalMemberDeliveryDcPrice'] > 0) {
                                            $memberDeliveryDcPrice = $val['deliveryPolicyCharge'];
                                        }
                                        echo gd_currency_display($memberDeliveryDcPrice);
                                        ?>
                                    </td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalCouponGoodsDcPrice']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalDivisionCouponOrderDcPrice']) ?></td>
                                <?php if (($rowDelivery == 0 && $data['mallSno'] == DEFAULT_MALL_NUMBER) || ($rowAll == 0 && $data['mallSno'] > DEFAULT_MALL_NUMBER)) { ?>
                                    <td <?= $orderDeliveryRowSpan ?> class="text-right"><?= gd_currency_display($val['divisionDeliveryCharge']) ?></td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalGoodsDivisionUseDeposit']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalGoodsDivisionUseMileage']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($settlePrice) ?></td>
                                <?php if (($rowDelivery == 0 && $data['mallSno'] == DEFAULT_MALL_NUMBER) || ($rowAll == 0 && $data['mallSno'] > DEFAULT_MALL_NUMBER)) { ?>
                                    <td <?= $orderDeliveryRowSpan ?> class="text-right"><?= gd_currency_display($val['deliveryInsuranceFee']); ?></td>
                                    <td <?= $orderDeliveryRowSpan ?> class="text-right">
                                        <?php
                                        $realDeliveryCharge = $val['deliveryCharge'] - $val['divisionDeliveryCharge'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
                                        if ($val['totalMemberDeliveryDcPrice'] > 0) {
                                            $realDeliveryCharge -= $val['deliveryPolicyCharge'];
                                        }
                                        if ($realDeliveryCharge < 0) {
                                            $realDeliveryCharge = 0;
                                        }
                                        echo gd_currency_display($realDeliveryCharge);
                                        ?>
                                    </td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?>><?= gd_currency_display($val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'])?></td>
                                <?php if ($rowScm == 0) { ?>
                                    <td <?= $orderScmRowSpan ?> class="text-center"><?= $val['companyNm'] ?></td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?> class="center">
                                    <?php if (empty($val['beforeStatusStr']) === false) { ?>
                                        <div><?= $val['beforeStatusStr'] ?> &gt;</div>
                                    <?php } ?>
                                    <div><?= $val['orderStatusStr'] ?></div>
                                </td>
                            </tr>
                            <?php
                            if ($val['addGoodsCnt'] > 0) {
                                foreach ($val['addGoods'] as $aVal) {
                                    ?>
                                    <tr class="text-center add-goods">
                                        <td class="text-center"><span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span></td>
                                        <td class="text-left">
                                            <?= gd_html_add_goods_image($aVal['addGoodsNo'], $aVal['imageNm'], $aVal['imagePath'], $aVal['imageStorage'], 30, $aVal['goodsNm'], '_blank'); ?>
                                            <div class="goods_name one-line hand" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo'] ?>');"><?= gd_html_cut($aVal['goodsNm'], 46, '..') ?>
                                                <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..') ?>)</small>
                                            </div>
                                        </td>
                                        <td class="goods_cnt"><?= number_format($aVal['goodsCnt']) ?></td>
                                        <td class="text-right"><?= gd_currency_display($aVal['goodsPrice'] * $aVal['goodsCnt']) ?></td>
                                    </tr>
                                    <?php
                                }
                            }

                            if (empty($val['orderDeliverySno']) === false) {
                                $escrowDelivery = $val['orderDeliverySno'];
                            }
                            if (empty($val['invoiceNo']) === false) {
                                $escrowInvoiceNo = $val['invoiceNo'];
                            }
                            $sortNo--;
                            $rowDelivery++;
                            $rowScm++;
                            $rowAll++;
                        }

                        // 전체 결제금액에 배송비 포함
                        $totalSettlePrice += $val['deliveryCharge'] + $val['deliveryInsuranceFee'] - $val['divisionDeliveryCharge'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
                        if ($val['totalMemberDeliveryDcPrice'] > 0) {
                            $totalSettlePrice -= $val['deliveryPolicyCharge'];
                        }
                    }
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php if ($statusFl) { ?>
        <div class="table-action">
            <div class="pull-left form-inline">
                <span class="action-title">선택한 상품을</span>
                <?php
                // 에스크로 주문이고 배송등록이 안되어 있다면
                $bundleChangeStatus = $order->getOrderStatusList(null, ['o','c','s','e','r','f'], ['g2','g3','g4','b2','b3','b4','d1','r1']);
                echo gd_select_box('bundleChangeStatus', 'changeStatus', $bundleChangeStatus, null, null, '==상품상태==', $disabled, 'form-control js-status-change');
                unset($bundleChangeStatus);
                ?>
                으(로)
                <button type="button" class="btn btn-white js-refund-status">변경</button>
                <!--            <button type="button" class="btn btn-white js-return-stock">재고환원</button>-->
            </div>
            <div class="pull-right pdt5">
                <strong>총 결제금액</strong> : <strong><?=gd_currency_display($totalSettlePrice)?></strong> (결제금액 + 배송비)
            </div>
        </div>
    <?php } ?>
</form>
<?php if (!$isProvider) { ?>
<div class="row">
    <!-- 쿠폰/할인/혜택 정보 -->
    <div class="col-md-12">
        <div class="table-title gd-help-manual">
            쿠폰/할인/혜택 정보
        </div>
        <?php
        include $layoutOrderViewBenefitInfo;
        ?>
        <!-- 쿠폰/할인/혜택 정보 -->
    </div>
</div>
<?php } ?>
<form id="frmRefund" name="frmRefund" action="./order_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="refund_complete"/>
    <input type="hidden" name="orderNo" value="<?= $data['orderNo'] ?>"/>
    <input type="hidden" name="handleSno" value="<?= $handleSno ?>"/>
    <input type="hidden" name="isAll" value="<?= $isAll ?>"/>
    <div class="table-title gd-help-manual">환불 금액 정보</div>
    <div class="table-responsive">
        <table id="refundGoodsInfo" class="table table-rows">
            <thead>
            <tr>
                <th class="width3p">번호</th>
                <th class="width5p">상품<br/>주문번호</th>
                <th class="width-3xs">이미지</th>
                <th>주문상품</th>
                <th class="width3p">수량</th>
                <th class="width5p">판매가</th>
                <th class="width5p">매입가</th>
                <th class="width5p">할인금액</th>
                <th class="width5p">사용<br><?=$depositUse['name']?></th>
                <th class="width5p">사용<br><?=$mileageUse['name']?></th>
                <th class="width5p">결제금액</th>
                <th class="width5p">배송비<br>할인금액</th>
                <th class="width5p">배송비<br>실 결제금액</th>
                <th class="width5p">적립<br><?=$mileageUse['name']?></th>
                <th class="width5p">사용<br><?=$depositUse['name']?> 환불</th>
                <th class="width5p">배송비사용<br><?=$depositUse['name']?> 환불</th>
                <th class="width5p">사용<br><?=$mileageUse['name']?> 환불</th>
                <th class="width5p">배송비사용<br><?=$mileageUse['name']?> 환불</th>
                <th class="width5p">해외배송<br>보험료</th>
                <th class="width5p">배송비<br>환불</th>
                <th class="width5p">적립<br><?=$mileageUse['name']?> 차감</th>
                <th class="width5p">환불수수료</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (isset($data['goods']) === true) {
                $sortNo = $data['cnt']['goods']['goods'];// 번호 설정
                $totalGoodsPrice = 0;
                $totalDcPrice = 0;
                $totalDeliveryDcPrice = 0;
                $totalUseDeposit = 0;
                $totalUseMileage = 0;
                $totalSettlePrice = 0;
                $totalDeliveryCharge = 0;
                $totalDeliveryInsuranceFee = 0;
                $totalGiveMileage = 0;
                $totalRefundUseDeposit = 0;
                $totalRefundUseMileage = 0;
                $totalRefundGiveMileage = 0;
                $totalRefundCharge = 0;
                $totalCompleteCashPrice = 0;
                $totalCompletePgPrice = 0;
                $totalCompleteDepositPrice = 0;
                $totalCompleteMileagePrice = 0;
                $refundGroupCd = 0;
                $rowAll = 0;
//                $userHandleSno = '';
                foreach ($data['goods'] as $sKey => $sVal) {
                    $rowScm = 0;
                    foreach ($sVal as $dKey => $dVal) {
                        $rowDelivery = 0;
                        foreach ($dVal as $key => $val) {
                            // 하단의 환불 방법 설정내 들어갈 내용 설정
                            if ($handleSno == $val['handleSno']) {
                                $handleData = $val;
                            }

                            // 결제금액
                            $settlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['totalGoodsDivisionUseDeposit'] - $val['totalGoodsDivisionUseMileage'] - $val['totalDivisionCouponOrderDcPrice'] - $val['totalMyappDcPrice'];

                            // 주문상태 모드 (한자리)
                            $statusMode = substr($val['orderStatus'], 0, 1);

                            // 합계금액 계산
                            $totalGoodsPrice += ($val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])) + $val['addGoodsPrice'];
                            $totalCostPrice += ($val['goodsCnt'] * ($val['costPrice'] + $val['optionCostPrice']));
                            $totalDcPrice += $val['goodsDcPrice'] + $val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice'] + $val['totalCouponGoodsDcPrice'] + $val['totalDivisionCouponOrderDcPrice'] + $val['totalMyappDcPrice'];
                            $totalSettlePrice += $settlePrice;
                            $totalGoodsUseDeposit += $val['totalGoodsDivisionUseDeposit'];
                            $totalGoodsUseMileage += $val['totalGoodsDivisionUseMileage'];
                            $totalDeliveryUseDeposit += $val['divisionGoodsDeliveryUseDeposit'];
                            $totalDeliveryUseMileage += $val['divisionGoodsDeliveryUseMileage'];
                            $totalUseDeposit += $val['totalGoodsDivisionUseDeposit'] + $val['divisionGoodsDeliveryUseDeposit'];
                            $totalUseMileage += $val['totalGoodsDivisionUseMileage'] + $val['divisionGoodsDeliveryUseMileage'];
                            $totalGiveMileage += $val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'];
                            $totalRefundDeliveryCharge += $val['refundDeliveryCharge'];
                            $totalRefundUseDeposit += $val['refundUseDeposit'];
                            $totalRefundUseMileage += $val['refundUseMileage'];
                            $totalRefundGiveMileage += $val['refundGiveMileage'];
                            $totalRefundCharge += $val['refundCharge'];

                            // 환불 금액 설정 합계
                            // 계산 오류로 인해 제거 && ($userHandleSno != $val['userHandleSno'])
                            // TODO 이상없으면 userHandleSno 코드 제거 처리
                            if ($refundGroupCd != $val['refundGroupCd']) {
                                $totalCompleteCashPrice += $val['completeCashPrice'];
                                $totalCompletePgPrice += $val['completePgPrice'];
                                $totalCompleteDepositPrice += $val['completeDepositPrice'];
                                $totalCompleteMileagePrice += $val['completeMileagePrice'];
                                $refundGroupCd = $val['refundGroupCd'];
//                                $userHandleSno = $val['userHandleSno'];
                            }

                            // rowspan 처리
                            $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';
                            $orderScmRowSpan = ' rowspan="' . ($data['cnt']['scm'][$sKey]) . '"';
                            $orderDeliveryRowSpan = ' rowspan="' . ($data['cnt']['delivery'][$dKey]) . '"';

                            // 기본 배송업체 설정
                            if (empty($val['deliverySno']) === true) {
                                $val['orderDeliverySno'] = $deliverySno;
                            }
                            if ($val['totalMemberDeliveryDcPrice'] > 0) {
                                $val['realDeliveryCharge'] -= $val['deliveryPolicyCharge'];
                            }
                            ?>
                            <tr id="statusCheck_<?= $statusMode ?>_<?= $val['sno'] ?>" class="text-center nowrap">
                                <td <?= $orderAddGoodsRowSpan ?>>
                                    <input type="hidden" name="refund[<?=$val['handleSno']?>][sno]" value="<?= $val['handleSno'] ?>"/>
                                    <input type="hidden" name="refund[<?=$val['handleSno']?>][returnStock]" value="n"/>
                                    <?= $sortNo ?>
                                </td>
                                <td <?= $orderAddGoodsRowSpan ?> class="font-num"><?= $val['sno'] ?></td>
                                <td>
                                    <?php if ($val['goodsType'] === 'addGoods') { ?>
                                        <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                    <?php } else { ?>
                                        <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank') ?>
                                    <?php } ?>
                                </td>
                                <td class="text-left">
                                    <?php if ($val['goodsType'] === 'addGoods') { ?>
                                        <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                        <a title="추가 상품명" onclick="addgoods_register_popup('<?= $val['goodsNo'] ?>');"><strong><?= gd_html_cut($val['goodsNm'], 46, '..') ?></strong></a>
                                    <?php } else { ?>
                                        <a href="#" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo'] ?>');"><strong><?= $val['goodsNm'] ?></strong></a>
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
                                                    echo '<span>(추가금 ' . gd_currency_display($oVal['optionTextPrice']) . ')</span>';
                                                }
                                                echo '</li>';
                                                echo '</ul>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="text-center"><?= number_format($val['goodsCnt']) ?></td>
                                <td class="text-right"><?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) ?></td>
                                <td class="text-right"><?= gd_currency_display(($val['costPrice'] + $val['optionCostPrice']) * $val['goodsCnt']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['goodsDcPrice'] + $val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice'] + $val['totalCouponGoodsDcPrice'] + $val['totalDivisionCouponOrderDcPrice'] + $val['totalMyappDcPrice']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalGoodsDivisionUseDeposit']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalGoodsDivisionUseMileage']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($settlePrice) ?></td>
                                <?php if (($rowDelivery == 0 && $data['mallSno'] == DEFAULT_MALL_NUMBER) || ($rowAll == 0 && $data['mallSno'] > DEFAULT_MALL_NUMBER)) { ?>
                                    <td <?= $orderDeliveryRowSpan ?> class="text-right">
                                        <?php
                                        $divisionDeliveryCharge = $val['divisionDeliveryCharge'];
                                        if ($val['totalMemberDeliveryDcPrice'] > 0) {
                                            $divisionDeliveryCharge += $val['deliveryPolicyCharge'];
                                        }
                                        echo gd_currency_display($divisionDeliveryCharge);
                                        ?>
                                    </td>
                                    <td <?= $orderDeliveryRowSpan ?> class="text-right">
                                        <?php
                                        $realDeliveryCharge = $val['deliveryCharge'] - $val['divisionDeliveryCharge'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
                                        if ($val['totalMemberDeliveryDcPrice'] > 0) {
                                            $realDeliveryCharge -= $val['deliveryPolicyCharge'];
                                        }
                                        if ($realDeliveryCharge < 0) {
                                            $realDeliveryCharge = 0;
                                        }
                                        echo gd_currency_display($realDeliveryCharge);
                                        ?>
                                    </td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'])?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="form-inline default text-right border-left">
                                    <?php if ($val['totalGoodsDivisionUseDeposit'] > 0) { ?>
                                        <?php if ($statusFl) { ?>
                                            <input type="hidden" name="refund[<?=$val['handleSno']?>][refundUseDeposit]" class="form-control text-right input-sm width-2xs" value="<?=gd_money_format($val['totalGoodsDivisionUseDeposit'], false)?>"  data-original="<?=gd_money_format($val['totalGoodsDivisionUseDeposit'], false)?>">
                                            <?=gd_currency_display($val['totalGoodsDivisionUseDeposit'])?>
                                            <?php
                                        } else {
                                            echo gd_currency_display($val['refundUseDeposit']);
                                        }
                                        ?>
                                    <?php } else { ?>
                                        -
                                    <?php } ?>
                                </td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right default"><?= gd_currency_display($val['divisionGoodsDeliveryUseDeposit']) ?></td>
                                <td <?= $orderAddGoodsRowSpan ?> class="form-inline default text-right">
                                    <?php if ($val['totalGoodsDivisionUseMileage'] > 0) { ?>
                                        <?php if ($statusFl) { ?>
                                            <input type="hidden" name="refund[<?=$val['handleSno']?>][refundUseMileage]" class="form-control text-right input-sm width-2xs" value="<?=gd_money_format($val['totalGoodsDivisionUseMileage'], false)?>" data-original="<?=gd_money_format($val['totalGoodsDivisionUseMileage'], false)?>">
                                            <?=gd_currency_display($val['totalGoodsDivisionUseMileage'])?>
                                            <?php
                                        } else {
                                            echo gd_currency_display($val['refundUseMileage']);
                                        }
                                        ?>
                                    <?php } else { ?>
                                        -
                                    <?php } ?>
                                </td>
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right default"><?= gd_currency_display($val['divisionGoodsDeliveryUseMileage']) ?></td>
                                <?php if (($rowDelivery == 0 && $data['mallSno'] == DEFAULT_MALL_NUMBER) || ($rowAll == 0 && $data['mallSno'] > DEFAULT_MALL_NUMBER)) { ?>
                                    <td <?= $orderDeliveryRowSpan ?> class="text-right"><?= gd_currency_display($val['deliveryInsuranceFee']) ?></td>
                                <?php } ?>
                                <?php if ($statusFl) { ?>
                                    <?php if (($rowDelivery == 0 && $data['mallSno'] == DEFAULT_MALL_NUMBER) || ($rowAll == 0 && $data['mallSno'] > DEFAULT_MALL_NUMBER)) { ?>
                                        <td <?= $orderDeliveryRowSpan ?> class="form-inline default text-right">
                                            <?php if ($val['deliveryCharge'] > 0) { ?>
                                                <?=gd_currency_symbol()?>
                                                <input type="text" name="refund[<?=$val['handleSno']?>][refundDeliveryCharge]" class="form-control text-right text-blue input-sm width-2xs" value="<?=gd_money_format($val['realDeliveryCharge'], false)?>" data-original="<?=gd_money_format($val['realDeliveryCharge'], false)?>">
                                                <?=gd_currency_string()?>
                                            <?php } else { ?>
                                                -
                                            <?php } ?>
                                        </td>
                                    <?php } ?>
                                <?php } else { ?>
                                    <td <?= $orderAddGoodsRowSpan ?> class="form-inline default text-right">
                                        <?php if ($val['deliveryCharge'] > 0) { ?>
                                            <?= gd_currency_display($val['refundDeliveryCharge']); ?>
                                        <?php } else { ?>
                                            -
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                <td <?= $orderAddGoodsRowSpan ?> class="form-inline default text-right">
                                    <?php
                                    $goodsTotalMileage = $val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'];
                                    if ($goodsTotalMileage > 0) { ?>
                                        <?php if ($statusFl) { ?>
                                            <input type="hidden" name="refund[<?=$val['handleSno']?>][originGiveMileage]" value="<?=$goodsTotalMileage?>">
                                            <?=gd_currency_symbol()?>
                                            <input type="text" name="refund[<?=$val['handleSno']?>][refundGiveMileage]" class="form-control text-right text-danger input-sm width-2xs" value="<?=$goodsTotalMileage?>" data-original="<?=$goodsTotalMileage?>">
                                            <?=gd_currency_string()?>
                                            <?php
                                        } else {
                                            echo gd_currency_display($val['refundGiveMileage']);
                                        }
                                        ?>
                                    <?php } else { ?>
                                        -
                                    <?php } ?>
                                </td>
                                <td <?= $orderAddGoodsRowSpan ?> class="form-inline default text-right">
                                    <?php if ($statusFl) { ?>
                                        <?=gd_currency_symbol()?>
                                        <input type="text" name="refund[<?=$val['handleSno']?>][refundCharge]" class="form-control text-right text-danger input-sm width-2xs" value="0">
                                        <?=gd_currency_string()?>
                                        <?php
                                    } else {
                                        echo gd_currency_display($val['refundCharge']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            if ($val['addGoodsCnt'] > 0) {
                                foreach ($val['addGoods'] as $aVal) {
                                    ?>
                                    <tr class="text-center add-goods">
                                        <td class="text-center">
                                            <span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span></td>
                                        <td class="text-left">
                                            <?= gd_html_add_goods_image($aVal['addGoodsNo'], $aVal['imageNm'], $aVal['imagePath'], $aVal['imageStorage'], 30, $aVal['goodsNm'], '_blank'); ?>
                                            <div class="goods_name one-line hand" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo'] ?>');"><?= gd_html_cut($aVal['goodsNm'], 46, '..') ?>
                                                <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..') ?>)</small>
                                            </div>
                                        </td>
                                        <td class="goods_cnt"><?= number_format($aVal['goodsCnt']) ?></td>
                                        <td class="text-right"><?= gd_currency_display($aVal['goodsPrice'] * $aVal['goodsCnt']) ?></td>
                                    </tr>
                                    <?php
                                }
                            }

                            if (empty($val['orderDeliverySno']) === false) {
                                $escrowDelivery = $val['orderDeliverySno'];
                            }
                            if (empty($val['invoiceNo']) === false) {
                                $escrowInvoiceNo = $val['invoiceNo'];
                            }

                            $sortNo--;
                            $rowDelivery++;
                            $rowScm++;
                            $rowAll++;
                        }

                        // 배송비 합계 (주문상품 테이블 기준)
                        $totalDeliveryCharge += $val['deliveryCharge'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
                        $totalRealDeliveryCharge += $val['realDeliveryCharge'];
                        $totalDeliveryInsuranceFee += $val['deliveryInsuranceFee'];

                        // 환불예정금액 및 실환불금액에 실제 환불된 배송비 포함 처리
                        if ($statusFl) {
                            $totalDeliveryCharge -= $val['refundDeliveryCharge'];
                        }

                        // 배송비 합계 (배송비 테이블 기준)
                        $totalDeliveryDcPrice += $val['divisionDeliveryCharge'];
                        if ($val['totalMemberDeliveryDcPrice'] > 0) {
                            $totalDeliveryDcPrice += $val['deliveryPolicyCharge'];
                        }
                        $totalRealDelivery = $totalDeliveryCharge - $totalDeliveryDcPrice;
                        if ($totalRealDelivery < 0 ) {
                            $totalRealDelivery = 0;
                        }
                    }
                }
            }
            ?>
            </tbody>
            <tfoot>
            <tr class="nowrap">
                <th colspan="5" class="text-right">합계금액</th>
                <td class="text-right"><?=gd_currency_display($totalGoodsPrice)?></td>
                <td class="text-right"><?=gd_currency_display($totalCostPrice)?></td>
                <td class="text-right"><?=gd_currency_display($totalDcPrice)?></td>
                <td class="text-right"><?=gd_currency_display($totalGoodsUseDeposit)?></td>
                <td class="text-right"><?=gd_currency_display($totalGoodsUseMileage)?></td>
                <td class="text-right"><?=gd_currency_display($totalSettlePrice)?></td>
                <td class="text-right"><?=gd_currency_display($totalDeliveryDcPrice)?></td>
                <td class="text-right"><?=gd_currency_display($totalRealDelivery)?></td>
                <td class="text-right"><?=gd_currency_display($totalGiveMileage)?></td>
                <td class="text-right form-inline border-left" colspan="2">
                    <input type="hidden" name="check[totalUseDeposit]" value="0">
                    <?php if ($statusFl) { ?>
                        <input type="hidden" name="tmp[totalUseDeposit]" disabled="disabled" class="form-control text-right input-sm width-2xs" value="<?= gd_money_format($totalUseDeposit, false) ?>" data-original="<?= gd_money_format($totalUseDeposit, false) ?>">
                        <?=gd_currency_display($totalUseDeposit)?>
                        <?php
                    } else {
                        echo gd_currency_display($totalRefundUseDeposit);
                    }
                    ?>
                </td>
                <td class="form-inline text-right" colspan="2">
                    <?php if ($statusFl) { ?>
                        <input type="hidden" name="check[totalUseMileage]" value="0">
                        <input type="hidden" name="tmp[totalUseMileage]" disabled="disabled" class="form-control text-right input-sm width-2xs" value="<?=gd_money_format($totalUseMileage, false)?>" data-original="<?=gd_money_format($totalUseMileage, false)?>">
                        <?=gd_currency_display($totalUseMileage)?>
                        <?php
                    } else {
                        echo gd_currency_display($totalRefundUseMileage);
                    }
                    ?>
                </td>
                <td class="form-inline text-right">
                    <?php if ($statusFl) { ?>
                        <input type="hidden" name="check[totalDeliveryInsuranceFee]" value="0">
                        <?=gd_currency_symbol()?>
                        <input type="text" name="tmp[totalDeliveryInsuranceFee]" disabled="disabled" class="form-control text-right text-blue input-sm width-2xs" value="<?=gd_money_format($totalDeliveryInsuranceFee, false)?>" data-original="<?=gd_money_format($totalDeliveryInsuranceFee, false)?>">
                        <?=gd_currency_string()?>
                        <?php
                    } else {
                        echo gd_currency_display($totalDeliveryInsuranceFee);
                    }
                    ?>
                </td>
                <td class="form-inline text-right">
                    <?php if ($statusFl) { ?>
                        <input type="hidden" name="check[totalDeliveryCharge]" value="0">
                        <?=gd_currency_symbol()?>
                        <input type="text" name="tmp[totalDeliveryCharge]" disabled="disabled" class="form-control text-right text-blue input-sm width-2xs" value="<?=gd_money_format($totalRealDeliveryCharge, false)?>" data-original="<?=gd_money_format($totalRealDeliveryCharge, false)?>">
                        <?=gd_currency_string()?>
                        <?php
                    } else {
                        echo gd_currency_display($totalRefundDeliveryCharge);
                    }
                    ?>
                </td>
                <td class="form-inline text-right">
                    <?php if ($statusFl) { ?>
                        <input type="hidden" name="check[totalGiveMileage]" value="0">
                        <?=gd_currency_symbol()?>
                        <input type="text" name="tmp[totalGiveMileage]" disabled="disabled" class="form-control text-right text-red input-sm width-2xs" value="<?=gd_money_format($totalGiveMileage, false)?>" data-original="<?=gd_money_format($totalGiveMileage, false)?>">
                        <?=gd_currency_string()?>
                        <?php
                    } else {
                        echo gd_currency_display($totalRefundGiveMileage);
                    }
                    ?>
                </td>
                <td class="form-inline text-right">
                    <?php if ($statusFl) { ?>
                        <?=gd_currency_symbol()?>
                        <input type="text" name="tmp[totalRefundCharge]" disabled="disabled" class="form-control text-right text-red input-sm width-2xs" value="0" data-original="0">
                        <?=gd_currency_string()?>
                    <?php } else {
                        echo gd_currency_display($totalRefundCharge);
                    }
                    ?>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>

    <input type="hidden" name="tmp[refundMinusMileage]" value="y">
    <input type="hidden" name="tmp[memberMileage]" value="<?=$memInfo['mileage']?>">

    <?php if ($statusFl) {
        // $totalRealDeliveryCharge 배송비는 환불완료 여부에 따라 변화될 수 있는 값으로 실제 배송비로 가져와 계산한다.
        $realRefundPrice = $totalSettlePrice + $totalDeliveryInsuranceFee + $totalDeliveryCharge - ($totalDeliveryCharge - $totalRealDeliveryCharge);
    } else {
        $realRefundPrice = $totalSettlePrice + $totalDeliveryInsuranceFee + ($totalDeliveryCharge - $totalRealDeliveryCharge);
    }
    ?>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col class="width-xl"/>
            <col class="width-md"/>
            <col class="width-xl"/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>환불예정금액</th>
            <td>
                <?php if ($statusFl) { ?>
                    <input type="hidden" name="check[totalSettlePrice]" value="<?= $realRefundPrice ?>"/>
                    <?= gd_currency_symbol() ?>
                    <span id="firstRefundPrice"><?= gd_money_format($realRefundPrice) ?></span>
                    <?= gd_currency_string() ?>
                    <?php
                } else {
                    echo gd_currency_display($realRefundPrice - $totalDeliveryDcPrice);
                }
                ?>
            </td>
            <th>총 환불수수료</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <?=gd_currency_symbol()?>
                    <input type="text" name="check[totalRefundCharge]" readonly="readonly" class="form-control text-right input-sm width-xs" value="0">
                    <?=gd_currency_string()?>
                    <?php
                } else {
                    echo gd_currency_display($totalRefundCharge);
                }
                ?>
            </td>
            <th>실 환불금액</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <input type="hidden" name="check[totalRefundPrice]" value="0">
                    <?=gd_currency_symbol()?>
                    <input type="text" name="realRefundPrice" disabled="disabled" class="form-control text-right input-sm width-xs" value="<?=gd_money_format($realRefundPrice, false)?>">
                    <?=gd_currency_string()?>
                    <?php
                } else {
                    echo gd_currency_display($realRefundPrice - $totalDeliveryDcPrice - $totalRefundCharge);
                }
                ?>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">환불 부가 정보</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th><?=$depositUse['name']?> 정보</th>
            <td>
                이 주문 결제 시 사용한 <?=$depositUse['name']?> 잔여 금액 :
                (최초 사용금액 <?=gd_money_format($data['useDeposit']) . $depositUse['unit']?> / 환불금액 / 고객의 현재 <?=$depositUse['name']?> 총액 <?=gd_money_format($memInfo['deposit']) . $depositUse['unit']?>)
            </td>
        </tr>
        <tr>
            <th><?=$mileageUse['name']?> 정보</th>
            <td>
                이 주문 결제 시 사용한 <?=$mileageUse['name']?> 잔여 금액 :
                (최초 사용금액 <?=gd_money_format($data['useMileage']) . $mileageUse['unit']?> / 환불금액 / 고객의 현재 <?=$mileageUse['name']?> 총액 <?=gd_money_format($memInfo['mileage']) . $mileageUse['unit']?>)
            </td>
        </tr>
        <?php if (empty($data['isDefaultMall']) === true) { ?>
            <tr>
                <th>해외배송 보험료</th>
                <td>
                    <?= gd_currency_display($data['totalDeliveryInsuranceFee']); ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th>배송비 정보</th>
            <td>
                <p>최초 결제 배송비 : <?=gd_currency_display($data['totalDeliveryCharge'])?></p>
                <p>추가 발생 배송비</p>
            </td>
        </tr>
        <tr>
            <th>
                쿠폰 정보
                <?php if ($statusFl) { ?>
                    <button type="button" class="btn btn-sm btn-black js-restore-coupon">사용 쿠폰복원</button>
                <?php } ?>
            </th>
            <td>
                <?php if ($statusFl) { ?>
                    <div id="selectedCoupon" class="selected-btn-group"></div>
                <?php } else { ?>
                    <?php
                    if ($orderCoupon !== false) {
                        foreach ($orderCoupon as $coupon) {
                            switch ($coupon['couponUseType']) {
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
                            switch ($coupon['couponKindType']) {
                                case 'sale':
                                    $couponKindType = '상품할인';
                                    break;
                                case 'add':
                                    $couponKindType = '마일리지적립';
                                    break;
                                case 'delivery':
                                    $couponKindType = '배송비할인';
                                    break;
                            }
                            echo $couponUseType . '(' . $couponKindType . ') : ' . $coupon['couponNm'] . ' (' . number_format($coupon['couponBenefit']) . ($coupon['couponBenefitType'] == 'fix' ? gd_currency_string() : '%') . ')<br>';
                        }
                    } else {
                        echo '복원된 쿠폰이 없습니다.';
                    }
                }
                ?>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="table-title gd-help-manual">환불 방법 설정</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col class="width-xl"/>
            <col class="width-md"/>
            <col >
        </colgroup>
        <tr>
            <th>환불수단</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <?= gd_select_box(null, 'info[refundMethod]', $refundMethod, null, $handleData['refundMethod'], null) ?>
                    <?php
                } else {
                    echo $handleData['refundMethod'];
                }
                ?>
            </td>
            <th>실 환불금액</th>
            <td>
                <?php if ($statusFl) { ?>
                    <?=gd_currency_symbol()?><span id="txtRealRefundPrice"><?=gd_money_format($realRefundPrice, false);?></span><?=gd_currency_string()?>
                    <?php
                } else {
                    echo gd_currency_display($realRefundPrice - $totalDeliveryDcPrice - $totalRefundCharge);
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>환불 금액 설정</th>
            <td colspan="3" class="form-inline">
                <dl class="dl-horizontal mgb5">
                    <dt>환불 금액 합계</dt>
                    <dd class="form-inline">
                        <?php if ($statusFl) { ?>
                            <?=gd_currency_symbol()?>
                            <input type="text" name="userRealRefundPrice" disabled="disabled" class="form-control text-right input-sm width-xs" value="0">
                            <?=gd_currency_string()?>
                            <span class="notice-info">환불 금액 합계는 "실 환불금액"과 동일해야 환불완료처리가 가능합니다.</span>
                            <?php
                        } else {
                            echo gd_currency_display($realRefundPrice - $totalDeliveryDcPrice - $totalRefundCharge);
                        }
                        ?>
                    </dd>
                </dl>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <label class="control-label" id="completeCashPrice">
                            현금환불
                            <?php if ($statusFl) { ?>
                                <input type="text" class="form-control text-right js-number" name="info[completeCashPrice]" value="0" />
                                <?php
                            } else {
                                echo gd_currency_display($totalCompleteCashPrice);
                            }
                            ?>
                        </label>
                        <label class="control-label pdl10" id="completePgPrice">
                            PG환불
                            <?php if ($statusFl) { ?>
                                <input type="text" class="form-control text-right js-number" name="info[completePgPrice]" value="0" />
                                <?php
                            } else {
                                echo gd_currency_display($totalCompletePgPrice);
                            }
                            ?>
                        </label>
                        <label class="control-label pdl10" id="completeDepositPrice">
                            예치금환불
                            <?php if ($statusFl) { ?>
                                <input type="text" class="form-control text-right js-number" name="info[completeDepositPrice]" value="0" />
                                <?php
                            } else {
                                echo gd_currency_display($totalCompleteDepositPrice);
                            }
                            ?>
                        </label>
                        <label class="control-label pdl10" id="completeMileagePrice">
                            기타환불
                            <?php if ($statusFl) { ?>
                                <input type="text" class="form-control text-right js-number" name="info[completeMileagePrice]" value="0" />
                                <?php
                            } else {
                                echo gd_currency_display($totalCompleteMileagePrice);
                            }
                            ?>
                        </label>

                        <div id="completeRestPrice" class="notice-info">
                            남아있는 금액 <span class="rest-price">0</span>원
                        </div>
                    </div>
                </div>
                <p id="infoCompletePgPrice" class="notice-danger">PG환불: 계약된 PG사로 입력된 금액만큼 카드 승인취소 요청이 됩니다.</p>
                <p class="payco-notice notice-danger display-none">페이코를 통한 바로이체 결제건의 부분취소는, 주문취소 상태만 연동되며 실제환불은 별도로 구매자에게 지급하셔야 합니다.</p>
                <p id="infoCompleteDepositPrice" class="notice-danger">예치금환불: 입력된 금액만큼의 예치금이 회원에게 자동 지급됩니다.</p>
                <p id="infoCompleteMileagePrice" class="notice-danger">기타환불: 입력된 금액만큼 운영자가 별도로 구매자에게 지급해야 합니다.</p>
                <p class="payco-notice-msg notice-danger display-none">페이코 결제에 대해 "기타 환불"을 선택하시면, 환불 처리 연동되지 않으므로 실제환불은 별도로 구매자에게 지급하셔야 합니다.</p>
            </td>
        </tr>
        <tr>
            <th>환불사유</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <?= gd_select_box(null, 'info[handleReason]', $cancelReason, null, $handleData['handleReason'], null) ?>
                    <?php
                } else {
                    echo $handleData['handleReason'];
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>환불 상세 사유</th>
            <td colspan="5">
                <?php if ($statusFl) { ?>
                    <textarea name="info[handleDetailReason]" rows="5" class="form-control"><?= gd_isset($handleData['handleDetailReason']) ?></textarea>
                    <?php
                } else {
                    echo $handleData['handleDetailReason'];
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>환불 계좌정보</th>
            <td class="form-inline" colspan="5">
                <?php if ($statusFl) { ?>
                    <?= gd_select_box(null, 'info[refundBankName]', $bankNm, null, $handleData['refundBankName'], '= 은행 선택 =') ?>
                    <label class="control-label">계좌번호 :</label>
                    <input type="text" name="info[refundAccountNumber]" value="<?=$handleData['refundAccountNumber']?>" class="form-control width-lg js-number" maxlength="30"/>
                    <label class="control-label">예금주 :</label>
                    <input type="text" name="info[refundDepositor]" value="<?= $handleData['refundDepositor'] ?>" class="form-control width-2xs"/>
                    <?php
                } else {
                    echo $handleData['refundBankName'] . ' / ' . $handleData['refundAccountNumber'] . ' / ' . $handleData['refundDepositor'];
                }
                ?>
            </td>
        </tr>
    </table>

    <div class="row">
        <div class="col-xs-12">
            <div class="table-title gd-help-manual">관리자메모</div>

            <table class="table table-rows mgb5">
                <colgroup>
                    <col class="width10p" />
                    <col class="width10p" />
                    <col class="width15p" />
                    <col class="width15p" />
                    <col class="width30p" />
                    <col class="width15p" />
                </colgroup>
                <thead>
                <tr>
                    <th>작성일</th>
                    <th>작성자</th>
                    <th>메모 구분</th>
                    <th>상품주문번호</th>
                    <th>메모 내용</th>
                    <th>관리</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (empty($memoData) === false) {
                foreach ($memoData as $mKey => $mVal) {
                ?>
                <tbody id="orderGoodsMemoData<?= $mKey; ?>">
                <tr data-mall-sno="<?= $mVal['mallSno'] ?>">
                    <td class="text-center"><span><?php if ($mVal['modDt']) { echo $mVal['modDt']; } else { echo $mVal['regDt']; } ?></span></td>
                    <td class="text-center">
                        <span class="managerId"><?= $mVal['managerId']; ?></span><br/>
                        <span class="managerNm">(<?= $mVal['managerNm']; ?>)</span>
                    </td>
                    <td class="text-center">
                        <span class="itemNm"><?= $mVal['itemNm']; ?></span>
                    </td>
                    <td class="text-center"><span class="orderGoodsSno"><?php if ($mVal['type'] == 'goods') { echo $mVal['orderGoodsSno']; } else { echo '-'; } ?></span></td>
                    <td>
                        <span class="content-memo"><?= $mVal['content']; ?></span>
                    </td>
                    <td class="text-center">
                        <span class="mod-button" style="padding-bottom: 5px;">
                            <button type="button" class="btn btn-sm btn-gray js-admin-memo-modify" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-memocd="<?=$mVal['memoCd'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-content="<?=$mVal['content'];?>" data-no="<?=$mVal['sno'];?>">수정</button>
                        </span>
                        <span class="del-button">
                            <button type="button" class="btn btn-sm btn-gray js-admin-memo-delete" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-no="<?=$mVal['sno'];?>">삭제</button>
                        </span>
                    </td>
                </tr>
                </tbody>
                <?php
                }
                }else{
                    ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            등록된 내용이 없습니다.
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="center"><?= $page->getPage(); ?></div>
        <?php if (!$isProvider) { ?>
            <div class="col-xs-12">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-sm">
                        <col>
                        <col class="width-sm">
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th>메모 유형</th>
                        <td>
                            <label class="radio-inline">
                                <input type="radio" name="memoType" value="order" <?= gd_isset($checked['memoType']['order']); ?>/>주문번호별
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="memoType" value="goods" <?= gd_isset($checked['memoType']['goods']); ?>/>상품주문번호별
                            </label>
                        </td>
                        <th>메모 구분</th>
                        <td>
                            <?= gd_select_box('orderMemoCd', 'orderMemoCd', $memoCd, null, null, '=메모 구분='); ?>
                        </td>
                    </tr>
                    <tr id="tr_goodsSelect" class="display-none">
                        <th>상품 선택</th>
                        <td colspan="3">
                            <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows table-fixed">
                                <thead>
                                <tr id="orderGoodsList">
                                    <th class="width5p"><input type="checkbox" id="allCheck" value="y"
                                                               onclick="check_toggle(this.id,'itemGoodsNo');"/></th>
                                    <th class="width10p">상품주문번호</th>
                                    <th class="width20p">주문상품</th>
                                    <th class="width15p">처리상태</th>
                                </tr>
                                </thead>
                                <?php
                                foreach($goodsData as $fKey => $fVal) {
                                    ?>
                                    <tbody id="addGoodsList<?= $fKey; ?>">
                                    <tr data-mall-sno="<?=$fVal['mallSno']?>">
                                        <td class="text-center">
                                            <input type="checkbox" name="sno[]" value="<?=$fVal['sno'];?>" >
                                        </td>
                                        <td class="text-center">
                                            <span class="sno"><?=$fVal['sno'];?></span>
                                        </td>
                                        <td>
                                            <span class="goodsNm" style="font-weight: bold;"><?=$fVal['goodsNm'];?></span><br/>
                                            <span class="optionInfo"><?=$fVal['optionInfo'];?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="orderStatus"><?=$fVal['orderStatus'];?></span>
                                        </td>
                                    </tr>
                                    </tbody>
                                    <?php
                                }
                                ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th>메모 내용</th>
                        <td colspan="3">
                            <textarea name="adminOrderGoodsMemo" class="form-control" rows="6"><?=$data['content']?></textarea>
                        </td>
                        <td class="width3p">
                            <button type="button" class="btn btn-red btn-sm js-orderViewInfoSave mgl5" data-submit-mode="adminOrdGoodsMemo">저장</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php }?>
    </div>
</form>

<script type="text/javascript">
    <!--

    /**
     * 마일리지 차감 선택 여부
     */
    var isOpendOnce = false;
    var orderChannelFl = '<?=$data['orderChannelFl']?>';
    var settleKind = '<?=$data['settleKind']?>';
    var orderGoodsCnt = '<?=$data['orderGoodsCnt']?>';

    $(document).ready(function () {
        // 저장버튼 클릭 (버튼과 폼의 위치가 틀려서 별도 정의)
        $('.js-refund-form').click(function(e){
            $('#frmRefund').submit();
        });

        // 합계 구하는 validate 메서드 추가
        $.validator.addMethod("sum", function (value, element, params) {
            var sumOfVals = 0;
            var parent = $(element).closest('td');
            parent.find('input.js-number').each(function () {
                if ($(this).val() == '') $(this).val(0);
                sumOfVals += parseInt($(this).val(), 10);
            });

            if (sumOfVals == params) return true;
            return false;
        }, function (params, element) {
            return '환불금액 설정 합계는 ' + numeral($('input[name="realRefundPrice"]').val()).format() + ' 입니다. 정확하게 설정해주세요.';
        });

        // 폼 체크
        $('#frmRefund').validate({
            debug: true,
            submitHandler: function(form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'info[completeCashPrice]' : {
                    sum: function() {
                        return $('input[name="realRefundPrice"]').val();
                    }
                },
                'info[completePgPrice]' : {
                    sum: function() {
                        return $('input[name="realRefundPrice"]').val();
                    }
                },
                'info[completeDepositPrice]' : {
                    sum: function() {
                        return $('input[name="realRefundPrice"]').val();
                    }
                },
                'info[completeMileagePrice]' : {
                    sum: function() {
                        return $('input[name="realRefundPrice"]').val();
                    }
                },
                'info[handleReason]': {
                    required: true
                },
                'info[refundMethod]': {
                    required: true
                },
                'info[handleDetailReason]': {
                    required: true
                },
                'info[refundBankName]': {
                    required: false
                },
                'info[refundAccountNumber]': {
                    required: false
                },
                'info[refundDepositor]': {
                    required: false
                }
            },
            messages: {
                'info[handleReason]': {
                    required: '환불사유를 선택하세요.'
                },
                'info[refundMethod]': {
                    required: '환불수단을 선택하세요.'
                },
                'info[handleDetailReason]': {
                    required: '환불 상세사유를 작성하세요.'
                },
                'info[refundBankName]': {
                    required: '환불 계좌정보를 선택하세요.'
                },
                'info[refundAccountNumber]': {
                    required: '환불 계좌번호를 입력하세요.'
                },
                'info[refundDepositor]': {
                    required: '환불 계좌의 예금주를 입력하세요.'
                },
            }
        });

        // 상품주문상태 일괄 변경 (체크박스 관련 부분만 폼을 별도로 생성해서 작업 되어진다)
        $('.js-refund-status').click(function (e) {
            if (!$('input[name*=\'bundle[statusCheck]\']:checkbox:checked').length) {
                alert('일괄 처리할 상품을 선택해주세요.');
                return false;
            }
            if ($('#bundleChangeStatus').val() == '') {
                alert('일괄 처리할 상품상태가 선택하지 않았습니다.');
                return false;
            }

            // 확인창 출력
            BootstrapDialog.confirm({
                type: BootstrapDialog.TYPE_WARNING,
                title: '주문상태 변경',
                message: '선택한 상품을 "' + $('#bundleChangeStatus option:selected').html() + '" 상태로 변경하시겠습니까?',
                callback: function (result) {
                    // 확인 버튼 클릭시
                    if (result) {
                        $('#frmRefundStatus').validate({
                            submitHandler: function (form) {
                                form.target = 'ifrmProcess';
                                form.submit();
                            }
                        });
                        $('#frmRefundStatus').submit();
                    }
                }
            });
        });

        // 쿠폰 복원 Ajax layer
        $('.js-restore-coupon').click(function (e) {
            var orderCd = [];
            $('input[name*=\'bundle[orderCd]\']:hidden').each(function(idx){
                orderCd.push($(this).val());
            });

            console.log(orderCd);

            var params = {
                orderNo: '<?= gd_isset($data['orderNo']);?>',
                orderCd: orderCd,
            };
            $.post('layer_restore_coupon.php', params, function (data) {
                layer_popup(data, '복원 쿠폰 선택', 'normal');
            });
        });

        // 재고환원 Ajax layer
        $('.js-return-stock').click(function (e) {
            var checkedGoods = [];

            if (!$('input[name*=\'bundle[statusCheck]\']:checkbox:checked').length) {
                alert('재고환원 할 상품을 체크해주세요.');
                return false;
            } else {
                $('input[name*=\'bundle[statusCheck]\']:checkbox:checked').each(function(idx){
                    checkedGoods.push($(this).val());
                });
            }

            var params = {
                orderNo: '<?= gd_isset($data['orderNo']);?>',
                orderGoodsNo: checkedGoods
            };
            $.post('layer_return_stock.php', params, function (data) {
                layer_popup(data, '재고환원', 'wide');
            });
        });

        // 원래의 금액 데이터로 설정 + 숫자 메서드 적용
        $('#refundGoodsInfo input[type=text]').each(function(idx){
            $(this).number_only();

            // 설정된 금액 이상 설정할 수 없도록 처리
            $(this).blur(function(e){
                if ($(this).val() > $(this).data('original')) {
                    alert(numeral($(this).data('original')).format() + '원 이상 입력하실 수 없습니다.');
                    $(this).val($(this).data('original')).trigger('blur');
                } else {
                    // real_refund_price에서 false 반환시 변경된 값 원복 처리
                    if (real_refund_price() === false) {
                        if ($(this).attr('name').search('refundGiveMileage')) {
                            var params = {
                                refundMinusMileage: $('input[name="tmp[refundMinusMileage]"]').val()
                            }
                            $.post('layer_mileage_method.php', params, function (data) {
                                layer_popup(data, '마일리지 차감 방법 선택');
                            });

                            // 원복처리
                            $(this).val($(this).data('original'));
                            real_refund_price();
                        }
                    }
                }
            });
        });

        // 환불수수료 blur 이벤트
        $('input[name=refundCharge]').blur(function(e){
            real_refund_price();
        });

        // 환불수단 선택에 따른 UI 표현 변경
        $('[name=\'info[refundMethod]\']').change(function (e) {
            // 변경시 모든 값 0으로 초기화 처리
            $('input[name="userRealRefundPrice"]').val(0);
            $('input[name="info[completeCashPrice]"]').val(0);
            $('input[name="info[completePgPrice]"]').val(0);
            $('input[name="info[completeDepositPrice]"]').val(0);
            $('input[name="info[completeMileagePrice]"]').val(0);

            // UI 엘리먼트 정의
            var completeBox = $('#completeCashPrice').closest('.panel'),
                completeCashprice = $('#completeCashPrice'),
                completePgPrice = $('#completePgPrice'),
                infoCompletePgPrice = $('#infoCompletePgPrice'),
                completeDepositPrice = $('#completeDepositPrice'),
                infoCompleteDepositPrice = $('#infoCompleteDepositPrice'),
                completeMileagePrice = $('#completeMileagePrice'),
                completeRestPrice = $('#completeRestPrice');

            switch ($(this).val()) {
                case '현금환불':
                    completeBox.show();
                    completeCashprice.show();
                    completePgPrice.hide();
                    completeDepositPrice.hide();
                    completeMileagePrice.hide();
                    completeRestPrice.hide();
                    infoCompletePgPrice.hide();
                    infoCompleteDepositPrice.hide();
                    break;

                case 'PG환불':
                    completeBox.show();
                    completeCashprice.hide();
                    completePgPrice.show();
                    completeDepositPrice.hide();
                    completeMileagePrice.hide();
                    completeRestPrice.hide();
                    infoCompletePgPrice.show();
                    infoCompleteDepositPrice.hide();

                    // 페이코 처리
                <?php
                if(isset($paycoCoupon) && $paycoCoupon > 0) {
                ?>
                    if(<?=$paycoCoupon?> > $('[name=\'check[totalSettlePrice]\']').val()) {
                    $(this).find('option:first-child').prop('selected', true).end().trigger('liszt:updated');
                    $('[name=\'info[completePgPrice]\']').attr('disabled', 'disabled').val('');
                    alert('환불예정금액이 페이코 쿠폰 사용액(<?=number_format($paycoCoupon)?>원)보다 작아 PG환불을 진행할 수 없습니다.');
                    return;
                }
                <?php
                } else if(isset($firstHand) && $firstHand == 'Y') {
                ?>
                    $(this).find('option:first-child').prop('selected', true).end().trigger('liszt:updated');
                    $('[name=\'info[completePgPrice]\']').attr('disabled', 'disabled').val('');
                    alert('수기환불된 내역이 있어 PG환불을 진행할 수 없습니다.');
                    return;
                <?php
                }
                ?>
                    break;

                case '예치금환불':
                    completeBox.show();
                    completeCashprice.hide();
                    completePgPrice.hide();
                    completeDepositPrice.show();
                    completeMileagePrice.hide();
                    completeRestPrice.hide();
                    infoCompletePgPrice.hide();
                    infoCompleteDepositPrice.show();
                    break;

                case '기타환불':
                    completeBox.show();
                    completeCashprice.hide();
                    completePgPrice.hide();
                    completeDepositPrice.hide();
                    completeMileagePrice.show();
                    completeRestPrice.hide();
                    infoCompletePgPrice.hide();
                    infoCompleteDepositPrice.hide();
                    break;

                case '복합환불':
                    completeBox.show();
                    completeCashprice.show();
                    completePgPrice.show();
                    completeDepositPrice.show();
                    completeMileagePrice.show();
                    completeRestPrice.show();
                    infoCompletePgPrice.show();
                    infoCompleteDepositPrice.show();
                    break;

                default:
                    completeBox.hide();
                    completeCashprice.hide();
                    completePgPrice.hide();
                    completeDepositPrice.hide();
                    completeMileagePrice.hide();
                    completeRestPrice.hide();
                    infoCompletePgPrice.hide();
                    infoCompleteDepositPrice.hide();
                    break;
            }
        });

        // 사용자 환불금액 blur 이벤트
        $('input[name*="info[complete"]').blur(function(e){
            user_refund_price();

            var realRefundPrice = parseInt($('input[name="realRefundPrice"]').val());
            var userRefundPrice = parseInt($('input[name="userRealRefundPrice"]').val());

            if ($('[name=\'info[refundMethod]\']').val() != '복합환불') {
                if (realRefundPrice !== userRefundPrice) {
                    alert('환불 금액 합계는 "실 환불금액"과 동일해야 환불진행이 가능합니다.');
                    $(this).val(0);
                }
            }

            user_refund_price();
        });

        $('select[name="info[refundMethod]"]').change(function(){
            payco_notice_msg(orderChannelFl, $(this).val());
        });

        real_refund_price();
        refund_method_set(orderChannelFl, settleKind);
        $('[name=\'info[refundMethod]\']').trigger('change');

        // 상품 주문번호별 일때 상품선택 노출
        $('input:radio[name="memoType"]').click(function () {
            if($(this).val() == 'goods'){
                $('#tr_goodsSelect').removeClass('display-none');
            }else{
                $('#tr_goodsSelect').addClass('display-none');
            }
        });

        // 메모 수정
        $('.js-admin-memo-modify').click(function () {
            if($(this).data('manager-sno') == $(this).data('m-sno')) {

                // 수정 모드로 변경
                $('input[name="mode"]').attr('value', 'admin_order_goods_memo_modify');
                $('input[name="no"]').attr('value',$(this).data('no'));
                $("input:radio[name=memoType]:radio[value=\'" + $(this).data('type') + "\']").prop('checked', true);
                $("input:checkbox[name=\"sno[]\"][value=\'" + $(this).data('sno') + "\']").attr("checked", true);
                $("#orderMemoCd").val($(this).data('memocd')).prop("selected", true);
                $("textarea[name='adminOrderGoodsMemo']").val($(this).data('content'));

                if ($(this).data('type') == 'order') {
                    $('#tr_goodsSelect').addClass('display-none');
                } else {
                    $('#tr_goodsSelect').removeClass('display-none');
                }
            }else{
                alert('메모를 등록한 관리자만 수정가능합니다.');
                return false;
            }
        });

        // 메모 삭제
        $('.js-admin-memo-delete').click(function () {
            if($(this).data('manager-sno') == $(this).data('m-sno')) {
                //var orderGoodsSno = $(this).data('sno');
                //var memoType = $(this).data('type');
                var no = $(this).data('no');
                dialog_confirm('선택한 관리자메모를 삭제하시겠습니까? 삭제하시면 복구 하실 수 없습니다.', function (result) {
                    if (result) {
                        //var orderNo = "<?= $orderNo;?>";
                        $.ajax({
                            method: "POST",
                            cache: false,
                            url: "../order/order_ps.php",
                            data: "mode=admin_order_goods_memo_delete&no=" + no,
                        }).success(function (data) {
                            console.log(data);
                        }).error(function (e) {
                            alert(e.responseText);
                            return false;
                        });
                    }
                });
            }else{
                alert('메모를 등록한 관리자만 삭제가능합니다.');
                return false;
            }
        });

        $('.js-orderViewInfoSave').click(function(){
            if($(this).data('submit-mode') === 'adminOrdGoodsMemo'){
                if($.trim($("textarea[name='adminOrderGoodsMemo']").val()) === ''){
                    alert('관리자 메모를 등록해주세요.');
                    return false;
                }

                if($('#refundForm>input[name="no"]').val()){
                    $('#refundForm>input[name="mode"]').attr('value', 'admin_order_goods_memo_modify');
                    //$("#refundForm>input[name='mode']").val('admin_order_goods_memo_modify');
                }else{
                    var mode = $('#refundForm>input[name="mode"]').val();
                    $("#refundForm>input[name='mode']").attr('value', mode);
                    //$("#refundForm>input[name='mode']").val($(this).data('submit-mode'));
                }


                var queryString = $("form[name=refundForm]").serialize();

                $.ajax({
                    method: "POST",
                    cache: false,
                    url: "../order/order_ps.php",
                    data: queryString,
                    //data: "mode=" + mode + "&order=" + orderNo + "&orderGoodsSno=" + orderGoodsSno + "&type=" + memoType,
                }).success(function (data) {
                    window.location.reload(true);
                }).error(function (e) {
                    alert(e.responseText);
                    return false;
                });

            }
        });
    });

    /**
     * 사용자 환불 금액 설정
     */
    function user_refund_price() {
        var realRefundPrice = parseInt($('input[name="realRefundPrice"]').val()),
            cashPrice = parseInt($('input[name="info[completeCashPrice]"]').val()),
            pgPrice = parseInt($('input[name="info[completePgPrice]"]').val()),
            depositPrice = parseInt($('input[name="info[completeDepositPrice]"]').val()),
            mileagePrice = parseInt($('input[name="info[completeMileagePrice]"]').val());
        var totalUserRefundPrice = cashPrice + pgPrice + depositPrice + mileagePrice;

        $('#completeRestPrice .rest-price').text(numeral(realRefundPrice - totalUserRefundPrice).format());
        $('input[name="userRealRefundPrice"]').val(totalUserRefundPrice);
    }

    /**
     * 실 환불금액 계산
     */
    function real_refund_price() {
        var useDeposit = 0;
        var useMileage = 0;
        var deliveryCharge = 0;
        var giveMileage = 0;
        var refundCharge = 0;

        $('#refundGoodsInfo tbody input[type=text]').each(function(idx){
            var type = $(this).prop('name').replace(/ *refund\[[^\]]*\] */g, "");
            var value = !$(this).val() ? 0 : parseInt($(this).val());
            switch (type) {
                case '[refundUseDeposit]':
                    useDeposit += value;
                    break;
                case '[refundUseMileage]':
                    useMileage += value;
                    break;
                case '[refundDeliveryCharge]':
                    deliveryCharge += value;
                    break;
                case '[refundGiveMileage]':
                    giveMileage += value;
                    break;
                case '[refundGiveMileage]':
                    giveMileage += value;
                    break;
                case '[refundCharge]':
                    refundCharge += value;
                    break;
            }
        });

        // 금액 계산
        var totalUseDeposit = $('input[name="tmp[totalUseDeposit]"]').data('original') - useDeposit;
        var totalUseMileage = $('input[name="tmp[totalUseMileage]"]').data('original') - useMileage;
        var totalDeliveryCharge = $('input[name="tmp[totalDeliveryCharge]"]').data('original') - deliveryCharge;
        var totalGiveMileage = $('input[name="tmp[totalGiveMileage]"]').data('original') - giveMileage;
        var expectedRefundPrice = <?=gd_money_format($realRefundPrice, false)?>;
        var firstRefundPrice = expectedRefundPrice - totalDeliveryCharge;
        var realRefundPrice = firstRefundPrice - refundCharge;

        // 상품별 환불금액 및 마일리지
        $('input[name="tmp[totalUseDeposit]"]').val(useDeposit);
        $('input[name="tmp[totalUseMileage]"]').val(useMileage);
        $('input[name="tmp[totalDeliveryCharge]"]').val(deliveryCharge);
        $('input[name="tmp[totalGiveMileage]"]').val(giveMileage);
        $('input[name="tmp[totalRefundCharge]"]').val(refundCharge);

        // 상품별 차액 금액 설정
        $('input[name="check[totalUseDeposit]"]').val(totalUseDeposit);
        $('input[name="check[totalUseMileage]"]').val(totalUseMileage);
        $('input[name="check[totalDeliveryCharge]"]').val(totalDeliveryCharge);
        $('input[name="check[totalGiveMileage]"]').val(totalGiveMileage);
        $('input[name="check[totalRefundCharge]"]').val(refundCharge);

        // 환불예정금액
        $('#firstRefundPrice').text(numeral(firstRefundPrice).format());

        // 실 환불금액
        $('input[name="check[totalRefundPrice]"]').val(realRefundPrice);
        $('input[name=realRefundPrice]').val(realRefundPrice);
        if ($('#txtRealRefundPrice').length) {
            $('#txtRealRefundPrice').text(numeral(realRefundPrice).format());
        }

        <?php
        if(isset($paycoCoupon) && $paycoCoupon > 0) {
        ?>
        if(<?=$paycoCoupon?> > firstRefundPrice)
        {
            if($('[name=\'info[refundMethod]\'] :selected').val() == 'PG환불') {
                $('[name=\'info[refundMethod]\'] :selected').attr('disabled', 'disabled');
                $('[name=\'info[refundMethod]\']').find('option:first-child').prop('selected', true).end().trigger('liszt:updated');
                $('[name=\'info[completePgPrice]\']').parent().parent().append('<p id="paycoCouponRefund" class="notice-danger">환불예정금액이 페이코 쿠폰 사용액(<?=number_format($paycoCoupon)?>원)보다 작아 PG환불을 진행할 수 없습니다.</p>');
                $('[name=\'info[completePgPrice]\']').attr('disabled', 'disabled').val('');
            }
        }
        <?php
        } else if(isset($firstHand) && $firstHand == 'Y') {
        ?>
        if($('[name=\'info[refundMethod]\'] :selected').val() == 'PG환불') {
            $('[name=\'info[refundMethod]\'] :selected').attr('disabled', 'disabled');
            $('[name=\'info[refundMethod]\']').find('option:first-child').prop('selected', true).end().trigger('liszt:updated');
            $('[name=\'info[completePgPrice]\']').parent().parent().append('<p id="paycoCouponRefund" class="notice-danger">수기환불된 내역이 있어 PG환불을 진행할 수 없습니다.</p>');
            $('[name=\'info[completePgPrice]\']').attr('disabled', 'disabled').val('');
        }
        <?php
        }
        ?>

        // 계산 후 차감해야 할 적립마일리지가 고객 보유 마일리지보다 큰 경우 예외 처리를 위해 false 반환
        if (!isOpendOnce && $('input[name="tmp[memberMileage]"]').val() < totalGiveMileage) {
            return false;
        }
    }

    function refund_method_set(orderChannelFl, settleKind) {
        if (orderChannelFl != 'payco') return;
        var checkLen = $('#refundGoodsInfo tr[id^="statusCheck_r"]').length;

        if (orderGoodsCnt == checkLen) { // 전체환불
            $('.payco-notice').addClass('display-none');
            $('#infoCompletePgPrice').removeClass('display-none').show();
            $('select[name="info[refundMethod]"] option').not('[value="PG환불"], [value="기타환불"]').wrap('<span>').parent().hide();
        } else if (checkLen > 0) { // 부분환불
            switch (settleKind.substr(1, 1)) {
                case 'b':
                    $('.payco-notice').removeClass('display-none');
                    if ($('select[name="info[refundMethod]"] option').parent().is('span')) {
                        $('select[name="info[refundMethod]"] option').not('[value="PG환불"], [value="기타환불"]').unwrap();
                    }
                    break;
                default:
                    $('.payco-notice').addClass('display-none');
                    $('#infoCompletePgPrice').removeClass('display-none').show();
                    $('select[name="info[refundMethod]"] option').not('[value="PG환불"], [value="기타환불"]').wrap('<span>').parent().hide();
                    break;
            }
        } else {
            $('.payco-notice').addClass('display-none');
            if ($('select[name="info[refundMethod]"] option').parent().is('span')) {
                $('select[name="info[refundMethod]"] option').not('[value="PG환불"], [value="기타환불"]').unwrap();
            }
        }
    }

    var payco_notice_msg = function(orderChannelFl, refundMethod){
        if (orderChannelFl != 'payco') return;

        if (refundMethod == '기타환불') {
            $('.payco-notice-msg').removeClass('display-none');
        } else {
            $('.payco-notice-msg').addClass('display-none');
        }
    }

    // 쿠폰할인혜택정보 토글
    $('.table-benefit-info .js-pay-toggle').click(function(e){
        var target = $(this).closest('tr').siblings('#' + $(this).data('target')).eq(0);
        var tr = $(this).closest('tr'),
            td = tr.find('td.th .list-unstyled');
        if (target.find('td').is(':visible')) {
            $(this).removeClass('active');
            $(this).closest('th').css({borderBottom: '1px solid #E6E6E6'});
            target.find('th').css({display: 'none'});
            target.find('td').css({display: 'none'});
        } else {
            $(this).addClass('active');
            $(this).closest('th').css({borderBottom: 'none'});
            target.find('th').css({display: ''});
            target.find('td').css({display: ''});
        }
    });

    // 쿠폰할인혜택정보 토글
    $('.table-benefit-info .js-pay-toggle').each(function(idx){
        var count = $(this).data('number');
        if (count == 0) {
            $(this).remove();
        }
    });
    //-->
</script>
