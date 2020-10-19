<?php
/**
 * 상태변경 팝업 - 주문반품
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>

<form name="backForm" id="backForm" action="../order/order_change_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="back"/>
    <input type="hidden" name="orderNo" value="<?= $data['orderNo']; ?>"/>
    <input type="hidden" name="orderChannelFl" value="<?= $data['orderChannelFl']; ?>"/>
    <table class="table table-rows">
        <thead>
        <tr>
            <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='back[statusCheck]'/></th>
            <th>상품<br/>주문번호</th>
            <th>이미지</th>
            <th>주문상품</th>
            <th>수량</th>
            <th>반품수량</th>
            <th>상품금액</th>
            <th>총 상품금액</th>
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
                foreach ($sVal as $dKey => $dVal) {
                    $rowDelivery = 0;
                    foreach ($dVal as $key => $val) {
                        // 주문상태 모드
                        $statusMode = substr($val['orderStatus'], 0, 1);

                        // rowspan 처리
                        $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                        $orderScmRowSpan = ' rowspan="' . ($data['cnt']['scm'][$sKey]) . '"';

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
                                    <input type="checkbox" name="back[statusCheck][<?= $val['sno']; ?>]" value="<?= $val['sno']; ?>" class="<?= gd_isset($onclickAction); ?>"/>
                                    <input type="hidden" name="back[statusMode][<?= $val['sno']; ?>]" value="<?= $val['orderStatus']; ?>"/>
                                    <input type="hidden" name="back[goodsType][<?= $val['sno']; ?>]" value="<?= $val['goodsType']; ?>"/>
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

                            <!-- 반품수량 -->
                            <td class="text-center">
                                <input type="hidden" name="back[goodsOriginCnt][<?= $val['sno']; ?>]" value="<?= $val['goodsCnt']; ?>"/>
                                <select name="back[goodsCnt][<?= $val['sno']; ?>]" <?=$claimElementDisabled?>>
                                    <?php for ($i = $val['goodsCnt']; $i >= 1; $i--) { ?>
                                        <option value="<?= $i; ?>"><?= number_format($i); ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <!-- 반품수량 -->

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
            <button type="button" class="btn btn-sm btn-black mgr5 js-select-goods-back">선택 상품 반품</button>
        </div>
    </div>
    <div id="viewStatusBackDetail" class="display-none">
        <!-- 반품 처리 -->
        <div class="table-title">
            <span class="gd-help-manual mgt30">반품 처리</span>
            <span class="mgl10 notice-info">반품 주문은 [취소/교환/반품/환불 관리 > 반품 리스트]에서 확인할 수 있습니다.</span>
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>반품사유</th>
                <td>
                    <label>
                        <?= gd_select_box(null, 'back[handleReason]', $backReason, null, null, null); ?>
                    </label>
                </td>
                <th>환불수단</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('refundMethod', 'back[refundMethod]', $refundMethod, null, null, null,$claimElementDisabled); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>상세사유</th>
                <td colspan="3">
                    <textarea class="form-control" name="back[handleDetailReason]" <?=$claimElementDisabled?>></textarea>
                    <div class="mgt5 mgb5">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="back[handleDetailReasonShowFl]" value="y">상세 사유를 고객에게 노출합니다.
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>환불 계좌정보</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('', 'back[refundBankName]', $bankNm, null, '', '= 은행 선택 =',$claimElementDisabled) ?>
                        &nbsp;
                        계좌정보 : <input type="text" name="back[refundAccountNumber]" class="form-control width-md js-number" <?=$claimElementDisabled?> maxlength="20"/>
                        &nbsp;
                        예금주 : <input type="text" name="back[refundDepositor]" class="form-control width-md" <?=$claimElementDisabled?> maxlength="20">
                    </div>
                </td>
            </tr>
        </table>
        <!-- 취소 처리 -->

        <div class="text-center">
            <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
            <button type="submit" class="btn btn-lg btn-black">확인</button>
        </div>
    </div>
</form>

<div class="mgt30 popup-claim-info-area">
    <div class="bold">도움말</div>
    <div><strong>·</strong> 환불수단은 “현금, PG, 예치금, 기타, 복합환불“ 중 선택할 수 있습니다.</div>
    <div><strong>·</strong> 반품접수 주문은 반품 리스트에서 확인할 수 있으며, 반품완료 후 환불 리스트의 환불 상세정보에서 환불완료처리를 하셔야 합니다.</div>
    <div><strong>·</strong> 현금영수증은 입금후 다른상품교환, 부분 환불완료로 결제금액이 변경된 경우에 발급금액이 자동으로 변경되지 않으니 취소 후 현금영수증 수동발급을 해주셔야 합니다.</div>
    <div><strong>·</strong> 발행요청 상태의 세금계산는 다른상품교환, 부분 환불완료로 결제금액이 변경되는 경우 세금계산서 발행액도 자동으로 변경됩니다.</div>
    <div><strong>·</strong> 고도빌로 전송된 전자세금계산서는 결제금액이 변경되어도 자동 재전송되지 않으니 고도빌에서 취소 및 재발행을 해주셔야 합니다. <a href="https://godobill.godo.co.kr/front/intro.php" target="_blank" style="color:#117efa !important;">[고도빌 바로가기 ▶]</a></div>
    <div><strong>·</strong> 일반세금계산서는 쇼핑몰 마이페이지에서 구매자가 인쇄한 후에도 결제금액이 변경되면 발행액도 자동으로 변경되며,</div>
    <div class="mgl8">쇼핑몰 마이페이지에서 고객이 세금계산서를 다시 인쇄할 경우 변경된 금액으로 인쇄됩니다.</div>
    <div><strong>·</strong> 네이버 페이 주문의 상세한 정보는 네이버 페이 센터에서 관리하실 것을 권장합니다. <a href="https://admin.pay.naver.com" target="_blank" style="color:#117efa !important;">[네이버 페이 센터 바로가기 ▶]</a></div>
</div>

<script>
    $(document).ready(function () {
        $("#backForm").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                mode: {
                    required: true,
                },
                orderNo: {
                    required: true,
                },
                'back[orderStatus]': {
                    required: true,
                },
                'back[handleReason]': {
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
                'back[orderStatus]': {
                    required: '반품처리 상태를 선택하세요.',
                },
                'back[handleReason]': {
                    required: '반품사유를 선택하세요.',
                },
            }
        });

        $('.js-layer-close').click(function () {
            window.close();
        });

        $('input[name*=statusCheck]').click(function () {
            $('#viewStatusBackDetail').addClass('display-none');
        });

        $('.js-select-goods-back').click(function () {
            if ($('input[name*=statusCheck]:checked').length < 1) {
                alert('반품할 상품을 선택해 주세요.');
                return;
            }

            $('#viewStatusBackDetail').removeClass('display-none');
        });
    });
</script>
