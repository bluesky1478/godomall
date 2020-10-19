<form name="formPassword" id="formPassword" method="post" action="../policy/manage_password_ps.php">
    <input type="hidden" name="mode" value="changePassword"/>
    <div id="adminSecuritySmsInformation">
        <div class="text-left mgb20">
            <?= $managerNm ?>님은 장기간 쇼핑몰 관리 비밀번호를 변경하지 않고 사용중입니다.
        </div>
        <div class="text-left mgb30">
            <p>방송통신위원회 고시 [개인정보의 기술적ㆍ관리적 보호조치 기준]에 따라, 개인정보취급자는 6개월에 1회 이상 비밀번호를 변경하여야 합니다.</p>
            <p>쇼핑몰 보안 및 운영자의 개인정보 보호를 위해 비밀번호를 변경하시기 바랍니다.</p>
        </div>
        <div class="form-inline">
            <table class="table table-cols">
                <tr>
                    <th>현재 비밀번호</th>
                    <td>
                        <label for="oldPassword"></label>
                        <input type="password" id="oldPassword" name="oldPassword"/>
                    </td>
                </tr>
                <tr>
                    <th>새 비밀번호</th>
                    <td>
                        <label for="password"></label>
                        <input type="password" id="password" name="password"/>
                    </td>
                </tr>
                <tr>
                    <th>새 비밀번호 확인</th>
                    <td>
                        <label for="passwordRe"></label>
                        <input type="password" id="passwordRe" name="passwordRe"/>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="text-center mgb30">
        <p>* 쇼핑몰 관리 비밀번호는 <b><u>영문대문자/영문소문자/숫자/특수문자 중 2가지 이상 조합하여 10자~16자</u></b> 로 입력하세요.
        </p>
        <p>* 비밀번호는 주기적으로 변경하고, 변경 시 타인이 추측하기 어려운 조합의 비밀번호를 사용하시기 바랍니다.</p>
        <p>* 연속적인 숫자나 생일, 전화번호 등 추측하기 쉬운 개인정보 및 아이디와 비슷한 비밀번호는 사용하지 않는 것을 권합니다.</p>
    </div>
    <div class="table-btn">
        <button type="button" class="btn btn-lg btn-white js-layer-clear">다시 입력</button>
        <button type="button" class="btn btn-lg btn-white js-layer-later">다음에 변경하기</button>
        <button type="button" class="btn btn-lg btn-gray js-layer-change">비밀번호 변경</button>
    </div>
</form>
<script type="text/javascript">
    <!--
    $(function () {
        $('#formPassword').validate({
            submitHandler: function (form) {
                console.log(form);
                $(':hidden[name="mode"]').val('changePassword');
                $.post('../policy/manage_password_ps.php', $('#formPassword').serializeArray(), function (data) {
                    if (data.result == 'ok') {
                        dialog_alert(data.message, '확인', {location: data.url});
                    } else {
                        process_close();
                        alert(data.message);
                    }
                });
            },
            rules: {
                "oldPassword": "required",
                "password": "required",
                "passwordRe": {
                    "equalTo": "[name=password]"
                }
            },
            messages: {
                "oldPassword": "현재 비밀번호를 입력해 주세요.",
                "password": "새 비밀번호를 입력해 주세요.",
                "passwordRe": {
                    "equalTo": "새 비밀번호와 확인 번호가 다릅니다."
                }
            }
        });

        $('.js-layer-change').click(function () {
            $('#formPassword').submit();
        });

        $('.js-layer-clear').click(function (e) {
            $('#formPassword')[0].reset();
        });

        $('.js-layer-later').click(function (e) {
            $(':hidden[name=mode]').val("laterPassword");
            $.post('../policy/manage_password_ps.php', $('#formPassword').serializeArray(), function (data) {
                console.log(data);
                if (data.result == 'ok') {
                    window.location.href = data.url;
                } else {
                    process_close();
                    alert(data.message);
                }
            });
        });

        function process_close() {
            if (!_.isUndefined(BootstrapDialog)) {
                var dialogs = BootstrapDialog.dialogs;
                if ($.isEmptyObject(dialogs)) {
                    var parents = [];
                    for (var i = 0; i < 10; i++) {
                        parents.push('parent');
                        dialogs = eval(parents.join('.') + '.BootstrapDialog.dialogs');
                        if (!$.isEmptyObject(dialogs)) {
                            break;
                        }
                    }
                }

                for (var index in dialogs) {
                    var dialog = dialogs[index];
                    if (dialog.isOpened()) {
                        if (layerPassword.$modal[0].id != dialog.$modal[0].id) {
                            dialog.close();
                        }
                    }
                }
            }
        }
    });

    //-->
</script>
