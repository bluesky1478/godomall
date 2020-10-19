<?php
/**
 * 상태변경 팝업 - 교환철회
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>
<table class="table table-rows js-exchange-cancel-table">
    <thead>
    <tr>
        <th>상품<br />주문번호</th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>수량</th>
        <th>상품금액</th>
        <th>총 상품금액</th>
        <th>배송비</th>
        <th>처리상태</th>
    </tr>
    </thead>

    <tbody>
    <?php
    if (isset($data['goods']) === true) {
        $rowAll = 0;
        foreach ($data['goods'] as $sKey => $sVal) {
            $rowCnt = $data['cnt']['goods']['all']; // 한 주문당 상품주문 수량
            $rowChk = 0; // 한 주문당 첫번째 주문 체크용
            $rowScm = 0;
            foreach ($sVal as $dKey => $dVal) {
                $rowDelivery = 0;
                foreach ($dVal as $key => $val) {

                    // rowspan 처리
                    $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                    $orderScmRowSpan = ' rowspan="' . ($data['cnt']['scm'][$sKey]) . '"';
                    $deliveryKeyCheck = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                    if ($deliveryKeyCheck !== $deliveryUniqueKey) {
                        $rowDelivery = 0;
                    }
                    $deliveryUniqueKey = $deliveryKeyCheck;
                    $orderDeliveryRowSpan = ' rowspan="' . $data['cnt']['delivery'][$deliveryUniqueKey] . '"';

                    $goodsPrice = $val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'];
                    $goodsSellFl = ($val['goodsSellFl'] === 'y' || $val['goodsSellMobileFl'] === 'y') ? '판매함' : '판매안함';
                    ?>
                    <tr data-sno="<?=$val['sno']?>" data-stock-cnt="<?=$val['stockCnt']?>" data-goods-price="<?=$goodsPrice?>" data-goods-sellFl="<?=$goodsSellFl?>">
                        <!-- 상품주문번호 -->
                        <td class="center"><?=$val['sno']?></td>
                        <!-- 상품주문번호 -->

                        <!-- 이미지 -->
                        <td class="js-exchange-area-image">
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } else { ?>
                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } ?>
                        </td>
                        <!-- 이미지 -->

                        <!-- 주문상품 -->
                        <td class="js-exchange-area-goodsNm">
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                   onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                                    <?=gd_html_cut($val['goodsNmStandard'] && $isUseMall === false ? $val['goodsNmStandard'] :  $val['goodsNm'], 46, '..') ?></a>
                            <?php } else { ?>
                                <?php if($val['timeSaleFl'] =='y') { ?>
                                    <img src='<?=PATH_ADMIN_GD_SHARE?>img/time-sale.png' alt='타임세일' />
                                <?php } ?>
                                <a href="javascript:void()" class="one-line" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>, '');">
                                    <?=$val['goodsNmStandard'] && $isUseMall === false ? gd_html_cut($val['goodsNmStandard'], 46, '..') :  gd_html_cut($val['goodsNm'], 46, '..') ?></a>
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
                        </td>
                        <!-- 주문상품 -->

                        <!-- 수량 -->
                        <td class="text-center">
                            <strong><?= number_format($val['goodsCnt']); ?></strong>
                            <?php if (isset($val['stockCnt']) === true) { ?>
                                <div title="재고">재고: <?= $val['stockCnt']; ?></div>
                            <?php } ?>
                        </td>
                        <!-- 수량 -->

                        <!-- 상품금액 -->
                        <td class="text-right">
                            <?php if ($isUseMall == true) { ?>
                                <?= gd_global_order_currency_display($goodsPrice, $data['exchangeRate'], $data['currencyPolicy']); ?>
                            <?php } else { ?>
                                <?= gd_currency_display($goodsPrice); ?>
                            <?php } ?>
                        </td>
                        <!-- 상품금액 -->

                        <!-- 총상품금액 -->
                        <td class="text-right">
                            <?php if ($isUseMall == true) { ?>
                                <?= gd_global_order_currency_display($goodsPrice * $val['goodsCnt'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                            <?php } else { ?>
                                <?= gd_currency_display($goodsPrice * $val['goodsCnt']); ?>
                            <?php } ?>
                        </td>
                        <!-- 총상품금액 -->

                        <!-- 배송비 -->
                        <?php if ($rowDelivery === 0) { ?>
                            <td <?= $orderDeliveryRowSpan; ?> class="text-center">
                                <?php if ($isUseMall == true) { ?>
                                    <?= gd_global_order_currency_display($val['deliveryCharge'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                <?php } else { ?>
                                    <?= gd_currency_display($val['deliveryCharge']); ?>
                                <?php } ?>
                                <br />
                                <?=$val['goodsDeliveryCollectFl'] == 'pre' ? '(선불)' : '(착불)';?>

                                <?php if (empty($data['isDefaultMall']) === true) { ?>
                                    <br>(총무게 : <?=$data['totalDeliveryWeight']?>kg)
                                <?php } ?>
                            </td>
                        <?php } ?>
                        <!-- 배송비 -->

                        <!-- 처리상태 -->
                        <td class="center">
                            <?php if ($val['beforeStatusStr'] && $statusMode == 'r') { ?>
                                <div class="text-muted" title="이전 상품별 주문 상태"><?= $val['beforeStatusStr']; ?> &gt;</div>
                            <?php } ?>

                            <p><?= $val['orderStatusStr']; ?></p>

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
                        <!-- 처리상태 -->
                    </tr>
                    <?php
                    $rowChk++;
                    $rowDelivery++;
                    $rowScm++;
                    $rowAll++;
                }
            }
        }
    } else {
        ?>
        <tr>
            <td class="no-data" colspan="8">교환철회 할 상품이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<form name="formOrderViewExchangeCancel" id="formOrderViewExchangeCancel" action="../order/order_change_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="" />
    <input type="hidden" name="orderNo" value="<?=$data['orderNo']?>" />
    <input type="hidden" name="orderGoodsSno" value="" />

    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="button" class="btn btn-lg btn-black js-layer-confirm">확인</button>
    </div>
</form>

<script>
    $(document).ready(function(){
        $(".js-layer-confirm").click(function(){
            $confirmMessage = "최초 교환 이전으로 복원됩니다.<br />정말 교환을 철회 하시겠습니까?";
            dialog_confirm($confirmMessage, function (result) {
                if (result) {
                    var orderGoodsSnoArr = [];
                    $(".js-exchange-cancel-table>tbody>tr").each(function(){
                        if($(this).attr('data-sno')){
                            orderGoodsSnoArr.push($(this).attr('data-sno'));
                        }
                    });
                    var orderGoodsSnoStr = orderGoodsSnoArr.join('<?=INT_DIVISION?>');

                    $("input[name='mode']").val('exchangeCancel');
                    $("input[name='orderGoodsSno']").val(orderGoodsSnoStr);
                    $("#formOrderViewExchangeCancel").submit();
                }
            });
        });
        $(".js-layer-close").click(function(){
            self.close();
        });
    });
</script>
