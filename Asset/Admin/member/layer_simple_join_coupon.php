<div id="layerSimpleJoinCoupon">
<?php if($page->recode['total'] < $page->recode['amount']) {?><p class="notice-danger">발급 이후 삭제된 쿠폰 정보는 노출되지 않습니다. 삭제된 쿠폰정보는 엑셀다운로드를 통해 확인 가능합니다.</p><?php } ?>
<table class="table table-rows">
    <thead>
    <tr>
        <th>번호</th>
        <th>쿠폰명</th>
        <th>사용기간</th>
        <th>쿠폰유형</th>
        <th>혜택구분</th>
        <th>상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($list) === false) {
        $i = 0;
        foreach ($list as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?= $page->idx--; ?></td>
                <td><?= $val['couponNm']; ?></td>
                <td><?= $val['useEndDate']; ?></td>
                <td><?= $val['couponUseType']; ?></td>
                <td><?= $val['couponKindType']?><br>(<?= $val['couponBenefit']; ?>)</td>
                <td>
                    <?php
                    if ($data[$key]['memberCouponUsable'] == 'YES') {
                        echo '사용가능';
                    } else if ($data[$key]['memberCouponUsable'] == 'USE_CART') {
                        echo '장바구니사용';
                    } else if ($data[$key]['memberCouponUsable'] == 'USE_ORDER') {
                        echo '주문사용';
                    } else if ($data[$key]['memberCouponUsable'] == 'EXPIRATION_START_PERIOD') {
                        echo '사용전';
                    } else if ($data[$key]['memberCouponUsable'] == 'EXPIRATION_END_PERIOD') {
                        echo '사용만료';
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="5" class="no-data">쿠폰이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });

    });

    function layer_list_search(pagelink) {

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }

        var parameters = {
            'pagelink': pagelink,
            'memNo': '<?php echo $memNo?>',
            'amount': '<?php echo $amount?>'
        };

        $.post('../member/layer_simple_join_coupon.php', parameters, function (data) {
            $('#layerSimpleJoinCoupon').html(data);
        });
    }

    //-->
</script>
