<style>
    .previewLayer{}
    .previewLayer > div{
        width: 100%;
        border-bottom: 1px solid #aaa;
    }
    .previewLayer .scroll{
        height:300px;
        overflow-y:auto;
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

    .previewLayer .head {
        padding:15px;
    }

    .previewLayer .contents-length {
        text-align:right;
        padding:5px
    }
</style>
<div class="previewLayer">
    <div class="head">
        <div style="float:left">
            <img src="<?=$data['goodsImageSrc']?>" width="100"  style="border:1px solid #ccc;max-height:150px">
        </div>
        <div style="float:left;vertical-align:top;padding-left:15px">
            <div><b><?=$data['goodsNm']?></b><br><?=gd_currency_display($data['goodsPrice'])?></div>
        </div>
        <div class="clear-both"></div>
    </div>
    <div class="scroll">
        <table class="table table-cols" style="margin-bottom:0;">
            <colgroup>
                <col class="width-lg">
            </colgroup>
            <tbody>
            <tr>
                <th>
                    <?php foreach($data['addFormData'] as $key=>$val) {?>
                        <div class="pdt5"><?=$key?> : <?=$val?></div>
                    <?php }?>
                    <?php foreach($data['option'] as $val) {?>
                    <div class="pdt5"><?=$val['name']?> : <?=$val['value']?></div>
                    <?php }?>
                </th>
            </tr>
            <tr>
                <td style="border-bottom:none">
                    <?= $data['viewContents'] ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="contents-length"><?=mb_strlen($data['viewContents'])?>/<?=$formCheckMinLength?></div>
    <div>
        <?php
        if ($data['uploadedFile']) {
            ?>
            <ul style="padding:5px">
                <?php foreach ($data['uploadedFile'] as $val) { ?>
                    <li style="display: inline-block;">
                        <img src="<?=$val['thumSrc']?>" width="80" height="80">
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
    </div>
</div>
