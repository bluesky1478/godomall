<style>
    .previewLayer{}
    .previewLayer > div{
        width: 100%;
        border-bottom: 1px solid #aaa;
    }
    .previewLayer .scroll{
        height:90px;
        width: 100%;
        overflow-y:auto;
        text-align: left;
    }
    .previewLayer > div > .table.table-cols{
        border-top: none;
    }
    .previewLayer > div > .table.table-cols > tbody > tr > th{
        background-color:#F6F6F6 !important;
    }
    .previewLayer > div > .table.table-cols > tbody > tr > th >div{
        color:#333;
    }
</style>
<div class="previewLayer">
    <div>
        <table class="table table-cols" style="margin-bottom:0;">
            <colgroup>
                <col class="width-lg">
            </colgroup>
            <tbody>
            <tr>
                <th>
                    <div>
                        주문일 : <?=$data['regDt']?><br>
                        환불사유 : <?=$data['handleReason']?><br>
                        환불상품 : <?=$data['goodsNm']?>
                    </div>
                </th>
            </tr>
            <tr>
                <td>
                    <div class="scroll">
                        <?php echo nl2br($data['handleDetailReason']); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right">
                    <?php if($data['handleDetailReasonShowFl'] == 'y') echo '고객에게 노출 중 입니다.' ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
