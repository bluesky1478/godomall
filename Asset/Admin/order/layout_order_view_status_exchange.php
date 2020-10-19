<?php
/**
 * 상태변경 팝업 - 상품교환
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>
<!-- 주문상품 -->
<form name="formOrderViewExchange" id="formOrderViewExchange">
    <input type="hidden" name="mode" value="" />
</form>

<table class="table table-rows">
    <thead>
    <tr>
        <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[orderCheck]' /></th>
        <th>상품<br />주문번호</th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>수량</th>
        <th>교환수량</th>
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
    if (isset($data['goods']) === true) {
        $rowAll = 0;
        $rowDelivery = 0;
        foreach ($data['goods'] as $sKey => $sVal) {
            $rowCnt = $data['cnt']['goods']['all']; // 한 주문당 상품주문 수량
            $rowChk = 0; // 한 주문당 첫번째 주문 체크용
            $rowScm = 0;
            $rowMultiShipping = 0;
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $key => $val) {

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

                    $goodsPrice = $val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'];
                    $goodsSellFl = ($val['goodsSellFl'] === 'y' || $val['goodsSellMobileFl'] === 'y') ? '판매함' : '판매안함';
                    $isUseMall = false;
                    if($val['mallSno'] > DEFAULT_MALL_NUMBER){
                        //$isUseMall = true;
                    }
                    ?>
                    <tr data-sno="<?=$val['sno']?>" data-stock-cnt="<?=$val['stockCnt']?>" data-goods-price="<?=$goodsPrice?>" data-goods-sellFl="<?=$goodsSellFl?>" data-order-info-cd="<?=(int)$val['orderInfoCd']?>" data-goods-change-able-option="<?=$val['changeAbleOrderGoodsInfo']?>" data-option-sno="<?=$val['optionSno']?>" data-goods-type="<?=$val['goodsType']?>">
                        <td class="center">
                            <div class="display-block">
                                <input type="checkbox" name="bundle[orderCheck][<?= $val['sno']; ?>]" value="<?= $val['sno']; ?>" class="<?= gd_isset($onclickAction); ?>"/>
                            </div>
                        </td>

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

                        <!-- 교환수량 -->
                        <td class="text-center">
                            <select name="exchangeCnt[<?= $val['sno']; ?>]">
                                <?php for ($i=$val['goodsCnt']; $i>=1; $i--) { ?>
                                    <option value="<?= $i; ?>"><?= number_format($i); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <!-- 교환수량 -->

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
                    $rowMultiShipping++;
                }
            }
        }
    } else {
    ?>
        <tr>
            <td class="no-data" colspan="11">교환할 상품이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="table-action">
    <div class="pull-right form-inline">
        <span class="action-title va-m">선택한 상품을</span>
        <button type="button" class="btn btn-sm btn-black mgr5 js-same-exchange">동일상품교환</button>
        <button type="button" class="btn btn-sm btn-black mgr5 js-another-exchange">다른상품교환</button>
    </div>
</div>
<!-- 주문상품 -->


<!-- 교환상품 -->
<div class="table-title mgt30">
    <span class="gd-help-manual">교환 상품</span>
</div>

<table class="table table-rows" id="exchangeTable">
    <thead>
    <tr>
        <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]' /></th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>옵션</th>
        <th>수량</th>
        <th>상품금액</th>
        <th>총 상품금액</th>
        <th>재고</th>
        <th>판매상태</th>
    </tr>
    </thead>

    <tbody>
        <tr class="no-data">
            <td class="no-data" colspan="9">교환할 상품이 없습니다.</td>
        </tr>
    </tbody>
</table>

<div class="table-action">
    <div class="pull-left form-inline">
        <button type="button" class="btn btn-white js-select-goods-delete">선택상품 삭제</button>
    </div>
    <div class="pull-right form-inline display-none js-exchange-add-goods-area">
        <button type="button" class="btn btn-sm btn-white mgr5 js-exchange-add-goods">상품추가</button>
    </div>
</div>

<div class="width100p text-center">
    <button type="button" class="btn btn-sm btn-red js-exchange-act">결제금액 계산</button>
</div>
<!-- 교환상품 -->

<!-- 교환처리 -->
<form name="exchangeForm" id="exchangeForm" action="../order/order_change_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="" />
    <input type="hidden" name="orderNo" value="<?=$data['orderNo']?>" />
    <input type="hidden" name="orderGoodsSno" value="" /> <!-- 동일상품교환 사용 -->
    <input type="hidden" name="orderGoodsCnt" value="" /> <!-- 동일상품교환 사용 -->
    <input type="hidden" name="changeOption" value="" /> <!-- 동일상품교환 사용 -->
    <input type="hidden" name="cartSno" value="" /> <!-- 타상품교환 사용 -->
    <input type="hidden" name="beforeOrderGoodsSno" value="" /> <!-- 타상품교환 사용 -->
    <input type="hidden" name="beforeOrderGoodsCnt" value="" /> <!-- 타상품교환 사용 -->

    <div id="viewStatusExchangeDetail" class="display-none">
        <div class="table-title">
            <span class="gd-help-manual mgt30">교환 처리</span>
            <span class="mgl10 notice-info">교환 주문은 <a href="order_list_exchange.php" target="_blank">[취소/교환/반품/환불 관리 > 교환 리스트]</a>에서 확인할 수 있습니다.</span>
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th class="require">교환사유</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box(null, 'handleReason', $refundReason, null, null, null); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>상세사유</th>
                <td>
                    <textarea class="form-control" name="handleDetailReason"></textarea>
                    <div class="mgt5 mgb5">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="handleDetailReasonShowFl" value="y">상세 사유를 고객에게 노출합니다.
                        </label>
                    </div>
                </td>
            </tr>
        </table>
        <!-- 취소 처리 -->

        <div id="exchangePriceHtml"></div>

        <div class="pull-right form-inline">1 / 2</div>

        <div class="text-center">
            <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
            <button type="button" class="btn btn-lg btn-black js-step-return">다음</button>
        </div>
    </div>

    <div id="viewStatusExchangeReturn" class="display-none">
        <div id="returnHtml"></div>

        <div class="pull-right form-inline">2 / 2</div>

        <div class="text-center">
            <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
            <button type="submit" class="btn btn-lg btn-black">확인</button>
        </div>
    </div>
</form>
<!-- 교환처리 -->

<div class="mgt30 popup-claim-info-area">
    <div class="bold">도움말</div>
    <div><strong>·</strong> 동일상품교환은 금액변경 없이 교환처리되므로 상품불량, 누락 등으로 인한 재배송일 경우 사용하시기 바랍니다.</div>
    <div><strong>·</strong> 다른상품교환으로 추가 입금이 필요한 경우 결제 수단 정보에 입금자명과 입금계좌를 입력해야 합니다.</div>
    <div><strong>·</strong> 차액 추가 결제는 최초 결제수단에 상관없이 무통장입금으로만 가능합니다.</div>
    <div><strong>·</strong> 차액 환불수단은 환불처리 상세정보에서 “현금, 마일리지, 예치금, 기타환불“ 중 선택할 수 있습니다.</div>
    <div><strong>·</strong> 교환 신규상품에 추가 할인이 필요한 경우 운영자 추가 할인 금액을 설정하여 결제금액을 조정할 수 있습니다.</div>
    <div><strong>·</strong> 현금영수증은 입금후 다른상품교환, 부분 환불완료로 결제금액이 변경된 경우에 발급금액이 자동으로 변경되지 않으니 취소 후 현금영수증 수동발급을 해주셔야 합니다.</div>
    <div><strong>·</strong> 발행요청 상태의 세금계산는 다른상품교환, 부분 환불완료로 결제금액이 변경되는 경우 세금계산서 발행액도 자동으로 변경됩니다.</div>
    <div><strong>·</strong> 고도빌로 전송된 전자세금계산서는 결제금액이 변경되어도 자동 재전송되지 않으니 고도빌에서 취소 및 재발행을 해주셔야 합니다. <a href="https://godobill.godo.co.kr/front/intro.php" target="_blank" style="color:#117efa !important;">[고도빌 바로가기 ▶]</a></div>
    <div><strong>·</strong> 일반세금계산서는 쇼핑몰 마이페이지에서 구매자가 인쇄한 후에도 결제금액이 변경되면 발행액도 자동으로 변경되며,</div>
    <div class="mgl8">쇼핑몰 마이페이지에서 고객이 세금계산서를 다시 인쇄할 경우 변경된 금액으로 인쇄됩니다.</div>
    <div><strong>·</strong> 네이버 페이 주문은 동일상품교환, 다른상품교환이 모두 불가능합니다.</div>
    <div><strong>·</strong> 네이버 페이 주문의 상세한 정보는 네이버 페이 센터에서 관리하실 것을 권장합니다. <a href="https://admin.pay.naver.com" target="_blank" style="color:#117efa !important;">[네이버 페이 센터 바로가기 ▶]</a></div>
</div>
<script>
    var currencySymbol = '<?=$currencySymbol?>';
    var currencyString = '<?=$currencyString?>';
    var isProvider = '<?=$isProvider?>';

    $(document).ready(function(){

        // 교환 폼 체크
        $('#exchangeForm').validate({
            submitHandler: function (form) {
                if($("#formOrderViewExchange>input[name='mode']").val() === 'anotherExchange'){
                    //다른상품 교환
                    $.each($("input[name*='bundle[statusCheck]']"), function () {
                        var enurikey = $(this).val().concat($(this).attr("data-goods-no"));
                        var enuriVal = $(this).closest('tr').find("input[name='enuri[]']").val();
                        $('#exchangeForm').append("<input type='hidden' name='enuri["+enurikey+"]' value='"+enuriVal+"' />");
                    });
                }
                else {
                    //동일상품교환
                    var orderGoodsArr = [];
                    var orderGoodsCntArr = [];
                    var changeOptionArr = [];
                    $.each($("input[name*='bundle[statusCheck]']"), function () {
                        orderGoodsArr.push($(this).val());
                        orderGoodsCntArr.push($(this).attr('data-goods-cnt'));
                    });
                    $.each($("select[name*='changeGoodsOption']"), function () {
                        // 추가상품의 옵션은 실제 처리를 하지 않는다.
                        if($(this).attr("data-goodsType") !== 'addGoods'){
                            changeOptionArr.push($(this).attr("data-orderGoodsSno") + '<?=INT_DIVISION?>' + $(this).val());
                        }
                    });

                    var orderGoodsSnoStr = orderGoodsArr.join('<?=INT_DIVISION?>');
                    var orderGoodsCntStr = orderGoodsCntArr.join('<?=INT_DIVISION?>');
                    var changeOptionStr = changeOptionArr.join('<?=STR_DIVISION?>');
                    $("#exchangeForm>input[name='orderGoodsSno']").val(orderGoodsSnoStr);
                    $("#exchangeForm>input[name='orderGoodsCnt']").val(orderGoodsCntStr);
                    $("#exchangeForm>input[name='changeOption']").val(changeOptionStr);
                }

                form.submit();
            }
        });

        //취소버튼
        $('.js-layer-close').click(function(){
            self.close();
        });

        // 동일상품교환
        $('.js-same-exchange').click(function(){
            var trHTML = '';
            var checkedBox = $('input[name*="bundle[orderCheck]"]:checked');
            if (checkedBox.length < 1) {
                alert('동일상품교환할 상품을 선택해 주세요.');
                return;
            }

            resetExchangeArea();

            // thead 셋팅
            setTheadLayout('same');

            //상품추가 버튼 숨김
            $(".js-exchange-add-goods-area").addClass("display-none");

            if($("#exchangeTable>tbody>tr:not('.no-data')").length > 0 && $("#formOrderViewExchange>input[name='mode']").val() !== "sameExchange"){
                $("#exchangeTable>tbody").html('');
            }

            //모드 정의
            $("#formOrderViewExchange>input[name='mode']").val("sameExchange");

            $.each(checkedBox, function () {
                var parentTrObj = $(this).closest("tr");
                var sno = parentTrObj.attr("data-sno"); //order goods sno
                var goodsImage = parentTrObj.find(".js-exchange-area-image").html();
                var goodsNm = parentTrObj.find(".js-exchange-area-goodsNm").html();
                var goodsCnt = parentTrObj.find('select[name*="exchangeCnt"]').val();
                var goodsPrice = parentTrObj.attr("data-goods-price");
                var stockCnt = parentTrObj.attr("data-stock-cnt");
                var goodsSellFl = parentTrObj.attr("data-goods-sellFl");
                var changeAbleOption = parentTrObj.attr("data-goods-change-able-option");
                var optionSno = parentTrObj.attr("data-option-sno");
                var goodsType = parentTrObj.attr("data-goods-type");

                if($("#exchangeTable>tbody").find(':checkbox[value='+sno+']').length > 0){
                    return true;
                }

                if($.trim(changeAbleOption) !== ''){
                    var selectOption = '';
                    var changeAbleOptionArr = changeAbleOption.split('<?php echo STR_DIVISION; ?>');
                    $.each(changeAbleOptionArr, function (key, val) {
                        var selected = '';
                        var optionInfo = val.split('<?php echo INT_DIVISION; ?>');
                        if(parseInt(optionSno) === parseInt(optionInfo[0])){
                            selected = "selected='selected'";
                        }
                        selectOption += "<option value='"+optionInfo[0]+"' "+selected+">" + optionInfo[1] + "</option>";
                    });
                    var selectHtml = "<select name='changeGoodsOption["+sno+"]' data-orderGoodsSno='"+sno+"' data-goodsType='"+goodsType+"'>" + selectOption + "</select>";
                }

                var complied = _.template($('#tbodySameGoodsTemplate').html());
                trHTML += complied({
                    sno: sno,
                    goodsImage: goodsImage,
                    goodsNm: goodsNm,
                    goodsCnt: goodsCnt,
                    goodsCntText: numeral(goodsCnt).format(),
                    goodsPrice: currencyDisplay(goodsPrice),
                    goodsTotalPrice: currencyDisplay(parseFloat(goodsPrice)*parseInt(goodsCnt)),
                    stockCnt: stockCnt,
                    goodsSellFl: goodsSellFl,
                    selectHtml: selectHtml,
                });
            });
            $("#exchangeTable>tbody>tr.no-data").remove();
            $("#exchangeTable>tbody").append(trHTML);
        });

        //다른상품교환
        $('.js-another-exchange').click(function(){
            resetExchangeArea();

            // thead 셋팅
            setTheadLayout('another');

            var checkedBox = $('input[name*="bundle[orderCheck]"]:checked');
            if (checkedBox.length < 1) {
                alert('다른상품으로 교환할 상품을 선택해 주세요.');
                return;
            }
            var orderInfoArray = [];
            var orderInfoUniqueArray = [];
            checkedBox.each(function(){
                orderInfoArray.push($(this).closest("tr").attr("data-order-info-cd"));
            });
            $.each(orderInfoArray, function(i, el){
                if($.inArray(el, orderInfoUniqueArray) === -1) {
                    orderInfoUniqueArray.push(el);
                }
            });
            if(orderInfoUniqueArray.length > 1){
                alert("같은 배송지의 상품만 다른상품교환 처리가 가능합니다.");
                return;
            }

            //모드 정의
            $("#formOrderViewExchange>input[name='mode']").val("anotherExchange");

            //상품추가 버튼 노출
            $(".js-exchange-add-goods-area").removeClass("display-none");
            $('.js-exchange-add-goods').trigger('click');
        });

        //상품추가
        $('.js-exchange-add-goods').click(function(){
            window.open('./popup_order_goods.php?loadPageType=orderViewExchange', 'popup_order_goods', 'width=1130, height=710, scrollbars=no');
        });

        //선택상품 삭제
        $('.js-select-goods-delete').click(function(){
            var checkedBox = $('input[name*="bundle[statusCheck]"]:checked');
            if(checkedBox.length < 1){
                alert("삭제할 교환상품을 선택해 주세요.");
                return;
            }
            setRestart();

            switch($("#formOrderViewExchange>input[name='mode']").val()){
                case 'anotherExchange' :
                    var snoArr = [];
                    $('input[name*="bundle[statusCheck]"]:checked').each(function(){
                        snoArr.push($(this).val());
                    });

                    $.post('./order_ps.php', { 'mode':'order_view_exchange_select_delete', 'cartSno':snoArr.join('<?=INT_DIVISION?>') }, function (cartData) {
                        $("#exchangeTable>tbody").empty();

                        setOrderViewExchangeLayout(cartData);
                    });
                    break;

                case 'sameExchange' :
                    $.each($('input[name*="bundle[statusCheck]"]:checked'), function () {
                        $(this).closest("tr").remove();
                    });
                    break;

                default : break;
            }

            if($('input[name*="bundle[statusCheck]"]').length < 1){
                resetExchangeArea();
            }
        });

        //교환상품의 체크박스 클릭시
        $(document).on('click', 'input[name*="bundle[statusCheck]"]', function(){
            if($(this).prop("checked") === true){
                $('input[name*="bundle[statusCheck]['+$(this).val()+']"]').prop("checked", true);
            }
            else {
                $('input[name*="bundle[statusCheck]['+$(this).val()+']"]').prop("checked", false);
            }
        });

        //운영자 추가 할인
        $(document).on('change', '.js-enuri', function(){
            var thisObj = $(this);

            if(Number(thisObj.val()) > Number(thisObj.attr('data-goods-settle-price'))){
                alert("운영자 추가 할인 금액은 상품결제가보다 클 수 없습니다.");
                thisObj.val(thisObj.attr('data-goods-settle-price'));
            }
            setRestart();
        });

        //교환접수
        $('.js-exchange-act').click(function(){
            if($("#exchangeTable>tbody>tr:not('.no-data')").length < 1 || (!$("#formOrderViewExchange>input[name='mode']").val())){
                alert("교환 접수 할 상품을 추가해 주세요.");
                return;
            }

            //모드 이관
            $("#exchangeForm>input[name='mode']").val($("#formOrderViewExchange>input[name='mode']").val());

            switch($("#exchangeForm>input[name='mode']").val()){
                case 'anotherExchange' :
                    //교환처리 구역 노출
                    $("#viewStatusExchangeDetail").removeClass("display-none");

                    var enuriArr = [];
                    $("input[name='enuri[]']").each(function(){
                        enuriArr.push($(this).val());
                    });
                    var enuriStr = enuriArr.join('<?=INT_DIVISION?>');

                    var parameter = {
                        'mode' : 'get_select_order_goods_exchange_data',
                        'orderNo': '<?= $data['orderNo']; ?>',
                        'beforeOrderGoodsSno' : $("#exchangeForm>input[name='beforeOrderGoodsSno']").val(),
                        'beforeOrderGoodsCnt' : $("#exchangeForm>input[name='beforeOrderGoodsCnt']").val(),
                        'cartSno' : $("input[name*='bundle[statusCheck]']").eq(0).val(),
                        'enuri' : enuriStr,
                    };
                    $.post('../order/ajax_order_view_status_exchange.php', parameter, function (htmlData) {
                        $("#exchangePriceHtml").html(htmlData);
                    });
                    break;

                case 'sameExchange' :
                    //교환처리 구역 노출
                    $("#viewStatusExchangeDetail").removeClass("display-none");
                    break;

                default : break;
            }
        });

        $(document).on('click', '.js-step-return', function(){
            // 취소하려는 부가결제금액
            var cancelAddPaymentPrice  = parseInt($("input[name='cancelDivisionUseMileage']").val()) + parseInt($("input[name='cancelDivisionUseDeposit']").val());
            // 부가결제 필수 취소 금액
            var requireCancelAddPayment = parseInt($("input[name='requireCancelAddPayment']").val());
            if(cancelAddPaymentPrice < requireCancelAddPayment){
                alert("부가결제 금액은 " + currencyDisplay(requireCancelAddPayment) + " 이상 취소되어야 합니다.");
                return;
            }

            if(!$("select[name='handleReason']").val()){
                alert("교환 사유를 선택해 주세요.");
                return;
            }

            $("#returnHtml").empty();
            dialog_confirm('교환후 차액은 ' + numeral($('input[name="totalChangePrice"]').val()*-1).format() + '<?= gd_currency_string(); ?> 입니다. <br/><br/>진행 하시겠습니까?',                 function (result) {
                if (result) {
                    var orderGoodsArr = [];
                    $.each($("input[name*='bundle[statusCheck]']"), function () {
                        orderGoodsArr.push($(this).val());
                    });
                    var orderGoodsSnoStr = orderGoodsArr.join('<?=INT_DIVISION?>');

                    $.ajax({
                        method: "POST",
                        data: {
                            'mode': 'get_select_order_return',
                            'orderNo': '<?= $data['orderNo']; ?>',
                            'statusMode': 'e',
                            'exchangeMode': $("#formOrderViewExchange>input[name='mode']").val(),
                            'sameExchangeOrderGoodsSno' : orderGoodsSnoStr,
                        },
                        cache: false,
                        async: false,
                        url: "../order/ajax_order_view_status_return.php",
                        success: function (data) {
                            if (data) {
                                $("#returnHtml").append(data);
                                $(".js-exchange-act").addClass('display-none');
                                $("#viewStatusExchangeDetail").addClass('display-none');
                                $("#viewStatusExchangeReturn").removeClass('display-none');
                            }
                        },
                    });
                } else {
                    $("#viewStatusExchangeReturn").addClass('display-none');
                }
                layer_close();
            });
        });
    });

    function resetExchangeArea()
    {
        $(".js-exchange-act").removeClass("display-none");

        $("#formOrderViewExchange>input[name='mode']").val('');
        $("#exchangeForm>input[name='mode']").val('');
        $("#viewStatusExchangeDetail, #viewStatusExchangeReturn").addClass("display-none");
        $("#exchangeTable>tbody, #exchangePriceHtml, #returnHtml").empty();
        $("input[name='orderGoodsSno'], input[name='orderGoodsCnt'], input[name='cartSno']").val('');
        $("input[name='beforeOrderGoodsSno'], input[name='beforeOrderGoodsCnt']").val('');
    }

    function setRestart()
    {
        $("#returnHtml, #exchangePriceHtml").empty();
        $("#viewStatusExchangeReturn, #viewStatusExchangeDetail").addClass('display-none');
        $(".js-exchange-act").removeClass('display-none');
    }

    function setAnotherExchangeLayout()
    {
        setRestart();
        $.post('./order_ps.php', { 'mode': 'order_view_exchange_get_goods' }, function (cartData) {
            setOrderViewExchangeLayout(cartData);
        });
    }

    function currencyDisplay(currency)
    {
        return currencySymbol + numeral(currency).format() + currencyString;
    }

    function setTheadLayout(displayType)
    {
        $("#exchangeTable>thead").html('');

        if(displayType === 'same'){
            var complied = _.template($('#theadSameTemplate').html());
        }
        else if (displayType === 'another'){
            if(isProvider){
                var complied = _.template($('#theadAnotherProviderTemplate').html());
            }
            else {
                var complied = _.template($('#theadAnotherTemplate').html());
            }
        }
        else { }

        var theadHtml = complied({});
        $("#exchangeTable>thead").html(theadHtml);
        // js-checkall 이벤트 등록
        init_checkbox_style();
    }

    function setOrderViewExchangeLayout(cartData)
    {
        var trHTML = '';

        $.each(cartData, function (key, val) {
            $.each(val, function (key1, val1) {
                $.each(val1, function (key2, val2) {
                    var timeSaleImage = '';
                    var goodsCdHTML = '';
                    var optionHTML = '';
                    var textOptionHTML = '';
                    var goodsSellFlHTML = '';
                    var goodsPrice = parseFloat(val2.price.goodsPrice) + parseFloat(val2.price.optionPrice) + parseFloat(val2.price.optionTextPrice);

                    if(val2.timeSaleFl) {
                        timeSaleImage = "<img src='/admin/gd_share/img/time-sale.png' alt='타임세일' />";
                    }
                    if(val2.goodsCd) {
                        goodsCdHTML = "<div class='font-kor' title='상품코드'>[" + val2.goodsCd + "]</div>";
                    }
                    if(val2.option.length > 0) {
                        for(i=0;i<val2.option.length;i++){
                            if(val2.option.length - 1 == i && val2.option[i].optionDeliveryStr != undefined && val2.option[i].optionDeliveryStr != "undefined" && val2.option[i].optionDeliveryStr != ""){
                                optionDeliveryStr = "[" +  val2.option[i].optionDeliveryStr + "]";
                            } else {
                                optionDeliveryStr = "";
                            }
                            optionHTML += "<dl class='dl-horizontal' title='옵션명'>";
                            optionHTML += "<dt>" + val2.option[i].optionName + " :</dt>";
                            optionHTML += "<dd>" + val2.option[i].optionValue + optionDeliveryStr + "</dd>";
                            optionHTML += "</dl>";
                        }
                    }
                    if(val2.optionText.length > 0) {
                        $.each(val2.optionText, function (optTextKey, optTextVal) {
                            textOptionHTML += "<ul class='list-unstyled' title='텍스트 옵션명'>";
                            textOptionHTML += "<li>" + optTextVal.optionName + "</li>";
                            textOptionHTML += "<li>";
                            textOptionHTML += optTextVal.optionValue + ' ';
                            if(optTextVal.optionTextPrice > 0){
                                textOptionHTML += "<span>(추가금 ";
                                textOptionHTML += currencyDisplay(optTextVal.optionTextPrice)+")</span>";
                            }
                            textOptionHTML += "</li>";
                            textOptionHTML += "</ul>";
                        });
                    }
                    if(val2.goodsSellFl === 'y'){
                        goodsSellFlHTML = '판매함';
                    }
                    else {
                        goodsSellFlHTML = '판매안함';
                    }

                    //추가상품
                    var trAddGoodsHTML = '';
                    if(val2.addGoods.length > 0 ) {
                        $.each(val2.addGoods, function (agKey, agVal) {
                            var addGoodsNmLayout = "<span class='label label-default'>추가</span>";
                            addGoodsNmLayout += "<a href='javascript:void();' class='one-line bold mgb5' title='추가상품명' onclick=\"addgoods_register_popup('"+agVal.addGoodsNo+"', '"+isProvider+"');\">" + agVal.addGoodsNm + "</a>";
                            addGoodsNmLayout += "</a>";
                            if(agVal.optionNm){
                                addGoodsNmLayout += "<dl class='dl-horizontal'>";
                                addGoodsNmLayout += "<dt>:</dt>";
                                addGoodsNmLayout += "<dd>" + agVal.optionNm + "</dd>";
                                addGoodsNmLayout += "</dl>";
                            }

                            if(agVal.viewFl === 'y'){
                                var goodsSellFlHTML = "판매함";
                            }
                            else {
                                var goodsSellFlHTML = "판매안함";
                            }

                            if(agVal.stockUseFl == 'n'){
                                var agStockText = '∞';
                            }
                            else {
                                var agStockText = agVal.stockCnt;
                            }

                            //상품 tr template
                            if(isProvider){
                                var complied = _.template($('#tbodyAnotherProviderGoodsTemplate').html());
                            }
                            else {
                                var complied = _.template($('#tbodyAnotherGoodsTemplate').html());
                            }
                            trAddGoodsHTML += complied({
                                sno: val2.sno, //cart sno
                                goodsImage: agVal.addGoodsImage,
                                goodsNm: addGoodsNmLayout,
                                goodsCnt: agVal.addGoodsCnt,
                                goodsCntText: numeral(agVal.addGoodsCnt).format(),
                                goodsPrice: currencyDisplay(agVal.addGoodsPrice),
                                goodsTotalPrice: currencyDisplay(parseFloat(agVal.addGoodsPrice)*parseInt(agVal.addGoodsCnt)),
                                goodsSettlePrice : parseFloat(agVal.addGoodsPrice)*parseInt(agVal.addGoodsCnt),
                                stockCnt: agStockText,
                                goodsSellFl: goodsSellFlHTML,
                                goodsNo : agVal.addGoodsNo,
                            });
                        });
                    }

                    //상품명 template
                    var goodsNmComplied = _.template($('#goodsNmTemplate').html());
                    var goodsNmLayout = goodsNmComplied({
                        isProvider : isProvider,
                        timeSaleImage: timeSaleImage,
                        goodsNo: val2.goodsNo,
                        goodsNm: val2.goodsNm,
                        goodsCdHTML: goodsCdHTML,
                        optionHTML: optionHTML,
                        textOptionHTML: textOptionHTML,
                    });

                    //상품 tr template
                    if(isProvider){
                        var complied = _.template($('#tbodyAnotherProviderGoodsTemplate').html());
                    }
                    else {
                        var complied = _.template($('#tbodyAnotherGoodsTemplate').html());
                    }
                    trHTML += complied({
                        sno: val2.sno, //cart sno
                        goodsImage: val2.goodsImage,
                        goodsNm: goodsNmLayout,
                        goodsCnt: val2.goodsCnt,
                        goodsCntText: numeral(val2.goodsCnt).format(),
                        goodsPrice: currencyDisplay(goodsPrice),
                        goodsTotalPrice: currencyDisplay(val2.price.goodsPriceSum + val2.price.optionPriceSum + val2.price.optionTextPriceSum),
                        goodsSettlePrice : ((parseFloat(val2.price.goodsPrice) + parseFloat(val2.price.optionPrice) + parseFloat(val2.price.optionTextPrice)) * val2.goodsCnt) - parseFloat(val2.price.goodsDcPrice),
                        stockCnt: val2.stockText,
                        goodsSellFl: goodsSellFlHTML,
                        goodsNo : val2.goodsNo,
                    });

                    trHTML += trAddGoodsHTML;
                });
            });
        });

        var checkedBox = $('input[name*="bundle[orderCheck]"]:checked');
        var beforeOrderGoodsSnoArr = [];
        var beforeOrderGoodsCntArr = [];
        checkedBox.each(function(){
            beforeOrderGoodsSnoArr.push($(this).val());
            beforeOrderGoodsCntArr.push($(this).closest("tr").find('select[name*="exchangeCnt"]').val());
        });
        var beforeOrderGoodsSnoStr = beforeOrderGoodsSnoArr.join('<?=INT_DIVISION?>');
        var beforeOrderGoodsCntStr = beforeOrderGoodsCntArr.join('<?=INT_DIVISION?>');
        $("#exchangeForm>input[name='beforeOrderGoodsSno']").val(beforeOrderGoodsSnoStr);
        $("#exchangeForm>input[name='beforeOrderGoodsCnt']").val(beforeOrderGoodsCntStr);
        $("#exchangeTable>tbody").html(trHTML);
    }
</script>

<!-- 동일상품교환 thead -->
<script type="text/html" id="theadSameTemplate">
    <tr>
        <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]' /></th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>옵션</th>
        <th>수량</th>
        <th>상품금액</th>
        <th>총 상품금액</th>
        <th>재고</th>
        <th>판매상태</th>
    </tr>
</script>

<!-- 다른상품교환 thead (본사) -->
<script type="text/html" id="theadAnotherTemplate">
    <tr>
        <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]' /></th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>수량</th>
        <th>상품금액</th>
        <th>총 상품금액</th>
        <th>재고</th>
        <th>판매상태</th>
        <th class='js-enuri-th-area' style='width: 130px !important;'>운영자 추가 할인 금액</th>
    </tr>
</script>

<!-- 다른상품교환 thead (공급사) -->
<script type="text/html" id="theadAnotherProviderTemplate">
    <tr>
        <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]' /></th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>수량</th>
        <th>상품금액</th>
        <th>총 상품금액</th>
        <th>재고</th>
        <th>판매상태</th>
    </tr>
</script>

<!-- 동일상품교환 tbody -->
<script type="text/html" id="tbodySameGoodsTemplate">
    <tr>
        <td class='text-center'><input type='checkbox' name='bundle[statusCheck][<%=sno%>]' value='<%=sno%>' data-goods-cnt="<%=goodsCnt%>" /></td>
        <td class='text-center'><%=goodsImage%></td>
        <td><%=goodsNm%></td>
        <td class='text-center'><%=selectHtml%></td>
        <td class='text-center'><%=goodsCntText%></td>
        <td class='text-right'><%=goodsPrice%></td>
        <td class='text-right'><%=goodsTotalPrice%></td>
        <td class='text-center'><%=stockCnt%></td>
        <td class='text-center'><%=goodsSellFl%></td>
    </tr>
</script>

<!-- 다른상품교환 tbody (본사) -->
<script type="text/html" id="tbodyAnotherGoodsTemplate">
    <tr>
        <td class='text-center'><input type='checkbox' name='bundle[statusCheck][<%=sno%>]' value='<%=sno%>' data-goods-cnt="<%=goodsCnt%>" data-goods-no="<%=goodsNo%>"/></td>
        <td><%=goodsImage%></td>
        <td><%=goodsNm%></td>
        <td class='text-center'><%=goodsCntText%></td>
        <td class='text-right'><%=goodsPrice%></td>
        <td class='text-right'><%=goodsTotalPrice%></td>
        <td class='text-center'><%=stockCnt%></td>
        <td class='text-center'><%=goodsSellFl%></td>
        <td class='text-center'><input type="text" name="enuri[]" class="form-control input-small js-number js-enuri" value="0" data-goods-settle-price="<%=goodsSettlePrice%>" /></td>
    </tr>
</script>

<!-- 다른상품교환 tbody (공급사) -->
<script type="text/html" id="tbodyAnotherProviderGoodsTemplate">
    <tr>
        <td class='text-center'><input type='checkbox' name='bundle[statusCheck][<%=sno%>]' value='<%=sno%>' data-goods-cnt="<%=goodsCnt%>" data-goods-no="<%=goodsNo%>"/></td>
        <td><%=goodsImage%></td>
        <td><%=goodsNm%></td>
        <td class='text-center'><%=goodsCntText%></td>
        <td class='text-right'><%=goodsPrice%></td>
        <td class='text-right'><%=goodsTotalPrice%></td>
        <td class='text-center'><%=stockCnt%></td>
        <td class='text-center'><%=goodsSellFl%></td>
    </tr>
</script>

<script type="text/html" id="goodsNmTemplate">
    <%=timeSaleImage%>
    <a href="javascript:void()" class="one-line" title="상품명" onclick="goods_register_popup('<%=goodsNo%>', '<%=isProvider%>', '');"><%=goodsNm%></a>

    <div class="info">
        <%=goodsCdHTML%>
        <%=optionHTML%>
        <%=textOptionHTML%>
    </div>
</script>
