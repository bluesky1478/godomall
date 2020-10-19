<?php
/**
 * 공통 상품주문번호별 리스트 레이아웃
 * 주문관리내 모든 리스트에서 사용
 *
 * !주의! CRM 주문내역, 주문상세, 클레임접수 리스트, 환불상세 모두 동시에 수정되어야 한다.
 *
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */

use Bundle\Component\Order\LogisticsOrder;
use Component\Naver\NaverPay;
$logisticsOrder = new LogisticsOrder();
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
                    echo "<th><input type='checkbox' value='y' class='js-checkall' data-target-name='statusCheck'  /></th>";
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
        /*
         * 중요!!! 복수배송지 사용시 리스트에서 출력되는 데이터
         * $orderData['goods'] 의 $sKey 는 scm no 에서 order info sno 로 대체되며
         * $dKey 는 delivery sno 에서 order delivery sno 로 대체된다.
         */
        $naverPay = new NaverPay();
        if (empty($data) === false && is_array($data)) {
            $sortNo = 1; // 번호 설정
            $rowAll = 0;
            $totalCnt = 0; // 주문서 수량 설정
            $totalGoods = 0; // 주문서 수량 설정
            $totalPrice = 0; // 주문 총 금액 설정
            foreach ($data as $orderNo => $orderData) {
                $rowCnt = $orderData['cnt']['goods']['all']; // 한 주문당 상품주문 수량
                $rowChk = 0; // 한 주문당 첫번째 주문 체크용
                $rowAddChk = 0; //
                $totalCnt++; // 주문서 수량
                foreach ($orderData['goods'] as $sKey => $sVal) {
                    $rowMultiShipping = 0;
                    $rowScm = 0;
                    foreach ($sVal as $dKey => $dVal) {
                        $rowDelivery = 0;
                        foreach ($dVal as $key => $val) {
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
//                            if ($key === 0) {
//                                $totalPrice = $totalPrice + $val['settlePrice']; // 주문 총 금액(누적)
//                            }
                            if (in_array($val['statusMode'], $statusListCombine)) {
                                $checkBoxCd = $orderNo;
                            } else {
                                if ($val['userHandleGoodsCnt'] > 0) {
                                    $checkBoxCd = $orderNo . INT_DIVISION . $val['sno'];
                                } else {
                                    $checkBoxCd = $orderNo . INT_DIVISION . $val['sno'];
                                }
                            }

                            // 주문일괄처리 제외대상 비활성화
                            $checkDisabled = '';
                            if($search['view'] == 'orderGoods') {
//                                $checkReservationResult = $logisticsOrder->checkReservation($val,'reservation', false);
//                                $checkDisabled = $checkReservationResult['result'] === false ? 'disabled="disabled"' : '';

                                $_firstKey = ($val['mpckKey'] && $val['reqDvCd'] != '02') ? $val['mpckKey'] : $val['orderInfoSno'];
                                $checkDisabled = empty($reservationInfo[$_firstKey]['reservelFl']) ? 'disabled' : '';
                            }
                            else {
                                $_firstKey = ($val['mpckKey'] && $val['reqDvCd'] != '02') ? $val['mpckKey'] : $val['orderInfoSno'];
                                $checkDisabled = empty($reservationInfo[$_firstKey]['reservelFl']) ? 'disabled' : '';
                            }

                            // rowspan 처리
                            $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                            //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scm no 를 order info sno 로 대체, dKey는 order delivery sno로 대체

                            $rowOrderInfoSnoCntSpan = ' rowspan="' . ($orderData['cnt']['orderInfoSnoCnt'][$val['orderInfoSno']]) . '"';
                            if(!$rowOrderInfoSnoCnt[$val['orderInfoSno']]) {
                                $rowOrderInfoSnoCnt[$val['orderInfoSno']] = 0;
                            }
                            if($useMultiShippingKey === true){
                                $rowScm = 0;
                                $orderMultiShippingRowSpan = ' rowspan="' . ($orderData['cnt']['multiShipping'][$sKey]) . '"';
                            }
                            else {
                                $orderScmRowSpan = ' rowspan="' . ($orderData['cnt']['scm'][$sKey]) . '"';
                            }
                            $orderDeliveryRowSpan = ' rowspan="' . ($orderData['cnt']['delivery'][$dKey]) . '"';
                            ?>
                            <tr class="text-center" data-mall-sno="<?=$val['mallSno']?>" data-channel="<?=$val['orderChannelFl']?>" data-orderinfosno="<?=$val['orderInfoSno']?>" data-packetcode="<?=$val['packetCode']?>" <?php if($val['reservationStatus'] == '예약전') echo 'data-reservation="y"'?>>
                                <!-- 주문리스트 그리드 항목 시작-->
                                <?php
                                if (count($orderGridConfigList) > 0) {
                                    foreach($orderGridConfigList as $gridKey => $gridName){
                                        ?>

                                        <?php if($gridKey === 'check'){ ?>
                                            <?php
                                            if ($search['view'] == 'order') {
                                                $tmpArrOrderGoodsNo = $orderInfoGroup[$val['orderInfoSno']];
                                                $checkBoxCd = implode(INT_DIVISION,$tmpArrOrderGoodsNo);
                                                ?>
                                                <?php if ($rowMultiShipping=== 0) {
                                                    ?>
                                                    <td <?= $orderMultiShippingRowSpan; ?>>
                                                        <input type="checkbox" name="statusCheck[]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>" data-packetcode="<?=$val['packetCode']?>" data-orderinfosno="<?=$val['orderInfoSno']?>" />
                                                        <?php
                                                        if(trim($val['packetCode']) !=''){ ?>
                                                            <div style="width:20px; height:15px; background-color:#8041D9; color:white; font-size:7px; margin: 0 auto; margin-top: 4px;">묶음</div>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } else {
                                                ?>
                                                <td>
                                                    <input type="checkbox" name="statusCheck[]" <?= $checkDisabled ?> value="<?= $val['sno']; ?>"/>

                                                    <?php if(trim($val['packetCode']) !== ''){ ?>
                                                        <div style="width:20px; height:15px; background-color:#8041D9; color:white; font-size:7px; margin: 0 auto; margin-top: 4px;">묶음</div>
                                                    <?php } ?>
                                                </td>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if($gridKey === 'no'){ ?>
                                            <td class="font-num">
                                                <small><?= $page->idx--; ?></small>
                                            </td>
                                        <?php } ?>
                                        <?php if($gridKey === 'regDt'){ ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?>
                                            </td>
                                        <?php } ?>
                                        <?php if($gridKey === 'orderNo' && $rowChk === 0){ ?>
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
                                                <?php } ?>
                                            </td>
                                        <?php } ?>
                                        <?php if($gridKey === 'orderName'  && $rowChk === 0){ ?>
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
                                        <?php } ?>



                                        <?php if($gridKey === 'orderGoodsNo'){ ?>
                                            <td>
                                                <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><?= $val['sno'] ?></a>
                                                <?php if ($val['orderChannelFl'] == 'naverpay') { ?>
                                                    <br>
                                                    <img  src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderGoodsNo']; ?>
                                                <?php }?>
                                            </td>
                                        <?php } ?>
                                        <?php if($gridKey === 'goodsCd'){ ?>
                                            <td>
                                                <?=$val['goodsNo']?>
                                                <?php if ($val['goodsCd']) { echo '(' . $val['goodsCd'] . ')'; } ?>
                                            </td>
                                        <?php } ?>
                                        <?php if($gridKey === 'orderGoodsNm'){ ?>
                                            <td class="text-left">
                                                <table>
                                                    <tr>
                                                        <td style="padding-right: 5px;">
                                                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                            <?php } else { ?>
                                                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                            <?php } ?>
                                                        </td>
                                                        <td style="border: 0px; ">
                                                            <?php if ($search['userHandleAdmFl'] == 'y' && empty($val['userHandleInfo']) === false) { ?>
                                                                <div>
                                                                    <?php foreach ($val['userHandleInfo'] as $userHandleInfo) { ?>
                                                                        <span class="label label-white"><?php echo $userHandleInfo; ?></span>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } ?>

                                                            <?php if(!$isUserHandle){ ?>
                                                                <?php if($statusMode === 'e'){ ?>
                                                                    <span class="label label-danger">교환취소</span><br />
                                                                <?php } else if ($statusMode === 'z'){ ?>
                                                                    <span class="label label-primary">교환추가</span><br />
                                                                <?php } else {}?>
                                                            <?php } ?>

                                                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                                                <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                                                   onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></a>
                                                            <?php } else { ?>
                                                                <?php if($val['timeSaleFl'] =='y') { ?>
                                                                    <img src='<?=PATH_ADMIN_GD_SHARE?>img/time-sale.png' alt='타임세일' />
                                                                <?php } ?>

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
                                        <?php } ?>
                                        <?php if($gridKey === 'goodsCnt'){ ?>
                                            <td class="goods_cnt"><strong><?= number_format($val['goodsCnt']); ?></strong></td>
                                        <?php } ?>
                                        <?php if($gridKey === 'goodsPrice'){ ?>
                                            <td><?= gd_currency_display($goodsPrice); ?></td>
                                        <?php } ?>

                                        <?php if($gridKey === 'processStatus'){ ?>
                                            <?php if ($currentStatusCode === 'o') { ?>
                                                <?php if ($rowChk === 0) { ?>
                                                    <td <?= $orderGoodsRowSpan ?>>
                                                        <?=$val['orderStatusStr']?>
                                                        <?php if ($val['statusMode'] == 'o') { ?>
                                                            <div class="mgt5">
                                                                <input type="button" onclick="status_process_payment('<?= $orderNo; ?>');" value="입금확인" class="btn btn-sm btn-black"/>
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <td>
                                                    <?php if ($currentStatusCode == 'r') { ?>
                                                        <div class="text-muted" title="이전 상품별 주문 상태"><?= $val['beforeStatusStr']; ?> &gt;</div>
                                                    <?php } ?>
                                                    <div title="주문 상품별 주문 상태"><?= $val['orderStatusStr']; ?></div>
                                                </td>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if($gridKey === 'deliverySno'){ ?>
                                            <?php if ($val['mallSno'] == DEFAULT_MALL_NUMBER) { ?>
                                                <?php if ($rowDelivery == 0) { ?>
                                                    <td <?= $orderDeliveryRowSpan; ?> class="font-num"><?= $val['deliverySno'] ?></td>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <?php if ($rowChk == 0) { ?>
                                                    <td <?= $orderGoodsRowSpan; ?> class="font-num"><?= $val['deliverySno'] ?></td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if($gridKey === 'scmNm' && $rowScm === 0){ ?>
                                            <td <?= $orderScmRowSpan; ?>><?= $val['companyNm'] ?></td>
                                        <?php } ?>
                                        <?php if($gridKey === 'invoiceNo'){ ?>
                                            <td>
                                                <div><?= $val['invoiceNo']; ?></div>
                                                <?php if (empty($val['invoiceCompanySno']) === false && empty($val['invoiceNo']) === false && (!$val['deliveryMethodFl'] || $val['deliveryMethodFl'] === 'delivery')) { ?>
                                                    <div>
                                                        <input type="button" onclick="delivery_trace('<?= $val['invoiceCompanySno']; ?>', '<?= $val['invoiceNo']; ?>');" value="배송추적" class="btn btn-sm btn-gray mgt5"/>
                                                    </div>
                                                <?php } ?>
                                            </td>
                                        <?php } ?>
                                        <?php if($gridKey === 'receiverName'){ ?>
                                            <?php if($useMultiShippingKey === true){ ?>
                                                <?php if($rowMultiShipping === 0){ ?>
                                                    <td <?= $orderMultiShippingRowSpan ?>><?= $val['receiverName'] ?></td>
                                                <?php } ?>
                                            <?php } else {?>
                                                <?php if($rowChk === 0){ ?>
                                                    <td <?= $orderGoodsRowSpan ?>>
                                                        <?php
                                                        if($val['multiShippingFl'] === 'y'){
                                                            if($val['multiShippingReceiverName']){
                                                                echo $val['multiShippingReceiverName'];
                                                                if(((int)$val['multiShippingReceiverNameCount']-1) > 0){
                                                                    echo ' 외 ' . ((int)$val['multiShippingReceiverNameCount']-1) . '명';
                                                                }
                                                            }
                                                        }
                                                        else {
                                                            echo $val['receiverName'];
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if($gridKey === 'packetCode'){ ?>
                                            <td><?= $val['packetCode'] ?></td>
                                        <?php } ?>
                                        <?php if($gridKey === 'receiverAddress'){ ?>
                                            <?php if($useMultiShippingKey === true){ ?>
                                                <?php if($rowMultiShipping === 0){ ?>
                                                    <td <?= $orderMultiShippingRowSpan ?>>
                                                        <?php if($val['receiverZonecode']){ echo "[".$val['receiverZonecode']."]"; } ?>
                                                        <?php if($val['receiverZipcode']){ echo "(".$val['receiverZipcode'].")"; } ?>
                                                        <br />
                                                        <?php if($val['receiverAddress']){ echo $val['receiverAddress']; } ?>
                                                        <?php if($val['receiverAddressSub']){ echo " ".$val['receiverAddressSub']; } ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } else {?>
                                                <?php if($rowChk === 0){ ?>
                                                    <td <?= $orderGoodsRowSpan ?>>
                                                        <?php if($val['receiverZonecode']){ echo "[".$val['receiverZonecode']."]"; } ?>
                                                        <?php if($val['receiverZipcode']){ echo "(".$val['receiverZipcode'].")"; } ?>
                                                        <br />
                                                        <?php if($val['receiverAddress']){ echo $val['receiverAddress']; } ?>
                                                        <?php if($val['receiverAddressSub']){ echo " ".$val['receiverAddressSub']; } ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                        <?php if($gridKey === 'multiShippingCd'){ ?>
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
                                        <?php } ?>
                                        <?php if($gridKey === 'orderMemo'){ ?>
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
                                                        if($val['multiShippingFl'] === 'y'){
                                                            if($val['multiShippingOrderMemo']){
                                                                echo gd_html_cut($val['multiShippingOrderMemo'], 30, '..');
                                                                if(((int)$val['multiShippingOrderMemoCount']-1) > 0){
                                                                    echo ' 외 ' . ((int)$val['multiShippingOrderMemoCount']-1) . '건';
                                                                }
                                                            }
                                                            else {
                                                                echo '-';
                                                            }
                                                        }
                                                        else {
                                                            if($val['orderMemo']){
                                                                echo gd_html_cut($val['orderMemo'], 30, '..');
                                                            }
                                                            else {
                                                                echo '-';
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>

                                        <?php if($gridKey === 'receipt' && $rowChk === 0){ ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['receiptFl'] != 'n') ? gd_html_image(PATH_ADMIN_GD_SHARE . 'img/receipt_icon/receipt_' . $val['receiptFl'] . '.png', null) : '미신청'?>
                                            </td>
                                        <?php } ?>

                                        <?php if($gridKey === 'gift' && $rowScm === 0){ ?>
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
                                        <?php } ?>

                                        <?php if($gridKey === 'userHandleOrderStatus'){ ?>
                                            <td class="text-center"><?=$orderStatusCodeByAdmin[$val['orderStatus']]?></td>
                                        <?php } ?>

                                        <?php if($gridKey === 'reservationStatus'){ ?>
                                            <td>
                                                <?=$val['reservationStatus']?>
<!--                                                <br>--><?//=$val['custUseNo']?><!--<br>-->
<!--                                                <span style="color:red">--><?//=$val['mpckKey']?><!--</span>-->
                                            </td>
                                        <?php } ?>
                                            <?php if ($gridKey === 'reservationCancel') { ?>
<!--                                            --><?php //  if ($search['view'] == 'order') { ?>
                                            <?php   if (true) { ?>
                                                <?php if ($rowMultiShipping === 0) { ?>
                                                    <td <?= $orderMultiShippingRowSpan ?>>
                                                    <?php if ($reservationInfo[$_firstKey]['cancelFl']) { ?>
                                                        <button type="button" class="btn btn-white js-btn-cancel"
                                                                data-pack="<?= $val['mpckKey'] ?>" data-packmix="<?=$reservationInfo[$val['orderInfoSno']]['mix']?>" data-mpckkeycnt="<?=gd_isset(count($checkOrderMpckKey[$val['mpckKey']]),0)?>">예약취소
                                                        </button>
                                                    <?php } ?>
                                                    </td>
                                                    <?php
                                                }
                                                ?>

                                            <?php } else { ?>
                                                <td>
                                                    <?php if ($val['reqDvCd'] == '01' && empty($val['invoiceNo'])) { ?>
                                                        <button type="button" class="btn btn-white js-btn-cancel"
                                                                data-pack="<?= $val['sno'] ?>" data-mpckkeycnt="<?=gd_isset(count($checkOrderMpckKey[$val['mpckKey']]),0)?>">예약취소
                                                        </button>
                                                    <?php
                                                    } ?>
                                                </td>
                                            <?php }
                                        }
                                        if($gridKey === 'memo'){
                                            switch ($search['view']) {
                                                case 'exchange':
                                                    $handleInfo = '교환신청';
                                                    break;
                                                case 'back':
                                                    $handleInfo = '반품신청';
                                                    break;
                                                default:
                                                    $handleInfo = '환불신청';
                                                    break;
                                            }
                                            ?>
                                            <td data-order-no="<?php echo $val['orderNo']; ?>" data-handle-no="<?php echo $val['userHandleNo']; ?>" data-handle-info="<?php echo $handleInfo; ?>" data-handle-mode="<?php echo $val['userHandleMode']; ?>" data-goods-nm="<?php echo $val['goodsNm']; ?>">
                                                <button type="button" class="btn btn-sm btn-<?php echo empty($val['userHandleDetailReason']) === false ? 'gray js-handle-reason' : 'white'; ?>" data-type="user">고객</button><br />
                                                <button type="button" class="btn btn-sm btn-<?php echo empty($val['adminHandleReason']) === false ? 'gray js-handle-reason' : 'white'; ?> mgt5" data-type="admin" >운영자</button>
                                            </td>
                                        <?php } ?>

                                        <?php
                                    }
                                }
                                ?>
                                <!-- 주문리스트 그리드 항목 끝-->
                            </tr>
                            <?php
                            $rowChk++;
                            $rowScm++;
                            $rowDelivery++;
                            $rowAll++;
                            $rowMultiShipping++;
                            $rowOrderInfoSnoCnt[$val['orderInfoSno']] ++;
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

<script type="text/javascript">
    $(function(){
        var viewType = '<?=$search['view']?>';
        $('.js-btn-cancel').bind('click',function () {
            var confirmMsg = '예약을 취소하시겠습니까? (묶음배송 주문이 같이 예약되었다면 함께 예약이 취소됩니다)';
            var pack = $(this).data('pack');
            dialog_confirm(confirmMsg,function (data) {
                if(data) {
                    ifrmProcess.location.href='logistics_ps.php?mpckKey='+pack+'&mode=cancel';
                }
            })
        })

        $('.js-send-logistcs').bind('click',function () {
            if($('input[name*="statusCheck["]:checkbox:checked').length ==0) {
                alert("택배 예약할 주문을 선택해주세요");
                return false;
            }
            $('#frmOrderStatus').submit();
        })

        $('input[name*="statusCheck["]:checkbox,.js-checkall').click(function () {
            var chkCnt = $('input[name*="statusCheck["]:checkbox:checked').length;
            $("#selectOrderCnt").html(chkCnt);
        });

        if(viewType == 'order') {
            $('input[name*="statusCheck["]:checkbox').bind('click', function () {
                var orderInfoSno = $(this).data('orderinfosno');
                var packetCode = $(this).data('packetcode');
                if ($(this).is(':checked')) {
                    $('tr[data-orderinfosno=' + orderInfoSno + '][data-reservation="y"]').css('background-color', 'rgb(247, 247, 247)');
                    if (packetCode) {
                        $('tr[data-packetcode=' + packetCode + '][data-reservation="y"]').css('background-color', 'rgb(247, 247, 247)').find('[type=checkbox]').prop('checked', true);
                    }
                }
                else {
                    $('tr[data-orderinfosno=' + orderInfoSno + '][data-reservation="y"]').css('background-color', 'white');
                    if (packetCode) {
                        $('tr[data-packetcode=' + packetCode + '][data-reservation="y"]').css('background-color', 'white').find('[type=checkbox]').prop('checked', false);
                    }
                }
            })

            $('.js-checkall').unbind('click').bind('click', function () {
                var checked = $(this).is(':checked');
                $('input[name*="statusCheck["]:checkbox').each(function () {
                    if (($(this).is(':checked') == checked)) {
                    }
                    else {
                        $(this).trigger('click');
                    }
                })
            })
        }

        $('[name=orderStatusType]').bind('click',function () {
                $('[name="orderStatus[]"]').prop('disabled', $(this).val() == '').prop('checked',$(this).val() == 'possibleReservation');;
        })

        $('[name="orderStatus[]"]').bind('click',function () {
            $('[name=orderStatusType][value="possibleReservation"]').prop('checked',true);
        })


        $('.js-handle-reason').click(function(){
            var handleInfo = $(this).closest('td');
            var title = handleInfo.data('handle-info') + ' 메모';
            switch ($(this).data('type')) {
                case 'admin':
                    title = '운영자 ' + title;
                    break;
                case 'user':
                    title = '고객 ' + title;
                    break;
            }

            var childNm = 'handle_reason';
            var addParam = {
                mode: 'simple',
                layerTitle: title,
                layerFormID: childNm + "Layer",
                parentFormID: childNm + "Row",
                dataFormID: childNm + "Id",
                orderNo: handleInfo.data('order-no'),
                handleNo: handleInfo.data('handle-no'),
                handleMode: handleInfo.data('handle-mode'),
                goodsNm: handleInfo.data('goods-nm'),
                type: $(this).data('type'),
            };
            layer_add_info(childNm, addParam);
        });
    })
</script>
