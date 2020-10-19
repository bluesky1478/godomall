<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <tr>
        <th>반품거부 상세사유<?php if($layerMode != 'view') {?><br>(<span class="js-contents-length">0</span>/500자)<?php }?></th>
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
<div class="">
    <div class="notice-info">구매자에게 먼저 반품 불가사유를 안내하신 후 반품거부 처리를 진행해 주십시오.
    </div>
    <div class="notice-info">반품거부 사유는 구매자의 네이버페이 결제내역에 별도로 안내되므로, 상세히 입력해 주십시오.
    </div>
</div>
