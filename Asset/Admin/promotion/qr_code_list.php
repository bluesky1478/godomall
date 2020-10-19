<form id="frmList" action="" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="button" value="QR코드 등록" class="btn btn-red-line checkRegister"/>
    </div>
    <div class="table-title">
        QR코드 기본설정
    </div>
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width-2xs">
                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
            </th>
            <th class="width-2xs">번호</th>
            <th>QR코드명</th>
            <th>등록일</th>
            <th>내용수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $val) {
                ?>
                <tr class="center">
                    <td>
                        <input type="checkbox" name="chk[]" value="<?php echo $val['sno']; ?>"/>
                    </td>
                    <td class="font-num"><?php echo $page->idx--; ?></td>
                    <td><?php echo $val['qrName']; ?></td>
                    <td class="font-date"><?php echo substr($val['regDt'], 2, 8); ?></td>
                    <td><a href="#" class="btn btn-sm btn-white rowButton" name="rowEdit">수정</a></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="5">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button class="btn btn-white checkDelete" type="button">선택 삭제</button>
        </div>
    </div>
    <div class="center"><?php echo $page->getPage(); ?></div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('a[name=rowEdit]').on('click', function (e) {
            var sno = $(e.target).closest('tr').find('input[type=checkbox]').val();
            top.window.location.href = '../promotion/qr_code_edit.php?sno=' + sno;
        });

        $('input.checkRegister').on('click', function (e) {
            top.window.location.href = '../promotion/qr_code_edit.php';
        });

        $('button.checkDelete').on('click', function (e) {
            if ($(':checkbox:checked').length == 0) {
                alert('선택된 코드가 없습니다.');
                return;
            }
            dialog_confirm('선택한 코드를 삭제하시겠습니까?\n삭제된 코드는 복구하실 수 없습니다.', function () {
                $('input[name="mode"]').val('delete');
                var params = $('#frmList').serializeArray();
                post_with_reload('../promotion/qr_code_ps.php', params);
            });
        });
    });
    //-->
</script>
