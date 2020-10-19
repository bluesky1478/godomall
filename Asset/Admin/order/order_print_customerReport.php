<style>
    .table-rows > thead:first-child > tr:first-child > th {
        border-left: 1px solid #aeaeae;
    }
    .page-break {
        padding: 0 2px;
    }
</style>
<?php
$printCnt = 0;
foreach ($orderData as $key => $data) {
    // translator load
    if (empty($translator) == false) {
        $translator[$key]->register();
    }
    $printCnt++;
?>
<div class="page-break">
    <?php
    if($printCnt > 1 ){
        ?>
        <script type="text/javascript">
            $('.page-break + .page-break').css({'page-break-before': 'unset',
                                                'page-break-after': 'unset'});
        </script>
        <div class="page-header">
            <h3><?= $popupTitle;?></h3>
        </div>
    <?php } ?>
    <div class="table-title gd-help-manual">
        <?= __('주문자 정보'); ?>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th><?= __('주문번호'); ?></th>
            <td>
                <?php echo $data['orderNo']; ?>
            </td>
            <th><?= __('주문일'); ?></td>
            <td><?php echo gd_date_format(__('Y년 m월 d일 H시 i분'), gd_isset($data['regDt'])); ?></td>
        </tr>
        <tr>
            <th><?= __('주문자명'); ?></th>
            <td>
                <span class="text_emphasis"><?php echo gd_isset($data['orderName']); ?></span>
                <?php
                if (empty($data['memInfo']) === true) {
                    echo '<span class="text_emphasis"> / ' . __('비회원') . '</span>';
                } else {
                    echo '<span class="text_emphasis"> / ' . gd_isset($data['memInfo']['memId']);
                    if (gd_isset($data['memInfo']['nickNm'])) {
                        echo ' (' . $data['memInfo']['nickNm'] . ')';
                    }
                }
                ?>
            </td>
            <th><?= __('이메일'); ?></th>
            <td><?php echo gd_isset($data['orderEmail']); ?></td>
        </tr>
        <tr>
            <th><?= __('전화번호'); ?></th>
            <td><?php echo gd_isset($data['orderPhone']); ?></td>
            <th><?= __('휴대폰번호'); ?></th>
            <td><?php echo gd_isset($data['orderCellPhone']); ?></td>
        </tr>
        </tbody>
    </table>


    <div class="table-title gd-help-manual"><?= __('상품정보'); ?></div>
    <table class="table table-rows">
        <thead>
        <tr>
            <?php
            if($data['orderPrint']['orderPrintOdCsGoodsCode'] === 'y'){
                $data['orderGridConfigList']['goodsNo'] = __('상품코드');
            }
            if (count($data['orderGridConfigList']) > 0) {
                foreach($data['orderGridConfigList'] as $gridKey => $gridName){
                    if($gridKey === 'check'){
                        continue;
                    }
                    if($gridKey === 'goodsImage' && $data['orderPrint']['orderPrintOdCsImageDisplay'] !== 'y'){
                        continue;
                    }
                    if($gridKey === 'scmNm' && $data['orderPrint']['orderPrintOdCsScmDisplay'] !== 'y'){
                        continue;
                    }
                    if($gridKey === 'goodsCd' && $data['orderPrint']['orderPrintOdCsSelfGoodsCode'] !== 'y'){
                        continue;
                    }
                    if($gridKey === 'openLinkOption') {
                        continue;
                    }

                    echo "<th>".__($gridName)."</th>";
                }
            }
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        if (isset($data['goods']) === true) {
            $rowAll = 0;
            $sortNo = $data['cnt']['goods']['goods'];// 번호 설정
            foreach ($data['goods'] as $sKey => $sVal) {
                $rowCnt = $data['cnt']['goods']['all']; // 한 주문당 상품주문 수량
                $rowScm = 0;
                $rowMultiShipping = 0;
                foreach ($sVal as $dKey => $dVal) {
                    $rowDelivery = 0;
                    foreach ($dVal as $key => $val) {
                        $statusMode = substr($val['orderStatus'], 0, 1);

                        // rowspan 처리
                        $orderGoodsRowSpan = $rowAll === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scm no 를 order info sno 로 대체, dKey는 order delivery sno로 대체
                        if($data['useMultiShippingKey']){
                            $rowScm = 0;
                            $orderMultiShippingRowSpan = ' rowspan="' . ($data['cnt']['multiShipping'][$sKey]) . '"';
                        }
                        else {
                            $orderScmRowSpan = ' rowspan="' . ($data['cnt']['scm'][$sKey]) . '"';
                        }

                        $deliveryKeyCheck = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                        if ($deliveryKeyCheck !== $deliveryUniqueKey) {
                            $rowDelivery = 0;
                        }
                        $deliveryUniqueKey = $deliveryKeyCheck;
                        $orderDeliveryRowSpan = ' rowspan="' . $data['cnt']['delivery'][$deliveryUniqueKey] . '"';

                        // 기본 배송업체 설정
                        if (empty($val['deliverySno']) === true) {
                            $val['orderDeliverySno'] = $deliverySno;
                        }
                        ?>
                        <tr id="statusCheck_<?= $statusMode; ?>_<?= $key; ?>" class="text-center">
                            <?php
                            //주문상세 그리드 항목 시작
                            if (count($data['orderGridConfigList']) > 0) {
                                foreach ($data['orderGridConfigList'] as $gridKey => $gridName) {
                                    if($gridKey === 'check'){
                                        continue;
                                    }
                                    if($gridKey === 'goodsImage' && $data['orderPrint']['orderPrintOdCsImageDisplay'] !== 'y'){
                                        continue;
                                    }
                                    if($gridKey === 'scmNm' && $data['orderPrint']['orderPrintOdCsScmDisplay'] !== 'y'){
                                        continue;
                                    }
                                    if($gridKey === 'goodsCd' && $data['orderPrint']['orderPrintOdCsSelfGoodsCode'] !== 'y'){
                                        continue;
                                    }
                                    ?>
                                    <?php if($gridKey === 'no'){ //- 번호 ?>
                                        <td><?= $sortNo ?></td>
                                    <?php } //- 번호 ?>

                                    <?php if($gridKey === 'orderGoodsNo'){ // 상품주문번호 ?>
                                        <td><?= $val['sno'] ?></td>
                                    <?php } // 상품주문번호 ?>

                                    <?php if($gridKey === 'goodsCd'){ // 상품코드(자체상품코드) ?>
                                        <td><?=$val['goodsCd']?></td>
                                    <?php } // 상품코드(자체상품코드) ?>

                                    <?php if($gridKey === 'goodsTaxInfo'){ // 상품 부가세 ?>
                                        <td>
                                            <?php
                                            if(is_array($val['goodsTaxInfo'])){
                                                if($val['goodsTaxInfo'][0] === 't'){
                                                    echo $val['goodsTaxInfo'][1] . '%';
                                                }
                                                else {
                                                    echo __('면세');
                                                }
                                            }
                                            else {
                                                $goodsTaxInfoArr = explode(STR_DIVISION, $val['goodsTaxInfo']);
                                                if($goodsTaxInfoArr[0] === 't'){
                                                    echo $goodsTaxInfoArr[1] . '%';
                                                }
                                                else {
                                                    echo __('면세');
                                                }
                                            }
                                            ?>
                                        </td>
                                    <?php } // 상품 부가세 ?>

                                    <?php if($gridKey === 'goodsImage'){ // 이미지 ?>
                                        <td>
                                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                            <?php } else { ?>
                                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank') ?>
                                            <?php } ?>
                                        </td>
                                    <?php } // 이미지 ?>

                                    <?php if($gridKey === 'orderGoodsNm'){ // 주문상품 ?>
                                        <td class="text-left">
                                            <?php if($val['handleMode'] === 'e'){ ?>
                                                <span class="label label-danger">교환취소</span><br />
                                            <?php } else if ($val['handleMode'] === 'z'){ ?>
                                                <span class="label label-primary">교환추가</span><br />
                                            <?php } ?>

                                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                                <div class="goods_name hand text-primary" title="<?= __('상품명'); ?>" onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>');">
                                                    <span class="label label-default" title="<?= $val['sno'] ?>"><?= __('추가'); ?></span>
                                                    <?= $val['goodsNm']; ?>
                                                </div>
                                            <?php } else { ?>
                                                <div class="goods_name hand text-primary" title="<?= __('상품명'); ?>" onclick="goods_register_popup('<?= $val['goodsNo']; ?>');">
                                                    <?= $val['goodsNm']; ?>
                                                </div>
                                            <?php } ?>
                                            <div class="info">
                                                <?php

                                                // 옵션 처리
                                                if (empty($val['optionInfo']) === false) {
                                                    foreach ($val['optionInfo'] as $oKey => $oVal) {
                                                        if (count($val['optionInfo'])-1 == $oKey && !empty($oVal['deliveryInfoStr'])) {
                                                            $deliveryMsg = ' ['.$oVal['deliveryInfoStr'].']';
                                                        }else{
                                                            $deliveryMsg = '';
                                                        }
                                                        echo '<dl class="dl-horizontal" title="' . __('옵션명') . '">';
                                                        echo '<dt class="display-inline-block">' . $oVal['optionName'] . ' :</dt>';
                                                        echo '<dd class="display-inline-block">' . $oVal['optionValue'] . $deliveryMsg . '</dd>';
                                                        echo '</dl>';
                                                    }
                                                }

                                                // 텍스트 옵션 처리
                                                if (empty($val['optionTextInfo']) === false) {
                                                    foreach ($val['optionTextInfo'] as $oKey => $oVal) {
                                                        echo '<ul class="list-unstyled" title="' . __('텍스트 옵션명') . '">';
                                                        echo '<li>' . $oVal['optionName'] . ' :</li>';
                                                        echo '<li>' . $oVal['optionValue'] . ' ';
                                                        if ($oVal['optionTextPrice'] > 0) {
                                                            echo '<span>(' . __('추가금') . ' ' . gd_global_order_currency_display($oVal['optionTextPrice'], $data['exchangeRate'], $data['currencyPolicy'])  . ')</span>';
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
                                                    <div class="goods_name hand text-primary" title="<?= __('상품명'); ?>" onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>');">
                                                        <span class="label label-default" title="<?= $val['sno'] ?>"><?= __('추가'); ?></span>
                                                        <?= $val['goodsNm']; ?>
                                                    </div>
                                                <?php } else { ?>
                                                    <div class="goods_name hand text-primary" title="<?= __('상품명'); ?>" onclick="goods_register_popup('<?= $val['goodsNo']; ?>');">
                                                        <?= $val['goodsNm']; ?>
                                                    </div>
                                                <?php } ?>
                                                <div class="info">
                                                    <?php

                                                    // 옵션 처리
                                                    if (empty($val['optionInfo']) === false) {
                                                        foreach ($val['optionInfo'] as $oKey => $oVal) {
                                                            echo '<dl class="dl-horizontal" title="' . __('옵션명') . '">';
                                                            echo '<dt class="display-inline-block">' . $oVal['optionName'] . ' :</dt>';
                                                            echo '<dd class="display-inline-block">' . $oVal['optionValue'] . '</dd>';
                                                            echo '</dl>';
                                                        }
                                                    }

                                                    // 텍스트 옵션 처리
                                                    if (empty($val['optionTextInfo']) === false) {
                                                        foreach ($val['optionTextInfo'] as $oKey => $oVal) {
                                                            echo '<ul class="list-unstyled" title="' . __('텍스트 옵션명') . '">';
                                                            echo '<li>' . $oVal['optionName'] . ' :</li>';
                                                            echo '<li>' . $oVal['optionValue'] . ' ';
                                                            if ($oVal['optionTextPrice'] > 0) {
                                                                echo '<span>(' . __('추가금') . ' ' . gd_global_order_currency_display($oVal['optionTextPrice'], $data['exchangeRate'], $data['currencyPolicy']) . ')</span>';
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

                                    <?php if($gridKey === 'goodsCnt'){ //수량 ?>
                                        <td class="text-center"><?= number_format($val['goodsCnt']); ?></td>
                                    <?php } //수량 ?>

                                    <?php if($gridKey === 'goodsPrice'){ // 상품금액 ?>
                                        <td class="text-right">
                                            <?php if ($isUseMall == true) { ?>
                                                <?= gd_global_order_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                            <?php } else { ?>
                                                <?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']); ?>
                                            <?php } ?>
                                        </td>
                                    <?php } // 상품금액 ?>

                                    <?php if($gridKey === 'totalGoodsPrice' && $rowAll === 0){ // 총 상품금액 ?>
                                        <td class="text-center" <?=$orderGoodsRowSpan?>>
                                            <?php if ($isUseMall == true) { ?>
                                                <?= gd_global_order_currency_display($data['totalGoodsPrice'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                            <?php } else { ?>
                                                <?= gd_currency_display($data['totalGoodsPrice']); ?>
                                            <?php } ?>
                                        </td>
                                    <?php } // 총 상품금액 ?>

                                    <?php if($gridKey === 'costPrice'){ // 매입가 ?>
                                        <td><?=gd_global_order_currency_display($val['costPrice'], $data['exchangeRate'], $data['currencyPolicy'])?></td>
                                    <?php } // 매입가 ?>

                                    <?php if($gridKey === 'totalDcPrice' && $rowAll == 0){ // 총 할인금액 ?>
                                        <td <?=$orderGoodsRowSpan?>>
                                            <?=gd_global_order_currency_display($data['totalSalePrice'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        </td>
                                    <?php } // 총 할인금액 ?>

                                    <?php if($gridKey === 'totalUseAddedPrice' && $rowAll === 0){ // 총 부가결제금액 ?>
                                        <td <?=$orderGoodsRowSpan?>>
                                            <?=gd_global_order_currency_display($data['totalUseAddedPrice'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        </td>
                                    <?php } // 총 부가결제금액 ?>

                                    <?php if($gridKey === 'deliveryCharge' && $rowDelivery === 0){ // 배송비 ?>
                                        <td <?= $orderDeliveryRowSpan; ?>>
                                            <?php if ($isUseMall == true) { ?>
                                                <?= gd_global_order_currency_display($val['deliveryCharge'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                            <?php } else { ?>
                                                <?= gd_currency_display($val['deliveryCharge']); ?>
                                            <?php } ?>
                                        </td>
                                    <?php } // 배송비 ?>

                                    <?php if($gridKey === 'totalOrderPrice' && $rowAll === 0){ // 총 주문금액 ?>
                                        <td <?= $orderGoodsRowSpan ?>>
                                            <?=gd_global_order_currency_display($data['totalOrderPrice'], $data['exchangeRate'], $data['currencyPolicy'])?>
                                        </td>
                                    <?php } // 총 주문금액 ?>

                                    <?php if($gridKey === 'purchaseNm'){ //- 매입처 ?>
                                        <td>
                                            <?= (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && $val['purchaseNm']) ? $val['purchaseNm'] : '-' ?>
                                        </td>
                                    <?php } //- 매입처 ?>

                                    <?php if($gridKey === 'brandNm'){ // 브랜드 ?>
                                        <td><?=$brandData[$val['brandCd']]?></td>
                                    <?php } // 브랜드 ?>

                                    <?php if($gridKey === 'goodsModelNo'){ // 모델명 ?>
                                        <td><?=$val['goodsModelNo']?></td>
                                    <?php } // 모델명 ?>

                                    <?php if($gridKey === 'makerNm'){ // 제조사 ?>
                                        <td><?=$val['makerNm']?></td>
                                    <?php } // 제조사 ?>

                                    <?php if($gridKey === 'scmNm' && $rowScm === 0){ // 공급사 ?>
                                        <td <?=$orderScmRowSpan?> class="text-center"><?= $val['companyNm']; ?></td>
                                    <?php } // 공급사 ?>

                                    <?php if($gridKey === 'commission'){ //수수료율 ?>
                                        <td><?=$val['commission']?>%</td>
                                    <?php } //수수료율 ?>

                                    <?php if($gridKey === 'hscode'){ // HS코드 ?>
                                        <td>
                                            <?php
                                            $hscode = '';
                                            if($val['hscode']){
                                                echo $val['hscode'];
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
                                            <div><?= $val['orderStatusStr']; ?></div>
                                            <div>
                                                <?php
                                                if ($val['orderStatus'] == 'd1') {
                                                    echo gd_date_format('m-d H:i', gd_isset($val['deliveryDt']));
                                                }
                                                else if ($val['orderStatus'] == 'd3') {
                                                    echo gd_date_format('m-d H:i', gd_isset($val['finishDt']));
                                                }
                                                ?>
                                            </div>
                                        </td>
                                    <?php } // 처리상태 ?>

                                    <?php if($gridKey === 'invoiceNo'){ // 송장번호 ?>
                                        <td>
                                            <?php
                                            if (empty($val['invoiceCompanySno']) === false) {
                                                echo $deliveryCom[$val['invoiceCompanySno']] . '<br/>' . $val['invoiceNo'];
                                            }
                                            ?>
                                        </td>
                                    <?php } // 송장번호 ?>

                                    <?php if($gridKey === 'goodsNo'){ // 상품코드 ?>
                                        <td><?= $val['goodsNo'] ?></td>
                                    <?php } // 상품코드 ?>

                                    <?php if($gridKey === 'receiverAddress'){ // 수령자 주소 ?>
                                        <?php if($data['useMultiShippingKey']){ ?>
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
                                        <?php if($data['useMultiShippingKey']){ ?>
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
                                    <!-- 배송지 -->

                                    <?php if($gridKey === 'totalDeliveryCharge' && $rowAll === 0){ // 총 배송비 ?>
                                        <td <?= $orderGoodsRowSpan; ?>>
                                            <?php if ($isUseMall == true) { ?>
                                                <?= gd_global_order_currency_display($data['totalDeliveryCharge'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                            <?php } else { ?>
                                                <?= gd_currency_display($data['totalDeliveryCharge']); ?>
                                            <?php } ?>
                                        </td>
                                    <?php } // 총 배송비 ?>


                                    <?php
                                }
                            }
                            //주문상세 그리드 항목 끝
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
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="<?=count($data['orderGridConfigList'])?>" class="text-right">
                <strong><?= __('총 결제 금액'); ?></strong> :
                <?=gd_global_order_currency_display($data['totalGoodsPrice'], $data['exchangeRate'], $data['currencyPolicy'])?> +
                <?=gd_global_order_currency_display($data['totalDeliveryCharge'], $data['exchangeRate'], $data['currencyPolicy'])?><?= '(' . __('배송비') .')'; ?>

                <?php if($isProvider){ ?>
                    =
                    <strong><?=gd_global_order_currency_display($data['totalOrderPrice'], $data['exchangeRate'], $data['currencyPolicy'])?></strong>
                <?php } else { ?>
                    <?php if(($data['totalSalePrintPreviewPrice'] + $data['useMileage'] + $data['useDeposit']) < 0){ ?>
                        +
                    <?php } else { ?>
                        -
                    <?php } ?>

                    <?=gd_global_order_currency_display(abs($data['totalSalePrintPreviewPrice'] + $data['useMileage'] + $data['useDeposit']), $data['exchangeRate'], $data['currencyPolicy'])?>(<?= __('할인'); ?>) =
                    <strong><?=gd_global_order_currency_display($data['totalRealSettlePrice'], $data['exchangeRate'], $data['currencyPolicy'])?></strong>
                <?php } ?>
            </td>
        </tr>
        </tfoot>
    </table>

    <?php
    if (empty($data['gift']) === false) {
    ?>
        <div class="table-title gd-help-manual">
            <?= __('사은품 정보'); ?>
        </div>
        <table class="table table-rows">
            <thead>
            <tr>
                <th class="width30p"><?= __('사은품 지급조건명'); ?></th>
                <th class="width10p"><?= __('이미지'); ?></th>
                <th class="width30p"><?= __('사은품명'); ?></th>
                <th class="width10p"><?= __('수량'); ?></th>
                <th class="width30p"><?= __('사은품 설명'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($data['gift'] as $key => $val) {
                ?>
                <tr class="text-center">
                    <td><?= $val['presentTitle']; ?></td>
                    <td><?= html_entity_decode($val['imageUrl']); ?></td>
                    <td><?= $val['giftNm']; ?></td>
                    <td><?= $val['giveCnt']; ?></td>
                    <td><?= strip_tags(html_entity_decode($val['giftDescription'])); ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
    }
    ?>

    <?php if ($data['orderPrint']['orderPrintOdCsSettleInfoDisplay'] === 'y') { ?>
    <div class="row">
        <div class="col-xs-6">
            <div class="table-title gd-help-manual">
                <?= __('결제정보'); ?>
            </div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th><?= __('상품 금액'); ?></th>
                    <td class="input_area right">
                        <span class="font-num">
                            <?php echo gd_currency_display(gd_isset($data['totalGoodsPrice'])); ?>
                           <?php if (empty($data['isDefaultMall']) === true) { ?>
                               (<?= gd_global_order_currency_display(gd_isset($data['totalGoodsPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                           <?php } ?>
                        </span>
                    </td>
                </tr>
                <?php if(!$isProvider){ ?>
                <tr>
                    <th><?= __('할인금액'); ?></th>
                    <?php if(($data['totalSalePrintPreviewPrice'] + $data['useMileage'] + $data['useDeposit']) < 0){ ?>
                    <td class="input_area right text-primary">
                    (+)
                    <?php } else { ?>
                    <td class="input_area right text-danger">
                    (-)
                    <?php } ?>
                        <span class="font-num">
                            <?php echo gd_currency_display(abs($data['totalSalePrintPreviewPrice'] + $data['useMileage'] + $data['useDeposit'])); ?>
                            <?php if (empty($data['isDefaultMall']) === true) { ?>
                                (<?= gd_global_order_currency_display(($data['totalSalePrintPreviewPrice'] + $data['useMileage'] + $data['useDeposit']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            <?php } ?>
                        </span>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <th><?= __('배송비'); ?></th>
                    <td class="input_area right">
                        <div class="font-num text-primary">
                            (+) <?php echo gd_currency_display(gd_isset($data['totalDeliveryCharge'])); ?>
                            <?php if (empty($data['isDefaultMall']) === true) { ?>
                                (<?= gd_global_order_currency_display(($data['totalDeliveryCharge']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <?php if (empty($data['isDefaultMall']) === true && $data['totalDeliveryInsuranceFee'] > 0) { ?>
                    <tr>
                        <th><?= __('해외배송 보험료'); ?></th>
                        <td class="text-right">
                            <div class="text-primary">
                                (+) <?= gd_currency_display(gd_isset($data['totalDeliveryInsuranceFee'])); ?>
                                (<?= gd_global_order_currency_display(gd_isset($data['totalDeliveryInsuranceFee']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th><?= __('총 결제 금액'); ?></th>
                    <td class="input_area right">
                         <span class="number_emphasis">
                             <?php if($isProvider){ ?>
                                 <?php echo gd_currency_display(gd_isset($data['totalOrderPrice'])); ?>
                                 <?php if (empty($data['isDefaultMall']) === true) { ?>
                                     (<?= gd_global_order_currency_display(($data['totalOrderPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                                 <?php } ?>
                             <?php } else { ?>
                                 <?php echo gd_currency_display(gd_isset($data['totalRealSettlePrice'])); ?>
                                 <?php if (empty($data['isDefaultMall']) === true) { ?>
                                     (<?= gd_global_order_currency_display(($data['totalRealSettlePrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>)
                                 <?php } ?>
                             <?php } ?>
                        </span>
                    </td>
                </tr>
                <?php if (empty($data['isDefaultMall']) === true && substr($data['settleKind'], 0, 1) == 'o') { ?>
                    <tr>
                        <th><?= __('승인금액'); ?></th>
                        <td class="text-right">
                            <strong><?=$data['overseasSettleCurrency']?> <?= gd_isset($data['overseasSettlePrice']); ?></strong>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <th><?= __('총 적립 금액'); ?></th>
                    <td class="input_area right">
                        <span class="number_emphasis"><?php echo number_format(gd_isset($data['totalMileage'])); ?><?php echo $mileageUse['unit']?></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="col-xs-6">
            <div class="table-title gd-help-manual">
                <?= __('결제수단'); ?>
            </div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th><?= __('결제방법'); ?></th>
                    <td>
                        <span class="text_emphasis"><?php if (gd_isset($data['settle']['escrow']) == 'e') { ?><?= __('에스크로'); ?> <?php } ?><?php echo gd_isset($data['settle']['name']); ?></span>
                    </td>
                </tr>
                <?php if (gd_isset($data['settleKind']) == 'gb') { ?>
                    <tr>
                        <th><?= __('입금계좌'); ?></th>
                        <td><?php echo str_replace(STR_DIVISION, ' / ', gd_isset($data['bankAccount'])); ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= __('입금자명'); ?></th>
                        <td><span id="bankSender"><?php echo gd_isset($data['bankSender']); ?></span></td>
                    </tr>
                <?php } else { ?>
                    <?php if (gd_isset($data['settle']['method']) == 'c') { ?>
                        <tr>
                            <th><?= __('카드사명'); ?></th>
                            <td><?php echo gd_isset($data['pgSettleNm'][0]); ?></td>
                        </tr>
                        <?php if (gd_isset($data['pgSettleCd'][0]) != '' && gd_isset($data['pgSettleCd'][0]) != '0' && gd_isset($data['pgSettleCd'][0]) != '00') { ?>
                            <tr>
                                <th><?= __('할부개월'); ?></th>
                                <td><?php if (gd_isset($data['pgSettleCd'][1]) == '1') { ?>무이자 <?php } ?><?php echo gd_isset($data['pgSettleCd'][0]); ?><?= __('개월'); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else if (gd_isset($data['settle']['method']) == 'c') { ?>
                        <tr>
                            <th><?= __('이체은행'); ?></th>
                            <td><?php echo gd_isset($data['pgSettleNm'][0]); ?></td>
                        </tr>
                    <?php } else if (gd_isset($data['settle']['method']) == 'v') { ?>
                        <tr>
                            <th><?= __('입금계좌'); ?></th>
                            <td><?php echo gd_isset($data['pgSettleNm'][0]); ?> / <?php echo gd_isset($data['pgSettleNm'][1]); ?> / <?php echo gd_isset($data['pgSettleNm'][2]); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('입금기한'); ?></th>
                            <td><?php echo gd_isset($data['pgSettleCd'][0]); ?></td>
                        </tr>
                    <?php } else if (gd_isset($data['settle']['method']) == 'h') { ?>
                        <tr>
                            <th><?= __('통신사'); ?></th>
                            <td><?php echo gd_isset($data['pgSettleNm'][0]); ?></td>
                        </tr>
                        <?php if (empty($data['pgSettleCd'][0]) === false) { ?>
                            <tr>
                                <th><?= __('휴대폰번호'); ?></th>
                                <td><?php echo gd_isset($data['pgSettleCd'][0]); ?></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <th><?= __('결제확인일'); ?></th>
                    <td><?php echo gd_isset($data['paymentDt']); ?></td>
                </tr>
                <?php if (empty($data['isDefaultMall']) === false) { ?>
                <tr>
                    <th><?= __('영수증 신청여부'); ?></th>
                    <td>
                        <?php
                        if (gd_isset($data['receiptFl']) == 'n') {
                            echo __('미신청');
                            // 현금영수증인 경우
                        } else if (gd_isset($data['receiptFl']) == 'r') {
                            echo __('현금영수증 신청');
                            // 세금계산서인 경우
                        } else if (gd_isset($data['receiptFl']) == 't') {
                            echo __('세금계산서 신청');
                        }
                        ?>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>

    <?php
    if(count($data['multiShippingList']) > 0) {
    ?>
        <div class="row">
        <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->
        <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->
        <?php
        if(count($data['multiShippingList']) > 0) {
            foreach($data['multiShippingList'] as $key => $multiShippingData){
                ?>
                    <div class="col-xs-6">
                        <div class="table-title gd-help-manual">
                            <?= __('배송정보'); ?>
                            <?php
                            if((int)$multiShippingData['orderInfoCd'] === 1){
                                echo __('메인 배송지');
                            }
                            else {
                                echo __('추가 배송지') . ((int)$multiShippingData['orderInfoCd'] - 1);
                            }
                            ?>
                        </div>
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-md"/>
                                <col/>
                                <col class="width-md"/>
                                <col/>
                            </colgroup>
                            <tbody>
                            <?php if ($multiShippingData['deliveryVisit'] != 'y') { ?>
                            <tr>
                                <th><?= __('수령자'); ?></th>
                                <td colspan="3"><?php echo gd_isset($multiShippingData['receiverName']); ?></td>
                            </tr>
                            <tr>
                                <th><?= __('연락처'); ?></th>
                                <td colspan="3">
                                    <?php echo gd_isset($multiShippingData['receiverPhone']); ?> / <?php echo gd_isset($multiShippingData['receiverCellPhone']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= __('송장번호'); ?></th>
                                <td colspan="3"><?php echo gd_isset($data['invoiceNo']); ?></td>
                            </tr>
                            <tr>
                                <th>
                                    <?= __('배송일'); ?>
                                </th>
                                <td colspan="3">
                                    <?php echo gd_isset($multiShippingData['deliveryDt']); ?> <br>
                                    <?php echo gd_isset($multiShippingData['deliveryCompleteDt']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?= __('배송지'); ?></th>
                                <td colspan="3">
                                        <div><?php echo gd_isset($multiShippingData['receiverAddress']); ?> <?php echo gd_isset($multiShippingData['receiverAddressSub']); ?></div>
                                </td>
                            </tr>
                            <tr>
                                <th><?= __('배송메시지'); ?></th>
                                <td colspan="3"><?php echo gd_isset($multiShippingData['orderMemo']); ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($multiShippingData['deliveryVisit'] == 'a' || $multiShippingData['deliveryVisit'] == 'y') { ?>
                            <tr>
                                <th><?= __('방문수령'); ?></th>
                                <td>
                                    <table class="table table-cols">
                                        <colgroup>
                                            <col class="width-md"/>
                                            <col/>
                                        </colgroup>
                                        <tr>
                                            <th><?= __('방문 수령지 주소'); ?></th>
                                            <td><?php echo $data['visitDeliveryInfo']['address'][$multiShippingData['sno']][0] . (count($data['visitDeliveryInfo']['address'][$multiShippingData['sno']]) > 1 ? '외 ' . (count($data['visitDeliveryInfo']['address'][$multiShippingData['sno']]) - 1) . '건 ' : ''); ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('방문자 정보'); ?></th>
                                            <td><?php echo $multiShippingData['visitName'] . ' / ' . $multiShippingData['visitPhone']; ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('메모'); ?></th>
                                            <td><?php echo $multiShippingData['visitMemo']; ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php
            }
        }
        ?>
        <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->
        <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->
        </div>
        <div class="clear-both"></div>
    <?php
    } else {
    ?>
        <div class="table-title gd-help-manual">
            <?= __('배송정보'); ?>
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <?php if ($data['deliveryVisit'] != 'y') { ?>
            <tr>
                <th><?= __('수령자'); ?></th>
                <td><?php echo gd_isset($data['receiverName']); ?></td>
                <th><?= __('연락처'); ?></th>
                <td>
                    <?php if (empty($data['isDefaultMall']) === true) { ?>
                        (+<?php echo gd_isset($data['receiverPhonePrefix']); ?>)
                    <?php } ?>
                    <?php echo gd_isset($data['receiverPhone']); ?> /
                    <?php if (empty($data['isDefaultMall']) === true) { ?>
                        (+<?php echo gd_isset($data['receiverCellPhonePrefix']); ?>)
                    <?php } ?>
                    <?php echo gd_isset($data['receiverCellPhone']); ?></td>
            </tr>
            <tr>
                <th><?= __('송장번호'); ?></th>
                <td><?php echo gd_isset($data['invoiceNo']); ?></td>
                <th>
                    <?php if (empty($data['isDefaultMall']) === true) echo __('배송일'); else echo __('배송일(출고일)');?>
                </th>
                <td>
                    <?php echo gd_isset($data['deliveryDt']); ?> <br>
                    <?php echo gd_isset($data['deliveryCompleteDt']); ?>
                </td>
            </tr>
            <tr>
                <th><?= __('배송지'); ?></th>
                <td colspan="3">
                    <?php if (empty($data['isDefaultMall']) === true) { ?>
                        <div><?php echo gd_isset($data['receiverAddressSub']); ?>, <?php echo gd_isset($data['receiverAddress']); ?>, <?php echo gd_isset($data['receiverState']); ?>, <?php echo gd_isset($data['receiverCity']); ?>, <?php echo gd_isset($data['receiverCountry']); ?></div>
                    <?php } else { ?>
                        <div><?php echo gd_isset($data['receiverAddress']); ?> <?php echo gd_isset($data['receiverAddressSub']); ?></div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th><?= __('배송메시지'); ?></th>
                <td colspan="3"><?php echo gd_isset($data['orderMemo']); ?></td>
            </tr>
            <?php } ?>
            <?php if ($data['deliveryVisit'] == 'a' || $data['deliveryVisit'] == 'y') { ?>
                <tr>
                    <th><?= __('방문수령'); ?></th>
                    <td>
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-md"/>
                                <col/>
                            </colgroup>
                            <tr>
                                <th><?= __('방문 수령지 주소'); ?></th>
                                <td><?php echo $data['visitDeliveryInfo']['address'][$data['infoSno']][0] . (count($data['visitDeliveryInfo']['address'][$data['infoSno']]) > 1 ? '외 ' . (count($data['visitDeliveryInfo']['address'][$data['infoSno']]) - 1) . '건 ' : ''); ?></td>
                            </tr>
                            <tr>
                                <th><?= __('방문자 정보'); ?></th>
                                <td><?php echo $data['visitName'] . ' / ' . $data['visitPhone']; ?></td>
                            </tr>
                            <tr>
                                <th><?= __('메모'); ?></th>
                                <td><?php echo $data['visitMemo']; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div class="display-inline clear-both"></div>
    <?php
    }
    ?>

    <?php if (empty($data['addFieldData']) === false) { ?>
        <div class="table-title gd-help-manual">
            <?= __('추가 정보'); ?>
        </div>
        <table class="table table-cols">
            <tbody>
            <?php
            foreach ($data['addFieldData'] as $addFieldKey => $addFieldVal) {
                if ($addFieldVal['process'] == 'goods') {
                    foreach ($addFieldVal['data'] as $addDataKey => $addDataVal) {
                        ?>
                        <tr>
                            <th><?= $addFieldVal['name']; ?> : <?= $addFieldVal['goodsNm'][$addDataKey]; ?></th>
                        </tr>
                        <tr>
                            <td><?= $addDataVal; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <th><?= $addFieldVal['name']; ?></th>
                    </tr>
                    <tr>
                        <td><?= $addFieldVal['data']; ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <div class="display-inline clear-both"></div>
    <?php } ?>

    <div class="clear-both"></div>

    <!-- 관리자 메모 -->
    <?php if($data['orderPrint']['orderPrintOdCsAdminMemoDisplay'] === 'y'){ ?>
        <div class="row">
            <div class="col-xs-12">
                <div class="table-title gd-help-manual">
                    관리자 메모
                </div>
                <table class="table table-rows mgb5">
                    <colgroup>
                        <col />
                    </colgroup>
                    <thead>
                    <tr>
                        <th>작성내용</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($data['orderPrint']['orderPrintOdOrdGoodsMemoInfo'] as $key => $value){
                        ?>
                        <tr>
                            <td class="text-left">
                                <?php echo "<div>".nl2br($value)."</div>";?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="clear-both"></div>
    <?php } ?>
    <!-- 관리자 메모-->

    <!-- 하단 추가 정보 표시 -->
    <?php if($data['orderPrint']['orderPrintOdCsBottomInfo'] === 'y' && trim($data['orderPrint']['orderPrintOdCsBottomInfoText']) !== ''){ ?>
        <div class="row">
            <div class="col-xs-12">
                <div style="padding: 3px;"><?=nl2br($data['orderPrint']['orderPrintOdCsBottomInfoText'])?></div>
            </div>
        </div>
    <?php } ?>
    <!-- 하단 추가 정보 표시 -->

    <?php
    if ($printCnt != count($orderData)) {
        echo '<hr class="hidden-print" style="margin:20px 0px 20px 0px;  border-top:dashed 1px #000000;" />';
    }?>
</div>
<?php
}
?>
