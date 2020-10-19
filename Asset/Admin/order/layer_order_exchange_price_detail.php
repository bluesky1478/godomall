<table class="table table-rows">
    <colgroup>
        <col style="width: 150px;" />
        <col />
    </colgroup>
    <tr>
        <th>총 교환 결제금액(차액)</th>
        <td><?=((float)$exchangeHandleData['ehDifferencePrice']) ? gd_currency_display($exchangeHandleData['ehDifferencePrice']*-1) : gd_currency_display(0)?></td>
    </tr>
    <tr>
        <th>취소된 배송비</th>
        <td><?=gd_currency_display($exchangeHandleData['ehCancelDeliveryPrice'])?></td>
    </tr>
    <tr>
        <th>추가된 배송비</th>
        <td><?=gd_currency_display($exchangeHandleData['ehAddDeliveryPrice'])?></td>
    </tr>

    <?php if($exchangeHandleData['ehDifferencePrice'] < 0){ ?>
        <tr>
            <th>입금자명</th>
            <td><?=$exchangeHandleData['ehSettleName']?></td>
        </tr>
        <tr>
            <th>결제정보</th>
            <td><?=$exchangeHandleData['ehSettleBankAccountInfo']?></td>
        </tr>
    <?php } else if ($exchangeHandleData['ehDifferencePrice'] > 0) { ?>
        <tr>
            <th>환불수단</th>
            <td><?=$exchangeRefundMethodName[$exchangeHandleData['ehRefundMethod']]?></td>
        </tr>
        <?php if($exchangeHandleData['ehRefundMethod'] === 'bank'){ ?>
        <tr>
            <th>환불정보</th>
            <td><?=$exchangeHandleData['ehRefundBankName']?> / <?=$exchangeHandleData['ehRefundBankAccountNumber']?> / <?=$exchangeHandleData['ehRefundName']?></td>
        </tr>
        <?php } ?>
    <?php } else {?>

    <?php } ?>
</table>
<div class="text-center">
    <button type="button" class="btn btn-lg btn-black js-layer-close">닫기</button>
</div>
