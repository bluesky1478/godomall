<?php
/**
 * 공통 주문번호별 리스트 레이아웃
 * 주문통합|입금대기 리스트에서 사용
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
        <tr>
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
        </tr>
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
                $totalCnt++; // 주문서 수량
                foreach ($orderData['goods'] as $sKey => $sVal) {
                    foreach ($sVal as $dKey => $dVal) {
                        foreach ($dVal as $key => $val) {
                            $val['orderCellPhone'] =  StringUtils::numberToCellPhone($val['orderCellPhone']);
                            if ($key > 0) {
                                continue;
                            }
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
                            ?>
                            <tr class="text-center" data-mall-sno="<?=$val['mallSno']?>">
                                <?php
                                //주문리스트 그리드 항목 시작
                                if (count($orderGridConfigList) > 0) {
                                    foreach($orderGridConfigList as $gridKey => $gridName){
                                        ?>
                                        <?php if($gridKey === 'check'){ //선택 ?>
                                            <td>
                                                <input type="checkbox" name="statusCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>"/>
                                                <input type="hidden" name="orderStatus[<?= $val['statusMode'] ?>][]" value="<?= $val['orderStatus']; ?>"/>
                                                <input type="hidden" name="escrowCheck[<?= $val['statusMode'] ?>][]" <?= $checkDisabled ?> value="<?= $val['escrowFl'] . $val['escrowDeliveryFl']; ?>"/>
                                                <input type="hidden" name="orderChannelFl[<?= $val['statusMode'] ?>][<?= $checkBoxCd; ?>]" value="<?= $val['orderChannelFl']; ?>"/>
                                                <?php if (in_array($currentStatusCode, ['r', 'e', 'b'])) { ?>
                                                    <input type="hidden" name="handleSno[<?= $val['statusMode'] ?>][]" value="<?= $val['handleSno']; ?>"/>
                                                    <input type="hidden" name="beforeStatus[<?= $val['statusMode'] ?>][]" value="<?= $val['beforeStatus']; ?>"/>
                                                <?php } ?>
                                            </td>
                                        <?php } //선택 ?>

                                        <?php if($gridKey === 'no'){ //번호 ?>
                                            <td class="font-num">
                                                <small><?= $page->idx--; ?></small>
                                            </td>
                                        <?php } //번호 ?>

                                        <?php if($gridKey === 'domainFl'){ //상점구분 ?>
                                            <td class="font-kor">
                                                <span class="flag flag-16 flag-<?=$val['domainFl']?>"></span>
                                                <?=$val['mallName']?>
                                            </td>
                                        <?php } //상점구분 ?>

                                        <?php if($gridKey === 'regDt'){ //주문일시 ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?>
                                            </td>
                                        <?php } //주문일시 ?>

                                        <?php if($gridKey === 'paymentDt'){ //입금일시 ?>
                                            <td class="font-date nowrap">
                                                <?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['paymentDt'])); ?>
                                            </td>
                                        <?php } //입금일시 ?>

                                        <?php if($gridKey === 'orderNo'){ //주문번호 ?>
                                            <td class="order-no">
                                                <?php if ($val['firstSaleFl'] == 'y') { ?>
                                                    <p class="mgb0"><img src="<?=PATH_ADMIN_GD_SHARE?>img/order/icon_firstsale.png" alt="첫주문" /></p>
                                                <?php } ?>

                                                <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><?= $orderNo; ?></a><img src="<?=PATH_ADMIN_GD_SHARE?>img/icon_grid_open.png" alt="팝업창열기" class="hand mgl5" border="0" onclick="javascript:order_view_popup('<?=$orderNo?>', '<?=$isProvider?>');" />
                                                <?php if ($val['orderChannelFl'] == 'naverpay') { ?>
                                                    <p>
                                                        <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderNo']; ?></a>
                                                    </p>
                                                <?php } else if($val['orderChannelFl'] == 'payco') { ?>
                                                    <img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'payco.gif')->www() ?>"/>
                                                <?php } else if ($val['orderChannelFl'] == 'etc') { ?>
                                                    <p>
                                                        <a href="#;" onclick="javascript:open_order_link('<?=$orderNo?>', '<?=$openType?>', '<?=$isProvider?>')" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>">
                                                            <img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'etc.gif')->www() ?>"/> <?= $val['apiOrderNo']; ?>
                                                        </a>
                                                    </p>
                                                <?php } else { } ?>
                                                <?php if (empty($val['trackingKey']) === false) {echo '<div class="c-gdred">' . $channel['paycoShopping'] . '</div>';}?>
                                            </td>
                                        <?php } //주문번호 ?>

                                        <?php if($gridKey === 'orderName'){ //주문자 ?>
                                            <td class="js-member-info" data-member-no="<?= $val['memNo'] ?>" data-member-name="<?= $val['orderName'] ?>" data-cell-phone="<?= $val['smsCellPhone'] ?>">
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
                                        <?php } //주문자 ?>

                                        <?php if($gridKey === 'orderGoodsNm'){ //주문상품 ?>
                                            <td class="text-left">
                                                <?php if ($search['userHandleAdmFl'] == 'y' && empty($val['userHandleInfo']) === false) { ?>
                                                    <div>
                                                    <?php foreach ($val['userHandleInfo'] as $userHandleInfo) { ?>
                                                        <span class="label label-white"><?php echo $userHandleInfo; ?></span>
                                                    <?php } ?>
                                                    </div>
                                                <?php } ?>
                                                <?=$val['orderGoodsNm']?>
                                            </td>
                                        <?php } //주문상품 ?>

                                        <?php if($gridKey === 'orderGoodsNmGlobal'){ //주문상품(해외상점) ?>
                                            <td class="text-left">
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER && $val['orderGoodsNmGlobal']) ? $val['orderGoodsNmGlobal'] : '<div style="width:100%; text-align: center;">-</div>'?>
                                            </td>
                                        <?php } //주문상품(해외상점) ?>

                                        <?php if($gridKey === 'totalGoodsPrice'){ //총 상품금액 ?>
                                            <td><?= gd_currency_display($val['totalGoodsPrice']); ?></td>
                                        <?php } //총 상품금액 ?>

                                        <?php if($gridKey === 'totalDeliveryCharge'){ //총 배송비 ?>
                                            <td><?=gd_currency_display($val['totalDeliveryCharge'])?></td>
                                        <?php } //총 배송비 ?>

                                        <?php if($gridKey === 'totalDcPrice'){ //총 할인금액 ?>
                                            <td><?=gd_currency_display($val['totalDcPrice'])?></td>
                                        <?php } //총 할인금액 ?>

                                        <?php if($gridKey === 'totalUseAddedPrice'){ //총 부가결제금액 ?>
                                            <td><?=gd_currency_display($val['totalUseAddedPrice'])?></td>
                                        <?php } //총 부가결제금액 ?>

                                        <?php if($gridKey === 'totalOrderPrice'){ //총 주문금액 ?>
                                            <td><?=gd_currency_display($val['totalOrderPrice'])?></td>
                                        <?php } //총 주문금액 ?>

                                        <?php if($gridKey === 'totalRealSettlePrice'){ //총 실결제금액 ?>
                                            <td><?=gd_currency_display($val['totalRealSettlePrice'])?></td>
                                        <?php } //총 실결제금액 ?>

                                        <?php if($gridKey === 'totalGoodsPriceGlobal'){ //총 상품금액(해외상점) ?>
                                            <td>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalGoodsPrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } //총 상품금액(해외상점) ?>

                                        <?php if($gridKey === 'totalDeliveryChargeGlobal'){ // 총 배송비(해외상점) ?>
                                            <td>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalDeliveryCharge'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } // 총 배송비(해외상점) ?>

                                        <?php if($gridKey === 'totalDcPriceGlobal'){ //총 할인금액(해외상점) ?>
                                            <td>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalDcPrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } //총 할인금액(해외상점) ?>

                                        <?php if($gridKey === 'totalOrderPriceGlobal'){ //총 주문금액(해외상점) ?>
                                            <td>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['totalOrderPrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } //총 주문금액(해외상점) ?>

                                        <?php if($gridKey === 'totalRealSettlePriceGlobal'){ //총 실결제금액(해외상점) ?>
                                            <td>
                                                <?=($val['mallSno'] != DEFAULT_MALL_NUMBER) ? gd_global_order_currency_display($val['settlePrice'], $val['exchangeRate'], $val['currencyPolicy']) : '-'?>
                                            </td>
                                        <?php } //총 실결제금액(해외상점) ?>

                                        <?php if($gridKey === 'settleKind'){ //결제방법 ?>
                                            <td>
                                                <?php if (is_file(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_'.$val['settleKind'].'.gif'))) { ?>
                                                    <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_'.$val['settleKind'].'.gif')->www(), $val['settleKindStr']); ?>
                                                <?php } ?>
                                                <?php if ($val['useDeposit'] > 0) { ?>
                                                    <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_gd.gif')->www(), '예치금'); ?>
                                                <?php } ?>
                                                <?php if ($val['useMileage'] > 0) { ?>
                                                    <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_gm.gif')->www(), '마일리지'); ?>
                                                <?php } ?>
                                            </td>
                                        <?php } //결제방법 ?>

                                        <?php if($gridKey === 'settleStatus'){ //결제상태 ?>
                                            <td>
                                                <div title="주문 상품별 주문 상태">
                                                    <?php if (in_array(substr($val['orderStatus'], 0, 1), ['o','c'])) { ?>
                                                        미결제
                                                    <?php } elseif (in_array(substr($val['orderStatus'], 0, 1), ['f'])) { ?>
                                                        <?=$val['orderStatusStr']?>
                                                    <?php } else { ?>
                                                        결제확인
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        <?php } //결제상태 ?>

                                        <?php if($gridKey === 'noDelivery'){ //미배송 ?>
                                            <td class="font-num point1"><?=number_format($val['noDelivery'])?></td>
                                        <?php } //미배송 ?>

                                        <?php if($gridKey === 'deliverying'){ //배송중 ?>
                                            <td class="font-num point1"><?=number_format($val['deliverying'])?></td>
                                        <?php } //배송중 ?>

                                        <?php if($gridKey === 'deliveryed'){ //배송완료 ?>
                                            <td class="font-num point1"><?=number_format($val['deliveryed'])?></td>
                                        <?php } //배송완료 ?>

                                        <?php if($gridKey === 'cancel'){ //취소 ?>
                                            <td class="font-num point1"><?=number_format($val['cancel'])?></td>
                                        <?php } //취소 ?>

                                        <?php if($gridKey === 'exchange'){ //교환 ?>
                                            <td class="font-num point1"><?=number_format($val['exchange'])?></td>
                                        <?php } //교환 ?>

                                        <?php if($gridKey === 'back'){ //반품 ?>
                                            <td class="font-num point1"><?=number_format($val['back'])?></td>
                                        <?php } //반품 ?>

                                        <?php if($gridKey === 'refund'){ //환불 ?>
                                            <td class="font-num point1"><?=number_format($val['refund'])?></td>
                                        <?php } //환불 ?>

                                        <?php if($gridKey === 'receiverName'){ //수령자 ?>
                                            <td>
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
                                        <?php } //수령자 ?>

                                        <?php if($gridKey === 'receiverAddress'){ //수령자 주소 ?>
                                            <td>
                                                <?php if($val['receiverZonecode']){ echo "[".$val['receiverZonecode']."]"; } ?>
                                                <?php if($val['receiverZipcode']){ echo "(".$val['receiverZipcode'].")"; } ?>
                                                <br />
                                                <?php if($val['receiverAddress']){ echo $val['receiverAddress']; } ?>
                                                <?php if($val['receiverAddressSub']){ echo " ".$val['receiverAddressSub']; } ?>
                                            </td>
                                        <?php } //수령자 주소 ?>

                                        <?php if($gridKey === 'orderMemo'){ //배송 메시지 ?>
                                            <td>
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
                                        <?php } //배송 메시지 ?>

                                        <?php if($gridKey === 'receipt'){ // 영수증 신청여부 ?>
                                            <td>
                                                <?=($val['receiptFl'] != 'n') ? gd_html_image(PATH_ADMIN_GD_SHARE . 'img/receipt_icon/receipt_' . $val['receiptFl'] . '.png', null) : '미신청'?>
                                            </td>
                                        <?php } // 영수증 신청여부 ?>

                                        <?php if($gridKey === 'gift'){ // 사은품 ?>
                                            <td class="font-kor nowrap text-left">
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

                                        <?php if($gridKey === 'adminMemo'){ // 관리자메모 ?>
                                            <td class="text-center" data-order-no="<?= $val['orderNo'] ?>" data-reg-date="<?= $val['regDt'] ?>">
                                                <button type="button" class="btn btn-sm btn-<?php if($data[$orderNo]['adminOrdGoodsMemo']){ echo 'gray'; }else{ echo 'white';} ?> js-super-admin-memo" data-order-no="<?= $val['orderNo']; ?>" data-memo="<?=$data[$orderNo]['adminOrdGoodsMemo'];?>">보기</button>
                                            </td>
                                        <?php } // 관리자메모 ?>

                                        <?php if($gridKey === 'regDtInterval'){ // 경과일자 ?>
                                            <td><?=gd_interval_day($val['regDt'], date('Y-m-d H:i:s'));?>일</td>
                                        <?php } // 경과일자 ?>

                                        <?php if($gridKey === 'bankSender'){ // 입금자 ?>
                                            <td><?=$val['bankSender']?></td>
                                        <?php } // 입금자 ?>

                                        <?php if($gridKey === 'bankAccount'){ // 입금계좌 ?>
                                            <td><?= str_replace(STR_DIVISION, ' / ', gd_isset($val['bankAccount'])); ?></td>
                                        <?php } // 입금계좌 ?>

                                        <?php if($gridKey === 'phoneNumber') {// 주문자 번호 ?>
                                            <td><?= $val['orderCellPhone']?></td>
                                        <?php } // 주문자 번호 ?>

                                        <?php if($gridKey === 'orderTypeFl') { // 주문유형 ?>
                                            <td><?= $val['orderTypeFlNm']?></td>
                                        <?php } // 주문유형 ?>
                                        <?php
                                    }
                                }
                                //주문리스트 그리드 항목 끝
                                ?>
                            </tr>
                            <?php
                        }
                    }
                }
            }
        }
        else {
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
