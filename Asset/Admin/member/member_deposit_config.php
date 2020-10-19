<form id="frmDepositConfig" name="frmDepositConfig" action="member_ps.php" method="post">
    <input type="hidden" name="mode" value="deposit_config"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        예치금 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="payUsableFl" id="payUsableFlY" value="y" <?php echo $checked['payUsableFl']['y']; ?> data-target=".payUsableFl">
                    사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="payUsableFl" value="n" <?php echo $checked['payUsableFl']['n']; ?>>
                    사용안함
                </label>
            </td>
        </tr>
        <tr class="payUsableFl">
            <th class="require">쇼핑몰 노출 이름</th>
            <td>
                <input type="text" name="name" value="<?php echo $data['name']; ?>" class="form-control js-maxlength width-lg"/>
            </td>
        </tr>
        <tr class="payUsableFl">
            <th class="require">쇼핑몰 노출 단위</th>
            <td>
                <input type="text" name="unit" value="<?php echo $data['unit']; ?>" class="form-control js-maxlength width-xs"/>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#frmDepositConfig").validate({
            dialog: false,
            submitHandler: function (form) {
                // 기본값 체크
                if ($('input[name="name"]').val() == '') {
                    $('input[name="name"]').val('예치금');
                }
                if ($('input[name="unit"]').val() == '') {
                    $('input[name="unit"]').val('원');
                }
                if ($('input[name="name"]').val().length > 10 || $('input[name="unit"]').val().length > 10) {
                    alert('최대 10자리 이상 입력하실 수 없습니다.');
                    return false;
                }
                // 예치금 보유 고객이 한명이라도 있는 경우 실행
                <?php if ($data['isDepositMember'] === true) { ?>
                if ($('input[name="payUsableFl"]:checked').val() == 'n') {
                    dialog_confirm('계정에 예치금이 있는 회원이 있습니다.\n예치금 사용을 할 수 없도록 설정하시는 경우 회원과의 분쟁소지가 될 수 있습니다.\n예치금 사용불가로 설정하시겠습니까?', function (result) {
                        if (result) {
                            form.target = 'ifrmProcess';
                            form.submit();
                        }
                    });
                    return;
                }
                <?php } ?>
                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        $(':radio[name="payUsableFl"]').change(function () {
            if ($('#payUsableFlY').prop('checked')) {
                $('.payUsableFl').removeClass('display-none');
            } else {
                $('.payUsableFl').addClass('display-none');
            }
        }).trigger('change');
    });
    //-->
</script>
