<?php
/**
 * 공통 상품주문번호별 리스트 레이아웃
 * 주문관리내 모든 리스트에서 사용
 *
 * !주의! CRM 주문내역, 주문상세, 클레임접수 리스트, 환불상세 모두 동시에 수정되어야 한다.
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
                            $val['orderCellPhone'] =  StringUtils::numberToCellPhone($val['orderCellPhone']);

                            $statusMode = substr($val['orderStatus'], 0, 1);

                            $orgGoodsPrice = $val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']; // 판매가
                            $goodsPrice = $val['goodsCnt'] * $orgGoodsPrice; // 상품 주문 금액
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
                                if ($val['userHandleGoodsCnt'] > 0) {
                                    $checkBoxCd = $orderNo . INT_DIVISION . $val['sno'] . INT_DIVISION . $val['userHandleNo'] . INT_DIVISION . $val['userHandleGoodsCnt'] . INT_DIVISION . $val['goodsCnt'];
                                } else {
                                    $checkBoxCd = $orderNo . INT_DIVISION . $val['sno'] . INT_DIVISION . $val['userHandleNo'] . INT_DIVISION . $val['goodsCnt'] . INT_DIVISION . $val['goodsCnt'];
                                }
                            }

                            // 주문일괄처리 제외대상 비활성화
                            if ($isUserHandle) {
                                $checkDisabled = ($isUserHandle && $val['userHandleFl'] != 'r' ? 'disabled="disabled"' : '');
                            } else {
                                $checkDisabled = '';
                            }

                            // rowspan 처리
                            $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';

                            //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scm no 를 order info sno 로 대체, dKey는 order delivery sno로 대체
                            if($useMultiShippingKey === true){
                                $rowScm = 0;
                                $orderMultiShippingRowSpan = ' rowspan="' . ($orderData['cnt']['multiShipping'][$sKey]) . '"';
                            }
                            else {
                                $orderScmRowSpan = ' rowspan="' . ($orderData['cnt']['scm'][$sKey]) . '"';
                            }

                            $deliveryKeyCheck = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                            if ($deliveryKeyCheck !== $deliveryUniqueKey) {
                                $rowDelivery = 0;
                            }
                            $deliveryUniqueKey = $deliveryKeyCheck;
                            $orderDeliveryRowSpan = ' rowspan="' . $orderData['cnt']['delivery'][$deliveryUniqueKey] . '"';

                            //배송업체가 설정되어 있지 않을시 기본 배송업체 select
                            $selectInvoiceCompanySno = $val['invoiceCompanySno'];
                            if((int)$selectInvoiceCompanySno < 1){
                                if ($val['deliveryMethodFl'] == 'delivery') {
                                    $selectInvoiceCompanySno = $deliverySno;
                                } else {
                                    $selectInvoiceCompanySno = $deliveryEtcCom[$val['deliveryMethodFl']];
                                }
                            }
                            ?>
                            <tr class="text-center" data-mall-sno="<?=$val['mallSno']?>" data-channel="<?=$val['orderChannelFl']?>">
                                <?php
                                //주문리스트 그리드 항목 시작
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
                                                        <input type="hidden" name="invoiceCompanySno[<?= $val['statusMode'] ?>][]" value="<?= $val['invoiceCompanySno']; ?>"/>
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
                                                <td>
                                                    <input type="checkbox" name="statusCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>"/>
                                                    <input type="hidden" name="orderStatus[<?= $val['statusMode'] ?>][]" value="<?= $val['orderStatus']; ?>"/>
                                                    <input type="hidden" name="escrowCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $val['escrowFl'] . $val['escrowDeliveryFl']; ?>"/>
                                                    <input type="hidden" name="invoiceCompanySno[<?= $val['statusMode'] ?>][]" value="<?= $val['invoiceCompanySno']; ?>"/>
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
                                            <td class="font-num">
                                                <small><?= $page->idx--; ?></small>
                                            </td>
                                        <?php } // 번호 ?>

                                        <?php if($gridKey === 'domainFl' && $rowChk === 0){ //상점구분 ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="font-kor">
                                                <span class="flag flag-16 flag-<?=$val['domainFl']?>"></span>
                                                <?=$val['mallName']?>
                                            </td>
                                        <?php } //상점구분 ?>

                                        <?php if($gridKey === 'regDt'){ // 주문일시 ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?>
                                            </td>
                                        <?php } // 주문일시 ?>

                                        <?php if($gridKey === 'paymentDt'){ // 입금일시 ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['paymentDt'])); ?>
                                            </td>
                                        <?php } // 입금일시 ?>

                                        <?php if($gridKey === 'deliveryDt'){ // 배송일시 ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['deliveryDt'])); ?>
                                            </td>
                                        <?php } // 배송일시 ?>

                                        <?php if($gridKey === 'deliveryCompleteDt'){ // 배송완료일시 ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['deliveryCompleteDt'])); ?>
                                            </td>
                                        <?php } // 배송완료일시 ?>

                                        <?php if($gridKey === 'finishDt'){ // 구매확정일시 ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['finishDt'])); ?>
                                            </td>
                                        <?php } // 구매확정일시 ?>

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

                                        <?php if($gridKey === 'orderName'  && $rowChk === 0){ // 주문자 ?>
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

                                        <?php if($gridKey === 'orderGoodsNo'){ // 상품주문번호 ?>
                                            <td>
                                                <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><?= $val['sno'] ?></a>
                                                <?php if ($val['orderChannelFl'] == 'naverpay') { ?>
                                                    <img  src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/>
                                                    <?= $val['apiOrderGoodsNo']; ?>
                                                <?php } else if ($val['orderChannelFl'] == 'etc') { ?>
                                                    <img  src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'etc.gif')->www() ?>"/>
                                                    <?= $val['apiOrderGoodsNo']; ?>
                                                <?php } else { } ?>
                                            </td>
                                        <?php } // 상품주문번호 ?>

                                        <?php if($gridKey === 'goodsCd'){ // 상품코드(자체상품코드) ?>
                                            <td>
                                                <?=$val['goodsNo']?>
                                                <?php if ($val['goodsCd']) { echo '(' . $val['goodsCd'] . ')'; } ?>
                                            </td>
                                        <?php } // 상품코드(자체상품코드) ?>

                                        <?php if($gridKey === 'goodsTaxInfo'){ // 상품 부가세 ?>
                                            <td>
                                                <?php
                                                $goodsTaxInfoArr = array();
                                                if($val['goodsTaxInfo']){
                                                    $goodsTaxInfoArr = explode(STR_DIVISION, $val['goodsTaxInfo']);
                                                    if($goodsTaxInfoArr[0] === 't'){
                                                        echo $goodsTaxInfoArr[1] . '%';
                                                    }
                                                    else {
                                                        echo '면세';
                                                    }
                                                }
                                                ?>
                                            </td>
                                        <?php } // 상품 부가세 ?>

                                        <?php if($gridKey === 'orderGoodsNm'){ // 주문상품 ?>
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
                                                                <?php if($val['handleMode'] === 'e'){ ?>
                                                                    <span class="label label-danger">교환취소</span><br />
                                                                <?php } else if ($val['handleMode'] === 'z'){ ?>
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
                                                                if(!empty($option[4])) {
                                                                    echo ' [' . $option[4].']';
                                                                }
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

                                        <?php if($gridKey === 'orderGoodsNmGlobal'){ //주문상품(해외상점) ?>
                                            <td class="text-left">
                                                <?php if($val['mallSno'] != DEFAULT_MALL_NUMBER){ ?>
                                                    <table>
                                                        <tr>
                                                            <td>
                                                                <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                    <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                                <?php } else { ?>
                                                                    <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                                                <?php } ?>
                                                            </td>
                                                            <td style="border: 0px;">
                                                                <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                                    <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                                                    <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                                                       onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></a>
                                                                <?php } else { ?>
                                                                    <?php if($val['timeSaleFl'] =='y') { ?>
                                                                        <img src='<?=PATH_ADMIN_GD_SHARE?>img/time-sale.png' alt='타임세일' />
                                                                    <?php } ?>

                                                                    <a href="javascript:void();" class="one-line bold mgb5" title="상품명"
                                                                       onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNmGlobal'], 46, '..'); ?></a>
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
                                        <?php } //주문상품(해외상점) ?>

                                        <?php if($gridKey === 'goodsCnt'){ //수량 ?>
                                            <td class="goods_cnt"><strong><?= number_format($val['goodsCnt']); ?></strong></td>
                                        <?php } //수량 ?>

                                        <?php if($gridKey === 'orgGoodsPrice'){ // 판매가 ?>
                                            <td><?= gd_currency_display($orgGoodsPrice); ?></td>
                                        <?php } // 판매가 ?>

                                        <?php if($gridKey === 'goodsPrice'){ // 상품금액 ?>
                                            <td><?= gd_currency_display($goodsPrice); ?></td>
                                        <?php } // 상품금액 ?>

                                        <?php if($gridKey === 'totalGoodsPrice' && $rowChk === 0){ // 총 상품금액 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?= gd_currency_display($val['totalGoodsPrice']); ?></td>
                                        <?php } // 총 상품금액 ?>

                                        <?php if($gridKey === 'deliveryCharge'){ // 배송비 ?>
                                            <?php if($useMultiShippingKey === true){ ?>
                                                <?php if ($rowDelivery == 0) { ?>
                                                    <td <?= $orderDeliveryRowSpan; ?> class="font-num"><?=gd_currency_display($val['deliveryCharge'])?></td>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <?php if($val['multiShippingFl'] === 'y'){ ?>
                                                    <?php if ($rowChk == 0) { ?>
                                                        <td <?= $orderGoodsRowSpan; ?> class="font-num">복수배송지<br /><?=$val['totalOrderInfoCount']?>건</td>
                                                    <?php } ?>
                                                <?php } else {?>
                                                    <?php if ($val['mallSno'] == DEFAULT_MALL_NUMBER) { ?>
                                                        <?php if ($rowDelivery == 0) { ?>
                                                            <td <?= $orderDeliveryRowSpan; ?> class="font-num"><?=gd_currency_display($val['deliveryCharge'])?></td>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if ($rowChk == 0) { ?>
                                                            <td <?= $orderGoodsRowSpan; ?> class="font-num"><?=gd_currency_display($val['deliveryCharge'])?></td>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } // 배송비 ?>

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

                                        <?php if($gridKey === 'totalRealSettlePrice' && $rowChk === 0){ // 총 실결제금액 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?=gd_currency_display($val['totalRealSettlePrice'])?></td>
                                        <?php } // 총 실결제금액 ?>

                                        <?php if($gridKey === 'goodsPriceGlobal'){ // 상품금액(해외상점) ?>
                                            <td>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($goodsPrice, $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 상품금액(해외상점) ?>

                                        <?php if($gridKey === 'totalGoodsPriceGlobal' && $rowChk === 0){ // 총 상품금액(해외상점) ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalGoodsPrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 총 상품금액(해외상점) ?>

                                        <?php if($gridKey === 'deliveryChargeGlobal'){ // 배송비(해외상점) ?>
                                            <?php if ($val['mallSno'] == DEFAULT_MALL_NUMBER) { ?>
                                                <?php if ($rowDelivery == 0) { ?>
                                                    <td <?= $orderDeliveryRowSpan; ?> class="font-num"><?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['deliveryCharge'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?></td>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <?php if ($rowChk == 0) { ?>
                                                    <td <?= $orderGoodsRowSpan; ?> class="font-num"><?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['deliveryCharge'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?></td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } // 배송비(해외상점) ?>


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
                                            <td <?= $orderGoodsRowSpan ?>>
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

                                        <?php if($gridKey === 'settleStatus'){ // 결제상태 ?>
                                            <td><?=$val['orderStatusStr']?></td>
                                        <?php } // 결제상태 ?>

                                        <?php if($gridKey === 'processStatus'){ // 처리상태 ?>
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
                                        <?php } // 처리상태 ?>

                                        <?php if($gridKey === 'purchaseNm'){ // 매입처 ?>
                                            <td>
                                                <?= (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && $val['purchaseNm']) ? $val['purchaseNm'] : '-' ?>
                                            </td>
                                        <?php } // 매입처 ?>

                                        <?php if($gridKey === 'brandNm'){ // 브랜드 ?>
                                            <td><?=$val['brandNm']?></td>
                                        <?php } // 브랜드 ?>

                                        <?php if($gridKey === 'goodsModelNo'){ // 모델명 ?>
                                            <td><?=$val['goodsModelNo']?></td>
                                        <?php } // 모델명 ?>

                                        <?php if($gridKey === 'makerNm'){ // 제조사 ?>
                                            <td><?=$val['makerNm']?></td>
                                        <?php } // 제조사 ?>

                                        <?php if($gridKey === 'deliverySno'){ // 배송번호 ?>
                                            <?php if ($val['mallSno'] == DEFAULT_MALL_NUMBER) { ?>
                                                <?php if ($rowDelivery == 0) { ?>
                                                    <td <?= $orderDeliveryRowSpan; ?> class="font-num"><?= $val['deliverySno'] ?></td>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <?php if ($rowChk == 0) { ?>
                                                    <td <?= $orderGoodsRowSpan; ?> class="font-num"><?= $val['deliverySno'] ?></td>
                                                <?php } ?>
                                            <?php } ?>
                                        <?php } // 배송번호 ?>

                                        <?php if($gridKey === 'scmNm' && $rowScm === 0){ // 공급사 ?>
                                            <td <?= $orderScmRowSpan; ?>><?= $val['companyNm'] ?></td>
                                        <?php } // 공급사 ?>

                                        <?php if($gridKey === 'commission'){ // 수수료율 ?>
                                            <td><?=$val['commission']?>%</td>
                                        <?php } // 수수료율 ?>

                                        <?php if($gridKey === 'costPrice'){ // 매입가 ?>
                                            <td><?=gd_currency_display(($val['costPrice'] + $val['optionCostPrice']) * $val['goodsCnt'])?></td>
                                        <?php } // 매입가 ?>

                                        <?php if($gridKey === 'invoiceNo'){ // 송장번호 ?>
                                            <td>
                                                <?php if ($currentStatusCode == 'g') { ?>
                                                    <?= gd_select_box(null, 'invoiceCompanySno[' . $val['statusMode'] . '][' . $val['sno'] . ']', $deliveryCom, null, $selectInvoiceCompanySno, null); ?>
                                                    <input type="text" name="invoiceNo[<?= $val['statusMode'] ?>][<?= $val['sno'] ?>]" value="<?= $val['invoiceNo']; ?>" class="form-control input-sm mgt5"/>
                                                <?php } else { ?>
                                                    <?php if (empty($val['invoiceCompanySno']) === false && empty($val['invoiceNo']) === false) { ?>
                                                        <small><?= $val['invoiceCompanyNm']; ?> / <?= $val['invoiceNo']; ?></small>
                                                        <?php if($val['deliveryMethodFl'] === 'delivery' || $val['deliveryMethodFl'] === 'packet' || !$val['deliveryMethodFl']){ ?>
                                                            <div class="delivery-trace">
                                                                <input type="button" onclick="delivery_trace('<?= $val['invoiceCompanySno']; ?>', '<?= $val['invoiceNo']; ?>');" value="배송추적" class="btn btn-sm btn-black">
                                                            </div>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            </td>
                                        <?php } // 송장번호 ?>

                                        <?php if($gridKey === 'receiverName'){ // 수령자 ?>
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
                                        <?php } // 수령자 ?>

                                        <?php if($gridKey === 'receiverAddress'){ // 수령자 주소 ?>
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
                                        <?php } // 수령자 주소 ?>

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
                                        <?php } // 배송 메시지 ?>

                                        <?php if($gridKey === 'receipt' && $rowChk === 0){ // 영수증 신청여부 ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?=($val['receiptFl'] != 'n') ? gd_html_image(PATH_ADMIN_GD_SHARE . 'img/receipt_icon/receipt_' . $val['receiptFl'] . '.png', null) : '미신청'?>
                                            </td>
                                        <?php } // 영수증 신청여부 ?>

                                        <?php if($gridKey === 'hscode'){ // HS코드 ?>
                                            <td>
                                                <?php
                                                $hscode = '';
                                                if($val['hscode']){
                                                    $hscode = json_decode(gd_htmlspecialchars_stripslashes($val['hscode']), true);
                                                    if ($hscode) {
                                                        foreach ($hscode as $hscodeKey => $hscodeValue) {
                                                            $tmpHscode[] = $hscodeKey . " : " . $hscodeValue;
                                                        }

                                                        echo implode("<br />", $tmpHscode);
                                                        unset($tmpHscode);
                                                    }
                                                }
                                                ?>
                                            </td>
                                        <?php } // HS코드 ?>

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

                                        <?php if($gridKey === 'adminMemo' && $rowChk === 0){ // 관리자메모 ?>
                                            <td <?= $orderGoodsRowSpan; ?> class="text-center" data-order-no="<?= $val['orderNo'] ?>" data-reg-date="<?= $val['regDt'] ?>">
                                                <button type="button" class="btn btn-sm btn-<?php if($data[$orderNo]['adminOrdGoodsMemo']){ echo 'gray'; }else{ echo 'white';} ?> js-super-admin-memo" data-order-no="<?= $val['orderNo']; ?>" data-memo="<?=$data[$orderNo]['adminOrdGoodsMemo'];?>">보기</button>
                                            </td>
                                        <?php } // 관리자메모 ?>

                                        <?php if($gridKey === 'regDtInterval'){ // 경과일자 ?>
                                            <td><?=gd_interval_day($val['regDt'], date('Y-m-d H:i:s'));?>일</td>
                                        <?php } // 경과일자 ?>

                                        <?php if($gridKey === 'bankSender'){ //입금자 ?>
                                            <td><?=$val['bankSender']?></td>
                                        <?php } //입금자 ?>

                                        <?php if($gridKey === 'bankAccount'){ //입금계좌 ?>
                                            <td><?= str_replace(STR_DIVISION, ' / ', gd_isset($val['bankAccount'])); ?></td>
                                        <?php } //입금계좌 ?>

                                        <?php if($gridKey === 'cancelDt'){ // 취소신청일(취소접수일) ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['cancelDt'])); ?></td>
                                        <?php } // 취소신청일(취소접수일) ?>

                                        <?php if($gridKey === 'cancelCompleteDt'){ // 취소완료일시 ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['cancelDt'])); ?></td>
                                        <?php } // 취소완료일시 ?>

                                        <?php if($gridKey === 'reason' && $rowChk === 0){ // 사유 ?>
                                            <td <?= $orderGoodsRowSpan ?>><?=$val['handleReason']?></td>
                                        <?php } // 사유 ?>

                                        <?php if($gridKey === 'exchangeDt'){ // 교환신청일(교환접수일) ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleRegDt'])); ?></td>
                                        <?php } // 교환신청일(교환접수일) ?>

                                        <?php if($gridKey === 'exchangeCompleteDt'){ // 교환완료일시 ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleDt'])); ?></td>
                                        <?php } // 교환완료일시 ?>

                                        <?php if($gridKey === 'backDt'){ // 반품신청일(반품접수일) ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleRegDt'])); ?></td>
                                        <?php } // 반품신청일(반품접수일) ?>

                                        <?php if($gridKey === 'backCompleteDt'){ // 반품완료일시 ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleDt'])); ?></td>
                                        <?php } // 반품완료일시 ?>

                                        <?php if($gridKey === 'refundDt'){ // 환불신청일(환불접수일) ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleRegDt'])); ?></td>
                                        <?php } // 환불신청일(환불접수일) ?>

                                        <?php if($gridKey === 'refundCompleteDt'){ // 환불완료일시 ?>
                                            <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['handleDt'])); ?></td>
                                        <?php } // 환불완료일시 ?>

                                        <?php if($gridKey === 'refundMethod'){ // 환불수단 ?>
                                            <td><?= $val['refundMethod'] ?></td>
                                        <?php } // 환불수단 ?>

                                        <?php if($gridKey === 'refundStatus'){ // 환불처리 ?>
                                            <td>
                                                <?php if ($val['orderStatus'] == 'r1') { ?>
                                                    <?php if (gd_date_format('Y-m-d H:i', $val['regDt']) < gd_date_format('Y-m-d H:i', '2019-07-10 07:40:00')) { //배포일 ?>
                                                        <button type="button" class="btn btn-sm btn-gray js-order-refund"  data-order-goods-no="<?=$val['sno']?>" data-channel="<?=$val['orderChannelFl']?>" data-order-no="<?= $val['orderNo'] ?>" data-handle-sno="<?= $val['handleSno'] ?>" data-mall-sno="<?=$val['mallSno']?>">환불처리</button>
                                                    <?php } else { ?>
                                                        <button type="button" class="btn btn-sm btn-gray js-order-refund-new"  data-order-goods-no="<?=$val['sno']?>" data-channel="<?=$val['orderChannelFl']?>" data-order-no="<?= $val['orderNo'] ?>" data-handle-sno="<?= $val['handleSno'] ?>" data-mall-sno="<?=$val['mallSno']?>">환불처리</button>
                                                    <?php }?>
                                                <?php } else if($val['orderStatus'] == 'r2' ){ ?>
                                                    처리중
                                                <?php } elseif ($val['orderStatus'] == 'r3' ) { ?>
                                                    <?php if($val['orderChannelFl'] == 'naverpay') {?>
                                                        처리완료
                                                    <?php } else {?>
                                                        <?php if (gd_date_format('Y-m-d H:i', $val['regDt']) < gd_date_format('Y-m-d H:i', '2019-07-10 07:40:00')) { //배포일 ?>
                                                            <button type="button" class="btn btn-sm btn-gray js-order-refund-detail" data-order-no="<?= $val['orderNo'] ?>"
                                                                    data-handle-sno="<?= $val['handleSno'] ?>" data-mall-sno="<?=$val['mallSno']?>">상세내역
                                                            </button>
                                                        <?php } else { ?>
                                                            <button type="button" class="btn btn-sm btn-gray js-order-refund-detail-new" data-order-no="<?= $val['orderNo'] ?>"
                                                                    data-handle-sno="<?= $val['handleSno'] ?>" data-mall-sno="<?=$val['mallSno']?>">상세내역
                                                            </button>
                                                        <?php }?>
                                                    <?php } ?>
                                                <?php } ?>
                                            </td>
                                        <?php } // 환불처리 ?>

                                        <?php if($gridKey === 'userHandleReason'){ // 사유 ?>
                                            <td <?= $orderAddGoodsRowSpan ?>><?= $val['userHandleReason']; ?></td>
                                        <?php } // 사유 ?>

                                        <?php if($gridKey === 'userHandleRegDt'){ //신청일시 ?>
                                            <td <?= $orderAddGoodsRowSpan ?> class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['userHandleRegDt'])); ?>
                                            </td>
                                        <?php } //신청일시 ?>

                                        <?php if($gridKey === 'userHandleActDt'){ // 처리일시 ?>
                                            <td <?= $orderAddGoodsRowSpan ?>><?=$val['handleRegDt']?></td>
                                        <?php } // 처리일시 ?>

                                        <?php if($gridKey === 'userHandleOrderStatus'){ // 주문상태 ?>
                                            <td class="text-center"><?=$orderStatusCodeByAdmin[$val['orderStatus']]?></td>
                                        <?php } // 주문상태 ?>

                                        <?php if($gridKey === 'userHandleGoodsCnt'){ // 신청수량 ?>
                                            <td <?= $orderAddGoodsRowSpan; ?>>
                                                <?php echo number_format($val['userHandleGoodsCnt']); ?>
                                            </td>
                                        <?php } // 신청수량 ?>

                                        <?php
                                        if($gridKey === 'memo'){ // 메모
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
                                        <?php } // 메모 ?>

                                        <?php if($gridKey === 'phoneNumber' && $rowChk === 0) {// 주문자 번호?>
                                            <td <?= $orderGoodsRowSpan ?>><?= $val['orderCellPhone']?></td>
                                        <?php } // 주문자 번호?>

                                        <?php if($gridKey === 'orderTypeFl') {// 주문유형?>
                                            <td><?= $val['orderTypeFlNm']?></td>
                                        <?php } // 주문유형?>
                                        <?php
                                    }
                                }
                                //주문리스트 그리드 항목 끝
                                ?>
                            </tr>
                            <?php
                            $rowChk++;
                            $rowScm++;
                            $rowDelivery++;
                            $rowAll++;
                            $rowMultiShipping++;
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
