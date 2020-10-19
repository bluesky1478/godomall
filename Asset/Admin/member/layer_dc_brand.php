<div class="table-scroll">
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width10p"/>
            <col class="width100p"/>
            <col class="width30p"/>
            <col class="width30p"/>
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>브랜드</th>
            <th>할인율</th>
        </tr>

        <?php
        $dcBrandInfo = json_decode($groupData['dcBrandInfo'], 'y');
        $tableNo = count($dcBrandInfo['cateCd']);
        foreach ($dcBrandInfo['cateCd'] as $key => $getData) {
            ?>
            <tr class="text-center">
                <td class="form-inline"><?php echo $tableNo--;?></td>
                <td class=""form-inline"><?=$getBrandData['data'][$getData]?></td>
                <td><?=($dcBrandInfo['goodsDiscount'][$key]) ? $dcBrandInfo['goodsDiscount'][$key] : 0?>%</td>
            </tr>
            <?php
        }
        ?>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<div class="text-center">
    <button class="btn btn-black js-layer-close">닫기</button>
</div>
<script type="text/javascript">
    $('js-layer-close').click(layer_close);
</script>