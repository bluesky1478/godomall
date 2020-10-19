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
        <input type="button" value="환불하기" class="btn btn-red js-refund-form">
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
                            $settlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['totalGoodsDivisionUseDeposit'] - $val['totalGoodsDivisionUseMileage'] - $val['totalDivisionCouponOrderDcPrice'] - $val['totalMyappDcPrice'];
                            $totalGoodsDcPrice = $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice'] + $val['enuri'] + $val['myappDcPrice'];

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
    <input type="hidden" name="mode" value="refund_complete"/>
    <input type="hidden" name="orderNo" value="<?= $data['orderNo'] ?>"/>
    <input type="hidden" name="handleSno" value="<?= $handleSno ?>"/>
    <input type="hidden" name="isAll" value="<?= $isAll ?>"/>
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
                <th>상품 할인혜택 금액
                    <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
                </th>
                <td colspan="3" id="refundGoodsDc"><?= gd_currency_display($refundData['totalRefundGoodsDcPriceSum']); ?></td>
            </tr>
            <tr class="display-none js-detail-display">
                <td colspan="4">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-lg"/>
                            <col/>
                            <col/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>상품할인 금액</th>
                            <td>
                                <div class="form-inline">
                                    <input type="text" name="refundGoodsDc" class="form-control width-sm js-number" value="<?= $refundData['refundGoodsDcPriceSum']; ?>" disabled="disabled"/> 원
                                </div>
                            </td>
                            <td colspan="2" rowspan="8" style="color: #999999;">
                                환불 상품에 적용된 할인 금액이므로 전체 취소됩니다.
                            </td>
                        </tr>
                        <?php if ($refundData['refundMyappDcPriceSum'] > 0) { ?>
                            <tr>
                                <th>상품할인(모바일앱) 금액</th>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="refundMyappDc" class="form-control width-sm js-number" value="<?= $refundData['refundMyappDcPriceSum']; ?>" disabled="disabled"/> 원
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th>회원 추가할인 금액</th>
                            <td>
                                <div class="form-inline">
                                    <input type="text" name="refundMemberDc" class="form-control width-sm js-number" value="<?= $refundData['refundMemberDcPriceSum']; ?>" disabled="disabled"/> 원
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>회원 중복할인 금액</th>
                            <td>
                                <div class="form-inline">
                                    <input type="text" name="refundMemberOverlapDc" class="form-control width-sm js-number" value="<?= $refundData['refundMemberOverlapDcPriceSum']; ?>" disabled="disabled"/> 원
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>운영자 할인 금액</th>
                            <td>
                                <div class="form-inline">
                                    <input type="text" name="refundGoodsEnuriDc" class="form-control width-sm js-number" value="<?= $refundData['refundGoodsEnuriDcPriceSum']; ?>" disabled="disabled"/> 원
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>상품쿠폰 할인 금액</th>
                            <td>
                                <div class="form-inline">
                                    <input type="text" name="refundGoodsCouponDc" class="form-control width-sm js-number" value="<?= $refundData['refundGoodsCouponDcPriceSum']; ?>" disabled="disabled"/> 원
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>주문쿠폰 할인 금액</th>
                            <td>
                                <div class="form-inline">
                                    <input type="text" name="refundOrderCouponDc" class="form-control width-sm js-number" value="<?= $refundData['refundOrderCouponDcPriceSum']; ?>" disabled="disabled"/> 원
                                </div>
                                <span class="mgl10" style="color: #999999; font-size: 11px;">(주문 쿠폰 할인 금액 : <span style="color: red;"><?= gd_money_format($refundData['totalRefundCouponOrderDcPrice']); ?></span>원 중 환불 상품에 적용된 할인금액)</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <th>
                    환불 수수료
                    <span class="flo-right" style="font-size: 10px; color: #117ef9;"><button type="button" class="btn btn-sm btn-link js-pay-toggle">보기</button></span>
                </th>
                <td colspan="3" id="refundCharge">0 원</td>
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
                                        $settlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['enuri'] - $val['totalDivisionCouponOrderDcPrice'] - $val['totalMyappDcPrice'];

                                        // 주문상태 모드 (한자리)
                                        $statusMode = substr($val['orderStatus'], 0, 1);

                                        // 합계금액 계산
                                        $totalGoodsPrice += ($val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])) + $val['addGoodsPrice'];
                                        $totalCostPrice += ($val['goodsCnt'] * ($val['costPrice'] + $val['optionCostPrice']));
                                        $totalDcPrice += $val['goodsDcPrice'] + $val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice'] + $val['totalCouponGoodsDcPrice'] + $val['enuri'] + $val['totalDivisionCouponOrderDcPrice'] + $val['totalMyappDcPrice'];
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
                                        $totalCompletePriceData['completeCashPrice'][$val['refundGroupCd']] = $val['completeCashPrice'];
                                        $totalCompletePriceData['completePgPrice'][$val['refundGroupCd']] = $val['completePgPrice'];
                                        $totalCompletePriceData['completeDepositPrice'][$val['refundGroupCd']] = $val['completeDepositPrice'];
                                        $totalCompletePriceData['completeMileagePrice'][$val['refundGroupCd']] = $val['completeMileagePrice'];

                                        $goodsTotalMileage = $val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'];

                                        $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
                                        $goodsDcPrice = $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice'] + $val['enuri'] + $val['myappDcPrice'];
                                        $refundPrice = $goodsPrice - $goodsDcPrice;

                                        // 기본 배송업체 설정
                                        if (empty($val['deliverySno']) === true) {
                                            $val['orderDeliverySno'] = $deliverySno;
                                        }
                                        ?>
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][sno]" value="<?= $val['sno'] ?>"/>
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][returnStock]" value="n"/>
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][originGiveMileage]" value="<?= $goodsTotalMileage; ?>">
                                        <input type="hidden" name="refund[<?= $val['handleSno'] ?>][refundGiveMileage]" value="<?= $goodsTotalMileage; ?>">
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
                                                        <input type="text" name="refund[<?= $val['handleSno'] ?>][refundCharge]" class="form-control width-sm js-number"/> 원
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
                            <?php
                            foreach ($refundData['refundDeliveryGoodsName'] as $orderDeliverySno => $goodsNm) {
                                $etcSameDeliveryFl = '';
                                if((int)$refundData['etcSameDeliverySno'][$orderDeliverySno] > 0){
                                    $etcSameDeliveryFl = 'y';
                                }

                                $checkRefund = ($refundData['refundDeliveryPrice'][$orderDeliverySno] - $refundData['refundDeliveryDcPrice'][$orderDeliverySno]) - $refundData['realDeliveryCharge'][$orderDeliverySno];
                                ?>
                                <tr>
                                    <th>
                                        <?= $goodsNm; ?><br/>의 환불 실 배송비 금액
                                    </th>
                                    <td colspan="3" id="refundDelivery<?= $orderDeliverySno; ?>" data-handle-sno="<?= $refundData['refundDeliveryHandleSno'][$orderDeliverySno] ?>">
                                        <?php if ($statusFl) { ?>
                                            <input type="hidden" name="refund[<?= $refundData['refundDeliveryHandleSno'][$orderDeliverySno] ?>][refundDeliveryCharge]" value="<?= $refundData['realDeliveryCharge'][$orderDeliverySno]; ?>"/>
                                            <input type="text" name="refundDeliveryCharge[<?= $orderDeliverySno; ?>]" class="form-control width-sm js-number-only" value="<?= $refundData['realDeliveryCharge'][$orderDeliverySno]; ?>" data-sno="<?= $orderDeliverySno; ?>" data-handle-sno="<?= $refundData['refundDeliveryHandleSno'][$orderDeliverySno] ?>" data-price="<?= $refundData['realDeliveryCharge'][$orderDeliverySno]; ?>" data-etc-same-delivery-fl="<?= $etcSameDeliveryFl; ?>" />

                                            <?php if ($checkRefund == 0) { ?>
                                                <span style="color: #999999; font-size: 11px;">
                                                    (실 배송비 금액 <?= gd_currency_display($refundData['realDeliveryCharge'][$orderDeliverySno]); ?>
                                                    =
                                                    배송비 <?= gd_currency_display($refundData['refundDeliveryPrice'][$orderDeliverySno]); ?>
                                                    -
                                                    배송비 할인 <?= gd_currency_display($refundData['refundDeliveryDcPrice'][$orderDeliverySno]); ?>)
                                                </span>
                                            <?php } else { ?>
                                                <span style="color: #999999; font-size: 11px;">
                                                    (실 배송비 금액 <?= gd_currency_display($refundData['realDeliveryCharge'][$orderDeliverySno]); ?>
                                                    =
                                                    배송비 <?= gd_currency_display($refundData['refundDeliveryPrice'][$orderDeliverySno]); ?>
                                                    -
                                                    배송비 할인 <?= gd_currency_display($refundData['refundDeliveryDcPrice'][$orderDeliverySno]); ?>
                                                    -
                                                    이전 환불 배송비 금액 <?= gd_currency_display($checkRefund); ?>)
                                                </span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <?= gd_currency_display($refundData['refundDeliveryCharge'][$orderDeliverySno]); ?>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </colgroup>
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

                        <?php if ($statusFl) { ?>
                        <tr>
                            <th>부가결제 필수 환불 금액</th>
                            <td>
                                <span id="minRefundAddPaymentPrice">0</span>원

                                <span class="mgl10" style="color: #999999; font-size: 11px;">
                                    상품 사용금액 : <span id="minRefundGoodsAddPaymentPrice">0</span>원
                                     +
                                    배송비 사용금액 : <span id="minRefundDeliveryAddPaymentPrice">0</span>원
                                </span>
                            </td>
                        </tr>
                        <?php } ?>
                        <!-- 예치금 환불 금액 -->
                        <tr>
                            <th><?=$depositUse['name']?> 환불 금액</th>
                            <td>
                                <?php if ($statusFl) { ?>
                                    <div class="form-inline mgb5">
                                        <input type="text" class="form-control width-sm js-number-only" name="info[refundGoodsUseDeposit]" value="<?=gd_isset($refundData['refundGoodsDepositSum'], 0)?>" data-max-price="<?= gd_isset($refundData['totalRefundGoodsUseDepositPrice'], 0); ?>" />

                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                            주문의 상품에 사용된 <?=$depositUse['name']?> 총 금액 :
                                            <span style="color: red;">
                                                <?= gd_money_format($refundData['totalRefundGoodsUseDepositPrice']); ?>
                                            </span>
                                            <?=$depositUse['unit']?>
                                        </span>
                                    </div>

                                    <div class="form-inline mgb5">
                                        <input type="text" class="form-control width-sm js-number-only" name="info[refundDeliveryUseDeposit]" value="<?=gd_isset($refundData['refundDeliveryDepositSum'], 0)?>" data-max-price="<?= gd_isset($refundData['totalRefundDeliveryUseDepositPrice'], 0); ?>" />

                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                            주문의 배송비 사용된 <?=$depositUse['name']?> 총 금액 :
                                            <span style="color: red;">
                                                <?= gd_money_format($refundData['totalRefundDeliveryUseDepositPrice']); ?>
                                            </span>
                                            <?=$depositUse['unit']?>
                                        </span>
                                    </div>
                                <?php } else { ?>
                                    <?=gd_currency_display($totalRefundUseDeposit)?>
                                    &nbsp;
                                    <span style="color: #999999; font-size: 11px;">
                                        (상품: <?=gd_currency_display($totalRefundUseDeposit-$totalRefundDeliveryDeposit)?>, 배송비: <?=gd_currency_display($totalRefundDeliveryDeposit)?>)
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
                                        <input type="text" class="form-control width-sm js-number-only" name="info[refundGoodsUseMileage]" value="<?=gd_isset($refundData['refundGoodsMileageSum'], 0)?>" data-max-price="<?= gd_isset($refundData['totalRefundGoodsUseMileagePrice'], 0); ?>" />

                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                            주문의 상품에 사용된 <?=$mileageUse['name']?> 총 금액 :
                                            <span style="color: red;">
                                                <?= gd_money_format($refundData['totalRefundGoodsUseMileagePrice']); ?>
                                            </span>
                                            <?=$mileageUse['unit']?>
                                        </span>
                                    </div>

                                    <div class="form-inline mgb5">
                                        <input type="text" class="form-control width-sm js-number-only" name="info[refundDeliveryUseMileage]" value="<?=gd_isset($refundData['refundDeliveryMileageSum'], 0)?>" data-max-price="<?= gd_isset($refundData['totalRefundDeliveryUseMileagePrice'], 0); ?>" />

                                        <span class="mgl10" style="color: #999999; font-size: 11px;">
                                            주문의 배송비에 사용된 <?=$mileageUse['name']?> 총 금액 :
                                            <span style="color: red;">
                                                <?= gd_money_format($refundData['totalRefundDeliveryUseMileagePrice']); ?>
                                            </span>
                                            <?=$mileageUse['unit']?>
                                        </span>
                                    </div>
                                <?php } else { ?>
                                    <?=gd_currency_display($totalRefundUseMileage)?>
                                    &nbsp;
                                    <span style="color: #999999; font-size: 11px;">
                                        (상품: <?=gd_currency_display($totalRefundUseMileage-$totalRefundDeliveryMileage)?>, 배송비: <?=gd_currency_display($totalRefundDeliveryMileage)?>)
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
                                        <input type="text" class="form-control width-sm js-number-only js-addPaymentCommission" data-payment-type="deposit" name="info[refundUseDepositCommission]" value="0" />

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
                                            0
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
                                        <input type="text" class="form-control width-sm js-number-only js-addPaymentCommission" data-payment-type="mileage" name="info[refundUseMileageCommission]" value="0" />

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
                                            0
                                        </span>
                                        원
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
                <th>상품 환불금액</th>
                <th colspan="3" id="totalRefundGoodsPrice"><?= gd_currency_display($refundData['totalRefundGoodsPrice']); ?></th>
            </tr>
            <tr>
                <th>배송비 환불금액</th>
                <th colspan="3" id="totalRefundDeliveryPrice">
                    <?php if ($statusFl) { ?>
                    <?= gd_currency_display($refundData['totalRefundDeliveryPrice']); ?>
                    <?php } else { ?>
                    <?= gd_currency_display($totalRefundDeliveryCharge); ?>
                    <?php } ?>
                </th>
            </tr>
            <tr>
                <th>총 환불금액</th>
                <th colspan="3" id="totalRefundPrice">
                    <?php if ($statusFl) { ?>
                        <?= gd_currency_display($refundData['totalRefundPrice']); ?>
                    <?php } else { ?>
                        <?= gd_currency_display($totalRefundCompletePrice); ?>
                    <?php } ?>
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
                        <input type="hidden" name="info[refundDeliveryInsuranceFee]" value="<?=gd_isset($refundData['refundOverseaDeliveryPriceSum'], 0)?>" />
                        <?= gd_currency_display($refundData['refundOverseaDeliveryPriceSum']); ?>
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
                    <?php if ($data['orderChannelFl'] == 'payco' && $data['settleKind'] == 'fv' && $data['paycoData']['refundUseYn'] == 'Y') { ?>
                    <span class="notice-danger payco-fv-notice">(PG환불로 처리 가능한 주문입니다.)</span>
                    <?php } ?>
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

                <?php if ($data['orderChannelFl'] == 'payco' && $data['settleKind'] == 'fv' && $data['paycoData']['refundUseYn'] == 'Y') { ?>
                <div class="pg-notice notice-danger">(계약된 PG사로 입력된 금액만큼 취소 요청이 됩니다.)</div>
                <?php } else { ?>
                <div class="pg-notice notice-danger">(계약된 PG사로 입력된 금액만큼 카드 승인취소 요청이 됩니다.)</div>
                <?php } ?>
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
                                    <input type="radio" name="returnStockFl" value="n" checked="checked"/> 복원안함
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="returnStockFl" value="y"/> 복원함
                                </label>
                            </td>
                        </tr>
                        <?php if (count($couponData) > 0) { ?>
                        <tr>
                            <th>쿠폰 복원 설정</th>
                            <td>
                                <label class="radio-inline">
                                    <input type="radio" name="returnCouponFl" value="n" checked="checked"/> 복원안함
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="returnCouponFl" value="y"/> 복원함
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
                                                    <input type="radio" name="returnCoupon[<?= $val['memberCouponNo']; ?>]" value="n" checked="checked"/> 복원안함
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" name="returnCoupon[<?= $val['memberCouponNo']; ?>]" value="y"/> 복원함
                                                </label>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php if(count($giftData) > 0) { ?>
                            <tr>
                                <th>사은품 지급 설정</th>
                                <td>
                                    <label class="radio-inline">
                                        <input type="radio" name="returnGiftFl" value="y" checked="checked"/> 지급함
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="returnGiftFl" value="n"/> 지급안함
                                    </label>
                                </td>
                            </tr>
                            <tr class="display-none js-detail-display">
                                <td colspan="2">
                                    <table class="table table-rows">
                                        <thead>
                                        <tr>
                                            <th>사은품조건명</th>
                                            <th>사은품</th>
                                            <th>수량</th>
                                            <th>지급여부</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($giftData as $key => $val) { ?>
                                            <tr>
                                                <td><?= $val['presentTitle']; ?></td>
                                                <td><?= html_entity_decode($val['imageUrl']); ?><?= $val['giftNm']; ?></td>
                                                <td><?= $val['giveCnt']; ?></td>
                                                <td>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="returnGift[<?= $val['sno']; ?>]" value="y" checked="checked"/> 지급함
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="returnGift[<?= $val['sno']; ?>]" value="n"/> 지급안함
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
                <button type="submit" class="btn btn-lg btn-black">확인</button>
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
                var goodsRefundAddPaymentPrice = parseInt($("input[name='info[refundGoodsUseDeposit]']").val()) + parseInt($("input[name='info[refundGoodsUseMileage]']").val());
                var etcRefundGoodsAddPaymentPrice = $("input[name='etcRefundGoodsAddPaymentPrice']").val();
                if(etcRefundGoodsAddPaymentPrice > goodsRefundAddPaymentPrice){
                    alert("부가결제 금액(상품)은 " + numeral(etcRefundGoodsAddPaymentPrice).format() + '<?= gd_currency_string(); ?> ' + " 이상 환불되어야 합니다.");
                    return false;
                }

                var deliveryRefundAddPaymentPrice = parseInt($("input[name='info[refundDeliveryUseDeposit]']").val()) + parseInt($("input[name='info[refundDeliveryUseMileage]']").val());
                var etcRefundDeliveryAddPaymentPrice = $("input[name='etcRefundDeliveryAddPaymentPrice']").val();
                if(etcRefundDeliveryAddPaymentPrice > deliveryRefundAddPaymentPrice){
                    alert("부가결제 금액(배송비)은 " + numeral(etcRefundDeliveryAddPaymentPrice).format() + '<?= gd_currency_string(); ?> ' + " 이상 환불되어야 합니다.");
                    return false;
                }

                if ($('input[name="lessRefundPrice"]').val() == 0 && $('input[name="refundPriceSum"]').val() == $('input[name="userRealRefundPrice"]').val()) {
                    var refundDeliveryPriceSum = 0;
                    $.each($("input[name*='refundDeliveryCharge[']"), function (k, v) {
                        refundDeliveryPriceSum += parseInt($(this).val());
                    });
                    refundDeliveryPriceSum = refundDeliveryPriceSum - parseInt($("input[name='info[refundDeliveryUseDeposit]']").val()) - parseInt($("input[name='info[refundDeliveryUseMileage]']").val());
                    $("input[name='refundDeliveryPriceSum']").val(refundDeliveryPriceSum);
                    $("input[name='refundGoodsPriceSum']").val($("input[name='refundPriceSum']").val() - refundDeliveryPriceSum);


                    form.target = 'ifrmProcess';
                    form.submit();
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
                setrefundAllPrice(inputTextEl);
            } else if ($(this).val() === 'x') {
                inputTextEl.attr("disabled", true);
                inputTextEl.val(0);
                inputTextEl.trigger('blur');
                setrefundAllPrice(inputTextEl);
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

        // 환불 수수료
        $(document).on("keyup blur", "input[name*='refund[']", function () {
            var eachRefundSum = 0;
            $.each($('input[name*="][refundCharge]"]'), function (k, v) {
                if ($(this).val() == '') {
                    $(this).val(0);
                }
                if ($.isNumeric($(this).val())) {
                    eachRefundSum = eachRefundSum + parseInt($(this).val());
                }
            });
            refundCharge = eachRefundSum;

            $('#refundCharge').text(numeral(refundCharge).format() + '<?= gd_currency_string(); ?> ');
            setRefundPrice();
        });

        // 환불 배송비
        $(document).on("keyup blur", "input[name*='refundDeliveryCharge[']", function () {
            //상품의 예치금, 마일리지 환불 최대금액 체크
            var totalRefundGoodsPrice = parseInt('<?= $refundData['totalRefundGoodsPrice']; ?>');// 상품 환불 금액
            if((parseInt($("input[name='info[refundGoodsUseDeposit]']").val()) + parseInt($("input[name='info[refundGoodsUseMileage]']").val())) > totalRefundGoodsPrice){
                $("input[name='info[refundGoodsUseDeposit]'], input[name='info[refundGoodsUseMileage]']").val(0);
            }

            //배송비의 예치금, 마일리지 환불 최대금액 체크
            if((parseInt($("input[name='info[refundDeliveryUseDeposit]']").val()) + parseInt($("input[name='info[refundDeliveryUseMileage]']").val())) > refundDeliveryPrice){
                $("input[name='info[refundDeliveryUseDeposit]'], input[name='info[refundDeliveryUseMileage]']").val(0);
            }

            setDeliveryPriceCheck($(this));
        });

        // 사용된 예치금, 마일리지 환불금액
        $(document).on("blur", "input[name='info[refundGoodsUseDeposit]'], input[name='info[refundGoodsUseMileage]'], input[name='info[refundDeliveryUseDeposit]'], input[name='info[refundDeliveryUseMileage]']", function () {
            if(!$(this).val()) {
                $(this).val(0);
            }
            if(parseInt($(this).val()) > parseInt($(this).data('max-price'))){
                alert('최대 환불가능 금액을 초과할 수 없습니다.');
                $(this).val(0);

                setCommissionPrice('all');
                setCommissionMaxPriceText();
                return;
            }

            if(checkAddPaymentMax(refundDeliveryPrice, true) === false){
                setCommissionPrice('all');
                setCommissionMaxPriceText();
                return;
            }

            setRefundPrice();
        });

        function setDeliveryPriceCheck(obj) {
            if (obj.val() > parseInt(obj.data('price'))) {
                obj.val(obj.data('price'));
                alert('최대 환불 가능 금액을 초과할 수 없습니다.');
            }
            if (obj.val() < 0) {
                obj.val(0);
                alert('최소 환불 가능 금액은 0보다 작을 수 없습니다.');
            }
            if (obj.val() == '') {
                obj.val(0);
            }
            setDeliveryPrice();
        }

        function setDeliveryPrice() {
            refundDeliveryPrice = 0;
            refundSameDeliveryPrice = 0;
            $.each($("input[name*='refundDeliveryCharge[']"), function (k, v) {
                refundDeliveryPrice += parseInt($(this).val());
                $('input[name="refund[' + $(this).data('handle-sno') + '][refundDeliveryCharge]"]').val($(this).val());

                if($(this).data('etc-same-delivery-fl') === 'y'){
                    refundSameDeliveryPrice += parseInt($(this).val());
                }
            });
            setRefundPrice();
        }

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
                }
            });
            lessEachRefund = lessRefundPrice - refundPriceSum;
            $('#completeRestPrice').text(numeral(lessEachRefund).format() + '<?= gd_currency_string(); ?> ');
            $('input[name="lessRefundPrice"]').val(lessEachRefund);
            $('input[name="refundPriceSum"]').val(refundPriceSum);
        });
        $('#refundMethod').trigger('click');

        $('input[name="returnCouponFl"]').change(function (e) {
            if ($(this).val() == 'y') {
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

        function setRefundPrice() {
            // 상품 환불 금액
            var totalRefundGoodsPrice = parseInt('<?= $refundData['totalRefundGoodsPrice']; ?>');
            // 배송비 환불 금액
            var totalRefundDeliveryPrice = refundDeliveryPrice;
            // 사용된 예치금 환불 가능 금액
            var totalRefundDepositPrice = parseInt($("input[name='info[refundGoodsUseDeposit]']").val()) + parseInt($("input[name='info[refundGoodsUseMileage]']").val());
            // 사용된 마일리지 환불 가능 금액
            var totalRefundMileagePrice = parseInt($("input[name='info[refundDeliveryUseDeposit]']").val()) + parseInt($("input[name='info[refundDeliveryUseMileage]']").val());
            // 부가결제 필수 환불 금액
            var requireRefundAddPayment = 0;

            //남은 상품의 결제가 (부가결제금액 제외)
            var etcGoodsSettlePrice = parseInt($("input[name='etcGoodsSettlePrice']").val());
            var ableGoodsAddPaymentPrice = parseInt('<?=$refundData['totalRefundGoodsUseDepositPrice']?>') + parseInt('<?=$refundData['totalRefundGoodsUseMileagePrice']?>');
            // 남은 부가결제 금액이 남은 상품 의 결제가보다 크다면 강제적으로 해당 차익을 환불하여야 한다.
            if(ableGoodsAddPaymentPrice > etcGoodsSettlePrice){
                $("input[name='etcRefundGoodsAddPaymentPrice']").val(ableGoodsAddPaymentPrice-etcGoodsSettlePrice);
                requireRefundAddPayment += (ableGoodsAddPaymentPrice-etcGoodsSettlePrice);
            }
            else {
                $("input[name='etcRefundGoodsAddPaymentPrice']").val(0);
            }

            //남은 상품의 배송비 (부가결제금액 제외)
            var etcDeliverySettlePrice = parseInt($("input[name='etcDeliverySettlePrice']").val()) - parseInt(refundSameDeliveryPrice);
            var ableDeliveryAddPaymentPrice = parseInt('<?=$refundData['totalRefundDeliveryUseDepositPrice']?>') + parseInt('<?=$refundData['totalRefundDeliveryUseMileagePrice']?>');
            // 남은 부가결제 금액이 남은 배송비 의 결제가보다 크다면 강제적으로 해당 차익을 환불하여야 한다.
            if(ableDeliveryAddPaymentPrice > etcDeliverySettlePrice){
                $("input[name='etcRefundDeliveryAddPaymentPrice']").val(ableDeliveryAddPaymentPrice-etcDeliverySettlePrice);
                requireRefundAddPayment += (ableDeliveryAddPaymentPrice-etcDeliverySettlePrice);
            }
            else {
                $("input[name='etcRefundDeliveryAddPaymentPrice']").val(0);
            }

            $("input[name='etcRefundAddPaymentPrice']").val(requireRefundAddPayment);

            // 상품 환불 금액 - 환불 수수료
            totalRefundGoodsPrice = totalRefundGoodsPrice - refundCharge;
            // 총 환불금액
            var totalRefundPrice = totalRefundGoodsPrice + totalRefundDeliveryPrice - (totalRefundDepositPrice + totalRefundMileagePrice);

            $('#totalRefundGoodsPrice').text(numeral(totalRefundGoodsPrice).format() + '<?= gd_currency_string(); ?> ');
            $('#totalRefundDeliveryPrice').text(numeral(totalRefundDeliveryPrice).format() + '<?= gd_currency_string(); ?> ');
            $('#totalRefundPrice').text(numeral(totalRefundPrice).format() + '<?= gd_currency_string(); ?>');
            $('#txtRealRefundPrice').text(numeral(totalRefundPrice).format() + '<?= gd_currency_string(); ?>');
            $('input[name="check[totalDeliveryCharge]"]').val(totalRefundDeliveryPrice);
            $('input[name="check[totalRefundCharge]"]').val(refundCharge);
            $('input[name="check[totalRefundPrice]"]').val(totalRefundPrice);
            $('input[name="userRealRefundPrice"]').val(totalRefundPrice);
            $('input[name="lessRefundPrice"]').val(totalRefundPrice);
            $('#completeRestPrice').text(numeral(totalRefundPrice).format() + '<?= gd_currency_string(); ?> ');

            $('#minRefundGoodsAddPaymentPrice').text(numeral($("input[name='etcRefundGoodsAddPaymentPrice']").val()).format());
            $('#minRefundDeliveryAddPaymentPrice').text(numeral($("input[name='etcRefundDeliveryAddPaymentPrice']").val()).format());
            $('#minRefundAddPaymentPrice').text(numeral($("input[name='etcRefundAddPaymentPrice']").val()).format());

            // 부가결제수수료 최대 적용가능금액 text 노출
            setCommissionPrice('all');
            setCommissionMaxPriceText();
        }

        <?php if ($statusFl) { ?>
        setDeliveryPrice();
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
            $('input[name="mode"]').attr('value', 'refund_complete');
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
            setCommissionPrice('deposit');
        });

        // 마일리지 환불 금액 동시 입력
        $("input[name='refundUseMileageCommissionWithFl']").click(function () {
            setCommissionPrice('mileage');
        });

        // 예치금, 마일리지 부가결제 수수료 input 변경시
        $(document).on("change", ".js-addPaymentCommission", function () {
            // 부가결제 타입
            var paymentType = $(this).data('payment-type');
            // 입력된 부가결제 수수료 값
            var commissionPrice = parseInt($(this).val());

            if(paymentType === 'deposit'){
                // 입력된 예치금 총 합
                var useDepositPrice = parseInt($("input[name='info[refundGoodsUseDeposit]']").val()) + parseInt($("input[name='info[refundDeliveryUseDeposit]']").val());
                if(commissionPrice > useDepositPrice){
                    alert("최대 입력가능한 예치금 부가결제 수수료는  " + useDepositPrice + '<?= gd_currency_string(); ?> ' + " 입니다.");
                    $(this).val(useDepositPrice);
                    return;
                }
            }
            else if(paymentType === 'mileage'){
                // 입력된 마일리지 총 합
                var useMileagePrice = parseInt($("input[name='info[refundGoodsUseMileage]']").val()) + parseInt($("input[name='info[refundDeliveryUseMileage]']").val());
                if(commissionPrice > useMileagePrice){
                    alert("최대 입력가능한 마일리지 부가결제 수수료는  " + useMileagePrice + '<?= gd_currency_string(); ?> ' + " 입니다.");
                    $(this).val(useMileagePrice);
                    return;
                }
            }
            else { }
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

    function checkAddPaymentMax(refundDeliveryPrice, resetFl)
    {
        // 입력된 상품의 부가결제 금액 총합
        var totalGoodsAddPayment = parseInt($("input[name='info[refundGoodsUseDeposit]']").val()) + parseInt($("input[name='info[refundGoodsUseMileage]']").val());
        // 입력된 배송비의 부가결제 금액 총합
        var totalDeliveryAddPayment = parseInt($("input[name='info[refundDeliveryUseDeposit]']").val()) + parseInt($("input[name='info[refundDeliveryUseMileage]']").val());
        // 상품 환불 금액
        var totalRefundGoodsPrice = parseInt('<?= $refundData['totalRefundGoodsPrice']; ?>');

        // 상품의 예치금, 마일리지 환불 최대금액 체크
        if(totalGoodsAddPayment > totalRefundGoodsPrice){
            alert("부가결제 환불금액은 상품환불금액인 " + numeral(totalRefundGoodsPrice).format() + '<?= gd_currency_string(); ?> ' + "이상 환불 할 수 없습니다.");
            if(resetFl === true){
                $("input[name='info[refundGoodsUseDeposit]'], input[name='info[refundGoodsUseMileage]']").val(0);
            }
            return false;
        }
        // 배송비의 예치금, 마일리지 환불 최대금액 체크
        if(totalDeliveryAddPayment > refundDeliveryPrice){
            alert("부가결제 환불금액은 배송비환불금액인 " + numeral(refundDeliveryPrice).format() + '<?= gd_currency_string(); ?> ' + "이상 환불 할 수 없습니다.");
            if(resetFl === true){
                $("input[name='info[refundDeliveryUseDeposit]'], input[name='info[refundDeliveryUseMileage]']").val(0);
            }
            return false;
        }

        return true;
    }

    function setCommissionMaxPriceText()
    {
        // 입력된 예치금 총 합
        var useDepositPrice = parseInt($("input[name='info[refundGoodsUseDeposit]']").val()) + parseInt($("input[name='info[refundDeliveryUseDeposit]']").val());
        // 입력된 마일리지 총 합
        var useMileagePrice = parseInt($("input[name='info[refundGoodsUseMileage]']").val()) + parseInt($("input[name='info[refundDeliveryUseMileage]']").val());

        // 부가결제 수수료의 최대 환불금액 텍스트 노출
        $("#refundUseDepositCommissionMaxText").html(useDepositPrice);
        $("#refundUseMileageCommissionMaxText").html(useMileagePrice);
    }

    function setCommissionPrice(paymentType)
    {
        if(paymentType === 'all' || paymentType === 'deposit'){
            // 입력된 예치금 총 합
            var useDepositPrice = parseInt($("input[name='info[refundGoodsUseDeposit]']").val()) + parseInt($("input[name='info[refundDeliveryUseDeposit]']").val());
            // 입력된 예치금 부가결제 수수료
            var depositCommission = parseInt($("input[name='info[refundUseDepositCommission]']").val());

            if(depositCommission > useDepositPrice) {
                $("input[name='info[refundUseDepositCommission]']").val(useDepositPrice);
            }
            if($("input[name='refundUseDepositCommissionWithFl']").prop("checked") === true){
                $("input[name='info[refundUseDepositCommission]']").val(useDepositPrice);
            }
        }
        if(paymentType === 'all' || paymentType === 'mileage'){
            // 입력된 마일리지 총 합
            var useMileagePrice = parseInt($("input[name='info[refundGoodsUseMileage]']").val()) + parseInt($("input[name='info[refundDeliveryUseMileage]']").val());
            // 입력된 예치금 부가결제 수수료
            var mileageCommission = $("input[name='info[refundUseMileageCommission]']").val();

            if(mileageCommission > useMileagePrice) {
                $("input[name='info[refundUseMileageCommission]']").val(useMileagePrice);
            }
            if($("input[name='refundUseMileageCommissionWithFl']").prop("checked") === true){
                $("input[name='info[refundUseMileageCommission]']").val(useMileagePrice);
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
