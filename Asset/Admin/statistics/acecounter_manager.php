<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>
<div class="manager_div">
    에이스카운터+ 관리자 페이지가 새창에서 연결됩니다.<br />
    팝업 차단 설정이 되어있다면 설정 해제 후 아래 새로고침 버튼을 누르거나 화면을 다시 열어주세요.<br />
    <button name="link" id="link" class="btn btn-lg btn-black">새로고침</button>
</div>
<form name="loginForm" id="loginForm" method="post">
    <input type="hidden" name="shopKey" value="<?=$data['shopKey']?>" class="loginCheck" />
    <input type="hidden" name="hashData" value="<?=$data['hashData']?>" class="loginCheck" />
    <input type="hidden" name="shopSno" value="<?=$data['shopSno']?>" class="loginCheck" />
</form>
<style rel="stylesheet" type="text/css">
    .manager_div { text-align:center; height:500px; padding-top:200px; line-height:25px; }
</style>
<script type="text/javascript">
    $(function () {
        var shopKey = $("input[name='shopKey']").val();
        if(shopKey) {
            send();
        } else {
            location.href = '../statistics/acecounter_info.php';
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
            window.open('about:blank', "aceLogin");
            $("#loginForm").attr({"action":"<?=$data['action']?>", "target":"aceLogin"}).submit();
        }
    }
</script>
