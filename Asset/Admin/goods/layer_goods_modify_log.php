<div style="height:500px;overflow-x:auto">
    <table class="table table-rows table-fixed no-title-line">
        <tr>
            <th class="width10p">등록일시</th>
            <th class="width10p">수정일시</th>
            <th class="width7p">관리자</th>
            <th class="width10p">분류</th>
            <th>수정 전</th>
            <th>수정 후</th>
        </tr>
        <?php if($data) { ?>
            <?php foreach($data as $k => $v ) {
                if(!$v['updateDataSet']) continue;
            ?>
                <tr>
                    <td style="height:100%;"><?=$regDt?></td>
                    <td><?=$v['regDt']?></td>
                    <td style="word-break:break-all;"><?=$v['managerId']?></td>
                    <td><?=$modeList[$v['mode']]?></td>
                    <td style="word-wrap: break-word;"><?=$v['prevDataSet']?></td>
                    <td style="word-wrap: break-word;"><?=$v['updateDataSet']?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td class="no-data" colspan="6">변경 내역이 없습니다.</td>
            </tr>
        <?php } ?>
    </table>
</div>
