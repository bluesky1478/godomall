<?php
/**
 * 상태변경 팝업 - 상품추가
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>
<!-- 주문상품 -->
<form name="formOrderViewadd" id="formOrderViewadd">
    <input type="hidden" name="mode" value="" />
</form>

<table class="table table-rows">
    <thead>
    <tr>
        <th>상품<br />주문번호</th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>수량</th>
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
        foreach ($data['goods'] as $sKey => $sVal) {
            $rowCnt = $data['cnt']['goods']['all']; // 한 주문당 상품주문 수량
            $rowChk = 0; // 한 주문당 첫번째 주문 체크용
            $rowScm = 0;
            $rowMultiShipping = 0;
            foreach ($sVal as $dKey => $dVal) {
                $rowDelivery = 0;
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
                    <tr data-sno="<?=$val['sno']?>" data-stock-cnt="<?=$val['stockCnt']?>" data-goods-price="<?=$goodsPrice?>" data-goods-sellFl="<?=$goodsSellFl?>">
                        <!-- 상품주문번호 -->
                        <td class="center"><?=$val['sno']?></td>
                        <!-- 상품주문번호 -->

                        <!-- 이미지 -->
                        <td class="js-add-area-image">
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } else { ?>
                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } ?>
                        </td>
                        <!-- 이미지 -->

                        <!-- 주문상품 -->
                        <td class="js-add-area-goodsNm">
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
                                                echo gd_global_order_currency_display(gd_isset($oVal['optionTextPrice']), $data['addRate'], $data['currencyPolicy']);
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
                                <?= gd_global_order_currency_display($goodsPrice, $data['addRate'], $data['currencyPolicy']); ?>
                            <?php } else { ?>
                                <?= gd_currency_display($goodsPrice); ?>
                            <?php } ?>
                        </td>
                        <!-- 상품금액 -->

                        <!-- 총상품금액 -->
                        <td class="text-right">
                            <?php if ($isUseMall == true) { ?>
                                <?= gd_global_order_currency_display($goodsPrice * $val['goodsCnt'], $data['addRate'], $data['currencyPolicy']); ?>
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
                                    <?= gd_global_order_currency_display($val['deliveryCharge'], $data['addRate'], $data['currencyPolicy']); ?>
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
            <td class="no-data" colspan="<?php echo ($useMultiShippingKey === true) ? '9' : '8'; ?>">상품추가할 상품이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<!-- 주문상품 -->

<!-- 추가상품 -->
<div class="table-title mgt30">
    <span class="gd-help-manual">추가 상품</span>
</div>

<table class="table table-rows" id="addTable">
    <thead>
    <tr>
        <th><input type='checkbox' id='allCheck' value='y' class='js-checkall' data-target-name='bundle[statusCheck]' /></th>
        <th>이미지</th>
        <th>주문상품</th>
        <th>수량</th>
        <th>상품금액</th>
        <th>총 상품금액</th>
        <th>재고</th>
        <th>판매상태</th>
        <?php if($useMultiShippingKey === true){ ?>
            <th>배송지</th>
        <?php } ?>
        <th style="width: 130px;">운영자 추가 할인</th>
    </tr>
    </thead>

    <tbody>
    <tr class="no-data">
        <td class="no-data" colspan="<?php echo ($useMultiShippingKey === true) ? '9' : '8'; ?>">상품추가할 상품이 없습니다.</td>
    </tr>
    </tbody>
</table>

<div class="table-action">
    <div class="pull-left form-inline">
        <button type="button" class="btn btn-white js-select-goods-delete">선택상품 삭제</button>
    </div>
    <div class="pull-right form-inline">
        <button type="button" class="btn btn-sm btn-black mgr5 js-goods-add">상품추가</button>
    </div>
</div>

<div class="width100p text-center">
    <button type="button" class="btn btn-sm btn-red js-add-act">결제금액 계산</button>
</div>
<!-- 상품추가상품 -->

<!-- 상품추가처리 -->
<form name="addForm" id="addForm" action="../order/order_change_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="add" />
    <input type="hidden" name="orderNo" value="<?=$data['orderNo']?>" />
    <input type="hidden" name="multiShippingOrderInfoCd" value="" />
    <div id="viewStatusAddDetail" class="display-none">
        <div class="table-title">
            <span class="gd-help-manual mgt30">추가 금액 설정</span>
        </div>

        <div id="addPriceHtml"></div>

        <div class="text-center">
            <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
            <button type="submit" class="btn btn-lg btn-black">확인</button>
        </div>
    </div>
</form>
<!-- 상품추가처리 -->

<div class="mgt30 popup-claim-info-area">
    <div class="bold">도움말</div>
    <div><strong>·</strong> 결제수단 가상계좌, 가상계좌(에스크로)의 입금전 주문일 경우, 상품추가는 불가능합니다.</div>
    <div><strong>·</strong> 추가된 상품에 할인이 필요한 경우 운영자 추가 할인 금액을 설정하여 결제금액을 조정할 수 있습니다.</div>
    <div><strong>·</strong> 배송비는 추가상품에 적용된 배송비 조건별로 적용되며, 배송비 추가여부를 설정할 수 있습니다.</div>
    <div><strong>·</strong> 입금전 발급(발행)요청 상태의 현금영수증과 세금계산서는 상품추가로 결제금액이 변경되는 경우 발급(발행)금액도 자동으로 변경됩니다.</div>
    <div><strong>·</strong> 페이코, 네이버 페이 주문의 입금전 주문일 경우, 상품추가는 불가능합니다.</div>
    <div><strong>·</strong> 네이버 페이 주문의 상세한 정보는 네이버 페이 센터에서 관리하실 것을 권장합니다. <a href="https://admin.pay.naver.com" target="_blank" style="color:#117efa !important;">[네이버 페이 센터 바로가기 ▶]</a></div>
</div>

<script>
    var currencySymbol = '<?=$currencySymbol?>';
    var currencyString = '<?=$currencyString?>';
    var useMultiShippingKey = '<?=$useMultiShippingKey?>';
    var multiShippingInfoCdList = '<?=$multiShippingInfoCdList?>';

    $(document).ready(function(){
        // 상품추가 폼 체크
        $('#addForm').validate({
            dialog: false,
            submitHandler: function (form) {
                dialog_confirm('상품추가 결제 금액은 ' + numeral($('input[name="appointmentSettlePrice"]').val()).format() + '<?= gd_currency_string(); ?> 입니다. <br/><br/>진행 하시겠습니까?', function (result) {

                    $.each($("input[name*='bundle[statusCheck]']"), function () {
                        var enurikey = $(this).val().concat($(this).attr("data-goods-no"));
                        var enuriVal = $(this).closest('tr').find("input[name='enuri[]']").val();
                        $('#addForm').append("<input type='hidden' name='enuri["+enurikey+"]' value='"+enuriVal+"' />");
                    });

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
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                orderNo: {
                    required: '정상 접속이 아닙니다.(no)',
                },
            }
        });

        //취소버튼
        $('.js-layer-close').click(function(){
            self.close();
        });

        //상품추가
        $('.js-goods-add').click(function(){
            window.open('./popup_order_goods.php?loadPageType=orderViewAdd', 'popup_order_goods', 'width=1130, height=710, scrollbars=no');
        });

        //운영자 추가 할인
        $(document).on('change', '.js-enuri', function(){
            var thisObj = $(this);

            if(Number(thisObj.val()) > Number(thisObj.attr('data-goods-settle-price'))){
                alert("운영자 추가 할인 금액은 상품결제가보다 클 수 없습니다.");
                thisObj.val(thisObj.attr('data-goods-settle-price'));
            }
        });

        //선택상품 삭제
        $('.js-select-goods-delete').click(function(){
            var checkedBox = $('input[name*="bundle[statusCheck]"]:checked');
            if(checkedBox.length < 1){
                alert("삭제할 상품추가상품을 선택해 주세요.");
                return;
            }

            var snoArr = [];
            $('input[name*="bundle[statusCheck]"]:checked').each(function(){
                snoArr.push($(this).val());
            });

            $.post('./order_ps.php', { 'mode':'order_view_exchange_select_delete', 'cartSno':snoArr.join('<?=INT_DIVISION?>') }, function (cartData) {
                $("#addTable>tbody").empty();
                setOrderViewAddLayout(cartData);
            });

            resetAddArea();
        });

        $(document).on("keyup blur", ".js-enuri", function () {
            resetAddArea();
        });

        //상품추가접수
        $('.js-add-act').click(function(){
            if($("#addTable>tbody>tr:not('.no-data')").length < 1){
                alert("추가 할 상품을 추가해 주세요.");
                return;
            }

            var enuriArr = [];
            $("input[name='enuri[]']").each(function(){
                enuriArr.push($(this).val());
            });
            var enuriStr = enuriArr.join('<?=INT_DIVISION?>');

            //복수배송지
            var multiShippingOrderInfoCdStr = '';
            if(useMultiShippingKey){
                var multiShippingOrderInfoCdArr = [];
                $("select[name='multiShippingOrderInfoCd[]']").each(function(){
                    var thisCheckBoxObj = $(this).closest("tr").find("input[name^='bundle[statusCheck]']");
                    var thisKeyText = thisCheckBoxObj.val() + thisCheckBoxObj.attr("data-goods-no")
                    multiShippingOrderInfoCdArr.push(thisKeyText + '<?=INT_DIVISION?>' + $(this).val());
                });
               multiShippingOrderInfoCdStr = multiShippingOrderInfoCdArr.join('<?=STR_DIVISION?>');
            }

            $.ajax({
                method: "POST",
                data: {
                    'mode': 'get_select_order_goods_add_data',
                    'orderNo': '<?= $data['orderNo']; ?>',
                    'enuri' : enuriStr,
                    'multiShippingOrderInfoCd' : multiShippingOrderInfoCdStr,
                },
                cache: false,
                async: false,
                url: "../order/ajax_order_view_status_add.php",
                success: function (data) {
                    if (data) {
                        $("#addPriceHtml").empty();
                        $("#addPriceHtml").append(data);
                        $("#viewStatusAddDetail").removeClass('display-none');
                        $("input[name='multiShippingOrderInfoCd']").val(multiShippingOrderInfoCdStr);
                    }
                }
            });
        });

        //복수배송지 변경 시
        $(document).on("change", ".js-multiShipping-select", function () {
            var parentGoodsNo = $(this).closest("tr").attr("data-parent-goods-no");
            var thisValue = $(this).val();

            if($("#addTable>tbody>tr").length > 1){
                $("#addTable>tbody>tr").each(function(){
                    if($(this).attr("data-parent-goods-no") === parentGoodsNo){
                        $(this).find(".js-multiShipping-select").val(thisValue);
                    }
                });
            }

            resetAddArea();
        });
    });

    function resetAddArea()
    {
        $("#addPriceHtml").empty();
        $("#viewStatusAddDetail").addClass('display-none');
        $("input[name='multiShippingOrderInfoCd']").val('');
    }

    function setAnotherExchangeLayout()
    {
        resetAddArea();
        $.post('./order_ps.php', { 'mode': 'order_view_exchange_get_goods' }, function (cartData) {
            setOrderViewAddLayout(cartData);
        });
    }

    function currencyDisplay(currency)
    {
        return currencySymbol + numeral(currency).format() + currencyString;
    }

    function getMultiShippingSelectBox()
    {
        var multiShippingSelectBox  = '<select class="js-multiShipping-select" name="multiShippingOrderInfoCd[]">';
        var multiShippingArray = multiShippingInfoCdList.split('<?=INT_DIVISION?>');
        $(multiShippingArray).each(function(index, val){
            if(val < 2){
                var valueText = '메인';
            }
            else {
                var valueText = '추가' + (parseInt(val)-1);
            }
            multiShippingSelectBox += '<option value="'+val+'">' + valueText + '</option>';
        });
        multiShippingSelectBox += '</select>';

        return multiShippingSelectBox;
    }

    function setOrderViewAddLayout(cartData)
    {
        //복수배송지가 사용된 주문건일시
        var multiShippingSelectBox = '';
        if(useMultiShippingKey){
            multiShippingSelectBox  = getMultiShippingSelectBox();
        }

        var trHTML = '';
        $.each(cartData, function (key, val) {
            $.each(val, function (key1, val1) {
                $.each(val1, function (key2, val2) {
                    var timeSaleImage = '';
                    var goodsCdHTML = '';
                    var optionHTML = '';
                    var textOptionHTML = '';
                    var goodsSellFlHTML = '';
                    var multiShippingAreaDuisplay = ' display-none';

                    //복수배송지가 사용된 주문건일시
                    if(useMultiShippingKey){
                        multiShippingAreaDuisplay = '';
                        $(".js-multiShipping-area").removeClass('display-none');
                    }

                    if(val2.timeSaleFl) {
                        timeSaleImage = "<img src='/admin/gd_share/img/time-sale.png' alt='타임세일' />";
                    }
                    if(val2.goodsCd) {
                        goodsCdHTML = "<div class='font-kor' title='상품코드'>[" + val2.goodsCd + "]</div>";
                    }
                    if(val2.option.length > 0) {
                        $.each(val2.option, function (optKey, optVal) {
                            optionHTML += "<dl class='dl-horizontal' title='옵션명'>";
                            optionHTML += "<dt>" + optVal.optionName + " :</dt>";
                            optionHTML += "<dd>" + optVal.optionValue + "</dd>";
                            optionHTML += "</dl>";
                        });
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
                            addGoodsNmLayout += "<a href='javascript:void();' class='one-line bold mgb5' title='추가상품명' onclick=\"addgoods_register_popup('"+agVal.addGoodsNo+"', 'false');\">" + agVal.addGoodsNm + "</a>";
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

                            var agStock = '∞';
                            if (agVal.stockFl == 'y') {
                                var agStock = agVal.stockCnt;
                            }
                            //상품 tr template
                            var complied = _.template($('#goodsTemplate').html());
                            trAddGoodsHTML += complied({
                                sno: val2.sno, //cart sno
                                goodsImage: agVal.addGoodsImage,
                                goodsNm: addGoodsNmLayout,
                                goodsCnt: numeral(agVal.addGoodsCnt).format(),
                                goodsCntText: numeral(agVal.addGoodsCnt).format(),
                                goodsPrice: currencyDisplay(agVal.addGoodsPrice),
                                goodsTotalPrice: currencyDisplay(parseFloat(agVal.addGoodsPrice)*parseInt(agVal.addGoodsCnt)),
                                goodsSettlePrice : parseFloat(agVal.addGoodsPrice)*parseInt(agVal.addGoodsCnt),
                                stockCnt: agStock,
                                goodsSellFl: goodsSellFlHTML,
                                goodsNo : agVal.addGoodsNo,
                                multiShippingAreaDuisplay : multiShippingAreaDuisplay,
                                multiShippingSelectBox : multiShippingSelectBox,
                                parentGoodsNo : val2.goodsNo,
                            });
                        });
                    }

                    //상품명 template
                    var goodsNmComplied = _.template($('#goodsNmTemplate').html());
                    var goodsNmLayout = goodsNmComplied({
                        timeSaleImage: timeSaleImage,
                        goodsNo: val2.goodsNo,
                        goodsNm: val2.goodsNm,
                        goodsCdHTML: goodsCdHTML,
                        optionHTML: optionHTML,
                        textOptionHTML: textOptionHTML,
                    });

                    var gStock = '∞';
                    if (val2.stockFl == 'y') {
                        var gStock = val2.stockCnt;
                    }
                    //상품 tr template
                    var complied = _.template($('#goodsTemplate').html());
                    trHTML += complied({
                        sno: val2.sno, //cart sno
                        goodsImage: val2.goodsImage,
                        goodsNm: goodsNmLayout,
                        goodsCnt: val2.goodsCnt,
                        goodsCntText: numeral(val2.goodsCnt).format(),
                        goodsPrice: currencyDisplay(parseFloat(val2.price.goodsPrice) + parseFloat(val2.price.optionPrice) + parseFloat(val2.price.optionTextPrice)),
                        goodsTotalPrice: currencyDisplay((parseFloat(val2.price.goodsPrice) + parseFloat(val2.price.optionPrice) + parseFloat(val2.price.optionTextPrice)) * val2.goodsCnt),
                        goodsSettlePrice : ((parseFloat(val2.price.goodsPrice) + parseFloat(val2.price.optionPrice) + parseFloat(val2.price.optionTextPrice)) * val2.goodsCnt) - parseFloat(val2.price.goodsDcPrice),
                        stockCnt: gStock,
                        goodsSellFl: goodsSellFlHTML,
                        goodsNo : val2.goodsNo,
                        multiShippingAreaDuisplay : multiShippingAreaDuisplay,
                        multiShippingSelectBox : multiShippingSelectBox,
                        parentGoodsNo : val2.goodsNo,
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
            beforeOrderGoodsCntArr.push($(this).closest("tr").find('select[name*="addCnt"]').val());
        });
        var beforeOrderGoodsSnoStr = beforeOrderGoodsSnoArr.join('<?=INT_DIVISION?>');
        var beforeOrderGoodsCntStr = beforeOrderGoodsCntArr.join('<?=INT_DIVISION?>');
        $("#addForm>input[name='beforeOrderGoodsSno']").val(beforeOrderGoodsSnoStr);
        $("#addForm>input[name='beforeOrderGoodsCnt']").val(beforeOrderGoodsCntStr);
        $("#addTable>tbody").html(trHTML);
    }
</script>

<script type="text/html" id="goodsTemplate">
    <tr data-parent-goods-no="<%=parentGoodsNo%>">
        <td class='text-center'><input type='checkbox' name='bundle[statusCheck][<%=sno%>]' value='<%=sno%>' data-goods-cnt="<%=goodsCnt%>" data-goods-no="<%=goodsNo%>" /></td>
        <td><%=goodsImage%></td>
        <td><%=goodsNm%></td>
        <td class='text-center'><%=goodsCntText%></td>
        <td class='text-right'><%=goodsPrice%></td>
        <td class='text-right'><%=goodsTotalPrice%></td>
        <td class='text-center'><%=stockCnt%></td>
        <td class='text-center'><%=goodsSellFl%></td>
        <td class='text-center<%=multiShippingAreaDuisplay%>'><%=multiShippingSelectBox%></td>
        <td class='text-center'><input type="text" name="enuri[]" class="form-control input-small js-number js-enuri" value="0" data-goods-settle-price="<%=goodsSettlePrice%>" /></td>
    </tr>
</script>

<script type="text/html" id="goodsNmTemplate">
    <%=timeSaleImage%>
    <a href="javascript:void()" class="one-line" title="상품명" onclick="goods_register_popup('<%=goodsNo%>', false, '');"><%=goodsNm%></a>

    <div class="info">
        <%=goodsCdHTML%>
        <%=optionHTML%>
        <%=textOptionHTML%>
    </div>
</script>
