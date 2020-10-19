<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <tr>
        <th>발송지연사유</th>
        <td>
            <?php if($layerMode == 'view') {?>
                <?=$data['reason']?>
            <?php } else {?>
                <?=gd_select_box('naverCode', 'naverCode', $codeList); ?>
            <?php }?>
        </td>
    </tr>
    <tr>
        <th>발송기한</th>
        <td class="form-inline">
            <?php if($layerMode == 'view') {?>
                <?=$data['date']?>
            <?php } else {?>
                <div class="input-group js-datetimepicker">
                    <input type="text" name="date" maxlength="20" value="" class="form-control" maxlength="20">
                    <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                </div>
                <div class="notice-danger">발송기한은 수정되지 않으니, 발송 가능한 일자를 정확히 선택해주세요.</div>
            <?php }?>
        </td>
    </tr>
    <tr>
        <th>발송지연 상세사유<?php if($layerMode != 'view') {?><br>(<span class="js-contents-length">0</span>/500자)<?php }?></th>
        <td> <?php if($layerMode == 'view') {?>
                <?=nl2br($data['contents'])?>
            <?php } else {?>
                <textarea name="contents" rows="7" class="width100p form-control"></textarea>
            <?php }?>
        </td>
    </tr>
    </tbody>
</table>
<?php if($layerMode != 'view') {?>
    <div class="">
        <div class="notice-info">발송지연 안내 처리는 1회만 가능하며, 지연사유는 구매자에게 안내가 됩니다.</div>
        <div class="notice-info">발송기한 경과시까지 발송처리가 되지 않으면 구매자 취소요청 시 즉시 환불처리가 진행됩니다.</div>
        <div class="notice-info">입력하신 내용은 처리 후 수정이 불가능합니다. 발송기한을 정확하게 입력해주세요.</div>
    </div>
<?php }?>

