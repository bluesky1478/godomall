<?php if($superSmsMessage) {?>
<script type="text/javascript">
<!--
    $(function () {
        dialog_confirm("<?=$superSmsMessage?>", function (result) {
            if (result) {
                location.replace("../member/sms_charge.php");
            } else {
                window.location.reload();
            }
        });
    });
//-->
</script>
<?php exit; } ?>
<form name="frmAdminSmsAuth" id="frmAdminSmsAuth" method="post" action="../base/screen_security_ps.php" target="_blank">
    <input type="hidden" name="mode" value="checkSecurityNumber"/>
    <div id="adminScreenSecurityInformation">
        <div class="text-center security-information">
            관리자 보안을 위해 화면보안접속 기능 사용 중입니다.<br>
            <?php if(count($securitySelect) > 1) { ?>
                인증할 정보를 선택하여 인증번호 발송을 눌러주세요.<br><br>
            <span class="text-center form-inline">
                <?php echo gd_select_box('key', 'key', $securitySelect, null, null, null, null, 'form-control data-type'); ?>
            </span>
            <?php } else {
                foreach($securitySelect as $value => $text) {
                    echo '인증정보를 확인 후 인증번호 발송을 눌러주세요.<br><br>';
                    echo '<input type="hidden" name="key" class="data-type" value="'.$value.'" />';
                }
            } ?>
            <span class="text-center security-info data-security-smsSend display-none">&nbsp;<?= $cellPhone; ?></span>
            <span class="text-center security-info data-security-emailSend display-none">&nbsp;<?= $email; ?></span>
            &nbsp;
            <span class="text-center">
                <button type="button" class="btn btn-sm btn-gray js-layer-send">인증번호 발송</button>
            </span>
        </div>

        <div class="text-center screen-auth-information">
            <div>해당 인증번호를 아래 입력란에 입력 후<br/>보안인증을 진행하세요.</div>

            <div class="text-center div-auth-time ">
                <div class="div-time-count">인증번호 발송을 눌러주세요.<span id="m_timer" name="m_timer"></span></div>
            </div>
            <div class="text-center div-auth-number ">
                    <input type="text" id="authNumber" name="authNumber" value="" placeholder="8자리 인증번호 입력" class="width-lg" maxlength="8" disabled/><br>
                    <button type="button" class="btn btn-sm btn-gray js-layer-resend display-none">인증번호 재발송</button>
            </div>

            <div class="text-center capcha <?php if($retry < 6) { ?>display-none<?php } ?>">
                <div class="capcha-text">
                    <p class="pre">자동입력 방지를 위해 아래 이미지의 문자 및 숫자를<br>순서대로 입력해 주세요.</p>
                </div>
                <span class="capcha-img"><img src="../base/captcha.php" align="absmiddle" id="captchaImg"/></span>
                <span class="div-auth-number">
                    <input type="text" id="capchaNumber" name="capchaNumber" value="" placeholder="자동등록방지문자" class="width-sm capchaNumber" maxlength="5"/>
                </span>
                <div class="capcha-reload"><a href="#" class="captcha-reload">새로고침</a></div>

            </div>
        </div>

        <div class="table-btn security-btn">
            <button type="button" class="btn btn-lg btn-black js-layer-submit">인증완료</button>
        </div>
    </div>
</form>
<script type="text/javascript">
    <!--
    $(function () {
        var retry = <?=$retry?>;
        var sendRetry = 0;

        $('#frmAdminSmsAuth').validate({
            submitHandler: function (form) {
                $('input:hidden[name="mode"]').val('checkSecurityNumber');
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                "authNumber": 'required'
            },
            messages: {
                "authNumber": '인증번호를 입력해주세요.'
            }
        });
        // 인증번호 체크
        $('.js-layer-submit').click(function (e) {

            // validate 레이어 에서 처리 시 현재 레이어가 변경되어지므로 별도 체크를 함
            if ($.trim($('#authNumber').val()) == '') {
                alert('인증번호를 입력해주세요.');
                return false;
            }

            if(retry > 5) {
                if ($.trim($('#capchaNumber').val()) == '') {
                    alert('자동입력 방지문자를 입력해주세요.');
                    return false;
                }
            }

            $('#frmAdminSmsAuth').submit();
            $('input:hidden[name="mode"]').val('');
            clearInterval(window['timer_Mm_timer']);
            layer_close();

        });

        // 인증번호 전송
        $('.js-layer-send').click(function (e) {
            $('.js-layer-send').addClass("display-none");
            var params = {
                mode: $(".data-type").val()
            };

            <?php if(count($securitySelect) > 1) { ?>
            params = {
                mode: $(".data-type :selected").val()
            };
            <?php } ?>
            $.post('../base/screen_security_ps.php', params, function (data) {
                if (data.error == 0) {
                    // 인증정보 발송 성공
                    $(this).hide();
                    clearInterval(window['timer_Mm_timer']);
                    authCountDown();
                    dialog_alert('인증번호가 발송되었습니다.', '알림', {isReload: false});
                } else {
                    $('.js-layer-send').removeClass("display-none");
                    if (data.error == 1) {
                        if (data.message == 'SMS Point Fail') {
                            dialog_alert('SMS 포인트가 소진되어 인증수단이 이메일로 자동 전환됩니다.', '알림', {isReload: true});
                        } else {
                            dialog_alert('SMS 인증번호 전송에 실패하였습니다. 다시 시도해 주세요.', '알림', {isReload: true});
                        }
                    } else if(data.error == 2) {
                        // 이메일 발송실패
                        dialog_alert(data.message, '알림', {isReload: true});
                    } else if(data.error == 3) {
                        // 이메일 인증정보가 없는 경우
                        dialog_alert(data.message, '알림', {isReload: true});
                    } else {
                        dialog_alert('인증번호 발송이 실패되었습니다. 다시 시도해 주세요.', '알림', {isReload: true});
                    }
                }
            });
        });

        // 인증번호 재전송
        $('.js-layer-resend').click(function (e) {
            sendRetry += 1;
            <?php if(count($securitySelect) > 1) { ?>
            var params = {
                mode: $(".data-type :selected").val()
            };
            <?php } else { ?>
            var params = {
                mode: $(".data-type").val()
            };
            <?php } ?>

            $.post('../base/screen_security_ps.php', params, function (data) {
                if (data.error == 1) {
                    if (data.message == 'SMS Point Fail') {
                        dialog_alert('SMS 포인트가 소진되어 인증수단이 이메일로 자동 전환됩니다.', '알림', {isReload: true});
                    } else {
                        dialog_alert('SMS 인증번호 전송에 실패하였습니다. 다시 시도해 주세요.', '알림', {isReload: true});
                    }
                } else if(data.error == 2) {
                    // 이메일 발송실패
                    dialog_alert(data.message, '알림', {isReload: true});
                } else if(data.error == 3) {
                    // 이메일 인증정보가 없는 경우
                    dialog_alert(data.message, '알림', {isReload: true});
                } else if(data.error == 0) {
                    // 인증정보 발송 성공
                    $(this).hide();
                    clearInterval(window['timer_Mm_timer']);
                    authCountDown();
                    dialog_alert('인증번호가 발송되었습니다.', '알림', {isReload: false});
                } else {
                    dialog_alert('인증번호 발송이 실패되었습니다. 다시 시도해 주세요.', '알림', {isReload: true});
                }
            });
        });

        $('.captcha-reload').click(function (e) {
            $('#captchaImg').removeAttr('src');
            setTimeout(function () {
                var someDate = new Date();
                someDate = someDate.getTime();
                $('#captchaImg').attr('src', '../base/captcha.php?ch=' + someDate);
            }, 1);
        });

        function authCountDown() {
            $(".div-time-count").html('남은 인증시간 : <span id="m_timer" name="m_timer"></span>');
            $('#authNumber').removeAttr('disabled');

            $("[name='m_timer']").countdowntimer({
                minutes: 3,
                size: "14px",
                borderColor: "#ddd",
                fontColor: "#f91d11",
                backgroundColor: "#ddd",
                tickInterval: 1,
                timeUp: authTimeOut
            });
        }
        function authTimeOut() {
            $('.js-layer-send').removeClass("display-none");
            $('#authNumber').attr('disabled', 'disabled');
            $(".div-time-count").html('인증시간이 만료되었습니다.');
            alert('인증시간이 만료되었습니다. 재전송을 눌러주세요.');

            if(sendRetry > 5) {//자동입력방지
                $('.capcha').show();
            }
            return false;
        }

        <?php if(count($securitySelect) > 1) { ?>
        $(".data-type").change(function(e) {
            $(".security-info").hide();
            $(".data-security-" + $(this).val()).show();
        });

        $(".data-security-" + $(".data-type :selected").val()).show();
        <?php } else { ?>
        $(".data-security-" + $(".data-type").val()).show();
        <?php } ?>

        <?php if($alertMessage) {?>
        alert("<?=$alertMessage?>");
        <?php } ?>
    });
    //-->
</script>
