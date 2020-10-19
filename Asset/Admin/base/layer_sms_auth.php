<!-- //@formatter:off -->
<?php
    gd_isset($isInvalidPassword, false);
    gd_isset($isCsAuth, false);
    gd_isset($emptySmsPoint, false);
    gd_isset($securitySelect, []);
    gd_isset($emptyAuthMethod, false);
    $cntSecuritySelect = \count($securitySelect);
    $securitySelectBox = gd_select_box('key', 'key', $securitySelect, null, null, null, null, 'form-control data-type');
?>
<form name="frmAdminSmsAuth" id="frmAdminSmsAuth" method="post" action="./login_ps.php">
    <input type="hidden" name="mode" value="checkSmsNumber"/>
    <div id="adminSecuritySmsInformation">
        <?php if ($cntSecuritySelect === 0) { ?>
        <div class="text-left empty-auth-message">
            보안로그인을 사용 중입니다.
            <br/>로그인하신 운영자 계정은 인증정보가 없어 로그인이 불가하오니,
            <br/>최고운영자에게 문의하시기 바랍니다.
        </div>
        <?php } elseif ($isInvalidPassword === true) { ?>
            <div class="text-left div-point">
                마이고도에 설정된 SMS 인증번호와 일치하지 않아, SMS 를 발송할 수 없습니다.
                <br/>마이고도에서 올바른 SMS 인증번호로 설정하여 주시기 바랍니다.
                <br/>SMS 인증번호를 모르실 경우 1:1문의로 문의해주세요!
            </div>
        <?php } elseif ($isCsAuth === true) { ?>
        <div class="text-center sms-auth-information">
            <div class="form-inline">
                CS 계정의 로그인 보안을 위한 메일 정보를 등록해 주세요.<br/>인증된 정보는 저장되지 않습니다.<br>
                <span class="text-center form-inline">
                    <?= $securitySelectBox ?>
                </span>
                <div class="form-group data-security-emailSend send-phone-number">
                    <input type="text" class="form-control" name="newEmail" />@<input type="text" class="form-control" name="newEmailDomain" />
                </div>
                <button type="button" class="btn btn-sm btn-gray js-layer-resend first-send">인증번호 받기</button>
            </div>
        </div>
        <div class="text-center div-sms-auth">
            <div>해당 인증번호를 아래 입력란에 입력 후<br/>보안인증을 진행하시면 로그인이 완료됩니다.</div>
            <div class="width-lg div-input-number">
                <input type="text" id="smsAuthNumber" name="smsAuthNumber" value="" placeholder="인증번호를 입력해 주세요."
                       class="width-lg" maxlength="8"/>
                <button type="button" class="btn btn-sm btn-gray js-layer-resend display-none">인증번호 재전송</button>
            </div>
            <div class="div-time-count">인증번호 발송을 눌러주세요.<span id="m_timer"></span></div>
        </div>
        <?php } elseif ($emptySmsPoint === true) { ?>
        <div class="text-center div-point">
        SMS 포인트가 없습니다. <br/>SMS 포인트 충전하기를 통해 충전 후 인증번호를 발송하시기 바랍니다.
        </div>
        <div class="text-center div-sms-auth mgt10 div-point">
            <button type="button" class="btn btn-md btn-gray js-point-charge">SMS 포인트 충전하기</button>
        </div>
        <?php } elseif ($cntSecuritySelect > 1) { ?>
        <div class="text-center sms-auth-information">
            <div class="form-inline">
                로그인 보안을 위한 인증번호가<br/>관리자 정보에 등록된 아래 인증수단으로 발송됩니다.<br>
                <span class="text-center form-inline">
                    <?= $securitySelectBox; ?>
                </span>
                <span class="text-center send-phone-number data-security-smsReSend"><?= $cellPhone; ?></span>
                <span class="text-center send-phone-number data-security-emailSend display-none"><?= $email; ?></span>
                <button type="button" class="btn btn-sm btn-gray js-layer-resend first-send">인증번호 받기</button>
            </div>
        </div>

        <div class="text-center div-sms-auth">
            <div>해당 인증번호를 아래 입력란에 입력 후<br/>보안인증을 진행하시면 로그인이 완료됩니다.</div>
            <div class="width-lg div-input-number">
                <input type="text" id="smsAuthNumber" name="smsAuthNumber" value="" placeholder="인증번호를 입력해 주세요."
                       class="width-lg" maxlength="8"/>
                <button type="button" class="btn btn-sm btn-gray js-layer-resend display-none">인증번호 재전송</button>
            </div>
            <div class="div-time-count">인증번호 발송을 눌러주세요.<span id="m_timer"></span></div>
        </div>
        <?php } elseif ($cntSecuritySelect === 1) {
            foreach ($securitySelect as $key => $text) { ?>
                <input type="hidden" name="key" class="data-type" value="<?=$key?>" />
                <?php if ($key === 'smsReSend') {?>
                    <div class="text-center sms-auth-information">
                        로그인 보안을 위한 인증번호가<br/> 관리자 정보에 등록된 아래 <?= $text ?> 번호로 발송되었습니다.
                        <p class="text-center send-phone-number"><?= $cellPhone; ?></p>
                    </div>
                <?php } elseif ($key === 'emailSend') { ?>
                    <div class="text-center sms-auth-information">
                        로그인 보안을 위한 인증번호가<br/> 관리자 정보에 등록된 아래 <?= $text ?>로 발송되었습니다.
                        <p class="text-center send-phone-number"><?= $email; ?></p>
                    </div>
                <?php } ?>

                <div class="text-center div-sms-auth">
                    <div>해당 인증번호를 아래 입력란에 입력 후<br/>보안인증을 진행하시면 로그인이 완료됩니다.</div>
                    <div class="width-lg div-input-number">
                        <input type="text" id="smsAuthNumber" name="smsAuthNumber" value="" placeholder="인증번호를 입력해 주세요." class="width-lg" maxlength="8"/>

                    </div>
                    <span class="div-time-count">인증번호 발송을 눌러주세요.<span id="m_timer"></span></span>
                    <button type="button" class="btn btn-sm btn-gray js-layer-resend display-none">인증번호 재전송</button>
                </div>
            <?php } ?>
        <?php } ?>
        <div class="text-center div-sms-auth capcha display-none">
            <div class="capcha-text">
                <p class="pre">자동입력 방지를 위해 아래 이미지의 문자 및 숫자를<br>순서대로 입력해 주세요.</p>
            </div>
            <span class="capcha-img"></span>
                <span class="div-auth-number">
                    <input type="text" id="capchaNumber" name="capchaNumber" value="" placeholder="자동등록방지문자" class="width-sm capchaNumber" maxlength="5"/>
                </span>
            <div class="capcha-reload"><a href="javascript:captchaReload();">새로고침</a></div>
        </div>
    </div>

    <div class="table-btn sms-point display-none">
        <button type="button" class="btn btn-lg btn-black js-layer-close">확인</button>
    </div>
    <div class="table-btn default display-none">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="button" class="btn btn-lg btn-black js-layer-submit">인증완료</button>
    </div>
</form>
<!-- //@formatter:on -->

<script type="text/javascript">
    <!--
    function captchaReload() {
        $('#captchaImg').removeAttr('src');
        setTimeout(function () {
            var someDate = new Date();
            someDate = someDate.getTime();
            var img = '<img src="' + '../base/captcha.php?ch=' + someDate + '" align="absmiddle" id="captchaImg"/>';
            $('.capcha-img').html(img);
        }, 1);
    }

    var layer_sms_auth = {
        empty_sms_point: false,
        empty_auth_method: false,
        cnt_security_select: 0,
        retry: 1,
        func_close: function (e) {
            clearInterval(window['timer_Mm_timer']);
            var $table_btn_default = $('.table-btn.default');
            if ($table_btn_default.data('display') === 'newEmail') {
                $('.div-point').removeClass('display-none');
                $('.sms-auth-information').addClass('display-none');
                $('.div-sms-auth:eq(0)').removeClass('display-none');
                $('.div-sms-auth:eq(1)').addClass('display-none');
                $('.table-btn.sms-point').toggleClass('display-none');
                $table_btn_default
                    .data('display', '')
                    .toggleClass('display-none');
                e.stopPropagation();
            } else {
                $('input:hidden[name="mode"]').val('');
                layer_close();
                location.reload();
            }
        }
    };

    layer_sms_auth.empty_sms_point = <?= $emptySmsPoint ? 'true' : 'false' ?>;
    layer_sms_auth.empty_auth_method = <?= $emptyAuthMethod ? 'true' : 'false' ?>;
    layer_sms_auth.cnt_security_select = <?= $cntSecuritySelect ?>;
    layer_sms_auth.retry = <?= $retry ?>;

    $(function () {
        var send_retry = 0;

        var $point_charge = $('.js-point-charge');
        if ($point_charge.length > 0) {
            $point_charge.click(function () {
                godo.layer.point_charge();
            });
        }


        if (layer_sms_auth.retry > 4) {
            $('.capcha').removeClass('display-none');
            captchaReload();
        }

        $('#capchaNumber').keyup(function () {
            this.value = this.value.toUpperCase();
        });

        // 인증번호 체크
        $('.js-layer-submit').click(function () {
            if ($.trim($('#smsAuthNumber').val()) === '') {
                alert('인증번호를 입력해주세요.');
                return false;
            }

            if (layer_sms_auth.retry > 4) {
                if ($.trim($('#capchaNumber').val()) === '') {
                    alert('자동입력 방지문자를 입력해주세요.');
                    return false;
                }
            }


            var $frm_admin_sms_auth = $('#frmAdminSmsAuth');
            $frm_admin_sms_auth.find('input[name=mode]').val('checkSmsNumber');
            var params = $frm_admin_sms_auth.serialize();

            function done(data) {
                if (data.error === 0) {
                    BootstrapDialog.closeAll();
                    BootstrapDialog.show({
                        title: "알림",
                        message: "관리자 접속이 승인되었습니다.",
                        onshown: function (dialog) {
                            setTimeout(function () {
                                dialog.close();
                            }, 1000);
                        },
                        onhidden: function () {
                            window.location.href = data.link;
                        }
                    });
                } else {
                    if (data.retry > 4) {
                        BootstrapDialog.show({
                            title: '알림',
                            message: data.message,
                            buttons: [{
                                label: '확인',
                                cssClass: 'btn-black',
                                hotkey: 13,
                                size: BootstrapDialog.SIZE_LARGE,
                                action: function (dialog) {
                                    dialog.close();
                                }
                            }],
                            onhide: function () {
                                var $capcha = $('.capcha');
                                $capcha.removeClass('display-none');
                                captchaReload();
                            }
                        });
                    } else {
                        var message = data.message;
                        if (message === '') {
                            message = '요청을 찾을 수 없습니다.';
                        }
                        dialog_alert(message);
                    }
                }
            }

            $.post('./login_ps.php', params).done(done);
        });

        // 인증번호 재전송
        var $js_layer_resend = $('.js-layer-resend');
        $js_layer_resend.click(function () {
            send_retry += 1;
            var params = {mode: $(".data-type").val()};
            if (layer_sms_auth.cnt_security_select > 1) {
                params.mode = $(".data-type :selected").val();
            }
            if (params.mode === 'smsReSend') {
                var newCellPhone = $('input[name=newCellPhone]').val();
                if (newCellPhone !== '') {
                    params.newCellPhone = newCellPhone;
                }
            } else if (params.mode === 'emailSend') {
                var newEmail = $('input[name=newEmail]').val();
                var newEmailDomain = $('input[name=newEmailDomain]').val();
                if (newEmail !== '' && newEmailDomain !== '') {
                    params.newEmail = newEmail + '@' + newEmailDomain;
                }
            }

            $js_layer_resend.addClass('display-none');
            $.post('./login_ps.php', params, function (data) {
                if (data.error === 0) {
                    dialog_alert("인증번호가 발송되었습니다.", '알림');
                    clearInterval(window['timer_Mm_timer']);
                    $('#smsAuthNumber').removeAttr('disabled');
                    smsAuthCountDown();
                } else {
                    $js_layer_resend.removeClass('display-none');
                    clearInterval(window['timer_Mm_timer']);
                    var $hidden_mode = $('input:hidden[name="mode"]');
                    $hidden_mode.val('');
                    if (data.error === 1) {
                        if (data.message === 'SMS Point Fail') {
                            dialog_alert('SMS 포인트가 소진되어 인증수단이 이메일로 자동 전환됩니다.', '알림');
                            $('.data-type').val('emailSend').trigger('change');
                            $('.dialog input[type=text]').val('');
                        } else {
                            dialog_alert('SMS 인증번호 전송에 실패하였습니다. 다시 시도해 주세요.', '알림');
                            $hidden_mode.val('');
                        }
                    } else if (data.error > 1 && data.error < 6) {
                        dialog_alert(data.message, '알림');
                    } else {
                        alert('인증번호 발송 중 오류가 발생하였습니다.');
                    }
                }
            });
        });

        // 인증번호 취소
        $('.js-layer-close').click(layer_sms_auth.func_close);

        function smsAuthCountDown() {
            $(".div-time-count").html('남은 인증시간 : <span id="m_timer"></span>');

            $('#m_timer').countdowntimer({
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
            $('#smsAuthNumber').attr('disabled', 'disabled');
            $js_layer_resend.removeClass('display-none');
            alert('인증시간이 만료되었습니다. 재전송을 눌러주세요.');
            if (send_retry > 4) {
                $('.capcha').show();
            }
            return false;
        }

        var security_selected_key = $('input[name=key]:hidden').val();

        if (layer_sms_auth.cnt_security_select > 1 || layer_sms_auth.empty_auth_method) {
            $(".data-type").change(function () {
                $(".send-phone-number").hide();
                $(".data-security-" + $(this).val()).show();
            });
        } else if (security_selected_key === 'emailSend') {
            $js_layer_resend.trigger("click");
        } else if (security_selected_key === 'smsReSend') {
            $js_layer_resend.trigger("click");
            smsAuthCountDown();
            $(".first-send").hide();
        }

        $(".data-type option:first").trigger('change');

        if (layer_sms_auth.empty_sms_point) {
            $('.table-btn.sms-point').removeClass('display-none');
            $('.table-btn.default').addClass('display-none');
        } else {
            var $empty_auth_message = $(".empty-auth-message");
            if ($empty_auth_message.length > 0) {
                $(".table-btn.default").addClass("display-none");
                $(".table-btn.sms-point").removeClass("display-none");
            } else {
                $(".table-btn.default").removeClass("display-none");
                $(".table-btn.sms-point").addClass("display-none");
            }
        }
    });
    //-->
</script>
