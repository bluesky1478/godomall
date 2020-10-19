<div id="inc_order_view" class="table-responsive">
    <table class="table table-rows">
        <thead>
        <?php
        if (count($orderGridConfigList) > 0) {
            foreach($orderGridConfigList as $gridKey => $gridName){
                //주문상세창 열기 옵션 설정
                if($gridKey === 'openLinkOption') {
                    if(gd_isset($gridName) === null) {
                        $openType = 'newTab';
                    } else {
                        $openType = $gridName;
                    }
                    continue; //테이블에서 노출 금지
                }
                if($gridKey === 'check'){
                    echo "<th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]' /></th>";
                }
                else if ($gridKey === 'processStatus'){
                    echo "<th>".$gridName." <input type='button' value='로그' class='btn btn-sm btn-white js-order-log' /></th>";
                }
                else {
                    echo "<th>".$gridName."</th>";
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

        // 주문 처리가 주문기준으로 되어야 할 주문 단계의 경우 체크가 동일 처리되게
        if (isset($data['goods']) === true) {
            $sortNo = $data['cnt']['goods']['goods'];// 번호 설정
            $rowAll = 0;
            foreach ($data['goods'] as $sKey => $sVal) {
                $rowCnt = $data['cnt']['goods']['all']; // 한 주문당 상품주문 수량
                $rowScm = 0;
                $rowMultiShipping = 0;
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        // 주문상태 모드
                        $statusMode = substr($val['orderStatus'], 0, 1);

                        // rowspan 처리
                        $orderGoodsRowSpan = $rowAll === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';

                        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scm no 를 order info sno 로 대체, dKey는 order delivery sno로 대체
                        if($useMultiShippingKey === true){
                            $rowScm = 0;
                            $orderMultiShippingRowSpan = ' rowspan="' . ($data['cnt']['multiShipping'][$sKey]) . '"';
                        }
                        else {
                            $orderScmRowSpan = ' rowspan="' . $data['cnt']['scm'][$sKey] . '"';
                        }

                        $deliveryKeyCheck = $val['deliverySno'] . '-' . $val['orderDeliverySno'] . '-' . $val['deliveryMethodFl'] . '-' . $val['goodsDeliveryCollectFl'];
                        if ($deliveryKeyCheck !== $deliveryUniqueKey) {
                            $rowDelivery = 0;
                        }
                        $deliveryUniqueKey = $deliveryKeyCheck;
                        $orderDeliveryRowSpan = ' rowspan="' . $data['cnt']['delivery'][$deliveryUniqueKey] . '"';
                        if ($val['goodsDeliveryCollectFl'] == 'later') {
                            $deliveryCharge = 0;
                        } else {
                            $deliveryCharge = ($val['goodsDeliveryFl'] == 'y' || ($val['goodsDeliveryFl'] != 'y' && $val['sameGoodsDeliveryFl'] == 'y')) ? $val['deliveryCharge'] : $val['goodsDeliveryCollectPrice'];
                        }

                        // 결제정보에 사용할 데이터 만들기
                        if ($val['goodsDcPrice'] > 0) {
                            $goodsDcPrice[$val['sno']] = $val['goodsDcPrice'];
                        }

                        //배송업체가 설정되어 있지 않을시 기본 배송업체 select
                        $selectInvoiceCompanySno = $val['invoiceCompanySno'];
                        if((int)$selectInvoiceCompanySno < 1){
                            $selectInvoiceCompanySno = $deliverySno;
                        }
                        $totalMemberDeliveryDcPrice = 0;
                        if ($val['totalMemberDeliveryDcPrice'] > 0) {
                            $totalMemberDeliveryDcPrice = $val['deliveryPolicyCharge'];
                        }
                        $divisionDeliveryCharge = $val['divisionDeliveryCharge'];
                        ?>
                        <tr id="statusCheck_<?= $statusMode; ?>_<?= $val['sno']; ?>" class="text-center" data-status-mode="<?=$statusMode?>" data-original-status-mode="<?=$val['orderStatus']?>" data-settle-kind="<?= $data['settleKind']; ?>" data-handle-mode="<?= $val['handleMode']; ?>">
                            <?php
                            // 주문상세 그리드 항목 시작
                            if (count($orderGridConfigList) > 0) {
                                foreach ($orderGridConfigList as $gridKey => $gridName) {
                            ?>
                                <?php if($gridKey === 'check'){ // 선택 ?>
                                    <td class="center">
                                        <div class="display-block">
                                            <input type="checkbox" name="bundle[statusCheck][<?= $val['sno']; ?>]" value="<?= $val['sno']; ?>" class="<?= gd_isset($checkBoxOnclickAction); ?>"/>
                                            <input type="hidden" name="bundle[statusMode][<?= $val['sno']; ?>]" value="<?= $val['orderStatus']; ?>"/>
                                            <input type="hidden" name="bundle[deliveryMethodFl][<?= $val['sno']; ?>]" value="<?= $val['deliveryMethodFl']; ?>"/>
                                            <input type="hidden" name="bundle[defaultInvoiceCompanySno][<?= $val['sno']; ?>]" value="<?= $val['invoiceCompanySno']; ?>"/>
                                            <input type="hidden" name="bundle[goods][sno][<?= $val['sno']; ?>]" value="<?= $val['sno']; ?>"/>
                                        </div>
                                    </td>
                                <?php } // 선택 ?>

                                <?php if($gridKey === 'no'){ // 번호 ?>
                                    <td><?= $sortNo ?></td>
                                <?php } // 번호 ?>

                                <?php if($gridKey === 'orderGoodsNo'){ // 상품주문번호 ?>
                                    <td>
                                        <?= $val['sno'] ?>
                                        <?php if ($data['orderChannelFl'] == 'naverpay') { ?>
                                            <p class="mgt5"><img src="<?=UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www()?>" /> <?= $val['apiOrderGoodsNo']; ?></p>
                                        <?php } else if ($data['orderChannelFl'] == 'etc') { ?>
                                            <p class="mgt5">
                                                <img src="<?=UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'etc.gif')->www()?>" />
                                                <?= $val['apiOrderGoodsNo']; ?>
                                            </p>
                                        <?php } else { } ?>
                                    </td>
                                <?php } // 상품주문번호 ?>

                                <?php if($gridKey === 'goodsCd'){ // 상품코드(자체상품코드) ?>
                                    <td><?=$val['goodsCd']?></td>
                                <?php } // 상품코드(자체상품코드) ?>

                                <?php if($gridKey === 'goodsTaxInfo'){ // 상품 부가세 ?>
                                    <td>
                                        <?php
                                        if(gettype($val['goodsTaxInfo']) !== 'array'){
                                            $val['goodsTaxInfo'] = explode(STR_DIVISION, $val['goodsTaxInfo']);
                                        }

                                        if($val['goodsTaxInfo'][0] === 't'){
                                            echo $val['goodsTaxInfo'][1] . '%';
                                        }
                                        else {
                                            echo '면세';
                                        }
                                        ?>
                                    </td>
                                <?php } // 상품 부가세 ?>

                                <?php if($gridKey === 'goodsImage'){ // 이미지 ?>
                                    <td>
                                        <?php if ($val['goodsType'] === 'addGoods') { ?>
                                            <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                        <?php } else { ?>
                                            <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                        <?php } ?>
                                    </td>
                                <?php } // 이미지 ?>

                                <?php if($gridKey === 'orderGoodsNm'){ // 주문상품 ?>
                                    <td class="text-left">
                                        <?php if (empty($val['userHandleInfo']) === false) { ?>
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
                                        <?php } else {}?>
                                        <?php if ($val['goodsType'] === 'addGoods') { ?>
                                            <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                            <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                               onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                                                <?=$val['goodsNmStandard'] && $isUseMall === false ? $val['goodsNmStandard'] :  $val['goodsNm']?></a>
                                        <?php } else { ?>
                                            <?php if($val['timeSaleFl'] =='y') { ?>
                                                <img src='<?=PATH_ADMIN_GD_SHARE?>img/time-sale.png' alt='타임세일' />
                                            <?php } ?>
                                            <a href="javascript:void()" class="one-line" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                                                <?=$val['goodsNmStandard'] && $isUseMall === false ? $val['goodsNmStandard'] :  $val['goodsNm'] ?></a>
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
                                                    if (count($val['optionInfo']) - 1 == $oKey && !empty($val['optionInfo'][0]['deliveryInfoStr'])){
                                                        $deliveryInfoStr = '['.$val['optionInfo'][0]['deliveryInfoStr'].']';
                                                    } else {
                                                        $deliveryInfoStr = '';
                                                    }
                                                    echo '<dl class="dl-horizontal" title="옵션명">';
                                                    echo '<dt>' . $oVal['optionName'] . ' :</dt>';
                                                    echo '<dd>' . $oVal['optionValue'] . $deliveryInfoStr . '</dd>';
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
                                                        echo '<span>(추가금 ';
                                                        if ($isUseMall) {
                                                            echo gd_global_order_currency_display(gd_isset($oVal['optionTextPrice']), $data['exchangeRate'], $data['currencyPolicy']);
                                                        } else {
                                                            echo gd_currency_display($oVal['optionTextPrice']);
                                                        }
                                                        echo ')</span>';
                                                    }
                                                    echo '</li>';
                                                    echo '</ul>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </td>
                                <?php } // 주문상품 ?>

                                <?php if($gridKey === 'orderGoodsNmGlobal'){ // 주문상품(해외상점) ?>
                                    <td class="text-left">
                                        <?php if($val['mallSno'] != DEFAULT_MALL_NUMBER){ ?>
                                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                                <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                                   onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                                                    <?=$val['goodsNm']?></a>
                                            <?php } else { ?>
                                                <?php if($val['timeSaleFl'] =='y') { ?>
                                                    <img src='<?=PATH_ADMIN_GD_SHARE?>img/time-sale.png' alt='타임세일' />
                                                <?php } ?>
                                                <a href="javascript:void()" class="one-line" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                                                    <?=$val['goodsNm']?></a>
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
                                                            echo '<span>(추가금 ';
                                                            echo gd_global_order_currency_display(gd_isset($oVal['optionTextPrice']), $data['exchangeRate'], $data['currencyPolicy']);
                                                            echo ')</span>';
                                                        }
                                                        echo '</li>';
                                                        echo '</ul>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        <?php } else { ?>
                                            <div style="width:100%; text-align: center;">-</div>
                                        <?php } ?>
                                    </td>
                                <?php } // 주문상품(해외상점) ?>

                                <?php if($gridKey === 'goodsCnt'){ // 수량 ?>
                                    <td class="text-center">
                                        <strong><?= number_format($val['goodsCnt']); ?></strong>
                                        <?php if (isset($val['stockCnt']) === true) { ?>
                                            <div title="재고">재고: <?= $val['stockCnt']; ?></div>
                                        <?php } ?>
                                    </td>
                                <?php } // 수량 ?>

                                <?php if($gridKey === 'orgGoodsPrice'){ // 판매가 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?= gd_global_order_currency_display($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                        <?php } else { ?>
                                            <?= gd_currency_display($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); ?>
                                        <?php } ?>
                                    </td>
                                <?php } // 판매가 ?>

                                <?php if($gridKey === 'goodsPrice'){ // 상품금액 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?= gd_global_order_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                        <?php } else { ?>
                                            <?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']); ?>
                                        <?php } ?>
                                    </td>
                                <?php } // 상품금액 ?>

                                <?php if($gridKey === 'totalGoodsPrice' && $rowAll == 0){ // 총 상품금액 ?>
                                    <td class="text-center" <?=$orderGoodsRowSpan?>>
                                        <?php if ($isUseMall == true) { ?>
                                            <?= gd_global_order_currency_display($data['totalGoodsPrice'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                        <?php } else { ?>
                                            <?= gd_currency_display($data['totalGoodsPrice']); ?>
                                        <?php } ?>
                                    </td>
                                <?php } // 총 상품금액 ?>

                                <?php if($gridKey === 'costPrice'){ // 매입가 ?>
                                    <td>
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display(($val['costPrice'] + $val['optionCostPrice']) * $val['goodsCnt'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display(($val['costPrice'] + $val['optionCostPrice']) * $val['goodsCnt'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 매입가 ?>

                                <?php if($gridKey === 'totalDcPrice' && $rowAll == 0){ // 총 할인금액 ?>
                                    <td <?=$orderGoodsRowSpan?>>
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($data['totalDcPrice'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($data['totalDcPrice'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 총 할인금액 ?>

                                <?php if($gridKey === 'totalUseAddedPrice' && $rowAll === 0){ // 총 부가결제금액 ?>
                                    <td <?=$orderGoodsRowSpan?>>
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($data['totalUseAddedPrice'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($data['totalUseAddedPrice'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 총 부가결제금액 ?>

                                <?php if($gridKey === 'deliveryCharge' && $rowDelivery === 0){ // 배송비 ?>
                                    <td <?= $orderDeliveryRowSpan; ?>>
                                        <?php if ($isUseMall == true) { ?>
                                            <?= gd_global_order_currency_display($val['deliveryCharge'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                        <?php } else { ?>
                                            <?= gd_currency_display($deliveryCharge); ?>
                                        <?php } ?>
                                        <br />
                                        <?=$val['goodsDeliveryCollectFl'] == 'pre' ? '(선불)' : '(착불)';?>

                                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                                            <br>(총무게 : <?=$data['totalDeliveryWeight']?>kg)
                                        <?php } ?>
                                    </td>
                                <?php } // 배송비 ?>

                                <?php if($gridKey === 'totalDeliveryCharge' && $rowAll === 0){ // 총 배송비 ?>
                                    <td <?= $orderGoodsRowSpan; ?>>
                                        <?php if ($isUseMall == true) { ?>
                                            <?= gd_global_order_currency_display($data['totalDeliveryCharge'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                        <?php } else { ?>
                                            <?= gd_currency_display($data['totalDeliveryCharge']); ?>
                                        <?php } ?>
                                    </td>
                                <?php } // 총 배송비 ?>

                                <?php if($gridKey === 'totalOrderPrice' && $rowAll === 0){ // 총 주문금액 ?>
                                    <td <?= $orderGoodsRowSpan ?>>
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($data['totalOrderPrice'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($data['totalOrderPrice'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 총 주문금액 ?>

                                <?php if($gridKey === 'purchaseNm'){ // 매입처 ?>
                                    <td>
                                        <?= (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && $val['purchaseNm']) ? $val['purchaseNm'] : '-' ?>
                                    </td>
                                <?php } // 매입처 ?>

                                <?php if($gridKey === 'brandNm'){ // 브랜드 ?>
                                    <td><?=$val['brandNm']?></td>
                                <?php } // 브랜드 ?>

                                <?php if($gridKey === 'goodsModelNo'){ //모델명 ?>
                                    <td><?=$val['goodsModelNo']?></td>
                                <?php } //모델명 ?>

                                <?php if($gridKey === 'makerNm'){ // 제조사 ?>
                                    <td><?=$val['makerNm']?></td>
                                <?php } // 제조사 ?>

                                <?php if($gridKey === 'scmNm' && $rowScm === 0){ // 공급사 ?>
                                    <td <?= $orderScmRowSpan; ?>><?= $val['companyNm'] ?></td>
                                <?php } // 공급사 ?>

                                <?php if($gridKey === 'commission'){ // 수수료율 ?>
                                    <td><?=$val['commission']?>%</td>
                                <?php } // 수수료율 ?>

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

                                    <?php if($gridKey === 'processStatus'){ // 처리상태 ?>
                                        <td class="center">
                                            <?php if ($val['beforeStatusStr'] && $statusMode == 'r') { ?>
                                                <div class="text-muted" title="이전 상품별 주문 상태"><?= $val['beforeStatusStr']; ?> &gt;</div>
                                            <?php } ?>
                                            <p>
                                                <?= $val['orderStatusStr']; ?>
                                                <input type="button" data-sno="<?= $val['sno']; ?>" data-name="<?= $val['goodsNm']; ?>" value="로그" class="btn btn-sm btn-white js-order-log"/>
                                            </p>
                                            <?php if($val['naverpayStatus']['code'] == 'DelayProductOrder'){    //발송지연?>
                                                <div style="padding-bottom:5px" data-sno="<?=$val['sno']?>" data-info="<?=$val['naverpayStatus']['text']?>" class="js-btn-naverpay-status-detail">
                                                    (<?=$val['naverpayStatus']['notice']?>)
                                                </div>
                                            <?php }?>

                                            <div>
                                                <?php
                                                if ($val['orderStatus'] == 'd1') {
                                                    echo gd_date_format('m-d H:i', gd_isset($val['deliveryDt']));
                                                } else if ($val['orderStatus'] == 'd3') {
                                                    echo gd_date_format('m-d H:i', gd_isset($val['finishDt']));
                                                }
                                                ?>
                                            </div>

                                            <?php if (empty($val['invoiceCompanySno']) === false && empty($val['invoiceNo']) === false && (!$val['deliveryMethodFl'] || $val['deliveryMethodFl'] === 'delivery' || $val['deliveryMethodFl'] === 'packet')) { ?>
                                                <div>
                                                    <input type="button" onclick="delivery_trace('<?= $val['invoiceCompanySno']; ?>', '<?= $val['invoiceNo']; ?>');" value="배송추적" class="btn btn-sm btn-gray mgt5"/>
                                                </div>
                                            <?php } ?>
                                        </td>
                                    <?php } // 처리상태 ?>

                                <?php if($gridKey === 'invoiceNo'){ // 송장번호 ?>
                                    <td class="center" style="width: 140px;">
                                        <?php if ($data['orderChannelFl'] == 'naverpay') { ?>
                                            <?=$val['apiDeliveryDataText']?>
                                        <?php }?>

                                        <?php if($val['hideDeliveryCompanySelectBox'] != 'y') { ?>
                                            <input type="text" value="<?=($val['invoiceCompanySno']) ? $deliveryCom[$selectInvoiceCompanySno] : ''?>" placeholder="<?=$val['deliveryMethodFlText']?>" class="form-control mgt5 width-sm" readonly="readonly"/>
                                        <?php }?>
                                        <input type="text" value="<?= $val['invoiceNo']; ?>" class="form-control mgt5 width-sm" readonly="readonly" />
                                    </td>
                                <?php } // 송장번호 ?>

                                <?php if($gridKey === 'totalUseAddedRefundPrice' && $rowAll === 0){ // 총 부가결제 환원금액 ?>
                                    <td <?= $orderGoodsRowSpan ?>>-</td>
                                <?php } // 총 부가결제 환원금액 ?>

                                <?php if($gridKey === 'totalCancelPrice' && $rowAll === 0){ // 총 취소금액 ?>
                                    <td <?= $orderGoodsRowSpan ?>>-</td>
                                <?php } // 총 취소금액 ?>

                                <?php if($gridKey === 'totalRefundPrice' && $rowAll === 0){ // 총 환불금액 ?>
                                    <td <?= $orderGoodsRowSpan ?>>-</td>
                                <?php } // 총 환불금액 ?>

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
                                        <?php if($rowAll === 0){ ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                <?php if($data['receiverZonecode']){ echo "[".$data['receiverZonecode']."]"; } ?>
                                                <?php if($data['receiverZipcode']){ echo "(".$data['receiverZipcode'].")"; } ?>
                                                <br />
                                                <?php if($data['receiverAddress']){ echo $data['receiverAddress']; } ?>
                                                <?php if($data['receiverAddressSub']){ echo " ".$data['receiverAddressSub']; } ?>
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
                                        <?php if($rowAll === 0){ ?>
                                            <td <?= $orderGoodsRowSpan ?>>
                                                1개
                                            </td>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } // 배송지 ?>
                                <?php if($gridKey === 'adminMemo' && $rowAll === 0){ // 관리자메모 ?>
                                    <td <?=$orderGoodsRowSpan?> class="text-center" data-order-no="<?= $data['orderNo'] ?>" data-reg-date="<?= $data['regDt'] ?>">
                                        <button type="button" class="btn btn-sm btn-<?php if($data['adminOrdGoodsMemo']){ echo 'gray'; }else{ echo 'white';} ?> js-super-admin-memo" data-order-no="<?= $data['orderNo']; ?>" data-memo="<?=$data['adminOrdGoodsMemo'];?>">보기</button>
                                    </td>
                                <?php } // 관리자메모 ?>

                                <!-- // 주문 상세 refund_view 탭에서만 호출 시작-->
                                <?php if($gridKey === 'refundPrice'){// 환불 상품금액
                                    $goodsBenefitMinusPrice = ($val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['couponGoodsDcPrice'] + $val['enuri'] + $val['divisionCouponOrderDcPrice']);
                                    ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?= gd_global_order_currency_display(((($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) - $goodsBenefitMinusPrice), $data['exchangeRate'], $data['currencyPolicy']); ?>
                                        <?php } else { ?>
                                            <?= gd_currency_display((($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) - $goodsBenefitMinusPrice); ?>
                                        <?php } ?>
                                    </td>
                                <?php } // 환불 상품금액 ?>
                                <?php if($gridKey === 'refundUseMileage'){ // 상품 환원 마일리지 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundUseMileage'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundUseMileage'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 상품 환원 마일리지 ?>
                                <?php if($gridKey === 'refundUseDeposit'){ //상품 환원 예치금 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundUseDeposit'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundUseDeposit'])?>
                                        <?php } ?>
                                    </td>
                                <?php } //상품 환원 예치금 ?>
                                <?php if($gridKey === 'refundCharge'){ // 상품 환불 수수료 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundCharge'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundCharge'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 상품 환불 수수료 ?>
                                <?php if($gridKey === 'refundUseMileageCommission'){ // 마일리지 환불 수수료 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($data['refundUseMileageCommission'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundUseMileageCommission'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 마일리지 환불 수수료 ?>
                                <?php if($gridKey === 'refundUseDepositCommission'){ // 예치금 환불 수수료 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundUseDepositCommission'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundUseDepositCommission'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 예치금 환불 수수료 ?>
                                <?php if($gridKey === 'refundDeliveryCharge'){ // 환불 배송비 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundDeliveryCharge'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundDeliveryCharge'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 환불 배송비 ?>
                                <?php if($gridKey === 'refundDeliveryUseMileage'){ // 배송비 환원 마일리지 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundDeliveryUseMileage'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundDeliveryUseMileage'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 배송비 환원 마일리지 ?>
                                <?php if($gridKey === 'refundDeliveryUseDeposit'){ // 배송비 환원 예치금 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundDeliveryUseDeposit'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundDeliveryUseDeposit'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 배송비 환원 예치금 ?>
                                <?php if($gridKey === 'refundDeliveryInsuranceFee'){ // 환불 해외배송보험료 ?>
                                    <td class="text-right">
                                        <?php if ($isUseMall == true) { ?>
                                            <?=gd_global_order_currency_display($val['refundDeliveryInsuranceFee'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        <?php } else { ?>
                                            <?=gd_currency_display($val['refundDeliveryInsuranceFee'])?>
                                        <?php } ?>
                                    </td>
                                <?php } // 환불 해외배송보험료 ?>
                                <!-- // 주문 상세 refund_view 탭에서만 호출끝-->
                            <?php
                                }

                            }
                            // 주문상세 그리드 항목 끝
                            ?>
                        </tr>
                        <?php
                        $sortNo--;
                        $rowDelivery++;
                        $rowScm++;
                        $rowAll++;
                        $rowMultiShipping++;
                    }
                }
            }
        } else {
            ?>
            <tr>
                <td class="no-data" colspan="<?=count($orderGridConfigList)?>"><?=$incTitle?>이 없습니다.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>


<?php if (isset($data['goods']) === true) { ?>
    <div class="table-action">
        <?php if($data['restoreStatus'] && $data['statusMode'] == 'c'){ ?>
        <?php } else { ?>
            <div class="pull-left form-inline">
                <?php if (!$isProvider || $data['statusMode'] !== 'r') { // 공급사 환불내역은 보여주면 안됨?>
                    <?php if ($selectBoxOrderStatus) { ?>
                        <span class="action-title">선택한 상품을</span>
                        <?= gd_select_box('bundleOrderStatus', 'bundle[orderStatus]', $selectBoxOrderStatus, null, null, '==상품상태==', $orderStateChangeDisabled, 'form-control js-status-change') ?>
                    <?php } ?>
                <?php } ?>

                    <?php if($data['statusMode'] !== 'f' && $data['statusMode'] !== 'c'){ ?>
                        <?= gd_select_box('applyDeliverySno', null, $deliveryCom, null, $deliverySno, null, null, 'form-control'); ?>
                        <input type="text" id="applyInvoiceNo" value="" class="form-control input-lg width-lg" />
                    <?php } ?>
                        <button type="button" class="btn btn-red js-order-status-delivery">일괄적용</button>
            </div>
        <?php } ?>

        <?php if(!$data['restoreStatus'] && $data['statusMode'] == 'c'){ ?>
        <?php } else { ?>
        <div class="pull-right form-inline">
            <?php
            if(count($actionButtonList) > 0){
                foreach($actionButtonList as $key => $buttonName){
                    $disabledButton = '';
                    if (in_array($data['orderChannelFl'], ['payco', 'naverpay', 'etc']) === true) {
                        if (($key != 'refund' && $key != 'back' && $key != 'exchange' && $key != 'exchangeCancel') && $data['orderChannelFl'] == 'payco') {
                            $disabledButton = ' disabled="disabled"';
                        } elseif (($key != 'refund' && $key != 'back') && $data['orderChannelFl'] == 'naverpay') {
                            $disabledButton = ' disabled="disabled"';
                        } elseif ($data['orderChannelFl'] == 'etc') {
                            $disabledButton = ' disabled="disabled"';
                        } else {

                        }
                    }
            ?>
                    <button type="button" class="btn btn-sm btn-black mgr5" onclick="javascript:order_view_status_popup('<?=$key?>', '<?=$data['orderNo']?>', '<?=$isProvider?>');" <?=$disabledButton?>><?=$buttonName?></button>
            <?php
                }
            }
            ?>
        </div>
        <?php } ?>

    </div>
<?php } ?>

<script type="text/javascript">
    <!--
    var statusStandardCode = eval(<?= $scriptStatusCode;?>);

    $(function(){
        $('.js-btn-naverpay-status-detail').bind('click',function () {  //네이버페이 상세사유 보기
            var sno = $(this).data('sno');
            var info = $(this).data('info');
            $.get('../order/layer_naverpay_order.php', {'mode': 'view','orderNo': '<?= gd_isset($data['orderNo']);?>',  'orderGoodsNo' : sno }, function (data) {
                if(data.substring(0,5) == 'error'){
                    var errorData = data.split("|");
                    alert(errorData[1]);
                    return;
                }

                BootstrapDialog.show({
                    title: info+'정보',
                    size: get_layer_size('wide'),
                    message: data,
                    closable: true,
                });

//                    layer_popup(data, dom.statusSelect.find('option:selected').text(), 'wide');
            });

        }).css('cursor','pointer').css('text-decoration','underline');

        // 주문상세_상품º주문번호별메모 보기
        $('.js-super-admin-memo').click(function (e) {
            var orderNo = $(this).closest('td').data('order-no');
            var regDt = $(this).closest('td').data('reg-date');

            var win = popup({
                url: 'popup_admin_order_goods_memo.php?orderNo='+orderNo+'&regDt='+regDt,
                target: '',
                width: '1000',
                height: '600',
                scrollbars: 'yes',
                resizable: 'yes'
            });
            win.focus();

            return win;
        });

        // 관리자메모 노출
        $('.js-super-admin-memo').on({
            'mouseover' :function (e) {
                var memoEmptyFl = $(this).data('memo');

                if(memoEmptyFl) {
                    var selectOrderNo = $(this).data('order-no');
                    var top = ($(this).position().top) - 50;  //보기 버튼의 Y 위치
                    var left = ($(this).position().left) - 900; //보기 버튼의 X 위치 - 레이어팝업의 크기만 큼 빼서 위치 조절
                    $.each($('.js-super-admin-memo').closest('td'), function (key, val) {
                        if ($(val).data('order-no') === selectOrderNo) {
                            $.post("../order/layer_admin_order_goods_memo", {orderNo: selectOrderNo}, function (result) {
                                console.log(result);
                                $('.js-super-admin-memo').after('<div class="memo_layer"></div>');
                                $('.memo_layer').html(result);
                                $('.memo_layer').css({
                                    "top": top
                                    , "left": left
                                    , "right": "300px"
                                    , "position": "absolute"
                                    , "width": "850px"
                                    , "overflow": "hidden"
                                    , "height": "auto"
                                    , "z-index": "999"
                                    , "border": "1px solid #cccccc"
                                    , "background": "#ffffff"

                                }).show();
                            }, "html");
                        }
                    });
                }
            },
            'mouseout'  :function (e) {
                $('.memo_layer').remove();
            }
        });
    })

    /**'
     * 주문 처리 상품의 가능 여부를 체크
     * 부모창인 order_view.php에서 참조한다.
     *
     * @param string chkMode 처리 코드
     */
    function set_check_status(chkCode) {
        // 주문 처리 가능한 상품만 체크
        for (var codeKey in statusStandardCode) {
            for (var i = 0; i < statusStandardCode[codeKey].length; i++) {
                var codeSubKey = statusStandardCode[codeKey][i];
                if (codeSubKey == chkCode) {
                    $('tr[id*="statusCheck_' + codeKey + '"]').removeClass('disabled');
                    $('tr[id*="addStatusCheck_' + codeKey + '"]').removeClass('disabled');
                    $('tr[id*="statusCheck_' + codeKey + '"] input').prop('disabled', false);
                    $('tr[id*="statusCheck_' + codeKey + '"] select').prop('disabled', false);
                }
            }
        }

        // 비활성화 셀에서 체크박스가 체크된 경우 자동 해제
        $('tr[id*=\'statusCheck_\']').each(function (i) {
            var checkboxId = $(this).find('td:eq(0)').find('input:checkbox').attr('id');
            if (typeof checkboxId != 'undefined') {
                if ($(this).hasClass('disabled')) {
                    $('#' + checkboxId).prop('checked', false);
                }
            }
        });
    }
    //-->
</script>
