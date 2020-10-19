<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>
<div class="manager_div">
    도매88 관리자 페이지가 새창에서 연결됩니다.<br />
    팝업 차단 설정이 되어있다면 설정 해제 후 아래 새로고침 버튼을 누르거나 화면을 다시 열어주세요.<br />
    <button name="link" id="link" class="btn btn-lg btn-black">새로고침</button>
</div>
<form name="loginForm" id="loginForm" method="post">
    <input type="hidden" name="partner_id" value="<?=$data['partnerId']?>" class="loginCheck" />
    <input type="hidden" name="secret_key" value="<?=$data['secretKey']?>" class="loginCheck" />
    <input type="hidden" name="identity" value="<?=$data['identity']?>" class="loginCheck" />
    <input type="hidden" name="service" value="<?=$data['service']?>" class="loginCheck" />
    <input type="hidden" name="response_type" value="<?=$data['responseType']?>" class="loginCheck" />
    <input type="hidden" name="data" value="<?=gd_htmlspecialchars($data['data'])?>" class="loginCheck" />
</form>
<style rel="stylesheet" type="text/css">
    .manager_div { text-align:center; height:500px; padding-top:200px; line-height:25px; }
</style>
<script type="text/javascript">
    $(function () {
        var secret_key = $("input[name='secret_key']").val();
        if(secret_key) {
            send();
        } else {
            location.href = '../service/service_info.php?menu=goods_dome88_info';
        }
        $("#link").click(function () {
            location.reload();
        });
    });
    function send() {
        var checks = true;
        $('.loginCheck').each(function () {
            if($(this).val() == '' || $(this).val() == undefined) {
                checks = false;
                return false;
            }
        });
        if(checks) {
            window.open('about:blank', "dome88Login");
            $("#loginForm").attr({"action":"<?=$data['action']?>", "target":"dome88Login"}).submit();
        }
    }
</script>
