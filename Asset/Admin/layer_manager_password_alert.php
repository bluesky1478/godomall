<div id="manager_password" style="border: #000000 solid 1px; z-index:600; position: fixed; background-color: #FFFFFF;left: 100px; top:100px; display: block;">
    <img src="<?=PATH_ADMIN_GD_SHARE?>img/development/layer_manager_password_alert.png" border="0" usemap="#manager_password_map">
    <map name="manager_password_map">
        <area shape="rect" coords="926,24,973,68" href="#" onclick="$('#manager_password').hide();" alt="닫기">
        <area shape="rect" coords="322,532,681,611" href="#" onclick="manager_password_change();" alt="바로가기">
    </map>
</div>
<script>
    function manager_password_change() {
        var loadChk = $('#layer_manager_password_change').length;
        $.ajax({
            url: '../share/layer_manager_password_change.php',
            type: 'get',
            async: false,
            success: function (data) {
                if (loadChk == 0) {
                    data = '<div id="layer_manager_password_change">' + data + '</div>';
                }
                var configure = {
                    title: "쇼핑몰 관리 비밀번호 변경안내",
                    size: BootstrapDialog.SIZE_WIDE,
                    message: $(data),
                    closable: true
                };
                // $('#manager_password').hide();
                BootstrapDialog.show(configure);
            }
        });
    }
</script>