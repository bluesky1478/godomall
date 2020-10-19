<?php if ($superAlertMessage) { ?>
    <script type="text/javascript">
        <!--
        $(function () {
            dialog_alert("<?=$superAlertMessage?>", '알림', {
                callback: function () {
                    var dialog = BootstrapDialog.dialogs[BootstrapDialog.currentId];
                    if (dialog) {
                        dialog.close();
                    }
                }
            });
        });
        //-->
    </script>
    <?php exit;
} else if ($message) { ?>
    <script type="text/javascript">
        <!--
        $(function () {
            dialog_alert("<?=$message?>", '알림');
        });
        //-->
    </script>
<?php }
$targetName = '엑셀';
if (empty($getValue['downloadTarget']) === false && $getValue['downloadTarget'] == 'file') {
    $targetName = '파일';
}
?>
<!-- //@formatter:off -->
<form name="frmAdminSmsAuth" id="frmAdminSmsAuth" method="post" action="../share/layer_excel_auth_ps.php">
    <input type="hidden" name="mode" value="checkSmsNumber"/>
    <input type="hidden" name="subject" value="<?=$getValue['subject'];?>"/>
    <div id="adminSecuritySmsInformation">
        <?php if(count($securitySelect) > 1) { ?>
        <div class="text-center sms-auth-information">
            개인정보 보호를 위한 <?=$targetName;?> 다운로드 보안 설정을 사용 중입니다.<br/>인증할 정보를 선택하여 인증번호 발송을 눌러주세요.<br>

            <span class="text-center form-inline">
                <?= gd_select_box('key', 'key', $securitySelect, null, null, null, null, 'form-control data-type'); ?>
            </span>
            <span class="text-center send-phone-number data-security-authSms"><?= $cellPhone; ?></span>
            <span class="text-center send-phone-number data-security-authEmail display-none"><?= $email; ?></span>
            <button type="button" class="btn btn-sm btn-gray js-layer-resend first-send">인증번호 발송</button>
        </div>

        <div class="text-center div-sms-auth">
            <div>해당 인증번호를 아래 입력란에 입력 후<br/>보안인증을 진행하세요.</div>
            <div class="div-time-count">인증번호 발송을 눌러주세요.<span id="m_timer"></span></div>
            <span class="div-time-out text-red display-none"><b>인증시간이 만료되었습니다.</b></span>
            <div class="width-lg div-input-number">
                <input type="text" id="smsAuthNumber" name="smsAuthNumber" value="" placeholder="8자리 인증번호 입력"
                       class="width-lg" maxlength="8"/>
            </div>
        </div>

        <?php } else {
            foreach($securitySelect as $key => $text) {
                ?>
                <input type="hidden" name="key" class="data-type" value="<?=$key?>" />
                <?php if($key == 'authSmsGodo') {?>
                    <div class="text-center sms-auth-godo-information">
                        개인정보 보호를 위한 <?=$targetName;?> 다운로드 보안 설정을 사용 중입니다.<br/>인증정보를 확인 후 인증번호 발송을 눌러주세요.<br>
                        <b class="text-center">고도회원 휴대폰번호 인증</b>
                        <button type="button" class="btn btn-sm btn-gray js-layer-resend first-send">인증번호 발송</button>
                    </div>
                <?php } ?>
                <?php if($key == 'authSms') {?>
                    <div class="text-center sms-auth-information">
                        개인정보 보호를 위한 <?=$targetName;?> 다운로드 보안 설정을 사용 중입니다.<br/>인증정보를 확인 후 인증번호 발송을 눌러주세요.<br>
                        <b class="text-center send-phone-number"><?= $cellPhone; ?></b>
                        <button type="button" class="btn btn-sm btn-gray js-layer-resend first-send">인증번호 발송</button>
                    </div>
                <?php } ?>
                <?php if($key == 'authEmail') {?>
                    <div class="text-center sms-auth-information">
                        개인정보 보호를 위한 <?=$targetName;?> 다운로드 보안 설정을 사용 중입니다.<br/>인증정보를 확인 후 인증번호 발송을 눌러주세요.<br>
                        <b class="text-center send-phone-number"><?= $email; ?></b>
                        <button type="button" class="btn btn-sm btn-gray js-layer-resend first-send">인증번호 발송</button>
                    </div>
                <?php } ?>

                <div class="text-center div-sms-auth">
                    <div>해당 인증번호를 아래 입력란에 입력 후<br/>보안인증을 진행하세요.</div>
                    <span class="div-time-count">인증번호 발송을 눌러주세요.<span id="m_timer"></span></span>
                    <span class="div-time-out text-red display-none"><b>인증시간이 만료되었습니다.</b></span>
                    <div class="width-lg div-input-number">
                        <input type="text" id="smsAuthNumber" name="smsAuthNumber" value="" placeholder="8자리 인증번호 입력"
                               class="width-lg" maxlength="8"/>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>

        <div class="text-center div-sms-auth capcha <?php if($retry < 5) { echo'display-none'; } ?>">
            <div class="capcha-text">
                <p class="pre">자동입력 방지를 위해 아래 이미지의 문자 및 숫자를<br>순서대로 입력해 주세요.</p>
            </div>
            <span class="capcha-img"><img src="../base/captcha.php" align="absmiddle" id="captchaImg"/></span>
                <span class="div-auth-number">
                    <input type="text" id="capchaNumber" name="capchaNumber" value="" placeholder="자동등록방지문자" class="width-sm capchaNumber" maxlength="5"/>
                </span>
            <div class="capcha-reload"><a href="javascript:captchaReload();">새로고침</a></div>

        </div>

    </div>

    <div class="table-btn">
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
            $('#captchaImg').attr('src', '../base/captcha.php?ch=' + someDate);
        }, 1);
    }

    $(function () {
        var $js_layer_resend = $('.js-layer-resend');
        var count_auth_method = <?= count(gd_isset($securitySelect, [])) ?>;
        var retry = <?=$retry?>;
        var sendRetry = 0;
        var $frmAdminSmsAuth = $('#frmAdminSmsAuth');
        var $mode = $frmAdminSmsAuth.find('input:hidden[name="mode"]').val();
        $frmAdminSmsAuth.validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                "smsAuthNumber": 'required'
            },
            messages: {
                "smsAuthNumber": '인증번호를 입력해주세요.'
            }
        });
        // 인증번호 체크
        $('.js-layer-submit').click(function () {
            // validate 레이어 에서 처리 시 현재 레이어가 변경되어지므로 별도 체크를 함
            if ($.trim($('#smsAuthNumber').val()) === '') {
                alert('인증번호를 입력해주세요.');
                return false;
            }

            if (retry > 4) {
                if ($.trim($('#capchaNumber').val()) === '') {
                    alert('자동입력 방지문자를 입력해주세요.');
                    return false;
                }
            }

            var params = $('#frmAdminSmsAuth').serialize();
            var success = function (data) {
                if (data.error === 0) {
                    if ($('input[name="subject"]').val() === 'crema') {
                        BootstrapDialog.dialogs[BootstrapDialog.currentId].close();
                        top.create_crema_csv();
                    } else {
                        var complied = _.template($('#downloadReason').html());
                        var message = complied();
                        BootstrapDialog.show({
                            title: '엑셀 다운로드 사유',
                            size: BootstrapDialog.SIZE_WIDE,
                            message: message,
                            buttons: [{
                                label: '확인',
                                cssClass: 'btn-black',
                                hotkey: 32,
                                size: BootstrapDialog.SIZE_LARGE,
                                action: function (dialog) {
                                    if ($('#excelDownloadReason').val() == '') {
                                        $('#reasonError').removeClass('display-none');
                                        return false;
                                    }
                                    dialog.close();
                                    $("input[name='excelDownloadReason']").val($('#excelDownloadReason').val());
                                    $("#frmExcelRequest").submit();
                                }
                            }]
                        });
                        BootstrapDialog.dialogs[BootstrapDialog.currentId].close();
                    }
                } else {
                    dialog_alert(data.message, '알림');
                }
            };
            $.post('../share/layer_excel_auth_ps.php', params, success);
        });
        $js_layer_resend.click(function () {
            $js_layer_resend.addClass("display-none");
            var params = {
                mode: $(".data-type").val()
            };
            sendRetry += 1;
            if (count_auth_method > 1) {
                params.mode = $(".data-type :selected").val();
            }
            $.post('../share/layer_excel_auth_ps.php', params, function (data) {
                dialog_alert(data.message, '알림');
                if (data.error === 0) {
                    clearInterval(window['timer_Mm_timer']);
                    $('#smsAuthNumber').removeAttr('disabled');
                    smsAuthCountDown();
                } else {
                    $js_layer_resend.removeClass("display-none");
                    $mode.val('');
                }
            });
        });
        // 인증번호 취소
        $('.js-layer-close').click(function () {
            clearInterval(window['timer_Mm_timer']);
            var dialog = BootstrapDialog.dialogs[BootstrapDialog.currentId];
            if (dialog) {
                dialog.close();     // 지정된 다이얼로그 닫기
            } else {
                $mode.val('');
                layer_close();
                location.reload();
            }
        });

        function smsAuthCountDown() {
            var $time_count = $('.div-time-count'), $time_out = $('.div-time-out');
            var display_class = 'display-none';
            $time_out.addClass(display_class);
            if ($time_count.hasClass(display_class)) {
                $time_count.removeClass(display_class)
            }
            $time_count.html('남은 인증시간 : <span id="m_timer"></span>');

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
            $('.js-layer-resend').removeClass("display-none");
            $('#smsAuthNumber').attr('disabled', 'disabled');
            var $time_count = $('.div-time-count'), $time_out = $('.div-time-out');
            var display_class = 'display-none';
            $time_count.addClass(display_class);
            if ($time_out.hasClass(display_class)) {
                $time_out.removeClass(display_class)
            }

            if (sendRetry > 4) {//자동입력방지
                $('.capcha').show();
            }
            return false;
        }

        if (count_auth_method > 1) {
            var $smsAuthNumber = $('#smsAuthNumber');
            var $selected_data_type = $(".data-type :selected");
            var value = $selected_data_type.val();
            $(".data-type").change(function () {
                value = $(this).val();
                $(".send-phone-number").hide();
                $(".data-security-" + value).show();
                clearInterval(window['timer_Mm_timer']);
                $smsAuthNumber.val('');
                $smsAuthNumber.attr({placeholder: "8자리 인증번호 입력", maxlength: 8});
                $frmAdminSmsAuth.find(':hidden[name="mode"]').val('checkSmsNumber');
                if (value === 'authSmsGodo') {
                    $frmAdminSmsAuth.find(':hidden[name="mode"]').val('checkAuthSmsGodo');
                    $smsAuthNumber.attr({placeholder: "6자리 인증번호 입력", maxlength: 6});
                }
            });
            $(".data-security-" + $selected_data_type.val()).show();
            $smsAuthNumber.attr({placeholder: "8자리 인증번호 입력", maxlength: 8});
            $frmAdminSmsAuth.find(':hidden[name="mode"]').val('checkSmsNumber');
            if (value === 'authSmsGodo') {
                $frmAdminSmsAuth.find(':hidden[name="mode"]').val('checkAuthSmsGodo');
                $smsAuthNumber.attr({placeholder: "6자리 인증번호 입력", maxlength: 6});
            }
        } else {
            if ($frmAdminSmsAuth.find(':hidden[name="key"]').val() === 'authSmsGodo') {
                $frmAdminSmsAuth.find(':hidden[name="mode"]').val('checkAuthSmsGodo');
                $frmAdminSmsAuth.find('#smsAuthNumber').attr({placeholder: "6자리 인증번호 입력", maxlength: 6});
            }
        }
    });
    //-->
</script>
<script type="text/html" id="downloadReason">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <tr style="border-top: 1px solid #E6E6E6;">
                <th>사유 선택</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('excelDownloadReason', 'excelDownloadReason', $reasonList, null, null, '=사유 선택=', null, 'form-control'); ?>
                        <div id="reasonError" class="text-red display-none">사유 선택은 필수입니다.</div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="well">
        <div class="notice-info">개인정보의 안전성 확보조치 기준(고시)에 의거하여 개인정보를 다운로드한 경우 사유 확인이 필요합니다.</div>
    </div>
</script>