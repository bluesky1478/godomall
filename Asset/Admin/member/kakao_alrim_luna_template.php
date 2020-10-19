<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>
<?php
if (gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === true) {
    ?>
    <ul class="nav nav-tabs mgb30">
        <li role="presentation">
            <a href="#biz" aria-controls="biz" role="tab" data-toggle="tab">비즈엠</a>
        </li>
        <li role="presentation" class="active">
            <a href="#luna" aria-controls="luna" role="tab" data-toggle="tab">루나소프트</a>
        </li>
    </ul>
    <?php
}
?>
<div class="manager_div">
    루나소프트 알림톡 관리자 페이지가 새창으로 연결됩니다.<br />
    팝업 차단 설정이 되어있다면 설정 해제 후 아래 새로고침 버튼을 누르거나 화면을 다시 열어주세요.<br />
    <button name="link" id="link" class="btn btn-lg btn-black">새로고침</button>
</div>
<style rel="stylesheet" type="text/css">
    .manager_div { text-align:center; height:500px; padding-top:200px; line-height:25px; }
</style>
<script type="text/javascript">
    $(function () {
        if('<?=$lunaUse?>' == 'y') {
            window.open('http://v1.lunasoft.co.kr/account/lunatalk_template.html', "aceLogin");
        } else {
            alert('루나소프트 알림톡 사용시 확인가능합니다');
        }
        $("#link").click(function () {
            location.reload();
        });
        // SMS 발송 조건 / 문구 설정 탭
        $('.nav-tabs a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
            var hrefValue = $(this).prop('href');
            var arrHrefValue = hrefValue.split('#');
            var keyValue = arrHrefValue[1];
            if(keyValue == 'biz'){
                location.href = 'kakao_alrim_template.php';
            }else if(keyValue == 'luna'){
                location.href = 'kakao_alrim_luna_template.php';
            }
        });
    });
</script>
