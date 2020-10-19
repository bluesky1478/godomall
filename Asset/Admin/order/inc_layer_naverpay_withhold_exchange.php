<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <tr>
        <th>보류사유</th>
        <td>
            <?php if($layerMode == 'view') {?>
                <?=$data['reason']?>
            <?php } else {?>
                <?=gd_select_box('naverCode', 'naverCode', $codeList); ?>
            <?php }?>
        </td>
    </tr>

    <tr>
        <th>교환배송비 금액</th>
        <td class="form-inline">
            <?php if($layerMode == 'view') {?>
                <?=$checkoutData['exchangeData']['ClaimDeliveryFeeDemandAmount']?>원
            <?php } else {?>네이버 페이 반품배송비로 설정된 금액에 따라 처리됩니다.
            <?php }?>
        </td>
    </tr>
    <?php if($layerMode == 'view') {?>
        <tr>
            <th>결제 방식</th>
            <td class="form-inline">
                <?=$checkoutData['exchangeData']['ClaimDeliveryFeePayMethod']?>
            </td>
        </tr>
    <?php }?>
    <tr <?php if($layerMode == 'view' && empty($data['extraData'])) {?>style="display:none"<?php }?> class="js-hide-tr-EXCHANGE_EXTRAFEE">
        <th>기타교환비용 금액</th>
        <td class="form-inline">
            <?php if($layerMode == 'view') {?>
                <?=$data['extraData']?>원
            <?php } else {?>
                <input type="text" name="extraData" maxlength="20" value="" class="form-control js-number" maxlength="20">
            <?php }?>
        </td>
    </tr>
    <tr>
        <th>구매자에게 전하실<br> 말씀  <?php if($layerMode != 'view') {?><br>(<span class="js-contents-length">0</span>/500자)<?php }?></th>
        <td>
            <?php if($layerMode == 'view') {?>
                <?=nl2br($data['contents'])?>
            <?php } else {?>
                <textarea name="contents" rows="7" class="width100p form-control"></textarea>
            <?php }?>
        </td>
    </tr>
    </tbody>
</table>
<div class="notice-info">교환배송비 및 기타반품비용으로 처리된 금액은 네이버페이센터에서 확인하시기 바랍니다.
</div>
<script>
    $('[name=naverCode]').bind('change',function () {
        $('.js-hide-tr-EXCHANGE_DELIVERYFEE').hide();
        $('.js-hide-tr-EXCHANGE_EXTRAFEE').hide();

        if($(this).val() == 'EXCHANGE_DELIVERYFEE'){
            $('.js-hide-tr-EXCHANGE_DELIVERYFEE').show();
        }
        else if($(this).val() == 'EXCHANGE_EXTRAFEE'){
            $('.js-hide-tr-EXCHANGE_EXTRAFEE').show();
        }

    })

    $('[name=naverCode]').trigger('change');
</script>
