<!-- 배송정보 -->
<div class="page-header js-affix affix-top">
    <h3>배송정보</h3>
</div>

<?php if(trim($deliveryTraceData['invoiceNumber']) !== ''){ ?>
    <table class="table table-rows">
        <colgroup>
            <?php if(trim($deliveryTraceData['invoiceNumber']) !== ''){ ?><col /><?php } ?>
            <?php if(trim($deliveryTraceData['goodsNm']) !== ''){ ?><col /><?php } ?>
            <?php if(trim($deliveryTraceData['goodsCnt']) !== ''){ ?><col /><?php } ?>
            <?php if(trim($deliveryTraceData['sendName']) !== ''){ ?><col /><?php } ?>
            <?php if(trim($deliveryTraceData['acceptName']) !== ''){ ?><col /><?php } ?>
            <?php if(trim($deliveryTraceData['startPlace']) !== ''){ ?><col /><?php } ?>
            <?php if(trim($deliveryTraceData['endPlace']) !== ''){ ?><col /><?php } ?>
        </colgroup>
        <thead>
        <tr>
            <?php if(trim($deliveryTraceData['invoiceNumber']) !== ''){ ?><th>송장번호</th><?php } ?>
            <?php if(trim($deliveryTraceData['goodsNm']) !== ''){ ?><th>품명</th><?php } ?>
            <?php if(trim($deliveryTraceData['goodsCnt']) !== ''){ ?><th>수량</th><?php } ?>
            <?php if(trim($deliveryTraceData['sendName']) !== ''){ ?><th>보내는 분</th><?php } ?>
            <?php if(trim($deliveryTraceData['acceptName']) !== ''){ ?><th>받으시는 분</th><?php } ?>
            <?php if(trim($deliveryTraceData['startPlace']) !== ''){ ?><th>발송 영업소</th><?php } ?>
            <?php if(trim($deliveryTraceData['endPlace']) !== ''){ ?><th>도착 영업소</th><?php } ?>
        </tr>
        </thead>

        <tbody>
        <tr>
            <?php if(trim($deliveryTraceData['invoiceNumber']) !== ''){ ?><td class="ta-c"><?php echo $deliveryTraceData['invoiceNumber']; ?></td><?php } ?>
            <?php if(trim($deliveryTraceData['goodsNm']) !== ''){ ?><td class="ta-c"><?php echo $deliveryTraceData['goodsNm']; ?></td><?php } ?>
            <?php if(trim($deliveryTraceData['goodsCnt']) !== ''){ ?><td class="ta-c"><?php echo $deliveryTraceData['goodsCnt']; ?></td><?php } ?>
            <?php if(trim($deliveryTraceData['sendName']) !== ''){ ?><td class="ta-c"><?php echo $deliveryTraceData['sendName']; ?></td><?php } ?>
            <?php if(trim($deliveryTraceData['acceptName']) !== ''){ ?><td class="ta-c"><?php echo $deliveryTraceData['acceptName']; ?></td><?php } ?>
            <?php if(trim($deliveryTraceData['startPlace']) !== ''){ ?><td class="ta-c"><?php echo $deliveryTraceData['startPlace']; ?></td><?php } ?>
            <?php if(trim($deliveryTraceData['endPlace']) !== ''){ ?><td class="ta-c"><?php echo $deliveryTraceData['endPlace']; ?></td><?php } ?>
        </tr>
        </tbody>
    </table>
<?php } else { ?>
    <div>배송정보가 없습니다.</div>
<?php } ?>

<!-- 배송정보 -->

<!-- 배송상태 -->
<div class="page-header mgt55">
    <h3>배송상태</h3>
</div>

<table class="table table-rows">
    <colgroup>
        <?php if($deliveryTraceData['stepStatusUseFl'] === 'y'){ ?><col /><?php } ?>
        <?php if($deliveryTraceData['stepRegDateUseFl'] === 'y'){ ?><col /><?php } ?>
        <?php if($deliveryTraceData['stepLocationUseFl'] === 'y'){ ?><col /><?php } ?>
        <?php if($deliveryTraceData['stepTelUseFl'] === 'y'){ ?><col /><?php } ?>
    </colgroup>
    <thead>
        <tr>
            <?php if($deliveryTraceData['stepStatusUseFl'] === 'y'){ ?><th>처리현황</th><?php } ?>
            <?php if($deliveryTraceData['stepRegDateUseFl'] === 'y'){ ?><th>처리일</th><?php } ?>
            <?php if($deliveryTraceData['stepLocationUseFl'] === 'y'){ ?><th>담당 점소</th><?php } ?>
            <?php if($deliveryTraceData['stepTelUseFl'] === 'y'){ ?><th>연락처</th><?php } ?>
        </tr>
    </thead>

    <tbody>
    <?php
    if($deliveryTraceData['stepCount'] > 0){
        foreach($deliveryTraceData['step'] as $key => $valueArray){
    ?>
        <tr>
            <?php if($deliveryTraceData['stepStatusUseFl'] === 'y'){ ?><td class="ta-c"><?php echo $valueArray['status']; ?></td><?php } ?>
            <?php if($deliveryTraceData['stepRegDateUseFl'] === 'y'){ ?><td class="ta-c"><?php echo $valueArray['regDate']; ?></td><?php } ?>
            <?php if($deliveryTraceData['stepLocationUseFl'] === 'y'){ ?><td class="ta-c"><?php echo $valueArray['location']; ?></td><?php } ?>
            <?php if($deliveryTraceData['stepTelUseFl'] === 'y'){ ?><td class="ta-c"><?php echo $valueArray['tel']; ?></td><?php } ?>
        </tr>
    <?php
        }
    } else {
    ?>
        <tr>
            <td class="ta-c" colspan="<?php echo $deliveryTraceData['colspan']; ?>"> 내용이 없습니다. </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<!-- 배송상태 -->
