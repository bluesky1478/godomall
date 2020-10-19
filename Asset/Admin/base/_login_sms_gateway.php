<script type="text/javascript">
    <!--
    $(function(){
        // 열려있는 다이얼로그 닫기
        if (top.BootstrapDialog != undefined) {
            var dialogs = top.BootstrapDialog.dialogs;

            for (var index in dialogs) {
                var dialog = dialogs[index];
                if (dialog.isRealized() && dialog.isOpened()) {
                    dialog.setTitle('로그인 보안 인증');
                    dialog.setClosable(false);

                    // sms_auth 페이지 호출
                    $.get('./layer_sms_auth.php', function(data){
                        dialog.setMessage($(data));
                    });

                    dialog.onHidden(function () {

                    });
                }
            }
        }
    });
    //-->
</script>
