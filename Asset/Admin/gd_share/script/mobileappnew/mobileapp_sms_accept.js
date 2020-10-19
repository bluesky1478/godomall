/**
* 모바일앱 메인
**/
$(document).ready(function(){
    $('.navbar').css('display', 'none');

    $('#smsAccept').click(function (e) {
        var device_uid = $('#device_uid').val();
        var params = {
            device_uid: device_uid
        };
        $.get('mobileapp_layer_sms_accept.php', params, function (data) {
            BootstrapDialog.show({
                title: '보안 인증',
                message: $(data),
                closable: false
            });
        });
    });

    $( "#smsAccept" ).trigger( "click" );
});
