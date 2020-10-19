<link type="text/css" href="/admin/gd_share/css/bootstrap-dialog.css" rel="stylesheet">
<script type="text/javascript" src="/admin/gd_share/script/bootstrap/bootstrap-dialog.js"></script>
<script type="text/javascript" src="/admin/gd_share/script/jquery/validation/jquery.validate.js"></script>
<div id="manager_password_app" style="width:95%; border: #000000 solid 1px; z-index:600; position: fixed; background-color: #FFFFFF;left: 10px; top:10px; display: block;">
    <img src="<?=PATH_ADMIN_GD_SHARE?>img/development/layer_manager_password_alert_app.png" border="0" style="width:100%;">
    <a href="javascript:manager_password_change();" style="margin:0; padding:0;position:absolute;bottom:10px;left:50%;"><img src="<?=PATH_ADMIN_GD_SHARE?>img/development/layer_manager_password_alert_app_btn.png" border="0"></a>
</div>
<script>
    function manager_password_change() {
        var loadChk = $('#layer_manager_password_change').length;
        var $ajax = $.ajax('../share/layer_manager_password_change.php', {
            method: "get",
        });
        $ajax.done(function (data) {
            if (loadChk == 0) {
                data = '<div id="layer_manager_password_change">' + data + '</div>';
            }
            var configure = {
                title: "쇼핑몰 관리 비밀번호 변경안내",
                size: BootstrapDialog.SIZE_WIDE,
                message: $(data),
                closable: true
            };
            // $('#manager_password_app').hide();
            BootstrapDialog.show(configure);
        });
    }
</script>