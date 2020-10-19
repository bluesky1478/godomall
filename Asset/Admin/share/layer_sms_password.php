<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */
?>
<!-- //@formatter:off -->
<form id="frmSmsPassword">
<table class="table table-cols">
<colgroup><col class="width-md"/><col/></colgroup>
<tr>
    <th class="require"><?= $displayInfo['title']; ?></th>
    <td class=form-inline>
        <label>
            <input type="password" class="form-control mgb5" name="password"/>
        </label>
        <div class="notice-danger">무단으로 SMS 포인트가 사용되지 않도록 SMS 인증번호로 인증 후 변경할 수 있습니다.</div>
        <div class="notice-info">SMS 인증번호는
            <a class="btn-link" href="javascript:gotoGodomall('main');">[마이고도 > 쇼핑몰 관리]</a> 에서 확인할 수 있습니다.
        </div>
    </td>
</tr>
<?php if ($displayInfo['useCaptcha'] === true) { ?>
<tr class="captcha">
    <th class="require">자동등록방지</th>
    <td class="form-inline">
        <div class="captcha-container width-2xl">
            <div class="captcha-img"><img src="../base/captcha.php" id="captchaImg"/></div>
            <div class="captcha-input">
                <div class="mgb5">보이는 순서대로<br/>숫자 및 문자를 모두 입력해 주세요.</div>
                <div class="mgb5">
                    <input type="text" id="capchaNumber" name="captchaNumber"
                           class="width-sm capchaNumber" maxlength="5" />
                </div>
                <div class="mgb5"><a href="#" class="btn btn-refresh btn-white btn-xs">이미지 새로고침</a></div>
            </div>
        </div>
    </td>
</tr>
<?php } ?>
</table>
<div class="text-center">
    <button type="button" class="btn btn-black btn-lg js-layer-submit">확인</button>
</div>
</form>
<!-- //@formatter:on -->
<script type="text/javascript">
    $(document).ready(function () {
        var obj = {
            frm: $('#frmSmsPassword'),
            password: null, captcha_number: null,
            is_limit: false, limit_message: null,
            useCaptcha: function () {
                return obj.frm.find('.captcha').length > 0;
            }
        };
        obj.password = obj.frm.find('input[name=\'password\']');
        obj.captcha_number = obj.frm.find('input[name=\'captchaNumber\']');
        if (obj.useCaptcha()) {
            obj.frm.find('.capchaNumber').keyup(function (e) {
                e.target.value = e.target.value.toUpperCase();
            });
            obj.frm.find('.btn-refresh').click(function () {
                $('#captchaImg').removeAttr('src');
                setTimeout(function () {
                    var someDate = new Date();
                    someDate = someDate.getTime();
                    $('#captchaImg').attr('src', '../base/captcha.php?ch=' + someDate);
                }, 1);
            });
            obj.frm.find('.btn-refresh').trigger('click');
        } else {
            obj.password.focusin(function () {
                if (obj.is_limit) {
                    alert(obj.limit_message);
                }
            });
        }

        obj.frm.find('.js-layer-submit').click(function () {
            if (obj.password.val() === '') {
                alert('SMS 인증번호를 입력해주세요.');
                obj.password.focus();
                return false;
            }
            if (obj.captcha_number.val() === '' && obj.useCaptcha()) {
                alert('자동입력 방지문자를 입력해주세요.');
                obj.captcha_number.focus();
                return false;
            }
            var params = {
                mode: obj.useCaptcha() ? 'changePassword' : 'validPassword',
                password: obj.password.val(),
                captcha: obj.captcha_number.val()
            };
            $.post('../share/layer_sms_password_ps.php', params, function () {
                console.log(arguments);
                var response = arguments[0];
                if (response.error > 0) {
                    if (obj.useCaptcha()) {
                        obj.captcha_number.focus();
                    }
                    if (response.error === 300) {
                        obj.is_limit = true;
                        obj.limit_message = response.message;
                    }
                    alert(response.message);
                } else {
                    if (obj.useCaptcha()) {
                        dialog_alert(response.message, '알림', {
                            callback: function () {
                                BootstrapDialog.closeAll();
                                location.reload();
                            }
                        });
                    } else {
                        top.sms_password_callback(params.password);
                        BootstrapDialog.closeAll();
                    }
                }
            });
        });
    });
</script>
