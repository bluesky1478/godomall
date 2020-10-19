<input type="hidden" name="pageId" value="<?php echo gd_isset($getPageID); ?>"/>

<form name="frmDesign" id="frmDesign" method="post" action="../design/design_page_edit_ps.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="save"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>
    <input type="hidden" name="designPage" value="<?php echo gd_isset($getPageID); ?>"/>
    <input type="hidden" name="linkurl" value="<?php echo gd_isset($designInfo['file']['linkurl']); ?>"/>
    <input type="hidden" name="form_type" value="<?php echo gd_isset($designInfo['file']['form_type']); ?>"/>
    <input type="hidden" name="bgReset" value=""/>

    <div class="page-header js-affix mgb10">
        <h3><?php echo end($naviMenu->location); ?>
            <small>설정된 작업중인 스킨의 기본 레이아웃을 설정합니다.</small>
        </h3>
        <div class="btn-group">
            <input type="button" value="모든 페이지 일괄 적용" class="btn btn-red-box js-batch_apply"/>
            <input type="submit" value="기본 레이아웃 저장" class="btn btn-red" />
        </div>
    </div>

    <!-- 현재 작업 스킨 내용 시작 -->
    <?php include($layoutCurrentSkin); ?>
    <!-- 현재 작업 스킨 내용 끝 -->

    <!-- 디자인 맵 시작 -->
    <?php include($layoutDesignMap); ?>
    <!-- 디자인 맵 끝 -->

    <!-- 디자인 화일 정보 폼 시작 -->
    <?php include($layoutDesignForm); ?>
    <!-- 디자인 화일 정보 폼 끝 -->
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 디자인 스킨 레이아웃 설정 변경 하기
        $("#frmDesign").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
            },
            messages: {
            }
        });
    });

    // 모든 페이지 일괄 적용
    $('.js-batch_apply').click(function (e) {
        BootstrapDialog.show({
            title: '모든 페이지 일괄 적용',
            message: '모든 페이지에 현재의 기본 레이아웃이 적용됩니다.<br/>' +
                '진행후 복구가 되지 않으므로 신중하게 적용하세요.<br/><hr/>' +
                '※ 모든 페이지 일괄 적용 : 페이지 레이아웃만 적용, 배경설정은 수정되지 않음<br/>' +
                '※ 모든 페이지 일괄 적용(배경초기화) : 일괄 적용이 되면서 배경설정도 전부 없어짐',
            type: BootstrapDialog.TYPE_WARNING,
            buttons: [
            {
                id: 'btn-cancel',
                label: '적용 취소',
                action: function(dialogItself){
                    dialogItself.close();
                }
            },
            {
                id: 'btn-batch',
                label: '모든 페이지 일괄 적용',
                cssClass: 'btn-red',
                action: function(dialog) {
                    var $batchButton = this;
                    var $resetButton = dialog.getButton('btn-reset');
                    var $cancelButton = dialog.getButton('btn-cancel');
                    $batchButton.disable();
                    $resetButton.disable();
                    $cancelButton.disable();
                    $batchButton.spin();
                    dialog.setClosable(false);
                    dialog.setMessage('모든 페이지 일괄 적용 중입니다.');
                    $('input[name=\'mode\']').val('batch_apply');
                    $('#frmDesign').submit();
                }
            },
            {
                id: 'btn-reset',
                label: '모든 페이지 일괄 적용(배경초기화)',
                cssClass: 'btn-red',
                action: function(dialog) {
                    var $batchButton = dialog.getButton('btn-batch');
                    var $resetButton = this;
                    var $cancelButton = dialog.getButton('btn-cancel');
                    $batchButton.disable();
                    $resetButton.disable();
                    $cancelButton.disable();
                    $resetButton.spin();
                    dialog.setClosable(false);
                    dialog.setMessage('모든 페이지 일괄 적용(배경초기화) 중입니다.');
                    $('input[name=\'bgReset\']').val('yes');
                    $('input[name=\'mode\']').val('batch_apply');
                    $('#frmDesign').submit();
                }
            }]
        });
    });
    //-->
</script>
