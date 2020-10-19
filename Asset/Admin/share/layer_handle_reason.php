<h4><b><?php echo $goodsNm; ?></b></h4>
<table class="table table-rows">
    <colgroup>
        <col class="width-sm"/>
        <col/>
        <col class="width-sm"/>
        <col/>
    </colgroup>
    <?php if ($type == 'admin') { ?>
        <tr>
            <th class="th">작성일시</th>
            <td><?php echo $data['modDt']; ?></td>
            <th class="th">작성자</th>
            <td>
                <?php echo $managerInfo['managerNm']; ?>
                <?php if (!empty($managerInfo['managerId'])){
                    ?><?php echo '<br />(' . $managerInfo['managerId'] . ')'; ?><?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="4"><?php echo nl2br($data['adminHandleReason']); ?></td>
        </tr>
    <?php } else { ?>
        <tr>
            <th class="th">메모 내용</th>
            <td colspan="3"><?php echo nl2br($data['userHandleDetailReason']); ?></td>
        </tr>
        <?php if (in_array($handleMode, ['b', 'r']) === true) { ?>
        <tr>
            <th class="th">환불 계좌정보</th>
            <td colspan="3">
                <?php
                if (empty($data['userRefundBankName']) === false) {
                    echo $data['userRefundBankName'] . ' / ' . $data['userRefundAccountNumber'] . ' / ' . $data['userRefundDepositor'];
                }
                ?>
            </td>
        </tr>
        <?php } ?>
    <?php } ?>
</table>
<div class="center">
    <input type="button" value="닫기" class="btn btn-lg btn-white js-layer-close" />
</div>