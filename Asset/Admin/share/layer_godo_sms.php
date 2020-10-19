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
<table class="table table-cols">
    <tbody>
    <tr>
        <th>인증수단</th>
        <td>
            <label for="" class="control-label">
                <input type="radio" name="" class="form-control" checked="checked">고도 회원 휴대폰번호 인증
            </label>
            <button type="button" class="btn btn-sm btn-gray mgl20 js-request-smsauth">인증번호 요청</button>
        </td>
    </tr>
    <tr>
        <th>인증번호</th>
        <td class="form-inline">
            <input type="text" name="checkAuthKey" id="checkAuthKey" maxlength="8" size="8" class="form-control">
            <strong class="text-red">남은 인증시간 : <span id="countDown">-분 -초</span></strong>
        </td>
    </tr>
    </tbody>
</table>
<div class="text-center">
    <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
    <button type="submit" id="modify" class="btn btn-black btn-lg js-complete-smsauth">인증완료</button>
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
        var $js_request_smsauth = $('.js-request-smsauth');
        $js_request_smsauth.click(function(e){
            authRequest = 1;
            $.ajax({
                type: 'POST',
                url:'../share/layer_godo_sms_ps.php',
                data : 'mode=getSmsAuthKey',
                success: function(result) {
                    if (result.trim() == 'Y'){
                        stopCountDown();
                        initCountDown();
                        alert('인증번호를 발송했습니다.\n인증번호가 오지 않으면 휴대폰번호 정보가 고도 회원정보와\n일치하는지 확인해 주세요.');
                    } else {
                        $js_request_smsauth.removeClass('display-none');
                        alert('인증번호 발송에 실패하였습니다. 다시 요청해 주세요.');
                        authRequest = 0;
                    }
                },
                error: function() {
                    $js_request_smsauth.removeClass('display-none');
                    alert('인증번호 발송에 실패하였습니다. 다시 요청해 주세요.');
                    authRequest = 0;
                }
            });
        });

        // 인증번호 체크
        $('.js-complete-smsauth').click(function(e){
            // 요청이 없는 경우 요청하라고 권고
            if (authRequest === 0 || time <= 0) {
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
                                cssClass: 'btn-black',
                                action: function(dialog) {
                                    dialog.close();
                                    stopCountDown();
                                }
                            }],
                            onhidden: function(dialog) {
                                <?php if (isset($layerTitle)) { ?>
                                // 현 레이어에 새로운 레이어 콘텐츠를 넣는다.
                                $.get(uri, null, function (data) {
                                    $('.bootstrap-dialog-title').text('<?=$layerTitle?>');
                                    $('#layer-wrap').html(data);
                                });
                                <?php } else { ?>
                                // 받아온 폼데이터를 submit 처리
                                $('#frmHidden').submit();
                                <?php } ?>
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
        });
    });

    // 카운트 시작 함수 호출
    function initCountDown() {
        $(".js-request-smsauth").addClass("display-none");
        runCount = setInterval(function () {
            $.ajax({
                type: 'POST',
                url: '../share/layer_godo_sms_ps.php',
                data: 'mode=getRestTime',
                async: false,
                success: function (result) {
                    time = result;

                    // 시간이 다 됐을 시 카운트 정지
                    if (parseInt(time) <= 0) {
                        alert('인증시간이 초과되었으니, 인증번호를 다시 한번 요청해주세요.');
                        stopCountDown();
                        time = 0;
                    }

                    min = parseInt((time % 3600) / 60);
                    sec = time % 60;

                    if (min === 0) {
                        min = '00';
                    }

                    if (sec < 10) {
                        sec = '0' + sec;
                    }

                    $("#countDown").text(min + '분 ' + sec + '초');
                }
            });
        }, 1000);
    }

    // 카운트 정지
    function stopCountDown() {
        $('.js-request-smsauth').removeClass('display-none');
        clearInterval(runCount);
    }
</script>
