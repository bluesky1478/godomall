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
 * @link http://www.godo.co.kr
 */
?>

<div class="notice-info">
    쇼핑몰 보안을 위하여 고도 회원정보로 인증 후 접근하실 수 있습니다.
</div>
<table class="table table-cols" style="margin-top:10px;">
    <tbody>
    <tr>
        <th>인증수단</th>
        <td style="padding-top:10px !important; padding-bottom:10px !important;">
            <label for="" class="control-label">
                <input type="radio" name="" class="form-control" checked="checked" style="display:none">고도 회원 휴대폰번호 인증
            </label><br/>
            <button type="button" class="sms_button_black1 js-request-smsauth">인증번호 요청</button>
        </td>
    </tr>
    <tr>
        <th class="form-inline" style="padding-top:10px !important;">인증번호</th>
        <td class="form-inline" style="padding-top:10px !important;">
            <input type="text" name="checkAuthKey" id="checkAuthKey" maxlength="8" size="8" class="form-control" style="display:none;">
            <div><strong class="text-red" style="color:red">남은 인증시간 : <span id="countDown">-분 -초</span></strong></div>
        </td>
    </tr>
    </tbody>
</table>
<div class="text-center" style="margin-top:20px;">
    <input type="hidden" id="device_uid" value="<?=$device_uid?>">
    <button type="submit" id="modify" class="sms_button_red js-complete-smsauth">인증완료</button>
    <button type="button" class="sms_button_black2 js-layer-close">닫기</button>
</div>

<?php if (!isset($layerTitle)) { ?>
<form id="frmHidden" method="post" action="<?=$action?>" target="ifrmProcess">
    <?php foreach ($formData as $data) { ?>
        <input type="hidden" name="<?=$data['name']?>" value="<?=$data['value']?>">
    <?php } ?>
    <input type="hidden" name="mode" value="<?=$mode?>">
</form>
<?php } ?>

<script type="text/javascript">
    var min = 0;
    var sec = 0;
    var time = 0;
    var runCount;
    var uri = '<?=$requestUri?>';
    var authRequest = 0;

    $(document).ready(function () {
        // 제목변경
        $('.bootstrap-dialog-title').text('보안인증');

        // 인증번호 발송
        $('.js-request-smsauth').click(function(e){
            authRequest = 1;
            $.ajax({
                type: 'POST',
                url:'../share/layer_godo_sms_ps.php',
                data : 'mode=getSmsAuthKey',
                success: function(result) {
                    if (result.trim() == 'Y'){
                        $('#checkAuthKey').css('display', '');
                        stopCountDown();
                        initCountDown();
                        alert('인증번호를 발송했습니다.\n인증번호가 오지 않으면 휴대폰번호 정보가 고도 회원정보와\n일치하는지 확인해 주세요.');
                    }
                    else {
                        alert('인증번호 발송에 실패하였습니다. 다시 요청해 주세요.');
                        authRequest = 0;
                    }
                },
                error: function() {
                    alert('인증번호 발송에 실패하였습니다. 다시 요청해 주세요.');
                    authRequest = 0;
                }
            });
        });

        // 인증번호 체크
        $('.js-complete-smsauth').click(function(e){
            // 요청이 없는 경우 요청하라고 권고
            if (authRequest == 0 || time <= 0) {
                alert('인증번호를 요청해 주세요');
                return false;
            }

            // 인증번호 입력 확인
            var chkAuthKey = $("#checkAuthKey").val();
            if (chkAuthKey == '') {
                alert('인증번호를 입력해 주세요');
                return false;
            }

            $.ajax({
                type: 'POST',
                url:'../share/layer_godo_sms_ps.php',
                data : 'mode=checkSmsAuth&checkAuthKey=' + chkAuthKey,
                success: function(result) {
                    if (result.trim() == 'Y') {
                        BootstrapDialog.show({
                            title: '본인인증',
                            message: '인증이 완료되었습니다.',
                            buttons: [{
                                label: '확인',
                                action: function(dialog) {
                                    dialog.close();
                                    stopCountDown();
                                }
                            }],
                            onhidden: function(dialog) {
                                location.replace('/mobileapp/mobileapp_sms_accept_process.php?device_uid=' + $('#device_uid').val());
                            }
                        });
                    } else {
                        alert(result);
                    }
                },
                error: function() {
                    alert('인증을 다시 시도해 주시기 바랍니다.');
                }
            });
        });

        // 창닫기시 타이머 ajax 중지
        $('.js-layer-close').bind('click', function(e){
            stopCountDown();
            window.location.replace('https://mobileapp.godo.co.kr/new/app/login.php');
        });
    });

    // 카운트 시작 함수 호출
    function initCountDown() {
        runCount = setInterval("startCountDown()", 1000);
    }

    // 카운트 및 노출하기
    function startCountDown() {
        $.ajax({
            type: 'POST',
            url:'../share/layer_godo_sms_ps.php',
            data : 'mode=getRestTime',
            success: function(result) {
                time = result;

                // 시간이 다 됐을 시 카운트 정지
                if (parseInt(time) <= 0) {
                    alert('인증시간이 초과되었으니, 인증번호를 다시 한번 요청해주세요.');
                    $('#checkAuthKey').css('display', 'none');
                    stopCountDown();
                    time = 0;
                }

                min = parseInt((time%3600)/60);
                sec = time%60;

                if (min == 0) min = '00';
                if (sec < 10) sec = '0'+sec;

                $("#countDown").text(min + '분 ' + sec + '초');
            }
        });
    }

    // 카운트 정지
    function stopCountDown() {
        clearInterval(runCount);
    }
</script>
