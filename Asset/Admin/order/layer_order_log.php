<div class="table-title gd-help-manual">
    <?= $goodsNm ?>
</div>
<table class="table table-rows">
    <thead>
    <tr>
        <th>일자</th>
        <th>처리자</th>
        <?php if ($logType == 'all') { ?>
            <th>상품명</th>
        <?php } ?>
        <th>변경상태</th>
        <th>변경 사유</th>
    </tr>
    </thead>
    <?php
    if (empty($orderLog) === false) {
        foreach ($orderLog as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?php echo gd_date_format('Y-m-d H:i', $val['regDt']); ?></td>
                <td>
                    <div><?php if (empty($val['managerId']) === false) {
                            echo $val['managerId'];
                            if($val['managerNm']){
                                echo '(' . $val['managerNm'] . ')'.$val['deleteText'];
                            }
                        } ?></div>
                    <div class="text-muted"><?php echo $val['managerIp']; ?></div>
                </td>
                <?php if ($logType == 'all') { ?>
                    <td><?php echo $val['goodsNm']; ?></td>
                <?php } ?>
                <td class="text-left"><?php echo $val['logCode01']; ?> > <?php echo $val['logCode02']; ?></td>
                <td class="text-left"><?php echo $val['logDesc']; ?></td>
            </tr>
            <?php
        }
    }
    ?>
</table>
<div class="text-center">
    <button type="button" class="btn btn-lg btn-black js-layer-close">닫기</button>
</div>

<?php if ($logType == 'one') { ?>
<script type="text/javascript">
<!--
$('.bootstrap-dialog-title').html('주문 로그 보기 : <?php echo gd_htmlspecialchars_addslashes(gd_remove_only_tag($val['goodsNm']));?>');
//-->
</script>
<?php } ?>
