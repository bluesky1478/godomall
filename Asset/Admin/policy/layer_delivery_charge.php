<table class="table table-cols">
    <tbody>
    <tr>
        <th class="width-sm">배송비 유형</th>
        <td><?=$fixText?></td>
    </tr>
    <?php if (!$isProvider) { ?>
    <tr>
        <th class="width-sm">부가세율</th>
        <td><?=$taxFreeStr?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<?php if ($multipleDeliveryFl === true && $basic['fixFl'] != 'fixed') { ?>
    <div class="ly_dev_wrap">
        <div class="ly_btn <?= count($basic['deliveryMethodFlData']) == 2 ? 'two' : 'three'; ?>">
            <?php
            foreach ($basic['deliveryMethodFlData'] as $key => $val) {
                if (count($basic['deliveryMethodFlData']) == 2 && $basic['deliveryMethodFlKey'][$key] % 2 == 0) echo '<div class="row">';
                else {
                    if ($basic['deliveryMethodFlKey'][$key] % 3 == 0) echo '<div class="row">';
                }
                echo '<span class="delivery-method" onclick="selectDeliveryMethod(this, \'' . $key . '\')">' . $val . '</span>';
                if (count($basic['deliveryMethodFlData']) == 2 && $basic['deliveryMethodFlKey'][$key] % 2 == 1) echo '</div>';
                else {
                    if ($basic['deliveryMethodFlKey'][$key] % 3 == 2) echo '</div>';
                    else {
                        if (count($basic['deliveryMethodFlData']) - $basic['deliveryMethodFlKey'][$key] < 3 && ((count($basic['deliveryMethodFlData']) % 3 == 1 && $basic['deliveryMethodFlKey'][$key] % 3 == 0) || (count($basic['deliveryMethodFlData']) % 3 == 2 && $basic['deliveryMethodFlKey'][$key] % 3 == 1))) echo '</div>';
                    }
                }
            }
            ?>
        </div>
    </div>
    <script type="text/javascript">
        function selectDeliveryMethod(e, method) {
            $(e).parents(".ly_btn").find("span").removeClass("on");
            $(e).addClass("on");
            $('.delivery-method-tr').hide();
            $('.delivery-method-tr').filter('[data-method="' + method + '"]').show();
        }
        $(function(){
            $('.delivery-method').eq(0).trigger('click');
        })
    </script>
    <!--{ / }-->
<?php } ?>

<table class="table table-rows">
    <thead>
    <tr>
        <th>번호</th>
        <th>조건</th>
        <th>배송비</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if ($multipleDeliveryFl === true) {
        $sortNo = '';
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                if ($basic['fixFl'] == 'fixed' && empty($basic['deliveryMethodFlData'][$k]) === true) continue;
                ?>
                <tr class="text-center delivery-method-tr" data-method="<?=$k?>">
                    <td><?= $basic['fixFl'] != 'fixed' ? ++$sortNo[$k] : ++$sortNo; ?></td>
                    <td><?= $basic['fixFl'] != 'fixed' ? $v['condition'] : $basic['deliveryMethodFlData'][$k]; ?></td>
                    <td><?= gd_money_format($v['price']) ?>원</td>
                </tr>
            <?php
            }
        }
    } else {
        $sortNo = 1;
        foreach($data as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?= $sortNo++ ?></td>
                <td><?= $val['condition'] ?></td>
                <td><?= gd_money_format($val['price']) ?>원</td>
            </tr>
        <?php
        }
    }
    ?>
    </tbody>
</table>

<style>
    .ly_dev_wrap .ly_btn{display:table;margin:15px 0;border-collapse: collapse;}
    .ly_dev_wrap .ly_btn .row{display:table-row;}
    .ly_dev_wrap .ly_btn span{display: table-cell;width:50%;font-size:12px; color:#777; text-align:center;vertical-align:middle;border:1px solid #e7e7e7;box-sizing:border-box;padding:4px 10px 4px 10px;word-break: break-all;}
    .ly_dev_wrap .ly_btn.three span{width:33.33%;}
    .ly_dev_wrap .ly_btn span.on{position:relative;color:#fff;background-color:#bcbcbc;border:1px solid #bcbcbc;}
</style>