<form method="post" name="frmSuperAdminMemo" id="frmSuperAdminMemo" action="../order/bankda_match_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="bankdaAdminMemo">
    <input type="hidden" name="writeMode" value="<?= $writeMode; ?>">
    <input type="hidden" name="bankdaNo" value="<?= $bankdaNo ?>">
    <textarea name="adminMemo" class="form-control" rows="6" maxlength="500"><?= str_replace(['\r\n', '\n'], chr(10), gd_htmlspecialchars_stripslashes($data['adminMemo'])); ?></textarea>
    <?php if($data['regDt']) {
        $finalDate = ($data['modDt']) ? $data['modDt'] : $data['regDt'];
        ?>
        <div style='text-align:right;color:#AEAEAE;'>최종수정 : <?= $finalDate; ?> | <?= $data['managerId']; ?></div>
        <?php
    }
    ?>
    <div class="text-center mgt10">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <?php if(!$isProvider) {
            if(empty($data['adminMemo']) === false) {
                ?>
                <button type="submit" class="btn btn-lg btn-black">수정</button>
                <?php
            } else {
                ?>
                <button type="submit" class="btn btn-lg btn-black">저장</button>
            <?php }
        } ?>
    </div>
</form>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 폼 체크 후 전송
        $('#frmSuperAdminMemo').validate({
            dialog: false,
            rules: {
                adminMemo: 'required'
            },
            messages: {
                adminMemo: '입금내역메모를 입력해주세요.'
            },
            submitHandler: function (form) {
                // 현재 리스트에 있는 값 업데이트
                $.each($('.js-bankda-admin-memo'), function (key, val) {
                    if($(val).data('bankda-no') === <?=$bankdaNo?>) {
                        $(val).removeClass('btn-white').addClass('btn-gray').popover({
                            trigger: 'hover',
                            container: '#content',
                            html: true
                        });

                        var popover = $(val).attr('data-original-title', '입금내역메모').attr('data-content', $('textarea[name=adminMemo]').val().replace(/\n/g, '<br>'));
                        // content redraw
                        popover.data('bs.popover').setContent();

                        return false; // break
                    }
                });

                form.target = 'ifrmProcess';
                form.submit();
            }
        });
    });
    //-->
</script>
