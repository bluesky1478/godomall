<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="button" value="외부 스크립트 등록" class="btn btn-red-line js-out-side-script-register"/>
</div>

<form name="frmOutSideScript" id="frmOutSideScript" target="ifrmProcess" action="out_side_script_ps.php" method="post">
    <input type="hidden" name="mode" value="delete">
    <div>
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="chk"/></th>
                <th class="width5p">번호</th>
                <?php if ($mallCnt > 1) { ?>
                <th class="width10p">상점 구분</th>
                <?php } ?>
                <th>서비스명</th>
                <th class="width10p">등록일</th>
                <th class="width10p">등록자</th>
                <th class="width10p">사용설정</th>
                <th class="width15p">수정</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (count($data) > 0) {
                $number = 1;
                foreach ($data as $key => $val) {
                    if ($val['outSideScriptUse'] == 'y') {
                        $outSideScriptUse = '사용중';
                    } else if ($val['outSideScriptUse'] == 'n') {
                        $outSideScriptUse = '사용안함';
                    } else if ($val['outSideScriptUse'] == 't') {
                        $outSideScriptUse = '테스트모드';
                    }
                    if (empty($val['mallSno']) === true) {
                        $mallSno = 1;
                    } else {
                        $mallSno = $val['mallSno'];
                    }
            ?>
                <tr align="center">
                    <td><input type="checkbox" name="chk[<?= $key; ?>]" value="<?= $val['outSideScriptNo']; ?>" /></td>
                    <td><?= number_format($number); ?></td>
                    <?php if ($mallCnt > 1) { ?>
                    <td>
                        <span class="va-m flag flag-32 flag-<?php echo $mallList[$mallSno]['domainFl']; ?>"></span>
                        <?php echo $mallList[$mallSno]['mallName']; ?>
                        <input type="hidden" name="mallSno[<?= $key; ?>]" value="<?php echo $mallSno; ?>" />
                    </td>
                    <?php } ?>
                    <td><?= $val['outSideScriptServiceName']; ?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                    <td><?= $val['managerNm']; ?><br/>(<?= $val['managerId']; ?>)</td>
                    <td><?= $outSideScriptUse; ?></td>
                    <td><a href="./out_side_script_register.php?<?= (empty($val['mallSno']) === false) ? 'mallSno=' . $val['mallSno'] . '&' : ''?>outSideScriptNo=<?= $val['outSideScriptNo']; ?>" class="btn btn btn-white btn-xs">수정</a></td>
                </tr>
            <?php
                    $number++;
                }
            } else {
                ?>
                <tr align="center">
                    <td colspan="7">등록된 외부스크립트가 없습니다.</td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn btn-white js-btn-delete"/>선택삭제</button>
            </div>
        </div>
</form>

</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 선택한 삭제
        $('.js-btn-delete').bind('click', function () {
            if ($("input[name^='chk[']:checked").length < 1) {
                alert('삭제할 외부스크립트를 선택해주세요.');
                return;
            }

            dialog_confirm('선택하신 외부스크립트를 정말 삭제 하시겠습니까?\n\n삭제된 외부스크립트는 복구 되지 않습니다.', function (data) {
                if (data) {
                    $('input[name=mode]').val('delete');
                    $('#frmOutSideScript').submit();
                }
            });
        });
        $('.js-out-side-script-register').click(function () {
<?php
if (count($data) >= 10) {
?>
            alert('외부 스크립트는 최대 10개까지 등록가능합니다.');
            return false;
<?php
} else {
?>
            location.href="out_side_script_register.php";
<?php
}
?>
        });
    });
    //-->
</script>
