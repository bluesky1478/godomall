<form id="frmConfig" action="google_ads_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="type" value="config" />
    <input type="hidden" name="company" value="google" />
    <input type="hidden" name="mode" value="config" />

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red">
    </div>

    <div class="table-title">
        구글 상품 피드 설정
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>사용여부</th>
            <td>
                <label class="radio-inline" >
                    <input type="radio" name="feedUseFl" value="y" <?php echo gd_isset($checked['feedUseFl']['y']); ?> />사용
                </label>
                <label class="radio-inline" >
                    <input type="radio" name="feedUseFl" value="n" <?php echo gd_isset($checked['feedUseFl']['n']); ?> />사용안함
                </label>
            </td>
        </tr>
        <tr>
            <th>상품 피드 다운로드</th>
            <td>
                <input id="btn-download" type="button" class="btn btn-gray btn-sm" value="다운로드" <?php echo gd_isset($disabled); ?>/>
                <?php echo $lastFeedFileInfo; ?>
                <div class="notice-info">
                    다운로드 받은 파일은 구글 머천트 센터 설정의 상품 피드 메뉴에서 업로드하실 수 있습니다.
                </div>
            </td>
        </tr>
    </table>
</form>
<form id="checkTxtForm" action="google_ads_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="type" value="checkTxtFile"/>
</form>
<form id="downTxtForm" action="google_ads_ps.php" method="post" target= "ifrmProcess">
    <input type="hidden" name="type" value="download"/>
</form>
<script type="text/javascript">
<!--
function adsActivate() {
    if ($(':radio[name=feedUseFl]:checked').val() == 'y' && $(':radio[name=feedUseFl]:checked').val() != '<?=$data['feedUseFl']?>') {
        console.log('check & create feed');
        BootstrapDialog.show({
            title: '로딩중',
            message: '<img src="<?=PATH_ADMIN_GD_SHARE?>script/slider/slick/ajax-loader.gif"> <b>구글 상품 피드 생성 중입니다. 잠시만 기다려주세요.</b>',
            closable: true
        });
        $("#checkTxtForm").submit(); // txt 파일 생성 요청
    }
}

$(document).ready(function () {
    $('#btn-download').click(function () {
        console.log('download...');
        $("#downTxtForm").submit(); // txt 파일 다운로드
    });
});
//-->
</script>