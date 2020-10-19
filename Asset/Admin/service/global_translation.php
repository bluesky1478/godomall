<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>
<div class="translation_div">
    헬프마켓 서비스가 새창에서 연결됩니다.</br>
    팝업 차단 설정이 되어있다면 설정 해제 후 아래 새로고침 버튼을 누르거나 화면을 다시 열어주세요.</br>
    </br>
    헬프마켓은 고도와 오투잡이 제휴하여 진행하는 서비스 입니다.</br>
    번역 서비스는 '재능구매' 메뉴를 확인해주세요.</br>
    <button name="reload" id="reload" class="btn btn-lg btn-black">새로고침</button>
</div>
<style rel="stylesheet" type="text/css">
    .translation_div { text-align:center; height:500px; padding-top:200px; line-height:25px; }
</style>
<script type="text/javascript">
    $(document).ready(function () {
        window.open('https://www.godo.co.kr/promotion/help/help.gd#translate');

        $("#reload").click(function () {
            location.reload();
        });
    })
</script>
