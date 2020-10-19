<?php
/**
 * 공통 상품주문번호별 간편 리스트 레이아웃
 * 결제완료|상품준비중|배송중|배송완료|구매확정 리스트에서 사용
 *
 * !주의! CRM 주문내역, 주문상세, 클레임접수 리스트, 환불상세 모두 동시에 수정되어야 하며, layout_order_goods.php 반드시 확인이 필요하다.
 *
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
use Component\Naver\NaverPay;
use Framework\Utility\StringUtils;
?>

<div class="table-responsive">
    <table class="table table-rows order-list">
        <thead>
        <?php
        if (count($orderGridConfigList) > 0) {
            foreach($orderGridConfigList as $gridKey => $gridName){
                $addClass = '';
                //주문상세창 열기 옵션 설정
                if($gridKey === 'openLinkOption') {
                    if(gd_isset($gridName) === null) {
                        $openType = 'newTab';
                    } else {
                        $openType = $gridName;
                    }
                    continue; //테이블에서 노출 금지
                }
                if($gridKey === 'orderGoodsNm'){
                    $addClass = " class='orderGoodsNm' ";
                }
                if($gridKey === 'check'){
                    echo "<th><input type='checkbox' value='y' class='js-checkall' data-target-name='statusCheck'/></th>";
                }
                else {
                    echo "<th ".$addClass.">".$gridName."</th>";
                }
            }
        }
        ?>
        </thead>
        <tbody>
        <?php
        $naverPay = new NaverPay();
        if (empty($data) === false && is_array($data)) {
            $sortNo = 1; // 번호 설정
            $totalCnt = 0; // 주문서 수량 설정
            $totalGoods = 0; // 주문서 수량 설정
            $totalPrice = 0; // 주문 총 금액 설정
            foreach ($data as $orderNo => $orderData) {
                $rowCnt = $orderData['cnt']['goods']['all']; // 한 주문당 상품주문 수량
                $rowChk = 0; // 한 주문당 첫번째 주문 체크용
                $rowAddChk = 0; //
                $totalCnt++; // 주문서 수량
                foreach ($orderData['goods'] as $sKey => $sVal) {
                    $rowScm = 0;
                    $rowMultiShipping = 0;
                    foreach ($sVal as $dKey => $dVal) {
                        $rowDelivery = 0;
                        foreach ($dVal as $key => $val) {
                            $val['orderCellPhone'] =  StringUtils::numberToCellPhone($val['orderCellPhone']);

                            $statusMode = substr($val['orderStatus'], 0, 1);

                            $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                            $settlePrice = ($val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['divisionCouponOrderDcPrice'];
                            if ($val['orderChannelFl'] == 'naverpay') {
                                $checkoutData = json_decode($val['checkoutData'], true);
                                if ($naverPay->getStatusText($checkoutData)) {
                                    $naverImg = sprintf("<img src='%s' > ", \UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www());
                                    $val['orderStatusStr'] .= '<br>(' . $naverImg . $naverPay->getStatusText($checkoutData) . ')';
                                }
                            }
                            $totalGoods++; // 상품 수량
                            if ($key === 0) {
                                $totalPrice = $totalPrice + $val['settlePrice']; // 주문 총 금액(누적)
                            }
                            if (in_array($val['statusMode'], $statusListCombine)) {
                                $checkBoxCd = $orderNo;
                            } else {
                                $checkBoxCd = $orderNo . INT_DIVISION . $val['sno'];
                            }

                            // 주문일괄처리 제외대상 비활성화
                            if ($isUserHandle) {
                                $checkDisabled = ($isUserHandle && $val['userHandleFl'] != 'r' ? 'disabled="disabled"' : '');
                            } else {
                                $checkDisabled = '';
                            }

                            //배송업체가 설정되어 있지 않을시 기본 배송업체 select
                            $selectInvoiceCompanySno = $val['invoiceCompanySno'];
                            if((int)$selectInvoiceCompanySno < 1){
                                if ($val['deliveryMethodFl'] == 'delivery') {
                                    $selectInvoiceCompanySno = $deliverySno;
                                } else {
                                    $selectInvoiceCompanySno = $deliveryEtcCom[$val['deliveryMethodFl']];
                                }
                            }

                            // rowspan 처리
                            $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                            $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';
                            //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scm no 를 order info sno 로 대체, dKey는 order delivery sno로 대체
                            if($useMultiShippingKey === true){
                                $rowScm = 0;
                                $orderMultiShippingRowSpan = ' rowspan="' . ($orderData['cnt']['multiShipping'][$sKey]) . '"';
                            }
                            else {
                                $orderScmRowSpan = ' rowspan="' . ($orderData['cnt']['scm'][$sKey]) . '"';
                            }
                            ?>
                            <tr class="text-center" data-mall-sno="<?=$val['mallSno']?>">
                                <?php
                                // 주문리스트 그리드 항목 시작
                                if (count($orderGridConfigList) > 0) {
                                    foreach($orderGridConfigList as $gridKey => $gridName){
                                        ?>
                                        <?php if($gridKey === 'check'){ // 선택 ?>
                                            <?php if (in_array($currentStatusCode, $statusListCombine)) { ?>
                                                <?php if ($rowChk === 0) { ?>
                                                    <td <?= $orderGoodsRowSpan; ?>>
                                                        <input type="checkbox" name="statusCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>"/>
                                                        <input type="hidden" name="orderStatus[<?= $val['statusMode'] ?>][]" value="<?= $val['orderStatus']; ?>"/>
                                                        <input type="hidden" name="escrowCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $val['escrowFl'] . $val['escrowDeliveryFl']; ?>"/>
                                                        <input type="hidden" name="orderChannelFl[<?= $val['statusMode'] ?>][<?=$checkBoxCd?>]" value="<?= $val['orderChannelFl']; ?>"/>
                                                        <input type="hidden" name="deliveryMethodFl[<?= $val['statusMode']; ?>][<?=$checkBoxCd?>]" value="<?= $val['deliveryMethodFl']; ?>"/>
                                                        <?php if (in_array($currentStatusCode, ['r', 'e', 'b'])) { ?>
                                                            <input type="hidden" name="handleSno[<?= $val['statusMode'] ?>][]" value="<?= $val['handleSno']; ?>"/>
                                                            <input type="hidden" name="beforeStatus[<?= $val['statusMode'] ?>][]" value="<?= $val['beforeStatus']; ?>"/>
                                                        <?php } ?>

                                                        <?php if($currentStatusCode === 'g' && trim($val['packetCode']) !== ''){ ?>
                                                            <div style="width:20px; height:15px; background-color:#8041D9; color:white; font-size:7px; margin: 0 auto; margin-top: 4px;">묶음</div>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <td <?= $orderAddGoodsRowSpan ?>>
                                                    <input type="checkbox" name="statusCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>"/>
                                                    <input type="hidden" name="orderStatus[<?= $val['statusMode'] ?>][]" value="<?= $val['orderStatus']; ?>"/>
                                                    <input type="hidden" name="escrowCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $val['escrowFl'] . $val['escrowDeliveryFl']; ?>"/>
                                                    <input type="hidden" name="orderChannelFl[<?= $val['statusMode'] ?>][<?=$checkBoxCd?>]" value="<?= $val['orderChannelFl']; ?>"/>
                                                    <input type="hidden" name="deliveryMethodFl[<?= $val['statusMode']; ?>][<?=$checkBoxCd?>]" value="<?= $val['deliveryMethodFl']; ?>"/>
                                                    <?php if (in_array($currentStatusCode, ['r', 'e', 'b'])) { ?>
                                                        <input type="hidden" name="handleSno[<?= $val['statusMode'] ?>][]" value="<?= $val['handleSno']; ?>"/>
                                                        <input type="hidden" name="beforeStatus[<?= $val['statusMode'] ?>][]" value="<?= $val['beforeStatus']; ?>"/>
                                                    <?php } ?>

                                                    <?php if($currentStatusCode === 'g' && trim($val['packetCode']) !== ''){ ?>
                                                        <div style="width:20px; height:15px; background-color:#8041D9; color:white; font-size:7px; margin: 0 auto; margin-top: 4px;">묶음</div>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                        <?php } // 선택 ?>

                                        <?php if($gridKey === 'no'){ // 번호 ?>
                                            <td <?= $orderAddGoodsRowSpan ?> class="font-num">
                                                <small><?= $page->idx--; ?></small>
                                            </td>
                                        <?php } // 번호 ?>

                                        <?php if($gridKey === 'domainFl' && $rowChk === 0){ // 상점구분 ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="font-kor">
                                                <span class="flag flag-16 flag-<?=$val['domainFl']?>"></span><?=$val['mallName']?>
                                            </td>
                                        <?php } // 상점구분 ?>

                                        <?php if($gridKey === 'regDt' && $rowChk === 0){ // 주문일시 ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?>
                                            </td>
                                        <?php } // 주문일시 ?>

                                        <?php if($gridKey === 'paymentDt' && $rowChk === 0){ // 입금일시 ?>
                                            <td <?= $orderGoodsRowSpan ?> class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['paymentDt'])); ?>
                                            </td>
                                        <?php } // 입금일시 ?>

                                        <?php if($gridKey === 'orderNo' && $rowChk === 0){ // 주문번호 ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="order-no">
                                                <?php if ($val['firstSaleFl'] == 'y') { ?>
                                                    <p class="mgb0"><img src="<?=PATH_ADMIN_GD_SHARE?>img/order/icon_firstsale.png" alt="첫주문" /></p>
                                                <?php } ?>
                                                <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><?= $orderNo; ?></a><img src="<?=PATH_ADMIN_GD_SHARE?>img/icon_grid_open.png" alt="팝업창열기" class="hand mgl5" border="0" onclick="javascript:order_view_popup('<?=$orderNo?>', '<?=$isProvider?>');" />
                                                <?php if ($val['orderChannelFl'] == 'naverpay') { ?>
                                                    <p>
                                                        <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><img
                                                                    src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderNo']; ?></a>
                                                    </p>
                                                <?php } else if($val['orderChannelFl'] == 'payco') { ?>
                                                    <img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'payco.gif')->www() ?>"/>
                                                <?php } else if ($val['orderChannelFl'] == 'etc') { ?>
                                                    <p>
                                                        <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>">
                                                            <img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'etc.gif')->www() ?>"/>
                                                            <?= $val['apiOrderNo']; ?>
                                                        </a>
                                                    </p>
                                                <?php } else { } ?>
                                                <?php if (empty($val['trackingKey']) === false) {echo '<div class="c-gdred">' . $channel['paycoShopping'] . '</div>';}?>
                                            </td>
                                        <?php } // 주문번호 ?>

                                        <?php if($gridKey === 'orderName' && $rowChk === 0){ // 주문자 ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="js-member-info" data-member-no="<?= $val['memNo'] ?>" data-member-name="<?= $val['orderName'] ?>" data-cell-phone="<?= $val['smsCellPhone'] ?>">
                                                <?= $val['orderName'] ?>
                                                <p class="mgb0">
                                                    <?php if (!$val['memNo']) { ?>
                                                        <?php if (!$val['memNoCheck']) { ?>
                                                            <span class="font-kor">(비회원)</span>
                                                        <?php } else { ?>
                                                            <span class="font-kor">(탈퇴회원)</span>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if (!$isProvider) { ?>
                                                            <button type="button" class="btn btn-link font-eng js-layer-crm" data-member-no="<?= $val['memNo'] ?>">(<?= $val['memId'] ?>/<?=$val['groupNm']?>)
                                                        <?php } else { ?>
                                                            (<?= $val['memId'] ?>/<?=$val['groupNm']?>)
                                                        <?php } ?>
                                                        </button>
                                                    <?php } ?>
                                                </p>
                                            </td>
                                        <?php } // 주문자 ?>

                                        <?php if($gridKey === 'orderGoodsNm'){ // 주문상품 ?>
                                            <td class="text-left">
                                                <table>
                                                    <tr>
                                                        <td class="text-left border-right-none" style="width: 40px;">
                                                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                            <?php } else { ?>
                                                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                            <?php } ?>
                                                        </td>
                                                        <td class="text-left" style="border: 0px;">
                                                            <?php if ($search['userHandleAdmFl'] == 'y' && empty($val['userHandleInfo']) === false) { ?>
                                                                <div>
                                                                    <?php foreach ($val['userHandleInfo'] as $userHandleInfo) { ?>
                                                                        <span class="label label-white"><?php echo $userHandleInfo; ?></span>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } ?>

                                                            <?php if($val['handleMode'] === 'e'){ ?>
                                                                <span class="label label-danger">교환취소</span><br />
                                                            <?php } else if ($val['handleMode'] === 'z'){ ?>
                                                                <span class="label label-primary">교환추가</span><br />
                                                            <?php } ?>

                                                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                                                <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                                                   onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></a>
                                                            <?php } else { ?>
                                                                <a href="javascript:void();" class="one-line bold mgb5" title="상품명"
                                                                   onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></a>
                                                            <?php } ?>
                                                            <?php
                                                            // 옵션 처리
                                                            if (empty($val['optionInfo']) === false) {
                                                                echo '<div class="option_info" title="상품 옵션">';
                                                                foreach ($val['optionInfo'] as $option) {
                                                                    $tmpOption[] = $option[0] . ':' . $option[1];
                                                                }
                                                                echo implode(', ', $tmpOption);
                                                                echo '</div>';
                                                                unset($tmpOption);
                                                            }

                                                            // 텍스트 옵션 처리
                                                            if (empty($val['optionTextInfo']) === false) {
                                                                echo '<div class="option_info" title="텍스트 옵션">';
                                                                foreach ($val['optionTextInfo'] as $option) {
                                                                    $tmpOption[] = $option[0] . ':' . $option[1];
                                                                }
                                                                echo implode(', ', $tmpOption);
                                                                echo '</div>';
                                                                unset($tmpOption);}
                                                            ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        <?php } // 주문상품 ?>

                                        <?php if($gridKey === 'orderGoodsNmGlobal'){ // 주문상품(해외상점) ?>
                                            <td class="text-left">
                                                <?php if($val['mallSno'] != DEFAULT_MALL_NUMBER){ ?>
                                                    <table>
                                                        <tr>
                                                            <td class="text-left border-right-none" style="width: 40px;">
                                                                <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                    <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                                <?php } else { ?>
                                                                    <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                                <?php } ?>
                                                            </td>
                                                            <td class="text-left" style="border: 0px;">
                                                                <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                    <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                                                    <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                                                       onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></a>
                                                                <?php } else { ?>
                                                                    <a href="javascript:void();" class="one-line bold mgb5" title="상품명"
                                                                       onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></a>
                                                                <?php } ?>
                                                                <?php
                                                                // 옵션 처리
                                                                if (empty($val['optionInfo']) === false) {
                                                                    echo '<div class="option_info" title="상품 옵션">';
                                                                    foreach ($val['optionInfo'] as $option) {
                                                                        $tmpOption[] = $option[0] . ':' . $option[1];
                                                                    }
                                                                    echo implode(', ', $tmpOption);
                                                                    echo '</div>';
                                                                    unset($tmpOption);
                                                                }

                                                                // 텍스트 옵션 처리
                                                                if (empty($val['optionTextInfo']) === false) {
                                                                    echo '<div class="option_info" title="텍스트 옵션">';
                                                                    foreach ($val['optionTextInfo'] as $option) {
                                                                        $tmpOption[] = $option[0] . ':' . $option[1];
                                                                    }
                                                                    echo implode(', ', $tmpOption);
                                                                    echo '</div>';
                                                                    unset($tmpOption);}
                                                                ?>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                <?php } else { ?>
                                                    <div style="width:100%; text-align: center;">-</div>
                                                <?php } ?>
                                            </td>
                                        <?php } // 주문상품(해외상점) ?>

                                        <?php if($gridKey === 'totalGoodsPrice' && $rowChk === 0){ // 총 상품금액 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?= gd_currency_display($val['totalGoodsPrice']); ?></td>
                                        <?php } // 총 상품금액 ?>

                                        <?php if($gridKey === 'totalDeliveryCharge' && $rowChk === 0){ // 총 배송비 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?=gd_currency_display($val['totalDeliveryCharge'])?></td>
                                        <?php } // 총 배송비 ?>

                                        <?php if($gridKey === 'totalDcPrice' && $rowChk === 0){ // 총 할인금액 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?=gd_currency_display($val['totalDcPrice'])?></td>
                                        <?php } // 총 할인금액 ?>

                                        <?php if($gridKey === 'totalUseAddedPrice' && $rowChk === 0){ // 총 부가결제금액 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?=gd_currency_display($val['totalUseAddedPrice'])?></td>
                                        <?php } // 총 부가결제금액 ?>

                                        <?php if($gridKey === 'totalOrderPrice' && $rowChk === 0){ // 총 주문금액 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?=gd_currency_display($val['totalOrderPrice'])?></td>
                                        <?php } // 총 주문금액 ?>

                                        <?php if($gridKey === 'totalRealSettlePrice' && $rowChk === 0){ //?>
                                            <td <?= $orderGoodsRowSpan ?>><?=gd_currency_display($val['totalRealSettlePrice'])?></td>
                                        <?php } // 총 실결제금액 ?>

                                        <?php if($gridKey === 'totalGoodsPriceGlobal' && $rowChk === 0){ // 총 상품금액(해외상점) ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalGoodsPrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 총 상품금액(해외상점) ?>

                                        <?php if($gridKey === 'totalDeliveryChargeGlobal' && $rowChk === 0){ // 총 배송비(해외상점) ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalDeliveryCharge'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 총 배송비(해외상점) ?>

                                        <?php if($gridKey === 'totalDcPriceGlobal' && $rowChk === 0){ // 총 할인금액(해외상점) ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalDcPrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 총 할인금액(해외상점) ?>

                                        <?php if($gridKey === 'totalOrderPriceGlobal' && $rowChk === 0){ // 총 주문금액(해외상점) ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalOrderPrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 총 주문금액(해외상점) ?>

                                        <?php if($gridKey === 'totalRealSettlePriceGlobal' && $rowChk === 0){ // 총 실결제금액(해외상점) ?>
                                            <td <?= $orderGoodsRowSpan; ?>>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['settlePrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 총 실결제금액(해외상점) ?>

                                        <?php if($gridKey === 'settleKind' && $rowChk === 0){ // 결제방법 ?>
                                            <td <?= $orderGoodsRowSpan; ?>>
                                                <?php if (is_file(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $val['settleKind'] . '.gif'))) { ?>
                                                    <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $val['settleKind'] . '.gif')->www(), $val['settleKindStr']); ?>
                                                <?php } ?>
                                                <?php if ($val['useDeposit'] > 0) { ?>
                                                    <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_gd.gif')->www(), '예치금'); ?>
                                                <?php } ?>
                                                <?php if ($val['useMileage'] > 0) { ?>
                                                    <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_gm.gif')->www(), '마일리지'); ?>
                                                <?php } ?>
                                            </td>
                                        <?php } // 결제방법 ?>

                                        <?php if($gridKey === 'processStatus'){ // 처리상태 ?>
                                            <td><?=$val['orderStatusStr']?></td>
                                        <?php } // 처리상태 ?>

                                        <?php if($gridKey === 'receiverName'){ // 수령자 ?>
                                            <?php if($useMultiShippingKey === true){ ?>
                                                <?php if($rowMultiShipping === 0){ ?>
                                                    <td <?= $orderMultiShippingRowSpan ?>><?= $val['receiverName'] ?></td>
                                                <?php } ?>
                                            <?php } else {?>
                                                <?php if($rowChk === 0){ ?>
                                                    <td <?= $orderGoodsRowSpan ?>>
                                                        <?php
                                                        echo $val['receiverName'];
                                                        if($val['totalOrderInfoCount'] > 1){
                                                            echo ' 외 ' . ((int)$val['totalOrderInfoCount']-1) . '명';
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } // 수령자 ?>

                                        <?php if($gridKey === 'multiShippingCd'){ // 배송지 ?>
                                            <?php if($useMultiShippingKey === true){ ?>
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
                                            <?php } else {?>
                                                <?php if($rowChk === 0){ ?>
                                                    <td <?= $orderGoodsRowSpan ?>>
                                                        <?=$val['totalOrderInfoCount'].'개'?>
                                                    </td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } // 배송지 ?>

                                        <?php if($gridKey === 'receiverAddress' && $rowChk === 0){ //수령자 주소?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?php if($val['receiverZonecode']){ echo "[".$val['receiverZonecode']."]"; } ?>
                                                <?php if($val['receiverZipcode']){ echo "(".$val['receiverZipcode'].")"; } ?>
                                                <br />
                                                <?php if($val['receiverAddress']){ echo $val['receiverAddress']; } ?>
                                                <?php if($val['receiverAddressSub']){ echo " ".$val['receiverAddressSub']; } ?>
                                            </td>
                                        <?php } //수령자 주소?>

                                        <?php if($gridKey === 'orderMemo'){ // 배송 메시지 ?>
                                            <?php if($useMultiShippingKey === true){ ?>
                                                <?php if($rowMultiShipping === 0){ ?>
                                                    <td <?= $orderMultiShippingRowSpan ?>>
                                                        <?=($val['orderMemo']) ? gd_html_cut($val['orderMemo'], 30, '..') : '-'?>
                                                    </td>
                                                <?php } ?>
                                            <?php } else {?>
                                                <?php if($rowChk === 0){ ?>
                                                    <td <?= $orderGoodsRowSpan ?>>
                                                        <?php
                                                        if($val['orderMemo']){
                                                            echo gd_html_cut($val['orderMemo'], 30, '..');
                                                            if($val['multiShippingFl'] === 'y'){
                                                                echo ' 외' . ((int)$val['totalOrderInfoCount']-1) . '건';
                                                            }
                                                        }
                                                        else {
                                                            echo '-';
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } // 배송 메시지 ?>

                                        <?php if($gridKey === 'receipt' && $rowChk === 0){ // 영수증 신청여부 ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['receiptFl'] != 'n') ? gd_html_image(PATH_ADMIN_GD_SHARE . 'img/receipt_icon/receipt_' . $val['receiptFl'] . '.png', null) : '미신청'?>
                                            </td>
                                        <?php } // 영수증 신청여부 ?>

                                        <?php if($gridKey === 'gift' && $rowScm === 0){ // 사은품 ?>
                                            <td <?= $orderScmRowSpan; ?> class="font-kor nowrap text-left">
                                                <ul class="list-unstyled mgb0">
                                                    <?php
                                                    if ($val['gift']) {
                                                        foreach ($val['gift'] as $gift) {
                                                            if ($val['scmNo'] == $gift['scmNo']) {
                                                                ?>
                                                                <li><?= $gift['presentTitle'] ?> | <?= $gift['giftNm'] ?> | <?= $gift['giveCnt'] ?>개</li>
                                                                <?php
                                                            }
                                                        }
                                                    } ?>
                                                </ul>
                                            </td>
                                        <?php } // 사은품 ?>

                                        <?php if($gridKey === 'goodsCnt'){ // 수량 ?>
                                            <td class="goods_cnt"><?= number_format($val['goodsCnt']); ?></td>
                                        <?php } // 수량 ?>

                                        <?php if($gridKey === 'adminMemo' && $rowChk === 0){ // 관리자메모 ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="text-center" data-order-no="<?= $val['orderNo'] ?>" data-reg-date="<?= $val['regDt'] ?>">
                                                <button type="button" class="btn btn-sm btn-<?php if($data[$orderNo]['adminOrdGoodsMemo']){ echo 'gray'; }else{ echo 'white';} ?> js-super-admin-memo" data-order-no="<?= $val['orderNo']; ?>" data-memo="<?=$data[$orderNo]['adminOrdGoodsMemo'];?>">보기</button>
                                            </td>
                                        <?php } // 관리자메모 ?>

                                        <?php if($gridKey === 'invoiceNo'){ // 송장번호 ?>
                                            <?php if ($currentStatusCode == 'g') { ?>
                                                <!-- 상품준비중 리스트 -->
                                                <?php if ($rowChk === 0) { ?>
                                                    <td <?= $orderGoodsRowSpan ?>>
                                                        <?php if ($orderData['deliveryCombineDisplayMessage'] === true) { ?>
                                                            <!-- 개별등록 해제 문구 노출  -->

                                                            <input type="hidden" name="invoiceIndividualUnsetFl[<?=$val['orderNo']?>]" value="" />
                                                            <div class="js-invoice-unset-area" style="color: red;">
                                                                <input type="checkbox" name="invoiceIndividualUnset[]" value="<?=$val['orderNo']?>" data-combine-prevent="<?=$orderData['deliveryCombinePrevent']?>"/> 개별등록 해제
                                                            </div>

                                                            <?php if($orderData['deliveryCombinePrevent'] === false) {  ?>
                                                                <div class="js-invoice-area display-none">
                                                                    <?= gd_select_box(null, 'invoiceCompanySno[' . $val['orderNo'] . ']', $deliveryCom, null, $selectInvoiceCompanySno, null); ?>
                                                                    <input type="text" name="invoiceNo[<?= $val['orderNo'] ?>]" value="" class="form-control input-sm mgt5"/>
                                                                </div>
                                                            <?php } ?>
                                                            <!-- 개별등록 해제 문구 노출  -->

                                                        <?php } else { ?>
                                                            <?= gd_select_box(null, 'invoiceCompanySno[' . $val['orderNo'] . ']', $deliveryCom, null, $selectInvoiceCompanySno, null); ?>
                                                            <input type="hidden" name="invoiceIndividualUnsetFl[<?=$val['orderNo']?>]" value="<?=$val['orderNo']?>" />
                                                            <input type="text" name="invoiceNo[<?= $val['orderNo'] ?>]" value="<?= $val['invoiceNo']; ?>" class="form-control input-sm mgt5"/>
                                                            <!-- 개별등록 해제 문구 미노출 : 주문별 배송정보 등록이 가능한 상태 -->
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                                <!-- 상품준비중 리스트 -->
                                            <?php } else { ?>
                                                <td <?= $orderAddGoodsRowSpan; ?>>
                                                    <?php if (empty($val['invoiceCompanySno']) === false && empty($val['invoiceNo']) === false) { ?>
                                                        <small><?= $val['invoiceCompanyNm']; ?> / <?= $val['invoiceNo']; ?></small>
                                                        <?php if($val['deliveryMethodFl'] === 'delivery' || $val['deliveryMethodFl'] === 'packet' || !$val['deliveryMethodFl']){ ?>
                                                            <!-- 주문상품의 배송방식이 택배일때만 배송추적을 한다. -->
                                                            <div class="delivery-trace">
                                                                <input type="button" onclick="delivery_trace('<?= $val['invoiceCompanySno']; ?>', '<?= $val['invoiceNo']; ?>');" value="배송추적" class="btn btn-sm btn-black">
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                        <?php } // 송장번호 ?>

                                        <?php if($gridKey === 'phoneNumber' && $rowChk === 0) { // 주문자 번호?>
                                            <td <?= $orderGoodsRowSpan ?>><?= $val['orderCellPhone']?></td>
                                        <?php } // 주문자 번호?>

                                        <?php if($gridKey === 'orderTypeFl') {// 주문유형?>
                                            <td><?= $val['orderTypeFlNm']?></td>
                                        <?php } // 주문유형?>
                                        <?php
                                    }
                                }
                                // 주문리스트 그리드 항목
                                ?>
                                <!-- 끝-->
                            </tr>

                            <?php
                            $rowChk++;
                            $rowScm++;
                            $rowDelivery++;
                        }
                    }
                }
            }
        } else {
            ?>
            <tr>
                <td colspan="<?=count($orderGridConfigList)?>" class="no-data">
                    검색된 주문이 없습니다.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
