<form id="frmSetup" action="../policy/member_ps.php" method="post">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        이메일 인증
    </div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
            </colgroup>
            <tr>
                <th>사용설정</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="emailFl" value="y" <?= $checked['emailFl']['y'] ?>>
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="emailFl" value="n" <?= $checked['emailFl']['n'] ?>>
                        사용안함
                    </label>
                    <div class="notice-info notice-sm">회원정보에 등록된 이메일 주소로 비밀번호 찾기 인증번호를 발송합니다.</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        휴대폰번호 인증
    </div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
            </colgroup>
            <tr>
                <th>사용설정</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="smsFl" value="y" <?= $checked['smsFl']['y'] ?>>
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="smsFl" value="n" <?= $checked['smsFl']['n'] ?>>
                        사용안함
                    </label>
                    <div class="notice-info notice-sm">회원정보에 등록된 휴대폰 번호로 비밀번호 찾기 인증번호를 발송합니다.</div>
                    <div class="notice-info notice-sm">휴대폰 인증은 SMS 잔여포인트가 없으면 노출되지 않습니다. (잔여포인트 :
                        <span class="number text-darkred"><?= number_format(gd_get_sms_point(), 1); ?></span>
                        ) <a target="_blank" href="../member/sms_charge.php" class="btn-link">SMS포인트 충전하기</a>>
                    </div>
                    <div class="notice-info notice-sm">자동 SMS 설정 또는 카카오 알림톡 설정에서 ‘비밀번호 찾기 인증번호’ 항목을 자동발송으로 설정해야 인증번호가 발송됩니다.
                        <a target="_blank" href="../member/sms_auto.php" class="btn-link">SMS발송 상세설정</a>>
                        <a target="_blank" href="../member/kakao_alrim_setting.php" class="btn-link">카카오 알림톡 상세설정</a>>
                    </div>
                    <div class="notice-info notice-sm">회원정보에 휴대폰 정보가 등록되지 않은 회원에게는 노출되지 않습니다.</div>
                </td>
            </tr>
        </table>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var $frmSetup = $('#frmSetup');
        $frmSetup.validate({
            submitHandler: function (form) {
                var data = $(form).serializeArray();
                data.push({name: 'mode', value: 'password_find'});
                post_with_reload('../policy/member_ps.php', data);
            }
        });
    });
    //-->
</script>
