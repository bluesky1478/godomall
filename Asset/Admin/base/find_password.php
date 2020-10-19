<form id="frmFindPwd" method="post">
    <input type="hidden" name="mode" value="checkGodoMember">
    <input type="hidden" name="authType" value="">
    <div class="find-pwd">
        <div class="login-layout">
            <h1><img src="<?=PATH_ADMIN_GD_SHARE;?>img/logo_find_pwd.png"></h1>
            <div id="find-pwd-process">
                <p>고도 홈페이지 회원가입 시 등록하신 정보와 일치해야 인증번호를 받을 수 있습니다.</p>
                <div class="find-id-tab">
                    <a href="javascript:void(0);" class="phone on" data-type="sms">휴대폰 번호</a>
                    <a href="javascript:void(0);" class="email" data-type="email">이메일 주소</a>
                </div>
                <div class="login-form">
                    <div class="login-input">
                        <input type="text" name="managerId" placeholder="운영자아이디" class="form-control input-lg">
                        <input type="hidden" id="checkedId">
                    </div>
                    <div id="display-sms-type" class="cert-wrap-btn">
                        <input type="text" name="managerSms" placeholder="휴대폰 번호" class="form-control input-lg js-number">
                        <input type="hidden" id="checkedSms">
                        <button type="button" class="js-send-auth">인증번호 발송</button>
                    </div>
                    <div id="display-email-type" class="cert-wrap-btn display-none">
                        <input type="text" name="managerEmail" placeholder="이메일 주소" class="form-control input-lg" disabled="disabled">
                        <input type="hidden" id="checkedEmail">
                        <button type="button" class="js-send-auth">인증번호 발송</button>
                    </div>
                    <div class="cert-wrap">
                        <input type="text" name="otpNumber" placeholder="인증번호" class="form-control input-lg" disabled="disabled">
                        <button type="button" class="icon-x display-none"><img src="<?=PATH_ADMIN_GD_SHARE;?>img/icon_x.png"></button>
                    </div>
                    <div id="auth-time-check" class="text-wrap display-none">
                        <div id="auth-timer">유효시간 <span id="countDown">02 : 59</span></div>
                        <div id="expire-time" class="display-none text-red">인증번호 유효시간이 초과되었습니다.</div>
                    </div>
                    <div class="btn-wrap">
                        <button type="button" class="js-complete-auth">인증완료</button>
                    </div>
                </div>
                <div class="caution-form">
                    <strong class="caution-title">세팅메일에서도 아이디를 확인할 수 있습니다.</strong>
                    <ul>
                        <li>1. 고도 홈페이지 > 마이고도 > 쇼핑몰관리 > 쇼핑몰 목록</li>
                        <li>2. "서비스 관리" 항목의 <span class="text-red">[관리]</span> 버튼 클릭</li>
                        <li>3. "세팅메일 받기" 항목의 <span class="text-red">[메일보내기]</span> 버튼 클릭</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; NHN <a href="https://www.godo.co.kr" target="_blank">godo<span>:</span></a> Corp All Rights Reserved.
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    <!--
    var min = 0;
    var sec = 0;
    var time = 0;
    var runCount;
    var authRequest = 0;
    var type = '';
    var url = '';

    $(document).ready(function () {
        // 이메일, 휴대폰 선택
        $('.find-id-tab a').on('click', function() {
            // 기존 인증번호 및 유효시간 제거
            if ($('input[name="authType"]').val() != '') {
                $.ajax({
                    type: 'POST',
                    url: '../base/login_ps.php',
                    data: 'mode=deleteAdminAuth',
                });
            }

            authRequest = 0;
            type = $(this).data('type');
            url = type == 'sms' ? '../share/layer_godo_sms_ps.php' : '../share/layer_godo_mail_ps.php';
            $('.find-id-tab a').removeClass('on');
            $(this).addClass('on');
            $('input[name="authType"]').val(type);
            $('input[name="otpNumber"]').val('').prop('disabled', true);
            $('input[name="managerId"], input[name="otpNumber"]').val('');
            $('input[name="managerId"]').focus();
            $('#auth-time-check, .icon-x').addClass('display-none');
            if (type == "sms") {
                $('#display-email-type').addClass('display-none');
                $('#display-sms-type').removeClass('display-none');
                $('input[name="managerSms"]').prop('disabled', false).val('');
                $('input[name="managerEmail"]').prop('disabled', true);
            } else {
                $('#display-email-type').removeClass('display-none');
                $('#display-sms-type').addClass('display-none');
                $('input[name="managerSms"]').prop('disabled', true);
                $('input[name="managerEmail"]').prop('disabled', false).val('');
            }
        });

        // 디폴트 휴대폰
        $('.find-id-tab a:eq(0)').trigger('click');

        // 인증번호 입력시 삭제버튼 노출
        $('input[name="otpNumber"]').on('keyup', function() {
            if ($(this).val() == '') {
                $('.icon-x').addClass('display-none');
            } else {
                $('.icon-x').removeClass('display-none');
            }
        });

        // 인증번호 삭제
        $('.icon-x').on('click', function () {
            $('input[name="otpNumber"]').val('').focus();
            $('.icon-x').addClass('display-none');
        });

        // 인증번호 발송
        $('.js-send-auth').click(function(e) {
            var failMsg = '인증번호를 발송하지 못했습니다. 잠시 후 다시 시도해주세요.';
            var errEmptyMsg = '휴대폰번호를 입력해주세요.';
            var errCheckAdminId = false;
            var type = $('input[name="authType"]').val();
            var managerId = $('input[name="managerId"]');
            var managerCert = $('input[name="manager' + type.substring(0, 1).toUpperCase() + type.substring(1, type.length).toLowerCase() + '"]');

            if (managerId.val() == '') {
                dialog_alert('운영자아이디를 입력해주세요.', '본인인증', {
                    callback: function() {
                        focusElement(managerId);
                    }
                });
                return false;
            }

            if (managerCert.val() == '') {
                if (type == 'email') {
                    errEmptyMsg = '이메일을 입력해주세요.';
                }
                dialog_alert(errEmptyMsg, '본인인증', {
                    callback: function() {
                        focusElement(managerCert);
                    }
                });
                return false;
            }

            // 운영자 아이디 확인
            $.ajax({
                type: 'POST',
                url: '../base/login_ps.php',
                async: false,
                data: {mode: 'checkAdminId', managerId: managerId.val()},
                success: function(data) {
                    if (data.result) {
                        managerId.siblings('input[id="checkedId"]:hidden').val(managerId.val());
                    } else {
                        errCheckAdminId = true;
                        dialog_alert('운영자 아이디가 일치하지 않습니다. 확인 후 다시 입력해주세요.', '본인인증', {
                            callback: function() {
                                focusElement(managerId);
                            }
                        });
                    }
                },
                error: function() {
                    alert(failMsg);
                    return false;
                }
            });

            if (errCheckAdminId) {
                return false;
            }

            var params = $('#frmFindPwd').serialize();

            // 마이고도 정보 확인 및 인증번호 발송
            authRequest = 1;
            $.ajax({
                type: 'POST',
                url: url,
                data: params,
                success: function(checkResult) {
                    if (checkResult.trim() == 'Y') {
                        var authKeyMode = type == 'sms' ? 'getSmsAuthKey' : 'getMailAuthKey';
                        var authTarget = type == 'sms' ? $('input[name="managerSms"]').val() : $('input[name="managerEmail"]').val();
                        $.ajax({
                            type: 'POST',
                            url: url,
                            data: 'mode=' + authKeyMode + '&authTarget=' + authTarget + '&managerId=' + managerId.val(),
                            success: function(sendResult) {
                                if (sendResult.trim() == 'Y') {
                                    var otp = $('input[name="otpNumber"]');
                                    dialog_alert('인증번호를 발송했습니다. 3분 이내에 입력해주세요.', '본인인증', {
                                        callback: function() {
                                            otp.prop('disabled', false);
                                            otp.val('');
                                            focusElement(otp);
                                        }
                                    });
                                    authRequest = 1;
                                    $('#auth-time-check').removeClass('display-none');
                                    managerCert.siblings('input[id*="checked"]:hidden').val(managerCert.val());
                                    stopCountDown();
                                    initCountDown();
                                } else {
                                    authRequest = 0;
                                    alert(failMsg);
                                }
                            },
                            error: function() {
                                authRequest = 0;
                                alert(failMsg);
                            }
                        });
                    } else {
                        authRequest = 0;
                        var errMsg = type == 'sms' ? '입력하신 정보가 일치하지 않습니다. 고도 홈페이지의 휴대폰번호와 일치해야 인증번호를 받을 수 있습니다.' : '입력하신 정보가 일치하지 않습니다. 고도 홈페이지의 이메일과 일치해야 인증번호를 받을 수 있습니다.';
                        alert(errMsg);
                    }
                    return false;
                },
                error: function() {
                    authRequest = 0;
                    alert(failMsg);
                }
            });
        });

        // 인증완료
        $('.js-complete-auth').click(function(e){
            // 요청이 없는 경우 요청하라고 권고
            if (authRequest == 0 || time <= 0) {
                alert('인증번호를 입력해주세요');
                return false;
            }

            var otp = $('input[name="otpNumber"]');
            // 인증번호 입력 확인
            var chkAuthKey = $('input[name="otpNumber"]').val();
            if (chkAuthKey == '') {
                dialog_alert('인증번호를 입력해주세요.', '본인인증', {
                    callback: function() {
                        focusElement(otp);
                    }
                });
                return false;
            }

            var targetAuth = $('input[name="manager' + type.substring(0, 1).toUpperCase() + type.substring(1, type.length).toLowerCase() + '"]');
            var checkedAuth = $('input[id="checked' + type.substring(0, 1).toUpperCase() + type.substring(1, type.length).toLowerCase() + '"]');
            var targetId = $('input[name="managerId"]');
            var errTargetMsg = type == 'sms' ? '휴대폰 번호' : '이메일 주소';

            if (targetId.val() != $('#checkedId').val()) {
                dialog_alert('운영자 아이디가 일치하지 않습니다. 확인 후 다시 입력해주세요.', '본인인증', {
                    callback: function() {
                        focusElement(targetId);
                    }
                });
                return false;
            }
            if (targetAuth.val() != checkedAuth.val()) {
                dialog_alert(errTargetMsg + '가 일치하지 않습니다. 확인 후 다시 입력해주세요.', '본인인증', {
                    callback: function() {
                        focusElement(targetAuth);
                    }
                });
                return false;
            }

            var authCheckMode = type == 'sms' ? 'checkSmsAuth' : 'checkMailAuth';

            // 인증번호 체크
            $.ajax({
                type: 'POST',
                url: url,
                data: 'mode=' + authCheckMode + '&checkAuthKey=' + chkAuthKey,
                success: function(result) {
                    if (result.trim() == 'Y') {
                        BootstrapDialog.show({
                            title: '본인인증',
                            message: '인증이 완료되었습니다.',
                            buttons: [{
                                label: '확인',
                                cssClass: 'btn-black',
                                action: function(dialog) {
                                    dialog.close();
                                    stopCountDown();
                                }
                            }],
                            onhidden: function() {
                                // 비밀번호 입력으로 전환
                                var compiledDisplay = _.template($('#templateResetPassword').html());
                                var compiledHiddenForm = _.template($('#templateResetPasswordForm').html());
                                var templateData = {'managerId': targetId.val()};
                                $('#find-pwd-process').html(compiledDisplay);
                                $('body').append(compiledHiddenForm(templateData));
                            }
                        });
                    } else {
                        dialog_alert('인증번호가 일치하지 않습니다. 확인 후 다시 입력해주세요.', '본인인증', {
                            callback: function() {
                                focusElement(otp);
                            }
                        });
                    }
                },
                error: function() {
                    alert('인증을 다시 시도해 주시기 바랍니다.');
                }
            });
        });

        // 새 비밀번호 저장
        $('body').on('click', '.js-reset-pwd', function() {
            var pwd = $('input[name="password"]', '.login-form');
            var pwdRe = $('input[name="passwordRe"]', '.login-form');

            if (pwd.val() == '') {
                dialog_alert('새 비밀번호를 입력해주세요.', '경고', {
                    callback: function() {
                        focusElement(pwd);
                    }
                });
                return false;
            }

            if (pwd.val() != pwdRe.val()) {
                dialog_alert('비밀번호가 일치하지 않습니다.', '경고', {
                    callback: function() {
                        focusElement(pwdRe);
                    }
                });
                return false;
            }

            var _form = $('#frmResetPassword');
            $('input[name="password"]:hidden', _form).val(pwd.val());
            $.post('../policy/manage_password_ps.php', _form.serializeArray(), function (data) {
                if (data.result == 'ok') {
                    dialog_alert(data.message, '확인', {location: data.url});
                } else {
                    alert(data.message);
                }
            });

        });
    });

    // 카운트 시작 함수 호출
    function initCountDown() {
        if ($('#auth-timer').hasClass('display-none')) {
            $("#countDown").text('02 : 59');
            $('#auth-timer').removeClass('display-none');
            $('#expire-time').addClass('display-none');
        }
        runCount = setInterval("startCountDown()", 1000);
    }

    // 카운트 및 노출하기
    function startCountDown() {
        $.ajax({
            type: 'POST',
            url: url,
            data: 'mode=getRestTime',
            success: function(result) {
                time = result;

                // 시간이 다 됐을 시 카운트 정지
                if (parseInt(time) <= 0) {
                    stopCountDown();
                    time = 0;
                    $('#expire-time').removeClass('display-none');
                    $('#auth-timer').addClass('display-none');
                }

                min = parseInt((time%3600)/60);
                sec = time%60;

                if (min == 0) min = '0';
                if (sec < 10) sec = '0' + sec;

                $("#countDown").text('0' + min + ' : ' + sec);
            }
        });
    }

    // 카운트 정지
    function stopCountDown() {
        clearInterval(runCount);
    }

    // 포커스
    function focusElement(ele) {
        if (ele === 'undefined') {
            return false;
        }
        ele.focus();
    }
    //-->
</script>
<script type="text/template" id="templateResetPassword">
    <p>사용하실 비밀번호를 다시 입력해주세요.</p>
    <div class="login-form">
        <div class="login-input">
            <input type="password" name="password" placeholder="새 비밀번호" class="form-control input-lg">
        </div>
        <div class="cert-wrap">
            <input type="password" name="passwordRe" placeholder="새 비밀번호 확인" class="form-control input-lg">
        </div>
        <div class="text-wrap new">
            영문대문자/영문소문자/숫자/특수문자 중 2가지 이상 조합, <br/>
            10~16자리 이하로 설정할 수 있습니다.
        </div>
        <div class="btn-wrap">
            <button type="button" class="js-reset-pwd">확인</button>
        </div>
    </div>
</script>
<script type="text/template" id="templateResetPasswordForm">
    <form id="frmResetPassword">
        <input type="hidden" name="mode" value="changePassword">
        <input type="hidden" name="type" value="reset">
        <input type="hidden" name="managerId" value="<%=managerId%>">
        <input type="hidden" name="password">
    </form>
</script>
