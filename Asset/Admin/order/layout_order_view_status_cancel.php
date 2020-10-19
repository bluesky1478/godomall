<?php
/**
 * 상태변경 팝업 - 주문취소
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>
<table class="table table-rows">
    <thead>
    <tr>
        <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]'/></th>
        <th>상품<br/>주문번호</th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>수량</th>
        <th>취소수량</th>
        <th>상품금액</th>
        <th>총 상품금액</th>
        <?php if($useMultiShippingKey === true){ ?>
            <th>배송지</th>
        <?php } ?>
        <th>배송비</th>
        <th>처리상태</th>
    </tr>
    </thead>

    <tbody>
    <?php
    // 주문 처리가 주문기준으로 되어야 할 주문 단계의 경우 체크가 동일 처리되게
    if (isset($data['goods']) === true) {
        $sortNo = $data['cnt']['goods']['goods'];// 번호 설정
        $rowAll = 0;
        foreach ($data['goods'] as $sKey => $sVal) {
            $rowCnt = $data['cnt']['goods']['all']; // 한 주문당 상품주문 수량
            $rowChk = 0; // 한 주문당 첫번째 주문 체크용
            $rowScm = 0;
            $rowMultiShipping = 0;
            foreach ($sVal as $dKey => $dVal) {
                $rowDelivery = 0;
                foreach ($dVal as $key => $val) {
                    // 주문상태 모드
                    $statusMode = substr($val['orderStatus'], 0, 1);

                    // rowspan 처리
                    $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scm no 를 order info sno 로 대체, dKey는 order delivery sno로 대체
                    if($useMultiShippingKey === true){
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

                    // 결제정보에 사용할 데이터 만들기
                    if ($val['goodsDcPrice'] > 0) {
                        $goodsDcPrice[$val['sno']] = $val['goodsDcPrice'];
                    }

                    //배송업체가 설정되어 있지 않을시 기본 배송업체 select
                    $selectInvoiceCompanySno = $val['invoiceCompanySno'];
                    if ((int)$selectInvoiceCompanySno < 1) {
                        $selectInvoiceCompanySno = $deliverySno;
                    }
                    $totalMemberDeliveryDcPrice = 0;
                    if ($val['totalMemberDeliveryDcPrice'] > 0) {
                        $totalMemberDeliveryDcPrice = $val['deliveryPolicyCharge'];
                    }
                    $divisionDeliveryCharge = $val['divisionDeliveryCharge'];
                    $isUseMall = false;
                    if($val['mallSno'] > DEFAULT_MALL_NUMBER){
                        //$isUseMall = true;
                    }
                    ?>
                    <tr id="statusCheck_<?= $statusMode; ?>_<?= $val['sno']; ?>" class="text-center">
                        <td class="center">
                            <div class="display-block">
                                <input type="checkbox" name="bundle[statusCheck][<?= $val['sno']; ?>]" value="<?= $val['sno']; ?>" class="<?= gd_isset($onclickAction); ?>"/>
                                <input type="hidden" name="bundle[statusMode][<?= $val['sno']; ?>]" value="<?= $val['orderStatus']; ?>"/>
                                <input type="hidden" name="bundle[goods][sno][<?= $val['sno']; ?>]" value="<?= $val['sno']; ?>"/>
                            </div>
                        </td>

                        <!-- 상품주문번호 -->
                        <td>
                            <?= $val['sno'] ?>
                            <?php if ($data['orderChannelFl'] == 'naverpay') { ?>
                                <p class="mgt5"><img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderGoodsNo']; ?></p>
                            <?php } ?>
                        </td>
                        <!-- 상품주문번호 -->

                        <!-- 이미지 -->
                        <td>
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } else { ?>
                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } ?>
                        </td>
                        <!-- 이미지 -->

                        <!-- 주문상품 -->
                        <td class="text-left">
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                   onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                                    <?= gd_html_cut($val['goodsNmStandard'] && $isUseMall === false ? $val['goodsNmStandard'] : $val['goodsNm'], 46, '..') ?></a>
                            <?php } else { ?>
                                <?php if ($val['timeSaleFl'] == 'y') { ?>
                                    <img src='<?= PATH_ADMIN_GD_SHARE ?>img/time-sale.png' alt='타임세일'/>
                                <?php } ?>
                                <a href="javascript:void()" class="one-line" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                                    <?=$val['goodsNmStandard'] && $isUseMall === false ? gd_html_cut($val['goodsNmStandard'], 36, '..') :  gd_html_cut($val['goodsNm'], 36, '..') ?></a>
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
                            </div>
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

                        <!-- 취소수량 -->
                        <td class="text-center">
                            <select name="cancelCnt[<?= $val['sno']; ?>]">
                                <?php for ($i = $val['goodsCnt']; $i >= 1; $i--) { ?>
                                    <option value="<?= $i; ?>"><?= number_format($i); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <!-- 취소수량 -->

                        <!-- 상품금액 -->
                        <td class="text-right">
                            <?php if ($isUseMall == true) { ?>
                                <?= gd_global_order_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']), $data['exchangeRate'], $data['currencyPolicy']); ?>
                            <?php } else { ?>
                                <?= gd_currency_display(($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])); ?>
                            <?php } ?>
                        </td>
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

                        <!-- 배송지 -->
                        <?php if($useMultiShippingKey === true){ ?>
                            <?php if($rowMultiShipping === 0){ ?>
                                <td class="text-center" <?= $orderMultiShippingRowSpan ?>>
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
                        <?php } ?>
                        <!-- 배송지 -->

                        <!-- 배송비 -->
                        <?php if ($rowDelivery === 0) { ?>
                            <td <?= $orderDeliveryRowSpan; ?>>
                                <?php if ($isUseMall == true) { ?>
                                    <?= gd_global_order_currency_display($val['deliveryCharge'], $data['exchangeRate'], $data['currencyPolicy']); ?>
                                <?php } else { ?>
                                    <?= gd_currency_display($val['deliveryCharge']); ?>
                                <?php } ?>
                                <br/>
                                <?= $val['goodsDeliveryCollectFl'] == 'pre' ? '(선불)' : '(착불)'; ?>

                                <?php if (empty($data['isDefaultMall']) === true) { ?>
                                    <br>(총무게 : <?= $data['totalDeliveryWeight'] ?>kg)
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
                            <?php if ($val['naverpayStatus']['code'] == 'DelayProductOrder') {    //발송지연?>
                                <div style="padding-bottom:5px" data-sno="<?= $val['sno'] ?>" data-info="<?= $val['naverpayStatus']['text'] ?>" class="js-btn-naverpay-status-detail">
                                    (<?= $val['naverpayStatus']['notice'] ?>)
                                </div>
                            <?php } ?>

                            <div><?php if ($val['orderStatus'] == 'd1') {
                                    echo gd_date_format('m-d H:i', gd_isset($val['deliveryDt']));
                                } else if ($val['orderStatus'] == 'd3') {
                                    echo gd_date_format('m-d H:i', gd_isset($val['finishDt']));
                                } ?></div>
                            <div>
                            </div>
                            <?php if (empty($val['invoiceCompanySno']) === false && empty($val['invoiceNo']) === false && (!$val['deliveryMethodFl'] || $val['deliveryMethodFl'] === 'delivery')) { ?>
                                <div>
                                    <input type="button" onclick="delivery_trace('<?= $val['invoiceCompanySno']; ?>', '<?= $val['invoiceNo']; ?>');" value="배송추적" class="btn btn-sm btn-gray mgt5"/>
                                </div>
                            <?php } ?>
                        </td>
                        <!-- 처리상태 -->
                    </tr>
                    <?php
                    $sortNo--;
                    $rowChk++;
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
            <td class="no-data" colspan="<?= count($orderGridConfigList) ?>"><?= $incTitle ?>이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="table-action">
    <div class="pull-right form-inline">
        <button type="button" class="btn btn-sm btn-black mgr5 js-select-goods-cancel">선택 상품 취소</button>
    </div>
</div>

<form name="cancelForm" id="cancelForm" action="../order/order_change_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="cancel"/>
    <input type="hidden" name="orderNo" value="<?= $data['orderNo']; ?>"/>
    <input type="hidden" name="orderGoodsCancelSno" value=""/>
    <input type="hidden" name="orderGoodsCancelCnt" value=""/>
    <div id="viewStatusCancelDetail" class="display-none">
        <!-- 취소 처리 -->
        <div class="table-title">
            <span class="gd-help-manual mgt30">취소 처리</span>
            <span class="mgl10 notice-info">취소 주문은 [취소/교환/반품/환불 관리 > 취소 리스트]에서 확인할 수 있습니다.</span>
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>취소처리 상태</th>
                <td>
                    <label>
                        <?= gd_select_box(null, 'cancelMsg[orderStatus]', $order->getOrderStatusList('c', null, ['c1', 'c4']), null, null, '=취소 종류 선택='); ?>
                    </label>
                </td>
                <th>취소사유</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box(null, 'cancelMsg[handleReason]', $refundReason, null, null, null); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>상세사유</th>
                <td colspan="3">
                    <textarea class="form-control" name="cancelMsg[handleDetailReason]"></textarea>
                    <div class="mgt5 mgb5">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="cancelMsg[handleDetailReasonShowFl]" value="y">상세 사유를 고객에게 노출합니다.
                        </label>
                    </div>
                </td>
            </tr>
        </table>
        <!-- 취소 처리 -->

        <!-- 결제금액 정보 -->
        <div class="table-title">
            <span class="gd-help-manual mgt30">취소 금액 설정</span>
        </div>

        <div id="cancelPriceHtml"></div>

        <div class="pull-right form-inline">1 / 2</div>

        <div class="text-center">
            <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
            <button type="button" class="btn btn-lg btn-black js-step-return">다음</button>
        </div>
    </div>

    <div id="viewStatusCancelReturn" class="display-none">
        <div id="returnHtml"></div>

        <div class="pull-right form-inline">2 / 2</div>

        <div class="text-center">
            <button type="button" class="btn btn-lg btn-white js-layer-prev">이전</button>
            <button type="submit" class="btn btn-lg btn-black">확인</button>
        </div>
    </div>
</form>

<div class="mgt30 popup-claim-info-area">
    <div class="bold">도움말</div>
    <div><strong>·</strong> 결제수단 가상계좌, 가상계좌(에스크로)의 입금전 주문취소일 경우, 전체취소만 가능합니다.</div>
    <div><strong>·</strong> 상품할인, 회원 추가할인, 회원 중복할인, 상품쿠폰 할인, 운영자 추가할인, 모바일앱 할인은 취소 상품에 적용된 할인 금액이므로 상품에 적용된 전체금액의 취소만 가능합니다.</div>
    <div><strong>·</strong> 주문쿠폰 할인, 사용 예치금, 사용 마일리지는 취소 주문에 적용된 금액이므로 부분 또는 전체금액의 취소처리가 가능합니다.</div>
    <div><strong>·</strong> 배송비는 취소 상품에 적용된 배송비 조건별로 결제된 배송비 금액의 부분 또는 전체금액의 취소처리가 가능하며, </div>
    <div class="mgl8">추가 결제가 필요한 경우 배송비 조건별 추가 배송비 설정이 가능합니다.</div>
    <div><strong>·</strong> 입금전 발급(발행)요청 상태의 현금영수증과 세금계산서는 부분취소로 결제금액이 변경되는 경우 발급(발행)금액도 자동으로 변경됩니다.</div>
    <div><strong>·</strong> 페이코, 네이버 페이 주문의 입금전 주문취소일 경우, 전체취소만 가능합니다.</div>
    <div><strong>·</strong> 네이버 페이 주문의 상세한 정보는 네이버 페이 센터에서 관리하실 것을 권장합니다. <a href="https://admin.pay.naver.com" target="_blank" style="color:#117efa !important;">[네이버 페이 센터 바로가기 ▶]</a></div>
</div>

<script>
    $(document).ready(function () {
        var settleKind = '<?= $data['settleKind']; ?>';
        $("#cancelForm").validate({
            dialog: false,
            submitHandler: function (form) {
                if (settleKind === 'pv' || settleKind === 'ev' || settleKind === 'fv') {
                    if ($('input[name="appointmentSettlePrice"]').val() != 0) {
                        alert('결제수단 가상계좌, 가상계좌(에스크로)의 입금전 주문취소일 경우, 전체취소만 가능합니다.');
                        return false;
                    }
                }

                dialog_confirm('취소 후 결제 금액은 ' + numeral($('input[name="appointmentSettlePrice"]').val()).format() + '<?= gd_currency_string(); ?> 입니다. <br/><br/>진행 하시겠습니까?', function (result) {
                    if (result) {
                        form.target = 'ifrmProcess';
                        form.submit();
                    }
                });
            },
            rules: {
                mode: {
                    required: true,
                },
                orderNo: {
                    required: true,
                },
                'cancelMsg[orderStatus]': {
                    required: true,
                },
                'cancelMsg[handleReason]': {
                    required: true,
                },
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                orderNo: {
                    required: '정상 접속이 아닙니다.(no)',
                },
                'cancelMsg[orderStatus]': {
                    required: '취소처리 상태를 선택하세요.',
                },
                'cancelMsg[handleReason]': {
                    required: '취소사유를 선택하세요.',
                },
            }
        });

        $('.js-layer-prev').click(function () {
            $("#viewStatusCancelDetail").removeClass('display-none');
            $("#viewStatusCancelReturn").addClass('display-none');
        });

        $('input[name*=statusCheck]').click(function () {
            $("#cancelPriceHtml").empty();
            $('#viewStatusCancelDetail').addClass('display-none');
            $("#returnHtml").empty();
            $("#viewStatusCancelReturn").addClass('display-none');
        });

        $('.js-layer-close').click(function () {
            window.close();
        });

        $('.js-select-goods-cancel').click(function () {
            if ($('input[name*=statusCheck]:checked').length < 1) {
                alert('취소할 상품을 선택해 주세요.');
                return;
            }
            var orderGoodsCancelSno = [];
            var orderGoodsCancelCnt = [];
            $('input[name*=statusCheck]:checked').each(function (idx) {
                orderGoodsCancelSno.push($(this).val());
                var cancelCnt = $("select[name='cancelCnt[" + $(this).val() + "]'").val();
                orderGoodsCancelCnt.push(cancelCnt);
            });

            var orderGoodsCancelSnoString = orderGoodsCancelSno.join('||');
            var orderGoodsCancelCntString = orderGoodsCancelCnt.join('||');
            $('input[name="orderGoodsCancelSno"]').val(orderGoodsCancelSnoString);
            $('input[name="orderGoodsCancelCnt"]').val(orderGoodsCancelCntString);
            $("#returnHtml").empty();
            $("#viewStatusCancelReturn").addClass('display-none');
            $.ajax({
                method: "POST",
                data: {
                    'mode': 'get_select_order_goods_cancel_data',
                    'orderNo': '<?= $data['orderNo']; ?>',
                    'orderGoodsCancelSno': orderGoodsCancelSno,
                    'orderGoodsCancelCnt': orderGoodsCancelCnt
                },
                cache: false,
                async: false,
                url: "../order/ajax_order_view_status_cancel.php",
                success: function (data) {
                    if (data) {
                        $("#cancelPriceHtml").empty();
                        $("#cancelPriceHtml").append(data);
                        $("#viewStatusCancelDetail").removeClass('display-none');
                    }
                }
            });
        });

        $('.js-step-return').click(function () {
            if ($('select[name="cancelMsg[orderStatus]"]').val() == '') {
                alert('취소처리 상태를 선택하세요.');
                return false;
            }
            if ($('select[name="cancelMsg[handleReason]"]').val() == '') {
                alert('취소사유를 선택하세요.');
                return false;
            }

            $("#returnHtml").empty();

            $.ajax({
                method: "POST",
                data: {
                    'mode': 'get_select_order_return',
                    'orderNo': '<?= $data['orderNo']; ?>',
                },
                cache: false,
                async: false,
                url: "../order/ajax_order_view_status_return.php",
                success: function (data) {
                    if (data) {
                        $("#returnHtml").append(data);
                        $("#viewStatusCancelDetail").addClass('display-none');
                        $("#viewStatusCancelReturn").removeClass('display-none');
                    }
                },
            });
        });
    });
</script>
