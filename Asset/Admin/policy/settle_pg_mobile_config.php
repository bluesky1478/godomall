<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $('#frmPG').validate({
            submitHandler: function (form) {
                var checkAutoSetting = '<?php echo gd_isset($data['pgAutoSetting']); ?>';
                var checkPgId = '<?php echo gd_isset($data['pgId']);?>';
                if (checkAutoSetting != 'y' || checkPgId == '') {
                    alert('<?php echo $pgNm;?> 서비스 신청을 해주세요.');
                    return false;
                }
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
            },
            messages: {
            }
        });
    });
    //-->
</script>
<form id="frmPG" name="frmPG" action="settle_ps.php" method="post" enctype="multipart/form-data" target="ifrmProcess">
    <input type="hidden" name="mode" value="pg_mobile_config" />
    <input type="hidden" name="pgName" value="<?=$pgMode;?>" />
    <input type="hidden" name="pgAutoSetting" value="<?php echo $data['pgAutoSetting']; ?>"/>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?>
            <small>계약된 전자결제(PG)의 설정을 하실 수 있습니다.</small>
        </h3>
        <div class="btn-group">
            <input type="submit" value="PG 정보 저장" class="btn btn-red" />
        </div>
    </div>

    <div class="notice-danger">아래 전자결제(PG) 업체중 계약을 맺은 한곳만 클릭한 후 정보를 입력하세요</div>
    <ul class="nav nav-tabs nav-justified mgb30" role="tablist">
        <?php
        if(count($gPg)>1) {
            foreach ($gPg as $key => $val) {
                if ($key === $pgMode) {
                    $classNm = 'active';
                } else {
                    $classNm = '';
                }
                ?>
                <li role="presentation" class="<?= $classNm ?>">
                    <a href="settle_pg_mobile_config.php?pgMode=<?= $key ?>" role="tab"><?= $val ?></a>
                </li>
            <?php }
        }?>
    </ul>

    <?php include($layoutPgContent); ?>
</form>
