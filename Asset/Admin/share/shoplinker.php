<?php
if ($useShoplinker === 'n') {
?>
    <script type="text/javascript">
    // 다이얼로그 자동으로 닫기
    if (top.BootstrapDialog != undefined) {
        target = top;
    } else if (parent.BootstrapDialog != undefined) {
        target = parent;
    }

    target.BootstrapDialog.alert({
        type: BootstrapDialog.TYPE_INFO,
        title: '안내',
        message: '<div class="subject "><strong>마켓연동 서비스가 신청되어 있지 않습니다.<br/>신청 페이지로 이동합니다.</strong></div>',
        callback: function (result) {
            if (result) {
                location.href='/service/service_info.php?menu=convenience_shoplinker_info';
            }
        }
    });
    </script>
<?php
} else {
?>
    <!--form id="shoplinkerForm" name="shoplinkerForm" action="https://dev.shoplinker-s.com/eshop/login/godo_login" method="post" target="shoplinker"-->
    <form id="shoplinkerForm" name="shoplinkerForm" action="https://mgr.shoplinker-s.com/eshop/login/godo_login" method="post" target="shoplinker">
        <input type="hidden" name="shopKey" value="<?=$data['shopKey']?>">
        <input type="hidden" name="slinkerKey" value="<?=$data['slinkerKey']?>">
    </form>
    <iframe id='shoplinker' name='shoplinker' src='' frameborder='0' marginwidth='0' marginheight='0' width='100%' height='200'></iframe>
    <script>
        $(document).ready(function () {
            $('#shoplinkerForm').submit();

            $(window).resize(function() {
                $('#shoplinker').height($(window).height() - 89);
            });

            $('#shoplinker').height($(window).height() - 89);
        });
    </script>
<?php
}
?>

