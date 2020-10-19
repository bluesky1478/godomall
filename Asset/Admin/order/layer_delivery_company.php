<table class="table table-rows">
    <thead>
    <tr>
        <th class="width-2xs">번호</th>
        <th class="width-2xs">배송업체번호</th>
        <th>배송업체명</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (is_array($data)) {
        $num = 1;
        $arrUseFl = ['y' => '사용', 'n' => '미사용'];
        $arrBgColor = ['y' => '', 'n' => 'background:#f0f0f0'];
        $checkUseFl = 'y';
        foreach ($data as $key => $val) {
            if ($val['useFl'] == 'n') {
                continue;
            }
            ?>
            <tr class="text-center move-row<?php if ($val['useFl'] == 'n') { ?> active<?php } ?>">
                <td>
                    <?php echo $num; ?>
                    <input type="hidden" name="sno[]" value="<?php echo $val['sno']; ?>"/>
                </td>
                <td><?php echo $val['sno']; ?></td>
                <td><?=$val['companyName']?></td>
            </tr>
            <?php
            $num++;
        }
    }
    ?>
</table>
<div class="table-btn">
    <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
</div>

<script type="text/javascript">
    $(function(){
        $('input[name=completeFl]').click(function(e){
            $.get('../order/layer_order_invoice.php?groupCd=<?=$groupCd?>&completeFl=' + $(this).val(), function(data) {
                $('.modal-body').empty().html(data);
            });
        });
    });
</script>
