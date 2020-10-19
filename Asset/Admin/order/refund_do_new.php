<!-- 프린트 출력을 위한 form -->
<form id="frmOrderPrint" name="frmOrderPrint" action="" method="post" class="display-none">
    <input type="checkbox" name="orderNo" value="<?= gd_isset($data['orderNo']) ?>" checked="checked"/>
    <input type="hidden" name="orderPrintCode" value=""/>
    <input type="hidden" name="orderPrintMode" value=""/>
</form>
<!-- // 프린트 출력을 위한 form -->


<div class="page-header js-affix">
    <h3><?= end($naviMenu->location) ?>
        <small></small>
    </h3>
    <?php if ($statusFl) { ?>
        <input type="button" value="환불하기" class="btn btn-red js-refund-form js-submit-disabledFl">
    <?php } ?>
</div>

<form id="frmRefundStatus" method="post" action="./order_ps.php">
    <input type="hidden" name="mode" value="refund_rollback"/>
    <input type="hidden" name="orderNo" value="<?= gd_isset($data['orderNo']) ?>"/>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-4 pdt3">
                    <span class="flag flag-16 flag-<?= $data['domainFl']; ?>"></span>
                    <?= $data['mallName']; ?>
                    <?= str_repeat('&nbsp', 6); ?>

                    주문번호 : <span><a href="/order/order_view.php?orderNo=<?= $data['orderNo']; ?>" target="_blank" style="color:#117efa; font-weight: bold"><?= $data['orderNo']; ?></a>
                        <?php if (is_file(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $data['settleKind'] . '.gif'))) { ?>
                            <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $data['settleKind'] . '.gif')->www(), $data['settleKindStr']); ?>
                        <?php } ?>
                        <?php if ($data['useDeposit'] > 0) { ?>
                            <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_gd.gif')->www(), $data['settleKindStr']); ?>
                        <?php } ?>
                        <?php if ($data['useMileage'] > 0) { ?>
                            <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_gm.gif')->www(), $data['settleKindStr']); ?>
                        <?php } ?>
                    </span>
                </div>
                <div class="col-xs-4 pdt3 text-center">
                    해당 주문 상품 <strong><?= $data['orderGoodsCnt'] ?></strong>개 중 <strong class="text-red"><?= $data['cnt']['goods']['goods'] ?></strong>개의 상품 환불
                </div>
                <div class="col-xs-4 text-right">
                    <div class="form-inline">
                        <?= gd_select_box('orderPrintMode', null, ['report' => '주문내역서', 'customerReport' => '주문내역서 (고객용)', 'reception' => '간이영수증', 'particular' => '거래명세서', 'taxInvoice' => '세금계산서'], null, null, '=인쇄 선택=', null, 'form-control input-sm') ?>
                        <input type="button" onclick="order_print_popup($('#orderPrintMode').val(), 'frmOrderPrint', 'frmOrderPrint', 'orderNo', <?= $isProvider ? 'true' : 'false' ?>);" value="프린트" class="btn btn-sm btn-white"/>
                    </div>
                </div>
            </div>


            <div class="pull-right">

            </div>
        </div>
    </div>

    <!-- 결제정보 -->
    <div class="mgt20">
        <div class="table-title">
            <span class="gd-help-manual mgt30">결제 정보</span>
            <span class="mgl10 notice-info">요청사항/상담메모의 내용이 수정 또는 삭제된 경우 "저장" 버튼을 클릭해야 적용됩니다.</span>
        </div>

        <table class="table table-rows">
            <thead>
            <tr>
                <th>상품 판매금액</th>
                <th>총 배송비</th>
                <th>총 할인금액</th>
                <th>총 부가결제금액</th>
                <th>총 결제금액</th>
                <th>총 적립금액</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td class="center"><?= $commonData['totalGoodsPriceText'] ?></td>
                <td class="center"><?= $commonData['totalDeliveryChargeText'] ?></td>
                <td class="center"><?= $commonData['totalDcPriceText'] ?></td>
                <td class="center"><?= $commonData['totalUseAddedPriceText'] ?></td>
                <td class="center"><?= $commonData['settlePriceText'] ?></td>
                <td class="center"><?= $commonData['totalMileageText'] ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- 결제정보 -->

    <!-- 환불상품 -->
    <div class="mgt20">
        <div class="table-title">
            <span class="gd-help-manual mgt30">환불 상품</span>
        </div>
        <table class="table table-rows">
            <thead>
            <tr>
                <?php if ($statusFl) { ?>
                    <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]'/></th>
                <?php } ?>
                <th>접수일자</th>
                <th>사유</th>
                <th>공급사</th>
                <th>상품<br/>주문번호</th>
                <th>이미지</th>
                <th>주문상품</th>
                <th>수량</th>
                <th>매입가</th>
                <th>상품금액</th>
                <th>총 상품금액</th>
                <th>할인금액</th>
                <?php if($useMultiShippingKey === true){ ?>
                    <th>배송지</th>
                    <th>배송비</th>
                <?php } ?>
                <th>처리상태</th>
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
                    $rowMultiShipping = 0;
                    foreach ($sVal as $dKey => $dVal) {
                        $rowDelivery = 0;
                        foreach ($dVal as $key => $val) {
                            // 결제금액 (추가상품 분리 작업이후 addGoodsPrice는 0원으로 들어가짐)
                            $settlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['totalGoodsDivisionUseDeposit'] - $val['totalGoodsDivisionUseMileage'] - $val['totalDivisionCouponOrderDcPrice'];
                            $totalGoodsDcPrice = $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice'] + $val['enuri'];

                            $totalSettlePrice += $settlePrice;
                            $statusMode = substr($val['orderStatus'], 0, 1);

                            // rowspan 처리
                            $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';

                            // rowspan 처리
                            $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';

                            //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scm no 를 order info sno 로 대체, dKey는 order delivery sno로 대체
                            if($useMultiShippingKey === true){
                                $rowScm = 0;
                                $orderMultiShippingRowSpan = ' rowspan="' . ($data['cnt']['multiShipping'][$sKey]) . '"';
                            }
                            $deliveryUniqueKey = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                            $orderDeliveryRowSpan = ' rowspan="' . ($data['cnt']['delivery'][$deliveryUniqueKey]) . '"';
                            ?>
                            <tr id="statusCheck_<?= $statusMode; ?>_<?= $val['sno']; ?>" class="text-center">
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

                                <!-- 접수일자 -->
                                <td <?= $orderAddGoodsRowSpan ?> class="font-date"><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleRegDt'])); ?></td>
                                <!-- 접수일자 -->

                                <!-- 사유 -->
                                <td <?= $orderAddGoodsRowSpan ?>><?= $val['handleReason'] ?></td>
                                <!-- 사유 -->

                                <!-- 공급사 -->
                                <td class="text-center"><?= $val['companyNm'] ?></td>
                                <!-- 공급사 -->

                                <!-- 상품주문번호 -->
                                <td <?= $orderAddGoodsRowSpan ?> class="font-num"><?= $val['sno'] ?></td>
                                <!-- 상품주문번호 -->

                                <!-- 이미지 -->
                                <td>
                                    <?php if ($val['goodsType'] === 'addGoods') { ?>
                                        <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                    <?php } else { ?>
                                        <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank') ?>
                                    <?php } ?>
                                </td>
                                <!-- 이미지 -->

                                <!-- 주문상품 -->
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
                                <!-- 주문상품 -->

                                <!-- 수량 -->
                                <td class="text-center"><?= number_format($val['goodsCnt']) ?></td>
                                <!-- 수량 -->

                                <!-- 매입가 -->
                                <td class="text-right"><?= gd_currency_display($val['costPrice'] + $val['optionCostPrice']) ?></td>
                                <!-- 매입가 -->

                                <!-- 상품금액 -->
                                <td class="text-right"><?= gd_currency_display($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) ?></td>
                                <!-- 상품금액 -->

                                <!-- 총상품금액 -->
                                <td class="text-right">
                                    <?php if ($isUseMall == true) { ?>
                                        <?= gd_global_order_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                    <?php } else { ?>
                                        <?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']); ?>
                                    <?php } ?>
                                </td>
                                <!-- 총상품금액 -->

                                <!-- 상품할인금액 -->
                                <td <?= $orderAddGoodsRowSpan ?> class="text-right"><?= gd_currency_display($totalGoodsDcPrice) ?></td>
                                <!-- 상품할인금액 -->


                                <?php if($useMultiShippingKey === true){ ?>
                                    <!-- 배송지 -->
                                    <?php if($rowMultiShipping === 0){ ?>
                                        <td <?= $orderMultiShippingRowSpan ?>>
                                            <?php
                                            if((int)$val['orderInfoCd'] === 1){
                                                echo "메인";
                                            }
                                            else {
                                                echo "추가" . ((int)$val['orderInfoCd']-1);
                                            }
                                            ?>
                                        </td>
                                    <?php } ?>
                                    <!-- 배송지 -->

                                    <!-- 배송비 -->
                                    <?php if ($val['mallSno'] == DEFAULT_MALL_NUMBER) { ?>
                                        <?php if ($rowDelivery == 0) { ?>
                                            <td <?= $orderDeliveryRowSpan; ?> class="font-num"><?=gd_currency_display($val['deliveryCharge'])?></td>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?php if ($rowChk == 0) { ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="font-num"><?=gd_currency_display($val['deliveryCharge'])?></td>
                                        <?php } ?>
                                    <?php } ?>
                                    <!-- 배송비 -->
                                <?php } ?>

                                <!-- 처리상태 -->
                                <td <?= $orderAddGoodsRowSpan ?> class="center">
                                    <?php if (empty($val['beforeStatusStr']) === false) { ?>
                                        <div><?= $val['beforeStatusStr'] ?> &gt;</div>
                                    <?php } ?>
                                    <div><?= $val['orderStatusStr'] ?></div>
                                </td>
                                <!-- 처리상태 -->
                            </tr>
                            <?php
                            $sortNo--;
                            $rowChk++;
                            $rowAll++;
                            $rowDelivery++;
                            $rowMultiShipping++;
                        }
                    }
                }
            } else {
                ?>
                <tr>
                    <td class="no-data" colspan="<?= count($orderGridConfigList) ?>"><?= $incTitle ?>이 없습니다.</td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php if ($statusFl) { ?>
            <div class="table-action">
                <div class="pull-left form-inline">
                    <span class="action-title">선택한 상품을</span>
                    <?php
                    // 에스크로 주문이고 배송등록이 안되어 있다면
                    $bundleChangeStatus = $order->getOrderStatusList(null, ['o', 'c', 's', 'e', 'r', 'f'], ['g2', 'g3', 'g4', 'b2', 'b3', 'b4', 'd1', 'r1']);
                    echo gd_select_box('bundleChangeStatus', 'changeStatus', $bundleChangeStatus, null, null, '==상품상태==', $disabled, 'form-control js-status-change');
                    unset($bundleChangeStatus);
                    ?>
                    으(로)
                    <button type="button" class="btn btn-white js-refund-status">변경</button>
                </div>
            </div>
        <?php } ?>
    </div>
</form>
<?php if (!$isProvider) { ?>
    <div class="row">
        <!-- 쿠폰/할인/혜택 정보 -->
        <div class="col-md-12">
            <div class="table-title gd-help-manual">
                쿠폰/할인/혜택 정보
            </div>
            <?php
            // 아래 레이아웃에서 $data를 치환하여 사용하므로 원본 유지 차원에서 데이터를 보존
            $tmpData = $data;
            include $layoutOrderViewBenefitInfo;
            $data = $tmpData;
            unset($tmpData);
            ?>
            <!-- 쿠폰/할인/혜택 정보 -->
        </div>
    </div>
<?php } ?>
<form name="refundForm" id="refundForm" action="../order/order_change_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="refund_complete_new"/>
    <input type="hidden" name="orderNo" value="<?= $data['orderNo'] ?>"/>
    <input type="hidden" name="handleSno" value="<?= $handleSno ?>"/>
    <input type="hidden" name="isAll" value="<?= $isAll ?>"/>
    <!-- 계산에 필요한 정보 -->
    <input type="hidden" name="totalRealPayedPrice" id="totalRealPayedPrice" value="<?= $refundData['totalRealPayedPrice'] ?>"/>
    <input type="hidden" name="refundGoodsPrice" id="refundGoodsPrice" value="<?= $refundData['refundGoodsPriceSum'] ?>"/>
    <input type="hidden" name="refundAliveGoodsPriceSum" id="refundAliveGoodsPriceSum" value="<?= $refundData['refundAliveGoodsPriceSum'] ?>"/>
    <input type="hidden" name="refundAliveGoodsCount" id="refundAliveGoodsCount" value="<?= $refundData['refundAliveGoodsCount'] ?>"/>
    <input type="hidden" name="refundGoodsDcSum" id="refundGoodsDcSum" value="<?= $refundData['refundGoodsDcPriceSum'] ?>"/>
    <input type="hidden" name="refundGoodsCouponMileageOrg" id="refundGoodsCouponMileageOrg" value="<?= $refundData['refundGoodsCouponMileage'] ?>"/>
    <input type="hidden" name="refundGoodsCouponMileageMin" id="refundGoodsCouponMileageMin" value="<?= $refundData['refundMinGoodsCouponMileage'] ?>"/>
    <input type="hidden" name="refundGoodsCouponMileageMax" id="refundGoodsCouponMileageMax" value="<?= $refundData['refundGoodsCouponMileage'] ?>"/>
    <input type="hidden" name="refundOrderCouponMileageOrg" id="refundOrderCouponMileageOrg" value="<?= $refundData['refundOrderCouponMileage'] ?>"/>
    <input type="hidden" name="refundOrderCouponMileageMin" id="refundOrderCouponMileageMin" value="<?= $refundData['refundMinOrderCouponMileage'] ?>"/>
    <input type="hidden" name="refundOrderCouponMileageMax" id="refundOrderCouponMileageMax" value="<?= $refundData['refundOrderCouponMileage'] ?>"/>
    <input type="hidden" name="refundGroupMileageOrg" id="refundGroupMileageOrg" value="<?= $refundData['refundGroupMileage'] ?>"/>
    <input type="hidden" name="refundGroupMileageMin" id="refundGroupMileageMin" value="<?= $refundData['refundMinGroupMileage'] ?>"/>
    <input type="hidden" name="refundGroupMileageMax" id="refundGroupMileageMax" value="<?= $refundData['refundGroupMileage'] ?>"/>
    <input type="hidden" name="refundGoodsDcPriceSumMin" id="refundGoodsDcPriceSumMin" value="<?= $refundData['refundGoodsDcPriceSumMin'] ?>"/>
    <input type="hidden" name="refundGoodsDcPriceOrg" id="refundGoodsDcPriceOrg" value="<?= $refundData['refundGoodsDcPrice'] ?>"/>
    <input type="hidden" name="refundGoodsDcPriceMax" id="refundGoodsDcPriceMax" value="<?= $refundData['refundGoodsDcPriceMax'] ?>"/>
    <input type="hidden" name="refundGoodsDcPriceMaxOrg" id="refundGoodsDcPriceMaxOrg" value="<?= $refundData['refundGoodsDcPriceMax'] ?>"/>
    <input type="hidden" name="refundMemberAddDcPriceOrg" id="refundMemberAddDcPriceOrg" value="<?= $refundData['refundMemberAddDcPrice'] ?>"/>
    <input type="hidden" name="refundMemberAddDcPriceMax" id="refundMemberAddDcPriceMax" value="<?= $refundData['refundMemberAddDcPriceMax'] ?>"/>
    <input type="hidden" name="refundMemberAddDcPriceMaxOrg" id="refundMemberAddDcPriceMaxOrg" value="<?= $refundData['refundMemberAddDcPriceMax'] ?>"/>
    <input type="hidden" name="refundMemberOverlapDcPriceOrg" id="refundMemberOverlapDcPriceOrg" value="<?= $refundData['refundMemberOverlapDcPrice'] ?>"/>
    <input type="hidden" name="refundMemberOverlapDcPriceMax" id="refundMemberOverlapDcPriceMax" value="<?= $refundData['refundMemberOverlapDcPriceMax'] ?>"/>
    <input type="hidden" name="refundMemberOverlapDcPriceMaxOrg" id="refundMemberOverlapDcPriceMaxOrg" value="<?= $refundData['refundMemberOverlapDcPriceMax'] ?>"/>
    <input type="hidden" name="refundEnuriDcPriceOrg" id="refundEnuriDcPriceOrg" value="<?= $refundData['refundEnuriDcPrice'] ?>"/>
    <input type="hidden" name="refundEnuriDcPriceMax" id="refundEnuriDcPriceMax" value="<?= $refundData['refundEnuriDcPriceMax'] ?>"/>
    <input type="hidden" name="refundEnuriDcPriceMaxOrg" id="refundEnuriDcPriceMaxOrg" value="<?= $refundData['refundEnuriDcPriceMax'] ?>"/>
    <?php if ($myappUseFl) { ?>
    <input type="hidden" name="refundMyappDcPriceOrg" id="refundMyappDcPriceOrg" value="<?= $refundData['refundMyappDcPrice'] ?>"/>
    <input type="hidden" name="refundMyappDcPriceMax" id="refundMyappDcPriceMax" value="<?= $refundData['refundMyappDcPriceMax'] ?>"/>
    <input type="hidden" name="refundMyappDcPriceMaxOrg" id="refundMyappDcPriceMaxOrg" value="<?= $refundData['refundMyappDcPriceMax'] ?>"/>
    <?php } ?>
    <input type="hidden" name="refundGoodsCouponDcPriceOrg" id="refundGoodsCouponDcPriceOrg" value="<?= $refundData['refundGoodsCouponDcPrice'] ?>"/>
    <input type="hidden" name="refundGoodsCouponDcPriceMax" id="refundGoodsCouponDcPriceMax" value="<?= $refundData['refundGoodsCouponDcPriceMax'] ?>"/>
    <input type="hidden" name="refundGoodsCouponDcPriceMaxOrg" id="refundGoodsCouponDcPriceMaxOrg" value="<?= $refundData['refundGoodsCouponDcPriceMax'] ?>"/>
    <input type="hidden" name="refundOrderCouponDcPriceOrg" id="refundOrderCouponDcPriceOrg" value="<?= $refundData['refundOrderCouponDcPrice'] ?>"/>
    <input type="hidden" name="refundOrderCouponDcPriceMax" id="refundOrderCouponDcPriceMax" value="<?= $refundData['refundOrderCouponDcPriceMax'] ?>"/>
    <input type="hidden" name="refundOrderCouponDcPriceMaxOrg" id="refundOrderCouponDcPriceMaxOrg" value="<?= $refundData['refundOrderCouponDcPriceMax'] ?>"/>
    <input type="hidden" name="refundAliveDeliveryPriceSum" id="refundAliveDeliveryPriceSum" value="<?= $refundData['refundAliveDeliveryPriceSum'] ?>"/>
    <input type="hidden" name="refundDeliveryCouponDcPriceOrg" id="refundDeliveryCouponDcPriceOrg" value="<?= $refundData['refundDeliveryCouponDcPrice'] ?>"/>
    <input type="hidden" name="refundDeliveryCouponDcPriceMax" id="refundDeliveryCouponDcPriceMax" value="<?= $refundData['refundDeliveryCouponDcPrice'] ?>"/>
    <input type="hidden" name="refundDeliveryCouponDcPriceMaxOrg" id="refundDeliveryCouponDcPriceMaxOrg" value="<?= $refundData['refundDeliveryCouponDcPrice'] ?>"/>
    <input type="hidden" name="refundDepositPriceOrg" id="refundDepositPriceOrg" value="<?= $refundData['refundDepositPrice'] ?>"/>
    <input type="hidden" name="refundDepositPriceTotal" id="refundDepositPriceTotal" value="<?= $refundData['refundDepositPriceTotal'] ?>"/>
    <input type="hidden" name="refundDepositPriceMax" id="refundDepositPriceMax" value="<?= $refundData['refundDepositPriceMax'] ?>"/>
    <input type="hidden" name="refundDepositPriceMaxOrg" id="refundDepositPriceMaxOrg" value="<?= $refundData['refundDepositPriceMax'] ?>"/>
    <input type="hidden" name="refundMileagePriceOrg" id="refundMileagePriceOrg" value="<?= $refundData['refundMileagePrice'] ?>"/>
    <input type="hidden" name="refundMileagePriceTotal" id="refundMileagePriceTotal" value="<?= $refundData['refundMileagePriceTotal'] ?>"/>
    <input type="hidden" name="refundMileagePriceMax" id="refundMileagePriceMax" value="<?= $refundData['refundMileagePriceMax'] ?>"/>
    <input type="hidden" name="refundMileagePriceMaxOrg" id="refundMileagePriceMaxOrg" value="<?= $refundData['refundMileagePriceMax'] ?>"/>
    <?php
    foreach ($refundData['aAliveDeliverySno'] as $k => $v) {
        ?>
        <input type="hidden" name="aAliveDeliverySno[]" id="aAliveDeliverySno_<?= $v ?>" value="<?= $v ?>"/>
        <?php
    }
    ?>

    <!-- 메모관련 -->
    <input type="hidden" name="no" value="">
    <input type="hidden" name="oldManagerId" value="">

    <div id="viewStatusrefundDetail">
        <!-- 취소 처리 -->
        <div class="table-title">
            <div class="table-title gd-help-manual">환불 금액 정보</div>
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
                <col/>
                <col/>
            </colgroup>
            <tr>
                <th>상품 금액</th>
                <td colspan="3" id="refundGoods"><?= gd_currency_display($refundData['refundGoodsPriceSum']); ?></td>
            </tr>
            <tr>
                <th>취소가능 부가 적립금 금액
                    <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
                </th>
                <td colspan="3">
                    <span id="refundTotalMileage">
                        <?= gd_money_format($refundData['refundMileageSum']); ?> <?=$mileageUse["unit"]?>
                    </span>
                    <?php if ($statusFl) { ?>
                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                        (총 잔여 부가 적립금 :
                        <span style="color: red;">
                            <?= gd_money_format($refundData['refundMileageSum']); ?>
                        </span>
                            <?=$mileageUse['unit']?>)
                    </span>
                    <?php } ?>
                </td>
            </tr>
            <tr class="display-none js-detail-display">
                <td colspan="4">
                    <table class="table table-cols">
                        <colgroup>
                            <?php if ($statusFl) { ?>
                                <col class="width-lg"/>
                                <col class="width-2xl"/>
                                <col/>
                            <?php } else { ?>
                                <col class="width-lg"/>
                                <col/>
                            <?php } ?>
                        </colgroup>
                        <tr>
                            <th>취소 상품쿠폰 적립금 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGoodsCouponMileageFlag" value="F" <?php if ($refundData['refundGoodsCouponMileageNow'] < 1) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGoodsCouponMileageFlag" value="T" <?php if ($refundData['refundGoodsCouponMileage'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundGoodsCouponMileageNow'] > 0) { ?>checked="checked"<?php } ?> /> 상품쿠폰 적립금 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGoodsCouponMileage" id="refundGoodsCouponMileage" class="form-control width-sm js-number" value="<?= $refundData['refundGoodsCouponMileageNow']; ?>" <?php if ($refundData['refundGoodsCouponMileageNow'] < 1) { ?>disabled<?php } ?> /> <?=$mileageUse['unit']?>
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소가능 적립금 :
                                        <span style="color: red;">
                                            <?= gd_money_format($refundData['refundGoodsCouponMileage']); ?>
                                        </span>
                                            <?=$mileageUse['unit']?>)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGoodsCouponMileage" id="refundGoodsCouponMileage" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundGoodsCouponMileage']); ?>" disabled /> <?=$mileageUse['unit']?>
                                        </span>
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th>취소 주문쿠폰 적립금 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundOrderCouponMileageFlag" value="F" <?php if ($refundData['refundOrderCouponMileageNow'] < 1) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundOrderCouponMileageFlag" value="T" <?php if ($refundData['refundOrderCouponMileage'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundOrderCouponMileageNow'] > 0) { ?>checked="checked"<?php } ?> /> 주문쿠폰 적립금 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundOrderCouponMileage" id="refundOrderCouponMileage" class="form-control width-sm js-number" value="<?= $refundData['refundOrderCouponMileageNow']; ?>" <?php if ($refundData['refundOrderCouponMileageNow'] < 1) { ?>disabled<?php } ?>  /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소가능 적립금 :
                                        <span style="color: red;">
                                            <?= gd_money_format($refundData['refundOrderCouponMileage']); ?>
                                        </span>
                                            <?=$mileageUse['unit']?>)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundOrderCouponMileage" id="refundOrderCouponMileage" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundOrderCouponMileage']); ?>" disabled  /> <?=$mileageUse['unit']?>
                                        </span>
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th>취소 회원등급 적립금 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGroupMileageFlag" value="F" <?php if ($refundData['refundGroupMileage'] < 1) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGroupMileageFlag" value="T" <?php if ($refundData['refundGroupMileageNow'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundGroupMileageNow'] > 0) { ?>checked="checked"<?php } ?> /> 회원등급 적립금 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGroupMileage" id="refundGroupMileage" class="form-control width-sm js-number" value="<?= $refundData['refundGroupMileageNow']; ?>" <?php if ($refundData['refundGroupMileageNow'] < 1) { ?>disabled<?php } ?> /> <?=$mileageUse['unit']?>
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소가능 적립금 :
                                        <span style="color: red;">
                                            <?= gd_money_format($refundData['refundGroupMileage']); ?>
                                        </span>
                                            <?=$mileageUse['unit']?>)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGroupMileage" id="refundGroupMileage" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundGroupMileage']); ?>" disabled /> <?=$mileageUse['unit']?>
                                        </span>
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>환불 상품 할인혜택 금액
                    <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
                </th>
                <td colspan="3">
                    <input type="hidden" id="refundTotalGoodsDcPrice" value="0">
                    <span id="refundTotalGoodsDc">
                        <?= gd_money_format($refundData['refundGoodsDcPriceSum']); ?> 원
                    </span>
                    <?php if ($statusFl) { ?>
                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                        (총 잔여 할인 금액 :
                        <span style="color: red;">
                            <?= gd_money_format($refundData['refundGoodsDcPriceSum']); ?>
                        </span>
                        원
                            <?php
                            if ($refundData['refundGoodsDcPriceSumMin'] > 0) {
                                ?>
                                , 취소해야 할 최소 할인혜택 금액 :
                                <span style="color: red;">
                            <?= gd_money_format($refundData['refundGoodsDcPriceSumMin']); ?>
                        </span>
                                원
                                <?php
                            }
                            ?>
                            )
                    </span>
                    <?php } ?>
                </td>
            </tr>
            <tr class="display-none js-detail-display">
                <td colspan="4">
                    <table class="table table-cols">
                        <colgroup>
                            <?php if ($statusFl) { ?>
                                <col class="width-lg"/>
                                <col class="width-2xl"/>
                                <col/>
                            <?php } else { ?>
                                <col class="width-lg"/>
                                <col/>
                            <?php } ?>
                        </colgroup>
                        <tr>
                            <th>상품할인 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGoodsDcPriceFlag" value="F" <?php if ($refundData['refundGoodsDcPrice'] < 1 || $refundData['refundGoodsDcPriceNow'] == 0) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGoodsDcPriceFlag" value="T" <?php if ($refundData['refundGoodsDcPrice'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundGoodsDcPriceNow'] > 0) { ?>checked="checked"<?php } ?> /> 상품할인 금액 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGoodsDcPrice" id="refundGoodsDcPrice" class="form-control width-sm js-number" value="<?= $refundData['refundGoodsDcPriceNow']; ?>" <?php if ($refundData['refundGoodsDcPrice'] < 1 || $refundData['refundGoodsDcPriceNow'] == 0) { ?>disabled<?php } ?> /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소 가능 금액 :
                                        <span id="refundGoodsDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundGoodsDcPriceMax']); ?>
                                        </span>
                                        원)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGoodsDcPrice" id="refundGoodsDcPrice" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundGoodsDcPriceMax']); ?>" disabled /> 원
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th>회원 추가할인 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundMemberAddDcPriceFlag" value="F" <?php if ($refundData['refundMemberAddDcPrice'] < 1 || $refundData['refundMemberAddDcPriceNow'] == 0) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundMemberAddDcPriceFlag" value="T" <?php if ($refundData['refundMemberAddDcPrice'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundMemberAddDcPriceNow'] > 0) { ?>checked="checked"<?php } ?> /> 회원 추가할인 금액 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundMemberAddDcPrice" id="refundMemberAddDcPrice" class="form-control width-sm js-number" value="<?= $refundData['refundMemberAddDcPriceNow']; ?>" <?php if ($refundData['refundMemberAddDcPrice'] < 1 || $refundData['refundMemberAddDcPriceNow'] == 0) { ?>disabled<?php } ?>  /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소 가능 금액 :
                                        <span id="refundMemberAddDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundMemberAddDcPriceMax']); ?>
                                        </span>
                                        원)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundMemberAddDcPrice" id="refundMemberAddDcPrice" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundMemberAddDcPriceMax']); ?>" disabled  /> 원
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th>회원 중복할인 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundMemberOverlapDcPriceFlag" value="F" <?php if ($refundData['refundMemberOverlapDcPrice'] < 1 || $refundData['refundMemberOverlapDcPriceNow'] == 0) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundMemberOverlapDcPriceFlag" value="T" <?php if ($refundData['refundMemberOverlapDcPrice'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundMemberOverlapDcPriceNow'] > 0) { ?>checked="checked"<?php } ?> /> 회원 중복할인 금액 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundMemberOverlapDcPrice" id="refundMemberOverlapDcPrice" class="form-control width-sm js-number" value="<?= $refundData['refundMemberOverlapDcPriceNow']; ?>" <?php if ($refundData['refundMemberOverlapDcPrice'] < 1 || $refundData['refundMemberOverlapDcPriceNow'] == 0) { ?>disabled<?php } ?> /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소 가능 금액 :
                                        <span id="refundMemberOverlapDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundMemberOverlapDcPriceMax']); ?>
                                        </span>
                                        원)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundMemberOverlapDcPrice" id="refundMemberOverlapDcPrice" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundMemberOverlapDcPriceMax']); ?>" disabled /> 원
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th>운영자 할인 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundEnuriDcPriceFlag" value="F" <?php if ($refundData['refundEnuriDcPrice'] < 1 || $refundData['refundEnuriDcPriceNow'] == 0) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundEnuriDcPriceFlag" value="T" <?php if ($refundData['refundEnuriDcPrice'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundEnuriDcPriceNow'] > 0) { ?>checked="checked"<?php } ?> /> 운영자 할인 금액 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundEnuriDcPrice" id="refundEnuriDcPrice" class="form-control width-sm js-number" value="<?= $refundData['refundEnuriDcPriceNow']; ?>" <?php if ($refundData['refundEnuriDcPrice'] < 1 || $refundData['refundEnuriDcPriceNow'] == 0) { ?>disabled<?php } ?> /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소 가능 금액 :
                                        <span id="refundEnuriDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundEnuriDcPriceMax']); ?>
                                        </span>
                                        원)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundEnuriDcPrice" id="refundEnuriDcPrice" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundEnuriDcPriceMax']); ?>" disabled /> 원
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>

                        <?php if ($myappUseFl) { ?>
                        <tr>
                            <th>마이앱 할인 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundMyappDcPriceFlag" value="F" <?php if ($refundData['refundMyappDcPrice'] < 1 || $refundData['refundMyappDcPriceNow'] == 0) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundMyappDcPriceFlag" value="T" <?php if ($refundData['refundMyappDcPrice'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundMyappDcPriceNow'] > 0) { ?>checked="checked"<?php } ?> /> 마이앱 할인 금액 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundMyappDcPrice" id="refundMyappDcPrice" class="form-control width-sm js-number" value="<?= $refundData['refundMyappDcPriceNow']; ?>" <?php if ($refundData['refundMyappDcPrice'] < 1 || $refundData['refundMyappDcPriceNow'] == 0) { ?>disabled<?php } ?> /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소 가능 금액 :
                                        <span id="refundMyappDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundMyappDcPriceMax']); ?>
                                        </span>
                                        원)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundMyappDcPrice" id="refundMyappDcPrice" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundMyappDcPriceMax']); ?>" disabled /> 원
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                        <tr>
                            <th>상품쿠폰 할인 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGoodsCouponDcPriceFlag" value="F" <?php if ($refundData['refundGoodsCouponDcPrice'] < 1 || $refundData['refundGoodsCouponDcPriceNow'] == 0) { ?>checked="checked"<?php } ?> /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundGoodsCouponDcPriceFlag" value="T" <?php if ($refundData['refundGoodsCouponDcPrice'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundGoodsCouponDcPriceNow'] > 0) { ?>checked="checked"<?php } ?> /> 상품쿠폰 할인 금액 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGoodsCouponDcPrice" id="refundGoodsCouponDcPrice" class="form-control width-sm js-number" value="<?= $refundData['refundGoodsCouponDcPriceNow']; ?>" <?php if ($refundData['refundGoodsCouponDcPrice'] < 1 || $refundData['refundGoodsCouponDcPriceNow'] == 0) { ?>disabled<?php } ?> /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소 가능 금액 :
                                        <span id="refundGoodsCouponDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundGoodsCouponDcPriceMax']); ?>
                                        </span>
                                        원)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundGoodsCouponDcPrice" id="refundGoodsCouponDcPrice" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundGoodsCouponDcPriceMax']); ?>" disabled /> 원
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <th>주문쿠폰 할인 금액</th>
                            <?php if ($statusFl) { ?>
                                <td>
                                    <div class="form-inline">
                                        <label class="radio-inline">
                                            <input type="radio" name="refundOrderCouponDcPriceFlag" value="F" <?php if ($refundData['refundOrderCouponDcPrice'] < 1 || $refundData['refundOrderCouponDcPriceNow'] == 0) { ?>checked="checked"<?php } ?>  /> 취소안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="refundOrderCouponDcPriceFlag" value="T" <?php if ($refundData['refundOrderCouponDcPrice'] < 1) { ?>disabled<?php } ?> <?php if ($refundData['refundOrderCouponDcPriceNow'] > 0) { ?>checked="checked"<?php } ?> /> 주문쿠폰 할인 금액 취소
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundOrderCouponDcPrice" id="refundOrderCouponDcPrice" class="form-control width-sm js-number" value="<?= $refundData['refundOrderCouponDcPriceNow']; ?>" <?php if ($refundData['refundOrderCouponDcPrice'] < 1 || $refundData['refundOrderCouponDcPriceNow'] == 0) { ?>disabled<?php } ?> /> 원
                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                        (최대 취소 가능 금액 :
                                        <span id="refundOrderCouponDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundOrderCouponDcPriceMax']); ?>
                                        </span>
                                        원)
                                    </span>
                                    </div>
                                </td>
                            <?php } else { ?>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundOrderCouponDcPrice" id="refundOrderCouponDcPrice" class="form-control width-sm js-number" value="<?= gd_money_format($refundData['refundOrderCouponDcPriceMax']); ?>" disabled /> 원
                                    </div>
                                </td>
                            <?php } ?>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>
                    환불 수수료
                    <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
                </th>
                <td colspan="3">
                    <span id="refundCharge">
                    0원
                    </span>
                    <span class="mgl10" style="color: #999999; font-size: 11px;">
                    (최대 환불 수수료 처리 가능 금액 :
                    <span id="totalRefundChargeText" style="color: red;">
                        0
                    </span>
                    원)
                    </span>
                </td>
                <input type="hidden" id="totalRefundCharge" value="0">
                <input type="hidden" id="totalRefundChargeMax" value="0">
            </tr>
            <tr class="display-none js-detail-display">
                <td colspan="4">
                    <table class="table table-rows">
                        <thead>
                        <tr>
                            <th>상품<br/>주문번호</th>
                            <th>이미지</th>
                            <th>주문상품</th>
                            <th>상품금액</th>
                            <th>할인금액</th>
                            <th>환불금액</th>
                            <th>환불수수료</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (isset($data['goods']) === true) {
                            $totalGoodsPrice = 0;
                            $totalDcPrice = 0;
                            $totalDeliveryDcPrice = 0;
                            $totalUseMileage = 0;
                            $totalSettlePrice = 0;
                            $totalDeliveryCharge = 0;
                            $totalDeliveryInsuranceFee = 0;
                            $totalGiveMileage = 0;
                            $totalRefundUseDeposit = 0;
                            $totalRefundUseMileage = 0;
                            $totalRefundDeliveryDeposit = 0;
                            $totalRefundDeliveryMileage = 0;
                            $totalRefundGiveMileage = 0;
                            $totalRefundCharge = 0;
                            $totalRefundDeliveryInsuranceFee = 0;
                            $totalCompleteCashPrice = 0;
                            $totalCompletePgPrice = 0;
                            $totalCompleteDepositPrice = 0;
                            $totalCompleteMileagePrice = 0;
                            $settlePrice = 0;// 결제금액
                            $totalRefundCompletePrice = 0; // 총 환불금액 (환불완료 후 상세내역 노출)
                            $totalRefundUseDepositCommission = 0; // 예치금 부가결제 수수료
                            $totalRefundUseMileageCommission = 0; // 마일리지 부가결제 수수료
                            $totalCompletePriceData = [];
                            foreach ($data['goods'] as $sKey => $sVal) {
                                foreach ($sVal as $dKey => $dVal) {
                                    foreach ($dVal as $key => $val) {
                                        // 하단의 환불 방법 설정내 들어갈 내용 설정
                                        if ($handleSno == $val['handleSno']) {
                                            $handleData = $val;
                                        }
                                        // 결제금액
                                        $settlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['enuri'] - $val['totalDivisionCouponOrderDcPrice'];

                                        // 주문상태 모드 (한자리)
                                        $statusMode = substr($val['orderStatus'], 0, 1);

                                        // 합계금액 계산
                                        $totalGoodsPrice += ($val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])) + $val['addGoodsPrice'];
                                        $totalCostPrice += ($val['goodsCnt'] * ($val['costPrice'] + $val['optionCostPrice']));
                                        $totalDcPrice += $val['goodsDcPrice'] + $val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice'] + $val['totalCouponGoodsDcPrice'] + $val['enuri'] + $val['totalDivisionCouponOrderDcPrice'];
                                        $totalSettlePrice += $settlePrice;
                                        $totalGoodsUseDeposit += $val['totalGoodsDivisionUseDeposit'];
                                        $totalGoodsUseMileage += $val['totalGoodsDivisionUseMileage'];
                                        $totalDeliveryUseDeposit += $val['divisionGoodsDeliveryUseDeposit'];
                                        $totalDeliveryUseMileage += $val['divisionGoodsDeliveryUseMileage'];
                                        $totalGiveMileage += $val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'];
                                        $totalRefundDeliveryCharge += ($val['refundDeliveryCharge'] + $val['refundDeliveryUseDeposit'] + $val['refundDeliveryUseMileage']);
                                        $totalRefundUseDeposit += $val['refundUseDeposit'];
                                        $totalRefundUseMileage += $val['refundUseMileage'];
                                        $totalRefundDeliveryDeposit += $val['refundDeliveryUseDeposit'];
                                        $totalRefundDeliveryMileage += $val['refundDeliveryUseMileage'];
                                        $totalRefundGiveMileage += $val['refundGiveMileage'];
                                        $totalRefundCharge += $val['refundCharge'];
                                        $totalRefundCompletePrice += $val['refundPrice'];
                                        $totalRefundDeliveryInsuranceFee += $val['refundDeliveryInsuranceFee'];
                                        $totalRefundUseDepositCommission += $val['refundUseDepositCommission']; // 예치금 부가결제 수수료
                                        $totalRefundUseMileageCommission += $val['refundUseMileageCommission']; // 마일리지 부가결제 수수료

                                        // 환불 금액 설정 합계
                                        $totalCompletePriceData['completeCashPrice'][$val['refundGroupCd']] += $val['completeCashPrice'];
                                        $totalCompletePriceData['completePgPrice'][$val['refundGroupCd']] += $val['completePgPrice'];
                                        $totalCompletePriceData['completeDepositPrice'][$val['refundGroupCd']] += $val['completeDepositPrice'];
                                        $totalCompletePriceData['completeMileagePrice'][$val['refundGroupCd']] += $val['completeMileagePrice'];

                                        $goodsTotalMileage = $val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'];

                                        $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
                                        $goodsDcPrice = $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice'] + $val['enuri'];
                                        $refundPrice = $goodsPrice - $goodsDcPrice;

                                        // 최대 환불 수수료 계산
                                        $thisGoodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
                                        if ($thisGoodsPrice > $refundData['totalRealPayedPrice']) {
                                            $thisMaxRefundCharge = $refundData['totalRealPayedPrice'];
                                        } else {
                                            $thisMaxRefundCharge = $thisGoodsPrice;
                                        }

                                        // 기본 배송업체 설정
                                        if (empty($val['deliverySno']) === true) {
                                            $val['orderDeliverySno'] = $deliverySno;
                                        }
                                        ?>
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][sno]" value="<?= $val['sno'] ?>"/>
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][returnStock]" value="n"/>
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][originGiveMileage]" value="<?= $goodsTotalMileage; ?>">
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][refundGiveMileage]" value="<?= $goodsTotalMileage; ?>">
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][refundGoodsPrice]" value="<?= $goodsPrice; ?>">
                                        <tr id="statusCheck_<?= $statusMode; ?>_<?= $val['sno']; ?>" class="text-center">
                                            <!-- 상품주문번호 -->
                                            <td class="font-num">
                                                <?= $val['sno'] ?>
                                            </td>
                                            <!-- 상품주문번호 -->
                                            <!-- 이미지 -->
                                            <td>
                                                <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                    <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                                <?php } else { ?>
                                                    <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank') ?>
                                                <?php } ?>
                                            </td>
                                            <!-- 이미지 -->

                                            <!-- 주문상품 -->
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
                                            <!-- 주문상품 -->

                                            <!-- 총상품금액 -->
                                            <td class="text-right">
                                                <?php if ($isUseMall == true) { ?>
                                                    <?= gd_global_order_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                                <?php } else { ?>
                                                    <?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']); ?>
                                                <?php } ?>
                                            </td>
                                            <!-- 총상품금액 -->

                                            <!-- 상품할인금액 -->
                                            <td class="text-right"><?= gd_currency_display($goodsDcPrice) ?></td>
                                            <!-- 상품할인금액 -->

                                            <!-- 환불금액 -->
                                            <td class="text-right"><?= gd_currency_display($refundPrice) ?></td>
                                            <!-- 환불금액 -->

                                            <!-- 환불수수료 -->
                                            <td>
                                                <?php if ($statusFl) { ?>
                                                    <div class="form-inline">
                                                        <input type="text" name="refundCharge<?= $val['handleSno'] ?>" id="refundCharge<?= $val['handleSno'] ?>" class="form-control width-sm js-number" value="0" /> 원
                                                        <input type="hidden" name="refundCharge<?= $val['handleSno'] ?>Max" id="refundCharge<?= $val['handleSno'] ?>Max" class="form-control width-sm js-number" value="<?= $thisMaxRefundCharge; ?>" />
                                                        <input type="hidden" name="refundCharge<?= $val['handleSno'] ?>MaxOrg" id="refundCharge<?= $val['handleSno'] ?>MaxOrg" class="form-control width-sm js-number" value="<?= $thisGoodsPrice; ?>" />
                                                    </div>
                                                    <?php
                                                } else {
                                                    echo gd_currency_display($val['refundCharge']);
                                                }
                                                ?>
                                            </td>
                                            <!-- 환불수수료 -->
                                        </tr>
                                        <?php
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
                                    $totalDeliveryDcPrice += $val['divisionMemberDeliveryDcPrice'];
                                    $totalRealDelivery = $totalDeliveryCharge - $totalDeliveryDcPrice;
                                    if ($totalRealDelivery < 0 ) {
                                        $totalRealDelivery = 0;
                                    }
                                }
                            }
                            $totalCompleteCashPrice = array_sum($totalCompletePriceData['completeCashPrice']);
                            $totalCompletePgPrice = array_sum($totalCompletePriceData['completePgPrice']);
                            $totalCompleteDepositPrice = array_sum($totalCompletePriceData['completeDepositPrice']);
                            $totalCompleteMileagePrice = array_sum($totalCompletePriceData['completeMileagePrice']);
                            unset($totalCompletePriceData);
                        }
                        ?>
                        </tbody>
                    </table>
                </td>
            </tr>

            <!-- 배송비 환불 금액 -->
            <tr>
                <th>배송비 환불 금액</th>
                <td colspan="3">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-lg"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>
                                환불 배송비 금액
                            </th>
                            <td colspan="3" id="refundDeliveryPrice">
                                <?= $refundData['refundDeliveryPriceSum'] ?> 원
                            </td>
                        </tr>
                        <?php
                        $iRefundDeliveryCouponDcPriceMin = 0;
                        foreach ($refundData['aDeliveryAmount'] as $orderDeliverySno => $aVal) {
                            if (!in_array($orderDeliverySno, $refundData['aAliveDeliverySno'])) {
                                $sLastDeliveryFlag = 'T';
                                $iRefundDeliveryCouponDcPriceMin += $aVal['iCoupon'];
                            } else {
                                $sLastDeliveryFlag = 'F';
                            }
                            ?>
                            <tr>
                                <th>
                                    <?= $aVal['sName']; ?><br/>의 환불 실 배송비 금액
                                </th>
                                <td colspan="3" id="refundDelivery<?= $orderDeliverySno; ?>">
                                    <div class="form-inline mgb5">
                                        <?php if ($statusFl) { ?>
                                            <input type="hidden" name="refundDeliveryCharge_<?= $orderDeliverySno; ?>Max" id="refundDeliveryCharge_<?= $orderDeliverySno; ?>Max" value="<?= $aVal['iAmount']; ?>"/>
                                            <input type="hidden" name="refundDeliveryCharge_<?= $orderDeliverySno; ?>Coupon" id="refundDeliveryCharge_<?= $orderDeliverySno; ?>Coupon" value="<?= $aVal['iCoupon']; ?>"/>
                                            <input type="text" name="refundDeliveryCharge_<?= $orderDeliverySno; ?>" id="refundDeliveryCharge_<?= $orderDeliverySno; ?>" class="form-control width-sm js-number-only" value="<?= $aVal['iAmount']; ?>" <?php if ($sLastDeliveryFlag == 'T') { ?>readonly<?php } ?>/> 원
                                            <span style="color: #999999; font-size: 11px;">
                                        (최대 환불 가능 배송비 : <span id="refundDeliveryCharge_<?= $orderDeliverySno; ?>MaxText" style="color: red;"><?= gd_money_format($aVal['iAmount']); ?></span>
                                        =
                                        배송비 <?= gd_currency_display($aVal['iAmount'] + $aVal['iCoupon'] + $aVal['iGroup']); ?>
                                                -
                                        쿠폰 배송비 할인 <?= gd_currency_display($aVal['iCoupon']); ?>
                                                -
                                        회원등급 배송비 할인 <?= gd_currency_display($aVal['iGroup']); ?>)
                                    </span>
                                        <?php } else { ?>
                                            <input type="text" name="refundDeliveryCharge_<?= $orderDeliverySno; ?>" id="refundDeliveryCharge_<?= $orderDeliverySno; ?>" class="form-control width-sm js-number-only" value="<?= $aVal['iAmount']; ?>" disabled/> 원
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th>
                                환불 배송비 쿠폰 금액
                            </th>
                            <td colspan="3">
                                <div class="form-inline mgb5">
                                    <?php if ($statusFl) { ?>
                                        <input type="hidden" name="refundDeliveryCouponDcPriceMin" id="refundDeliveryCouponDcPriceMin" value="<?= $iRefundDeliveryCouponDcPriceMin; ?>"/>
                                        <input type="text" name="refundDeliveryCouponDcPrice" id="refundDeliveryCouponDcPrice" class="form-control width-sm js-number-only" value="<?= $refundData['refundDeliveryCouponDcPrice'] ?>" data-max-price="<?= gd_isset($refundData['refundDeliveryCouponDcPrice'], 0); ?>" /> 원
                                        <span style="color: #999999; font-size: 11px;">
                                        (
                                            <?php
                                            if ($iRefundDeliveryCouponDcPriceMin > 0) {
                                                ?>
                                                필수 취소 금액 : <span style="color: red;"><?= $iRefundDeliveryCouponDcPriceMin; ?></span>,
                                                <?php
                                            }
                                            ?>
                                            최대 취소 가능 금액 :
                                        <span id="refundDeliveryCouponDcPriceMaxText" style="color: red;">
                                            <?= gd_money_format($refundData['refundDeliveryCouponDcPrice']); ?>
                                        </span>
                                        원)
                                    </span>
                                    <?php } else { ?>
                                        <input type="text" name="refundDeliveryCouponDcPrice" id="refundDeliveryCouponDcPrice" class="form-control width-sm js-number-only" value="<?= $refundData['refundDeliveryCouponDcPrice'] ?>" disabled /> 원
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- 배송비 환불 금액 -->

            <!-- 부가결제 환불 금액 -->
            <tr>
                <th>부가결제 환불 금액</th>
                <td colspan="3">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-lg"/>
                            <col/>
                        </colgroup>
                        <!-- 부가결제 수수료 사용 여부 -->
                        <?php if ($statusFl) { ?>
                            <div class="mgb5">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="addPaymentChargeUseFl" value="y" />
                                    <span style="color: #999999; font-size: 11px;">
                                        부가결제 수수료 사용 여부
                                    </span>
                                </label>
                            </div>
                        <?php } ?>
                        <!-- 부가결제 수수료 사용 여부 -->

                        <!-- 예치금 환불 금액 -->
                        <tr>
                            <th><?=$depositUse['name']?> 환불 금액</th>
                            <td>
                                <?php if ($statusFl) { ?>
                                    <div class="form-inline mgb5">
                                        <input type="text" class="form-control width-sm js-number-only" name="refundDepositPrice" id="refundDepositPrice" value="<?=$refundData['refundDepositPriceNow']?>" /> <?=$depositUse['unit']?>

                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                            최대 반환 가능 <?=$depositUse['name']?> :
                                            <span id="refundDepositPriceMaxText" style="color: red;">
                                                <?= gd_money_format($refundData['refundDepositPriceMax']); ?>
                                            </span>
                                            <?=$depositUse['unit']?>
                                        </span>
                                    </div>

                                <?php } else { ?>
                                    <?=gd_currency_display($totalRefundUseDeposit + $totalRefundDeliveryDeposit)?>

                                    <span style="color: #999999; font-size: 11px;">
                                        (상품: <?=gd_currency_display($totalRefundUseDeposit)?>, 배송비: <?=gd_currency_display($totalRefundDeliveryDeposit)?>)
                                    </span>
                                <?php } ?>
                            </td>
                        </tr>
                        <!-- 예치금 환불 금액 -->

                        <!-- 마일리지 환불 금액 -->
                        <tr>
                            <th><?=$mileageUse['name']?> 환불 금액</th>
                            <td>
                                <?php if ($statusFl) { ?>
                                    <div class="form-inline mgb5">
                                        <input type="text" class="form-control width-sm js-number-only" name="refundMileagePrice" id="refundMileagePrice" value="<?=$refundData['refundMileagePriceNow']?>" /> <?=$mileageUse['unit']?>

                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                            최대 반환 가능 <?=$mileageUse['name']?> :
                                            <span id="refundMileagePriceMaxText" style="color: red;">
                                                <?= gd_money_format($refundData['refundMileagePriceMax']); ?>
                                            </span>
                                            <?=$mileageUse['unit']?>
                                        </span>
                                    </div>

                                <?php } else { ?>
                                    <?=gd_currency_display($totalRefundUseMileage + $totalRefundDeliveryMileage)?>

                                    <span style="color: #999999; font-size: 11px;">
                                        (상품: <?=gd_currency_display($totalRefundUseMileage)?>, 배송비: <?=gd_currency_display($totalRefundDeliveryMileage)?>)
                                    </span>
                                <?php } ?>
                            </td>
                        </tr>
                        <!-- 마일리지 환불 금액 -->

                        <!-- 예치금 부가결제 수수료 -->
                        <?php if ($statusFl) { ?>
                            <tr class="display-none js-addPaymentCommissionArea">
                                <th><?=$depositUse['name']?> 부가결제 수수료</th>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" class="form-control width-sm js-number-only js-addPaymentCommission" name="refundUseDepositCommission" id="refundUseDepositCommission" value="0" /> <?=$depositUse['unit']?>
                                        <input type="hidden" name="refundUseDepositCommissionMax" id="refundUseDepositCommissionMax" value="<?=$refundData['refundDepositPriceNow']?>" />
                                        <label class="checkbox-inline mgl10">
                                            <input type="checkbox" name="refundUseDepositCommissionWithFl" value="y" />
                                            <span style="color: #999999; font-size: 11px;">
                                                <?=$depositUse['name']?> 환불 금액 동일 적용
                                            </span>
                                        </label>
                                    </div>
                                    <div style="color: #999999; font-size: 11px;">
                                        최대 적용가능 금액 :
                                        <span id="refundUseDepositCommissionMaxText" style="color: red;">
                                            <?php if ($refundData['refundDepositPriceNow'] > 0) { ?>
                                            <?=gd_money_format($refundData['refundDepositPriceNow'])?>
                                            <?php } else { ?>
                                            0
                                            <?php } ?>
                                        </span>
                                        원
                                    </div>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php if ((int)$totalRefundUseDepositCommission > 0) { ?>
                                <tr>
                                    <th><?=$depositUse['name']?> 부가결제 수수료</th>
                                    <td class="form-inline">
                                        <?=gd_currency_display($totalRefundUseDepositCommission)?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        <!-- 예치금 부가결제 수수료 -->

                        <!-- 마일리지 부가결제 수수료 -->
                        <?php if ($statusFl) { ?>
                            <tr class="display-none js-addPaymentCommissionArea">
                                <th><?=$mileageUse['name']?> 부가결제 수수료</th>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" class="form-control width-sm js-number-only js-addPaymentCommission" name="refundUseMileageCommission" id="refundUseMileageCommission" value="0" /> <?=$mileageUse['unit']?>
                                        <input type="hidden" name="refundUseMileageCommissionMax" id="refundUseMileageCommissionMax" value="<?=$refundData['refundMileagePriceNow']?>" />
                                        <label class="checkbox-inline mgl10">
                                            <input type="checkbox" name="refundUseMileageCommissionWithFl" value="y" />
                                            <span style="color: #999999; font-size: 11px;">
                                            <?=$mileageUse['name']?> 환불 금액 동일 적용
                                        </span>
                                        </label>
                                    </div>
                                    <div style="color: #999999; font-size: 11px;">
                                        최대 적용가능 금액 :
                                        <span id="refundUseMileageCommissionMaxText" style="color: red;">
                                            <?php if ($refundData['refundMileagePriceNow'] > 0) { ?>
                                            <?=gd_money_format($refundData['refundMileagePriceNow'])?>
                                            <?php } else { ?>
                                            0
                                            <?php } ?>
                                        </span>
                                        <?=$mileageUse['unit']?>
                                    </div>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php if ((int)$totalRefundUseMileageCommission > 0) { ?>
                                <tr>
                                    <th><?=$mileageUse['name']?> 부가결제 수수료</th>
                                    <td class="form-inline">
                                        <?=gd_currency_display($totalRefundUseMileageCommission)?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        <!-- 마일리지 부가결제 수수료 -->
                    </table>
                </td>
            </tr>
            <!-- 부가결제 환불 금액 -->

            <tr>
                <th>취소 부가 적립금</th>
                <th colspan="3" id="totalRefundMileage"><?= gd_money_format($refundData['refundMileageSum']); ?> <?=$mileageUse["unit"]?></th>
            </tr>
            <tr>
                <th>상품 환불금액</th>
                <th colspan="3" id="totalRefundGoodsPrice"><?= gd_currency_display($refundData['refundGoodsPriceSum']); ?></th>
            </tr>
            <tr>
                <th>배송비 환불금액</th>
                <th colspan="3" id="totalRefundDeliveryPrice">
                    <?= gd_currency_display($refundData['totalDeliveryPrice']); ?>
                </th>
                <input type="hidden" id="totalRefundDeliveryPriceSum" value="0">
            </tr>
            <tr>
                <th>취소 할인 혜택</th>
                <th colspan="3" id="totalDcPrice">- <?= gd_currency_display($refundData['refundTotalGoodsDcPriceNow']); ?></th>
            </tr>
            <tr>
                <th>환불 부가결제 금액</th>
                <th colspan="3" id="totalEtcPrice">- <?= gd_currency_display($refundData['refundDepositPriceNow'] + $refundData['refundMileagePriceNow']); ?></th>
            </tr>
            <tr>
                <th>처리 부가 수수료</th>
                <th colspan="3" id="totalRefundChargePrice">- <?= gd_currency_display($refundData['totalRefundCharge']); ?></th>
            </tr>
            <tr>
                <th>총 환불금액</th>
                <th colspan="3" id="totalRefundPrice">
                    <?= gd_currency_display($refundData['totalRefundPrice']); ?>
                </th>
            </tr>
        </table>
        <!-- 결제금액 정보 -->
    </div>
    <div class="table-title gd-help-manual">환불 방법 설정</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col>
        </colgroup>
        <?php if($data['mallSno'] > DEFAULT_MALL_NUMBER){ ?>
            <tr>
                <th>해외배송 보험료 환불 금액</th>
                <td class="form-inline">
                    <?php if ($statusFl) { ?>
                        <input type="hidden" name="info[refundDeliveryInsuranceFee]" value="<?=gd_isset($refundData['refundOverseasDeliveryInsuranceFee'], 0)?>" />
                        <?= gd_currency_display($refundData['refundOverseasDeliveryInsuranceFee']); ?>
                    <?php } else { ?>
                        <?= gd_currency_display($totalRefundDeliveryInsuranceFee); ?>
                    <?php }  ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th>환불 금액</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <?php
                    $realRefundPrice = $totalSettlePrice + $totalDeliveryInsuranceFee + $totalDeliveryCharge - ($totalDeliveryCharge - $totalRealDeliveryCharge);
                    $realRefundPrice -= ($totalRefundUseDeposit + $totalRefundUseMileage);
                    ?>
                    <input type="hidden" name="check[totalSettlePrice]" value="<?= $realRefundPrice; ?>"/>
                    <input type="hidden" name="check[totalRefundCharge]" value="0">
                    <input type="hidden" name="check[totalDeliveryCharge]" value="<?= $refundData['totalRefundDeliveryPrice']; ?>">
                    <input type="hidden" name="check[totalRefundPrice]" value="<?= $refundData['totalRefundPrice']; ?>">
                    <input type="hidden" name="check[totalDeliveryInsuranceFee]" value="<?=gd_money_format($totalDeliveryInsuranceFee, false)?>">
                    <input type="hidden" name="check[totalDeliveryCharge]" value="<?=gd_money_format($totalRealDeliveryCharge, false)?>">
                    <input type="hidden" name="check[totalGiveMileage]" value="<?=gd_money_format($totalGiveMileage, false)?>">
                    <input type="hidden" name="tmp[refundMinusMileage]" value="y">
                    <input type="hidden" name="tmp[memberMileage]" value="<?=$memInfo['mileage']?>">
                    <input type="hidden" name="lessRefundPrice" value="<?=$refundData['totalRefundPrice']?>">
                    <input type="hidden" name="refundPriceSum" value="0">
                    <input type="hidden" name="refundGoodsPriceSum" value="0">
                    <input type="hidden" name="refundDeliveryPriceSum" value="0">
                    <input type="hidden" name="etcGoodsSettlePrice" value="<?=gd_isset($refundData['etcGoodsSettlePrice'], 0)?>" /> <!-- 남은상품의 결제금액 -->
                    <input type="hidden" name="etcDeliverySettlePrice" value="<?=gd_isset($refundData['etcDeliverySettlePrice'], 0)?>" /> <!-- 남은배송비의 결제금액 -->
                    <input type="hidden" name="etcRefundAddPaymentPrice" value="0" /> <!-- 강제 환불되어야 할 토탈 예치금+마일리지 금액 -->
                    <input type="hidden" name="etcRefundGoodsAddPaymentPrice" value="0" /> <!-- 강제 환불되어야 할 상품 예치금+마일리지 금액 -->
                    <input type="hidden" name="etcRefundDeliveryAddPaymentPrice" value="0" /> <!-- 강제 환불되어야 할 배송비 예치금+마일리지 금액 -->

                    <?= gd_currency_symbol() ?>
                    <input type="text" name="userRealRefundPrice" disabled="disabled" class="form-control text-right input-sm width-xs" value="<?= $refundData['totalRefundPrice']; ?>">
                    <input type="hidden" id="userRealRefundPriceWithoutRefundcharge" value="0">
                    <?= gd_currency_string() ?>
                    <div class="notice-info">
                        남아있는 금액 <span id="completeRestPrice" class="rest-price"></span>
                    </div>
                <?php } else { ?>
                    <?= gd_currency_display($totalRefundCompletePrice); ?>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>환불수단</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <?= gd_select_box('refundMethod', 'info[refundMethod]', $refundMethod, null, $handleData['refundMethod'], null) ?>
                <?php } else { ?>
                    <input type="hidden" id="refundMethod" value="<?= $handleData['refundMethod']; ?>">
                    <?= $handleData['refundMethod']; ?>
                <?php } ?>
            </td>
        </tr>
        <tr id="cashRefundPrice" class="js-refund-method">
            <th>현금 환불 금액</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <input type="text" class="form-control text-right input-sm width-xs js-number js-refund-price" name="info[completeCashPrice]" value=""/>
                    <?php
                } else {
                    echo gd_currency_display($totalCompleteCashPrice);
                }
                ?>
                <div class="notice-danger">(운영자가 직접 고객의 환불 계좌로 이체합니다.)</div>
            </td>
        </tr>
        <tr id="pgRefundPrice" class="js-refund-method">
            <th>PG 환불 금액</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <input type="text" class="form-control text-right input-sm width-xs js-number js-refund-price" name="info[completePgPrice]" value=""/>
                    <?php if(($data['pgRealTaxSupplyPrice']+$data['pgRealTaxVatPrice']+$data['pgRealTaxFreePrice']) > 0){ ?>
                        <div class="notice-info">
                            남아있는 PG금액 <?=gd_currency_display($data['pgRealTaxSupplyPrice']+$data['pgRealTaxVatPrice']+$data['pgRealTaxFreePrice'])?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <?=gd_currency_display($totalCompletePgPrice)?>
                <?php } ?>

                <div class="pg-notice notice-danger">(계약된 PG사로 입력된 금액만큼 카드 승인취소 요청이 됩니다.)</div>
                <div class="payco-notice notice-danger display-none">페이코를 통한 바로이체 결제건의 부분취소는, 주문취소 상태만 연동되며 실제환불은 별도로 구매자에게 지급하셔야 합니다.</div>
                <div class="oversea-notice notice-danger display-none">해당 주문은 해외PG로 결제된 주문으로 해외PG는 전액환불만 가능합니다. 원화 기준으로 전액 환불처리를 해주셔야 정상적으로 환불처리가 가능하니 참고 바랍니다.</div>
            </td>
        </tr>
        <tr id="depositRefundPrice" class="js-refund-method">
            <th>예치금 환불 금액</th>
            <td class="form-inline">
                <?php
                if ($statusFl) {
                    $completeDepositPriceDisable = '';
                    if((int)$data['memNo'] < 1){
                        $completeDepositPriceDisable = "disabled='disabled'";
                    }
                    ?>
                    <input type="text" class="form-control text-right input-sm width-xs js-number js-refund-price" name="info[completeDepositPrice]" value="" <?=$completeDepositPriceDisable?> />
                    <?php
                } else {
                    echo gd_currency_display($totalCompleteDepositPrice);
                }
                ?>
                <div class="notice-danger">(입력된 금액만큼의 예치금이 회원에게 자동 지급됩니다.)</div>
            </td>
        </tr>
        <tr id="mileageRefundPrice" class="js-refund-method">
            <th>기타 환불 금액</th>
            <td class="form-inline">
                <?php if ($statusFl) { ?>
                    <input type="text" class="form-control text-right input-sm width-xs js-number js-refund-price" name="info[completeMileagePrice]" value=""/>
                    <?php
                } else {
                    echo gd_currency_display($totalCompleteMileagePrice);
                }
                ?>
                <div class="notice-danger">(입력된 금액만큼 운영자가 별도로 구매자에게 지급해야 합니다.)</div>
                <div class="payco-notice-msg notice-danger display-none">페이코 결제에 대해 "기타 환불"을 선택하시면, 환불 처리 연동되지 않으므로 실제환불은 별도로 구매자에게 지급하셔야 합니다.</div>
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
                <div class="mgt5 mgb5">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="info[handleDetailReasonShowFl]" value="y" <?= ($handleData['handleDetailReasonShowFl'] == 'y') ? 'checked':'' ?>   <?= ($statusFl) ? '':'disabled'?>>상세 사유를 고객에게 노출합니다.
                    </label>
                </div>
            </td>
        </tr>
        <tr id="refundBank" class="display-none">
            <th>환불 계좌정보</th>
            <td class="form-inline" colspan="5">
                <?php if ($statusFl) { ?>
                    <?= gd_select_box(null, 'info[refundBankName]', $bankNm, null, $handleData['refundBankName'], '= 은행 선택 =') ?>
                    <label class="control-label">계좌번호 :</label>
                    <input type="text" name="info[refundAccountNumber]" value="<?= $handleData['refundAccountNumber'] ?>" class="form-control width-lg js-number" maxlength="30"/>
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
                    <?php if (!$isProvider) { ?>
                        <th>관리</th>
                    <?php }?>
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
                        <?php if($mVal['managerNm']){?><span class="managerNm">(<?= $mVal['managerNm']; ?>)</span><?php }?>
                    </td>
                    <td class="text-center">
                        <span class="itemNm"><?= $mVal['itemNm']; ?></span>
                    </td>
                    <td class="text-center"><span class="orderGoodsSno"><?php if ($mVal['type'] == 'goods') { echo $mVal['orderGoodsSno']; } else { echo '-'; } ?></span></td>
                    <td>
                        <span class="content-memo"><?=str_replace(['\r\n', '\n', chr(10)], '<br>', $mVal['content']);?></span>
                    </td>
                    <?php if (!$isProvider) { ?>
                        <td class="text-center">
                        <span class="mod-button" style="padding-bottom: 5px;">
                            <button type="button" class="btn btn-sm btn-gray js-admin-memo-modify" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-memocd="<?=$mVal['memoCd'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-content="<?=$mVal['content'];?>" data-no="<?=$mVal['sno'];?>">수정</button>
                        </span>
                            <span class="del-button">
                            <button type="button" class="btn btn-sm btn-gray js-admin-memo-delete" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-no="<?=$mVal['sno'];?>">삭제</button>
                        </span>
                        </td>
                    <?php } ?>
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
                                <input type="radio" name="memoType" value="order" checked="checked"/>주문번호별
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="memoType" value="goods" />상품주문번호별
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
                                                               onclick="check_toggle(this.id,'sno');"/></th>
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
                            <button type="button" class="btn btn-black btn-sm js-memo-reset mgb5" >초기화</button>
                            <button type="button" class="btn btn-red btn-sm js-refundViewMemoInfoSave mgl5" data-submit-mode="adminOrdGoodsMemo">저장</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php }?>
    </div>

    <?php if ($statusFl) { ?>
    <div id="viewStatusRefundReturn">
        <div id="returnHtml">
            <!-- 복원 설정 정보 -->
            <div id="returnHtml">
                <div class="table-title">
                    <span class="gd-help-manual mgt30">추가 설정</span>
                </div>

                <table class="table table-cols">
                    <colgroup>
                        <col class="width-lg"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>재고 수량 복원 설정</th>
                        <td>
                            <label class="radio-inline">
                                <input type="radio" name="returnStockFl" value="n"<?=$checked['returnStockFl']['n']?> /> 복원안함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="returnStockFl" value="y" <?=$checked['returnStockFl']['y']?> /> 복원함
                            </label>
                        </td>
                    </tr>
                    <?php if (count($couponData) > 0) { ?>
                        <tr>
                            <th>쿠폰 복원 설정</th>
                            <td>
                                <label class="radio-inline">
                                    <input type="radio" name="returnCouponFl" value="n" <?=$checked['returnCouponFl']['n']?> /> 복원안함
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="returnCouponFl" value="y" <?=$checked['returnCouponFl']['y']?> /> 복원함
                                </label>
                            </td>
                        </tr>
                        <tr class="display-none js-detail-display">
                            <td colspan="2">
                                <table class="table table-rows">
                                    <thead>
                                    <tr>
                                        <th>쿠폰명</th>
                                        <th>쿠폰종류</th>
                                        <th>할인금액</th>
                                        <th>적립금액</th>
                                        <th>복원여부</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($couponData as $key => $val) { ?>
                                        <tr>
                                            <td><?= $val['couponNm']; ?></td>
                                            <td><?= $val['couponUseType']; ?></td>
                                            <td><?= $val['couponPrice']; ?></td>
                                            <td><?= $val['couponMileage']; ?></td>
                                            <td>
                                                <label class="radio-inline">
                                                    <input type="radio" name="returnCoupon[<?= $val['memberCouponNo']; ?>]" value="n" <?=$checked['returnCouponFl']['n']?> /> 복원안함
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="returnCoupon[<?= $val['memberCouponNo']; ?>]" value="y" <?=$checked['returnCouponFl']['y']?> /> 복원함
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
        <?php } ?>

        <div class="text-center">
            <?php if ($statusFl) { ?>
                <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
                <button type="submit" class="btn btn-lg btn-black js-submit-disabledFl">확인</button>
            <?php } else { ?>
                <button type="button" class="btn btn-lg btn-black js-layer-close">확인</button>
            <?php } ?>
        </div>
    </div>
</form>

<script type="text/javascript">
    <!--
    var orderChannelFl = "<?=$data['orderChannelFl']?>";
    var settleKind = "<?=$data['settleKind']?>";
    var orderGoodsCnt = "<?=$data['orderGoodsCnt']?>";
    var refundReconfirmFl = "<?=$refundReconfirmFl?>";

    $(document).ready(function () {
        $('.js-refund-form').click(function(e){
            $('#refundForm').submit();
        });

        var refundCharge = 0;
        var refundDeliveryPrice = 0;
        var refundSameDeliveryPrice = 0;
        $("#refundForm").validate({
            dialog: false,
            submitHandler: function (form) {

                /*
                var tempRefundResult = 0;
                // 환불상품금액 + 배송비환불금액 + 예치금환불금액 + 마일리지환불금액 + 결제수단환불금액
                tempRefundResult = parseInt($('#refundGoodsPrice').val()) + parseInt($('#totalRefundDeliveryPriceSum').val()) - parseInt($('#refundTotalGoodsDcPrice').val()) - parseInt($('#refundDepositPrice').val()) - parseInt($('#refundMileagePrice').val());

                if (parseInt($('#userRealRefundPriceWithoutRefundcharge').val()) != parseInt(tempRefundResult)) {

                    return false;
                }

                if (parseInt($('#refundGoodsDcPriceSumMin').val()) > parseInt($('#refundTotalGoodsDcPrice').val())) {
                    alert('취소해야할 최소 할인혜택 금액을 맞춰 주셔야 합니다.');
                    return false;
                }
*/
                // 총환불금액 마이너스 경우
                var userRealRefundPrice = parseInt($('input[name="userRealRefundPrice"]').val());
                if (userRealRefundPrice < 0) {
                    alert("총 환불금액은 마이너스가 될 수 없습니다.");
                    return false;
                }

                var tempGoodsDcSum = parseInt($('#refundTotalGoodsDcPrice').val());
                if (tempGoodsDcSum > parseInt($('#refundGoodsPrice').val())) {
                    alert("환불 상품 할인혜택 금액은 상품 금액보다 클 수 없습니다.");
                    return false;
                }

                if (parseInt($('#refundGoodsDcPriceSumMin').val()) > parseInt($('#refundTotalGoodsDcPrice').val())) { // 최소 취소 상품할인금액
                    alert('상품 할인혜택 부분은 반드시 "취소해야 할 최소 할인혜택 금액" 이상으로 취소해주셔야 합니다.');
                    return false;
                }

                if (parseInt($('#totalRealPayedPrice').val()) > 0) {
                    if (parseInt($('#totalRefundCharge').val()) > parseInt($('#totalRealPayedPrice').val())) { // 총 환불 수수료가 실결제금액을 넘을 수 없음
                        alert('환불 수수료의 합이 실 결제금액(총 결제금액 : ' + numeral($('#totalRealPayedPrice').val()).format() + '원)보다 높습니다. 실 결제금액 이하로만 환불 수수료를 처리 할 수 있습니다.');
                        return false;
                    }

                    if ((parseInt($('#totalRefundCharge').val()) + parseInt(userRealRefundPrice)) > parseInt($('#totalRealPayedPrice').val())) { // 총 환불 수수료가 실결제금액을 넘을 수 없음
                        alert('환불 금액과 환불 수수료의 합이 실 결제금액(총 결제금액 : ' + numeral($('#totalRealPayedPrice').val()).format() + '원)보다 높습니다. 실 결제금액 이하로만 처리 할 수 있습니다.');
                        return false;
                    }
                } else { // 남은 실 결제금액이 0이면
                    if ((parseInt($('#totalRefundCharge').val()) + parseInt(userRealRefundPrice)) > 0) { // 환불 수수료를 포함한 환불금액은 0이상 될수없음
                        alert('해당주문에 남은 실 결제금액이 0입니다. 할인취소와 부가결제금액으로만 처리가 가능합니다.');
                        return false;
                    }
                }

                if ($('input[name="lessRefundPrice"]').val() == 0 && $('input[name="refundPriceSum"]').val() == $('input[name="userRealRefundPrice"]').val()) {
                    if(refundReconfirmFl == 'y') {
                        BootstrapDialog.show({
                            title: '환불 확인',
                            type: BootstrapDialog.TYPE_DANGER,
                            message: '주문번호 <?= $data['orderNo']; ?> 에서 '+ userRealRefundPrice +'원을 환불 하시겠습니까?',
                            buttons: [
                                {
                                    id: 'btn-cancel',
                                    label: '취소',
                                    action: function(dialogItself){
                                        dialogItself.close();
                                    }
                                },
                                {
                                    id: 'btn-del',
                                    label: '확인',
                                    cssClass: 'btn-danger',
                                    action: function(dialog) {
                                        var $successButton = this;
                                        var $cancelButton = dialog.getButton('btn-cancel');
                                        $successButton.disable();
                                        $cancelButton.disable();
                                        $('.js-submit-disabledFl').prop('disabled', true);
                                        dialog.setClosable(false);
                                        form.target = 'ifrmProcess';
                                        form.submit();
                                        setTimeout(function() {
                                            dialog.close();
                                        }, 1000);
                                        setTimeout(function() {
                                            $('.js-submit-disabledFl').prop('disabled', false);
                                        }, 3000);
                                    }
                                }
                            ]
                        });
                   } else {
                        form.target = 'ifrmProcess';
                        form.submit();
                    }
                } else {
                    alert('환불 금액은 환불 수단의 환불금액의 합계와 동일해야 환불진행이 가능합니다.');
                    return false;
                }
            },
            rules: {
                mode: {
                    required: true,
                },
                orderNo: {
                    required: true,
                },
                'info[refundMethod]': {
                    required: true
                },
                'info[handleReason]': {
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
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                orderNo: {
                    required: '정상 접속이 아닙니다.(no)',
                },
                'info[refundMethod]': {
                    required: '환불수단을 선택하세요.'
                },
                'info[handleReason]': {
                    required: '환불사유를 선택하세요.'
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

        $('.js-layer-close').click(function () {
            window.close();
        });

        // 금액 입력 이벤트
        $(document).on("keyup change blur", "input[id^='refund']", function () {
            //alert($(this).val());
            var sId = '';
            var sValue = '';
            var sPattern = /\,|\./g;

            if ($(this).val() == null || $(this).val() == '') {
                $(this).val(0);
            }

            sId = $(this).attr('id');

            // 환불수수료면 환불수수료 합이 최대환불수수료 처리가능금액을 넘는지 확인해서 넘으면 0처리
            if (sId.substring(0, 12) == 'refundCharge') {
                var sumRefundCharge = 0;
                <?php
                foreach ($data['goods'] as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $key => $val) {
                ?>
                sumRefundCharge += parseInt($('#refundCharge<?php echo $val['handleSno']; ?>').val());
                <?php
                }
                }
                }
                ?>
                if (sumRefundCharge > $('#totalRefundChargeMax').val()) {
                    alert('환불 수수료의 합계는 최대 ' + $('#totalRefundChargeMax').val() + '까지만 가능합니다.');
                    $(this).val(0);
                    reCalc();
                    return false;
                }
            }

            sValue = $(this).val();
            sValue = parseInt(sValue.replace(sPattern, ''));
            if (parseInt(sValue) > parseInt($('#' + sId + 'Max').val())) {
                alert('최대 ' + $('#' + sId + 'Max').val() + '까지만 사용 가능합니다.');
                $(this).val($('#' + sId + 'Max').val());
            } else {
                if (sId == 'refundGoodsCouponMileage' && parseInt($('#' + sId + 'Min').val()) > 0) {
                    if (parseInt(sValue) < parseInt($('#' + sId + 'Min').val())) {
                        alert('취소 후 남는 상품이 없거나 상품쿠폰 적립금을 배분 할 상품이 없기때문에 ' + $('#' + sId + 'Min').val() + '만큼 취소 하셔야합니다.');
                        $(this).val($('#' + sId + 'Min').val());
                    }
                } else if (sId == 'refundOrderCouponMileage' && parseInt($('#' + sId + 'Min').val()) > 0) {
                    if (parseInt(sValue) < parseInt($('#' + sId + 'Min').val())) {
                        alert('취소 후 남는 상품이 없거나 주문쿠폰 적립금을 배분 할 상품이 없기때문에 ' + $('#' + sId + 'Min').val() + '만큼 취소 하셔야합니다.');
                        $(this).val($('#' + sId + 'Min').val());
                    }
                } else if (sId == 'refundGroupMileage' && parseInt($('#' + sId + 'Min').val()) > 0) {
                    if (parseInt(sValue) < parseInt($('#' + sId + 'Min').val())) {
                        alert('취소 후 남는 상품이 없거나 회원등급 적립금을 배분 할 상품이 없기때문에 ' + $('#' + sId + 'Min').val() + '만큼 취소 하셔야합니다.');
                        $(this).val($('#' + sId + 'Min').val());
                    }
                } else {
                    $(this).val(sValue);
                }
            }

            if (sId == 'refundMileagePrice') {
                $('#refundUseMileageCommissionMax').val($('#' + sId).val());
                $('#refundUseMileageCommissionMaxText').text(numeral($('#' + sId).val()).format());
                if (parseInt($('#refundUseMileageCommission').val()) > parseInt($('#' + sId).val())) {
                    $('#refundUseMileageCommission').val(0);
                }
            }
            if (sId == 'refundDepositPrice') {
                $('#refundUseDepositCommissionMax').val($('#' + sId).val());
                $('#refundUseDepositCommissionMaxText').text(numeral($('#' + sId).val()).format());
                if ($('#refundUseDepositCommission').val() > $('#' + sId).val()) {
                    $('#refundUseDepositCommission').val(0);
                }
            }

            // 재계산 처리
            reCalc();
        });

        function reCalc() {
            var refundGoodsPrice = parseInt($('#refundGoodsPrice').val()); // 상품 환불금액
            //var aliveGoodsPrice = parseInt($('#refundAliveGoodsPriceSum').val()) - refundGoodsPrice; // 환불 후 남을 상품금액
            var refundDeliveryPrice = 0; // 배송비 환불금액
            var userRealRefundPrice = 0; // 총 환불금액

            // 적립예정 적립금
            var refundTotalMileage = 0;
            // 적립예정 적립금 취소값 계산
            if ($('input:radio[name="refundGoodsCouponMileageFlag"]:checked').val() == 'T') {
                refundTotalMileage += parseInt($('#refundGoodsCouponMileage').val());
            }
            if ($('input:radio[name="refundOrderCouponMileageFlag"]:checked').val() == 'T') {
                refundTotalMileage += parseInt($('#refundOrderCouponMileage').val());
            }
            if ($('input:radio[name="refundGroupMileageFlag"]:checked').val() == 'T') {
                refundTotalMileage += parseInt($('#refundGroupMileage').val());
            }

            $('#refundTotalMileage').text(numeral(refundTotalMileage).format() + '<?=$mileageUse["unit"]?>');
            $('#totalRefundMileage').text(numeral(refundTotalMileage).format() + '<?=$mileageUse["unit"]?>');

            // 배송비 계산
            var refundDeliveryPrice = 0;
            <?php
            foreach ($refundData['aDeliveryAmount'] as $orderDeliverySno => $aVal) {
            ?>
            var refundDeliveryCharge_<?php echo $orderDeliverySno; ?> = $('#refundDeliveryCharge_<?php echo $orderDeliverySno; ?>').val();
            refundDeliveryPrice += parseInt(refundDeliveryCharge_<?php echo $orderDeliverySno; ?>);
            <?php
            }
            ?>
            refundDeliveryPrice += parseInt($('#refundDeliveryCouponDcPrice').val());
            $('#refundDeliveryPrice').text(numeral(refundDeliveryPrice).format() + '원');
            $('#totalRefundDeliveryPrice').text(numeral(refundDeliveryPrice).format() + '원');
            $('#totalRefundDeliveryPriceSum').val(refundDeliveryPrice);

            // 상품 환불금액 + 배송비 환불금액
            userRealRefundPrice = parseInt(refundGoodsPrice) + parseInt(refundDeliveryPrice);

            // 총환불금액(상품 환불금액 + 배송비 환불금액)에서 환불수수료 제외하고 할인혜택과 부가결제의 max값 업데이트 처리
            if (userRealRefundPrice < parseInt($('#refundGoodsDcPriceMaxOrg').val())) {
                $('#refundGoodsDcPriceMax').val(userRealRefundPrice);
                $('#refundGoodsDcPriceMaxText').text(numeral(userRealRefundPrice).format());
                if ($('input:radio[name="refundGoodsDcPriceFlag"]:checked').val() == 'T' && userRealRefundPrice < parseInt($('#refundGoodsDcPrice').val())) {
                    $('#refundGoodsDcPrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundGoodsDcPriceMax').val($('#refundGoodsDcPriceMaxOrg').val());
                $('#refundGoodsDcPriceMaxText').text(numeral($('#refundGoodsDcPriceMaxOrg').val()).format());
            }
            if (userRealRefundPrice < parseInt($('#refundMemberAddDcPriceMaxOrg').val())) {
                $('#refundMemberAddDcPriceMax').val(userRealRefundPrice);
                $('#refundMemberAddDcPriceMaxText').text(numeral(userRealRefundPrice).format());
                if ($('input:radio[name="refundMemberAddDcPriceFlag"]:checked').val() == 'T' && userRealRefundPrice < parseInt($('#refundMemberAddDcPrice').val())) {
                    $('#refundMemberAddDcPrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundMemberAddDcPriceMax').val($('#refundMemberAddDcPriceMaxOrg').val());
                $('#refundMemberAddDcPriceMaxText').text(numeral($('#refundMemberAddDcPriceMaxOrg').val()).format());
            }
            if (userRealRefundPrice < parseInt($('#refundMemberOverlapDcPriceMaxOrg').val())) {
                $('#refundMemberOverlapDcPriceMax').val(userRealRefundPrice);
                $('#refundMemberOverlapDcPriceMaxText').text(numeral(userRealRefundPrice).format());
                if ($('input:radio[name="refundMemberOverlapDcPriceFlag"]:checked').val() == 'T' && userRealRefundPrice < parseInt($('#refundMemberOverlapDcPrice').val())) {
                    $('#refundMemberOverlapDcPrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundMemberOverlapDcPriceMax').val($('#refundMemberOverlapDcPriceMaxOrg').val());
                $('#refundMemberOverlapDcPriceMaxText').text(numeral($('#refundMemberOverlapDcPriceMaxOrg').val()).format());
            }
            if (userRealRefundPrice < parseInt($('#refundEnuriDcPriceMaxOrg').val())) {
                $('#refundEnuriDcPriceMax').val(userRealRefundPrice);
                $('#refundEnuriDcPriceMaxText').text(numeral(userRealRefundPrice).format());
                if ($('input:radio[name="refundEnuriDcPriceFlag"]:checked').val() == 'T' && userRealRefundPrice < parseInt($('#refundEnuriDcPrice').val())) {
                    $('#refundEnuriDcPrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundEnuriDcPriceMax').val($('#refundEnuriDcPriceMaxOrg').val());
                $('#refundEnuriDcPriceMaxText').text(numeral($('#refundEnuriDcPriceMaxOrg').val()).format());
            }
            if (userRealRefundPrice < parseInt($('#refundGoodsCouponDcPriceMaxOrg').val())) {
                $('#refundGoodsCouponDcPriceMax').val(userRealRefundPrice);
                $('#refundGoodsCouponDcPriceMaxText').text(numeral(userRealRefundPrice).format());
                if ($('input:radio[name="refundGoodsCouponDcPriceFlag"]:checked').val() == 'T' && userRealRefundPrice < parseInt($('#refundGoodsCouponDcPrice').val())) {
                    $('#refundGoodsCouponDcPrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundGoodsCouponDcPriceMax').val($('#refundGoodsCouponDcPriceMaxOrg').val());
                $('#refundGoodsCouponDcPriceMaxText').text(numeral($('#refundGoodsCouponDcPriceMaxOrg').val()).format());
            }
            if (userRealRefundPrice < parseInt($('#refundOrderCouponDcPriceMaxOrg').val())) {
                $('#refundOrderCouponDcPriceMax').val(userRealRefundPrice);
                $('#refundOrderCouponDcPriceMaxText').text(numeral(userRealRefundPrice).format());
                if ($('input:radio[name="refundOrderCouponDcPriceFlag"]:checked').val() == 'T' && userRealRefundPrice < parseInt($('#refundOrderCouponDcPrice').val())) {
                    $('#refundOrderCouponDcPrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundOrderCouponDcPriceMax').val($('#refundOrderCouponDcPriceMaxOrg').val());
                $('#refundOrderCouponDcPriceMaxText').text(numeral($('#refundOrderCouponDcPriceMaxOrg').val()).format());
            }
            if (userRealRefundPrice < parseInt($('#refundDepositPriceMaxOrg').val())) {
                $('#refundDepositPriceMax').val(userRealRefundPrice);
                $('#refundDepositPriceMaxText').text(numeral(userRealRefundPrice).format());
                if (userRealRefundPrice < parseInt($('#refundDepositPrice').val())) {
                    $('#refundDepositPrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundDepositPriceMax').val($('#refundDepositPriceMaxOrg').val());
                $('#refundDepositPriceMaxText').text(numeral($('#refundDepositPriceMaxOrg').val()).format());
            }
            if (userRealRefundPrice < parseInt($('#refundMileagePriceMaxOrg').val())) {
                $('#refundMileagePriceMax').val(userRealRefundPrice);
                $('#refundMileagePriceMaxText').text(numeral(userRealRefundPrice).format());
                if (userRealRefundPrice < parseInt($('#refundMileagePrice').val())) {
                    $('#refundMileagePrice').val(userRealRefundPrice);
                }
            } else {
                $('#refundMileagePriceMax').val($('#refundMileagePriceMaxOrg').val());
                $('#refundMileagePriceMaxText').text(numeral($('#refundMileagePriceMaxOrg').val()).format());
            }
            var totalEtcPrice = parseInt($('#refundDepositPrice').val()) + parseInt($('#refundMileagePrice').val());
            $('#totalEtcPrice').text('- ' + numeral(totalEtcPrice).format() + '원');

            // 취소가능 할인
            var refundDcPrice = 0;
            if ($('input:radio[name="refundGoodsDcPriceFlag"]:checked').val() == 'T') {
                refundDcPrice += parseInt($('#refundGoodsDcPrice').val());
            }

            if ($('input:radio[name="refundMemberAddDcPriceFlag"]:checked').val() == 'T') {
                refundDcPrice += parseInt($('#refundMemberAddDcPrice').val());
            }

            if ($('input:radio[name="refundMemberOverlapDcPriceFlag"]:checked').val() == 'T') {
                refundDcPrice += parseInt($('#refundMemberOverlapDcPrice').val());
            }

            if ($('input:radio[name="refundEnuriDcPriceFlag"]:checked').val() == 'T') {
                refundDcPrice += parseInt($('#refundEnuriDcPrice').val());
            }

            <?php if ($myappUseFl) { ?>
            if ($('input:radio[name="refundMyappDcPriceFlag"]:checked').val() == 'T') {
                refundDcPrice += parseInt($('#refundMyappDcPrice').val());
            }
            <?php } ?>

            if ($('input:radio[name="refundGoodsCouponDcPriceFlag"]:checked').val() == 'T') {
                refundDcPrice += parseInt($('#refundGoodsCouponDcPrice').val());
            }

            if ($('input:radio[name="refundOrderCouponDcPriceFlag"]:checked').val() == 'T') {
                refundDcPrice += parseInt($('#refundOrderCouponDcPrice').val());
            }

            $('#refundTotalGoodsDc').text(numeral(refundDcPrice).format() + '원');
            $('#refundTotalGoodsDcPrice').val(refundDcPrice);
            $('#totalDcPrice').text('- ' + numeral(refundDcPrice).format() + '원');

            // 예치금 마일리지
            var refundDepositPrice = $('#refundDepositPrice').val();
            var refundMileagePrice = $('#refundMileagePrice').val();

            userRealRefundPrice -= (parseInt(refundDcPrice) + parseInt(refundDepositPrice) + parseInt(refundMileagePrice));

            // 부가결제 수수료
            if ($('input:radio[name="addPaymentChargeUseFl"]:checked').val() == 'T') {
                userRealRefundPrice -= (parseInt($('#refundUseDepositCommission').val()) + parseInt($('#refundUseMileageCommission').val()));
            }

            // 잔여 할인 & 잔여 부가결제 금액들의 합계
            var tempOrgDc = parseInt($('#refundGoodsDcPriceOrg').val());
            var reminderPrice = parseInt(refundDcPrice);
            $('#userRealRefundPriceWithoutRefundcharge').val(userRealRefundPrice); // 환불 수수료 제외한 총 환불금액

            // 환불 수수료
            var totalRealRefundPrice = parseInt(refundGoodsPrice); // 상품 환불금액
            totalRealRefundPrice -= parseInt(refundDcPrice); // 취소 할인 혜택 제거
            totalRealRefundPrice -= (parseInt(refundDepositPrice) + parseInt(refundMileagePrice)); // 환불 부가결제 금액 제거
            if (parseInt(totalRealRefundPrice) < 0) {
                totalRealRefundPrice = 0;
            }
            if (parseInt(totalRealRefundPrice) < parseInt(refundGoodsPrice)) {
                $('#totalRefundChargeText').text(numeral(totalRealRefundPrice).format());
                $('#totalRefundChargeMax').val(totalRealRefundPrice);
            } else {
                $('#totalRefundChargeText').text(numeral(refundGoodsPrice).format());
                $('#totalRefundChargeMax').val(refundGoodsPrice);
            }

            if (parseInt($('#totalRealPayedPrice').val()) < parseInt($('#totalRefundChargeMax').val())) {
                $('#totalRefundChargeText').text(numeral($('#totalRealPayedPrice').val()).format());
                $('#totalRefundChargeMax').val($('#totalRealPayedPrice').val());
            }
            <?php
            foreach ($data['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
            foreach ($dVal as $key => $val) {
            ?>
            if (parseInt(totalRealRefundPrice) > parseInt($('#refundCharge<?php echo $val['handleSno']; ?>MaxOrg').val())) {
                $('#refundCharge<?php echo $val['handleSno']; ?>Max').val(parseInt($('#refundCharge<?php echo $val['handleSno']; ?>MaxOrg').val()));
            } else {
                $('#refundCharge<?php echo $val['handleSno']; ?>Max').val(totalRealRefundPrice);
            }
            <?php
            }
            }
            }
            ?>

            var refundCharge = 0;
            <?php
            foreach ($data['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
            foreach ($dVal as $key => $val) {
            ?>
            refundCharge += parseInt($('#refundCharge<?php echo $val['handleSno']; ?>').val());
            <?php
            }
            }
            }
            ?>
            userRealRefundPrice -= parseInt(refundCharge);
            $('#totalRefundChargePrice').text('- ' + numeral(refundCharge).format() + '원');

            // 배송비 환불금액표기에는 쿠폰가 포함이어야하지만 배송비 쿠폰금액은 총환불금액에서 빠져야 한다.
            userRealRefundPrice -= parseInt($('#refundDeliveryCouponDcPrice').val());

            $('#refundCharge').text(numeral(refundCharge).format() + '원');
            $('#totalRefundCharge').val(refundCharge);

            $('#totalRefundPrice').text(numeral(userRealRefundPrice).format() + '원');

            $('input[name=userRealRefundPrice]').val(userRealRefundPrice);
            $('input[name=lessRefundPrice]').val(userRealRefundPrice);
            $('#completeRestPrice').text(numeral(userRealRefundPrice).format() + '원');

            $.each($('.js-refund-price'), function (k, v) {
                $(this).val(0);
            });
            $('input[name="refundPriceSum"]').val(0);
        }

        $(document).on("click", "input[type='radio']", function () {
            var inputTextEl = $(this).closest("tr").find("input[type='text']");
            if ($(this).val() === 'n') { // n 은 y 랑
                inputTextEl.attr("disabled", true);
                inputTextEl.val(0);
                inputTextEl.trigger('blur');
            } else if ($(this).val() === 'y') {
                inputTextEl.attr("disabled", false);
                inputTextEl.val('');
                inputTextEl.trigger('blur');
            } else if ($(this).val() === 'a') { // a 는 x 랑
                inputTextEl.attr("disabled", false);
                inputTextEl.val(inputTextEl.data('price'));
                inputTextEl.trigger('blur');
            } else if ($(this).val() === 'x') {
                inputTextEl.attr("disabled", true);
                inputTextEl.val(0);
                inputTextEl.trigger('blur');
            }
        });

        $('#viewStatusrefundDetail .js-pay-toggle').click(function () {
            $(this).closest('tr').next('tr').toggleClass("display-none");
            $(this).toggleClass('active');
//
//            // 한개만 열림
//            var displayChk = $(this).hasClass('active');
//            $('.js-pay-toggle').removeClass('active');
//            $('.js-detail-display').addClass('display-none');
//            if (displayChk) {
//                $(this).removeClass('active');
//                $(this).closest('tr').next('tr').addClass('display-none');
//            } else {
//                $(this).addClass('active');
//                $(this).closest('tr').next('tr').removeClass('display-none');
//            }
        });

        // 환불 수단
        $(document).on("click change blur", "#refundMethod", function () {
            $.each($('.js-refund-price'), function (k, v) {
                $(this).val(0);
            });
            $('#completeRestPrice').text(numeral($('input[name="userRealRefundPrice"]').val()).format() + '<?= gd_currency_string(); ?> ');

            $('.js-refund-method').addClass('display-none');
            $('#refundBank').addClass('display-none');
            if ($(this).val() == '현금환불') {
                $('#cashRefundPrice').removeClass('display-none');
                $('#refundBank').removeClass('display-none');
            } else if ($(this).val() == 'PG환불') {
                $('#pgRefundPrice').removeClass('display-none');
                // 페이코 처리
                <?php
                if(isset($paycoCoupon) && $paycoCoupon > 0) {
                ?>
                if (<?=$paycoCoupon?> > $('[name=\'check[totalSettlePrice]\']').val())
                {
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
            } else if ($(this).val() == '예치금환불') {
                $('#depositRefundPrice').removeClass('display-none');
            } else if ($(this).val() == '기타환불') {
                $('#mileageRefundPrice').removeClass('display-none');
            } else if ($(this).val() == '복합환불') {
                $('.js-refund-method').removeClass('display-none');
                $('#refundBank').removeClass('display-none');
                <?php if (substr($data['settleKind'], 0, 1) != 'o' && substr($data['settleKind'], 1, 1) == 'v') { ?>
                $('input[name="info[completePgPrice]"]').val(0);
                $('#pgRefundPrice').addClass('display-none');
                <?php } ?>
            } else {

            }
        });

        // 남은 환불 금액
        $(document).on("keyup change blur", ".js-refund-price", function () {
            var lessRefundPrice = $('input[name="userRealRefundPrice"]').val();
            var lessEachRefund = 0;
            var refundPriceSum = 0;
            $.each($('.js-refund-price'), function (k, v) {
                if ($(this).val() == '') {
                    $(this).val(0);
                }
                if ($.isNumeric($(this).val())) {
                    refundPriceSum += parseInt($(this).val());
                    lessEachRefund = lessRefundPrice + parseInt($(this).val());
                } else {
                    $(this).focus();
                }
            });
            lessEachRefund = lessRefundPrice - refundPriceSum;
            $('#completeRestPrice').text(numeral(lessEachRefund).format() + '<?= gd_currency_string(); ?> ');
            $('input[name="lessRefundPrice"]').val(lessEachRefund);
            $('input[name="refundPriceSum"]').val(refundPriceSum);
        });
        $('#refundMethod').trigger('click');

        $('input[name="returnCouponFl"]').change(function (e) {
            if ($('input[name="returnCouponFl"]:checked').val() == 'y') {
                $(this).closest('tr').next('tr').removeClass('display-none');
            } else {
                $(this).closest('tr').next('tr').addClass('display-none');
            }
        });
        $('input[name="returnGiftFl"]').change(function (e) {
            if ($(this).val() == 'n') {
                $(this).closest('tr').next('tr').removeClass('display-none');
            } else {
                $(this).closest('tr').next('tr').addClass('display-none');
            }
        });
        $('input[name="returnCouponFl"]').trigger('change');

        <?php if ($statusFl) { ?>
        reCalc();
        <?php } else { ?>
        $('#refundCharge').text(numeral(<?= $totalRefundCharge; ?>).format() + '<?= gd_currency_string(); ?> ');
        <?php } ?>

        $('select[name="info[refundMethod]"]').change(function(){
            payco_notice_msg(orderChannelFl, $(this).val());
            oversea_notice_msg(settleKind);
        });

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
            if(($(this).data('manager-sno') == $(this).data('m-sno')) || ($(this).data('manager-sno') == 0)) {
                var contentStr = $(this).data('content').toString().replace(/\\r\\n/gi, "\n");
                //var contentStr = $(this).data('content').replace(/\\r\\n/gi, "\n");

                // 수정 버튼 누를때마다 체크박스 초기화
                $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("checked",false);

                // 수정 버튼 누를때마다 체크박스 초기화
                $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("checked",false);

                // 수정 모드로 변경
                $('input[name="mode"]').attr('value', 'admin_order_goods_memo_modify');
                $('input[name="no"]').attr('value',$(this).data('no'));
                $('input[name="oldManagerId"]').attr('value',$(this).data('manager-sno'));
                $("input:radio[name=memoType]:radio[value=\'" + $(this).data('type') + "\']").prop('checked', true);
                $("input:checkbox[name=\"sno[]\"][value=\'" + $(this).data('sno') + "\']").prop("checked", true);
                $("#allCheck, input:checkbox[name=\"sno[]\"][value!=\'" + $(this).data('sno') + "\']").prop("disabled", true);
                $("#orderMemoCd").val($(this).data('memocd')).prop("selected", true);
                $("textarea[name='adminOrderGoodsMemo']").val(contentStr);

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
            if(($(this).data('manager-sno') == $(this).data('m-sno')) || ($(this).data('manager-sno') == 0)) {
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
                            alert(data);
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

        // 메모 초기화
        $('.js-memo-reset').click(function () {
            $("input[name='memoType'][value='order']").prop("checked",true);
            $("#orderMemoCd").val($(this).data('memocd')).prop("selected", false);
            $("#allCheck, input:checkbox[name=\"sno[]\"][value!=\'" + $(this).data('sno') + "\']").prop("checked", false);
            $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("disabled", false);
            $("textarea[name='adminOrderGoodsMemo']").val('');
            $('#tr_goodsSelect').addClass('display-none');
            $('input[name="mode"]').attr('value', 'refund_complete_new');
            $('input[name="no"]').attr('value','');
        });

        $('.js-refundViewMemoInfoSave').click(function(){
            if($(this).data('submit-mode') === 'adminOrdGoodsMemo'){
                if($.trim($("textarea[name='adminOrderGoodsMemo']").val()) === ''){
                    alert('관리자 메모를 등록해주세요.');
                    return false;
                }

                var checkedValue = $("input[type=radio][name=memoType]:checked").val();
                var snoLength = $('input[name=\"sno[]\"]:checked').length;
                if(checkedValue == 'goods'){
                    if (!snoLength) {
                        alert('선택된 상품이 없습니다.');
                        return false;
                    }
                }else if(checkedValue == 'order'){
                    if(snoLength) {
                        $('input:checkbox[name=\"sno[]\"]').attr("checked", false);
                    }
                }

                if($('#refundForm>input[name="no"]').val()){
                    $('#refundForm>input[name="mode"]').attr('value', 'admin_order_goods_memo_modify');
                }else{
                    //var mode = $('#refundForm>input[name="mode"]').val();
                    $("#refundForm>input[name='mode']").val($(this).data('submit-mode'));
                }


                var queryString = $("form[name=refundForm]").serialize();

                $.ajax({
                    method: "POST",
                    cache: false,
                    url: "../order/order_ps.php",
                    data: queryString,
                }).success(function (data) {
                    alert(data);
                }).error(function (e) {
                    alert(e.responseText);
                    return false;
                });

            }
        });

        // 부가결제 수수료 사용 여부
        $("input[name='addPaymentChargeUseFl']").click(function () {
            if($(this).prop("checked") === true){
                $(".js-addPaymentCommissionArea").removeClass('display-none');
            }
            else {
                $(".js-addPaymentCommissionArea").addClass('display-none');
            }
        });

        // 예치금 환불 금액 동시 입력
        $("input[name='refundUseDepositCommissionWithFl']").click(function () {
            $('#refundUseDepositCommission').val($('#refundDepositPrice').val());
        });

        // 마일리지 환불 금액 동시 입력
        $("input[name='refundUseMileageCommissionWithFl']").click(function () {
            $('#refundUseMileageCommission').val($('#refundMileagePrice').val());
        });

        // 상품할인금액 radio change이벤트
        $(document).on("change", "input[name$='Flag']", function () {
            var sName = $(this).attr('name');
            sName = sName.replace('Flag', '');
            if ($(this).val() == 'T') {
                $("input[name='" + sName + "']").prop('disabled', false);
            } else {
                $("input[name='" + sName + "']").prop('disabled', true);
            }
            reCalc();
        });

    });

    function refund_method_set(orderChannelFl, settleKind) {
        if (orderChannelFl != 'payco') return;
        var checkLen = $('#frmRefundStatus tr[id^="statusCheck_r"]').length;

        if (orderGoodsCnt == checkLen) { // 전체환불
            $('.payco-notice').addClass('display-none');
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

    var oversea_notice_msg = function(settleKind){
        if (settleKind.substr(0, 1) == 'o') {
            $('.pg-notice').addClass('display-none');
            $('.oversea-notice').removeClass('display-none');
        } else {
            $('.pg-notice').removeClass('display-none');
            $('.oversea-notice').addClass('display-none');
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
