<form id="frmOrderStatus" name="frmOrderStatus" action="order_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="updateOrderStatusGlobal"/>

    <div class="page-header js-affix">
        <h3>해외몰 주문 상태 관리</h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red">
        </div>
    </div>

    <div class="table-title">
        주문 상태 관리
    </div>
    <div class="table-responsive">
        <table class="table table-cols orderstatus-board table-layout-fixed">
            <colgroup>
                <col class="width2p"/>
                <col class="width7p"/>
                <col class="width7p"/>
                <?php foreach ($mallList as $key => $value) {?>
                    <col class="width7p"/>
                <?php } ?>
            </colgroup>
            <thead>
            <tr>
                <th rowspan="2">구분</th>
                <th rowspan="2">기준상태</th>
                <th rowspan="2">사용여부</th>
                <th colspan="<?php echo $mallCnt; ?>">쇼핑몰페이지 노출이름</th>
            </tr>
            <tr>
                <?php foreach ($mallList as $key => $mall) {?>
                <th>
                    <span class="flag flag-32 flag-<?php echo $mall['domainFl']?> middle"></span>
                    <?php echo $mall['mallName']; ?>
                </th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td align="center" rowspan="<?php echo $orderStepCnt; ?>">주<br />문<br />상<br />태<br />별<br /><br />설<br />정</td>
                    <?php
                    foreach ($orderStep as $key => $value) {
                        foreach ($policy[$key] as $k => $v) {
                            if (is_array($v) === false) continue;
                            ?>
                            <td align="center"><?php echo $v['user']; ?></td>
                            <td align="center"><?php echo $useFl[$v['useFl']]; ?></td>
                            <?php foreach ($mallList as $mallSno => $mall) { ?>
                                <td align="center">
                                    <?php
                                    if ($mallSno == $defaultMallNumber) {
                                        echo $v['user'];
                                    } else {
                                    ?>
                                        <input type="text" name="orderStep[<?php echo $mallSno; ?>][<?php echo $key; ?>][<?php echo $k; ?>]" value="<?php echo $policyGlobal[$mallSno][$key][$k]['user']; ?>" />
                                    <?php } ?>
                                </td>
                            <?php
                            }
                            if ($key == 0 && $k == 0) {
                                echo '</tr><tr>';
                            }
                        }
                    }
                    ?>
                </tr>
                <tr>
                    <td align="center" rowspan="<?php echo $cancelStepCnt; ?>">취<br />소<br />상<br />태<br />별<br /><br />설<br />정</td>
                    <?php
                    foreach ($cancelStep as $key => $value) {
                        foreach ($policy[$key] as $k => $v) {
                            if (is_array($v) === false) continue;
                            ?>
                            <td align="center"><?php echo $v['user']; ?></td>
                            <td align="center"><?php echo $useFl[$v['useFl']]; ?></td>
                            <?php foreach ($mallList as $mallSno => $mall) { ?>
                                <td align="center">
                                    <?php
                                    if ($mallSno == $defaultMallNumber) {
                                        echo $v['user'];
                                    } else {
                                        ?>
                                        <input type="text" name="orderStep[<?php echo $mallSno; ?>][<?php echo $key; ?>][<?php echo $k; ?>]" value="<?php echo $policyGlobal[$mallSno][$key][$k]['user']; ?>" />
                                    <?php } ?>
                                </td>
                                <?php
                            }
                            if ($key == 0 && $k == 0) {
                                echo '</tr><tr>';
                            }
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>

</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

    });
    //-->
</script>
