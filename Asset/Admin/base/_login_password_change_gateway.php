<script type="text/javascript">
    <!--
    var layerPassword;
    $(function () {
        if (!layerPassword) {
            $.get('<?= $layerURL ?>', function (data) {
                layerPassword = top.BootstrapDialog.show({
                    name: "layer_password_change",
                    title: "쇼핑몰 관리 비밀번호 변경안내",
                    size: BootstrapDialog.SIZE_WIDE,
                    message: $(data),
                    closable: false
                });
            });
        }
    });
    //-->
</script>
