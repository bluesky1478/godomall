<input type="hidden" name="pageId" value="<?php echo gd_isset($getPageID); ?>"/>

<form name="frmDesign" id="frmDesign" method="post" action="design_page_edit_ps.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="save"/>
    <input type="hidden" name="skinType" value="<?php echo $skinType; ?>"/>
    <input type="hidden" name="designPage" value="<?php echo gd_isset($getPageID); ?>"/>
    <input type="hidden" name="linkurl" value="<?php echo gd_isset($designInfo['file']['linkurl']); ?>"/>
    <input type="hidden" name="form_type" value="<?php echo gd_isset($designInfo['file']['form_type']); ?>"/>
    <input type="hidden" name="designPagePreview" value=""/>

    <div class="page-header js-affix mgb10">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <?php if ($designUrl['isPreview'] == true){ ?>
            <a class="btn btn-white" href="<?php echo $designUrl['realLinkurl'];?>" target="_blank">화면보기</a>
            <?php } ?>
            <button type="button" data-page="<?php echo $getPageID;?>" class="btn btn-white js-remove">디자인 페이지 삭제</button>
            <button type="button" data-page="<?php echo $getPageID;?>" class="btn btn-red-box js-saveas">새이름으로 저장</button>
            <input type="submit" value="디자인 페이지 저장" class="btn btn-red" />
        </div>
    </div>

    <div id="design-area">
        <!-- 현재 작업 스킨 내용 시작 -->
        <?php include($layoutCurrentSkin); ?>
        <!-- 현재 작업 스킨 내용 끝 -->

        <!-- 디자인 맵 시작 -->
        <?php include($layoutDesignMap); ?>
        <!-- 디자인 맵 끝 -->

        <!-- 디자인 화일 정보 폼 시작 -->
        <?php include($layoutDesignForm); ?>
        <!-- 디자인 화일 정보 폼 끝 -->

        <!-- 디자인 화일 에디터 시작 -->
        <?php include($layoutDesignEditor); ?>
        <!-- 디자인 화일 에디터 끝 -->
    </div>
</form>

<div id="design-code" data-spy="affix" data-offset-top="130">
    <div id="design-code-title">
        <span class="pdl20">치환코드</span>
        <span class="design-code-close"></span>
    </div>

    <div id="design-code-search" class="form-inline" style="margin:15px;">
        <form id="designCodeSearchForm">
            <input type="hidden" name="designPageId" value="<?php echo gd_isset($getPageID); ?>"/>
            <input type="hidden" name="mode" value="search"/>
            <select name="key" class="form-control">
                <option value="all" <?php echo gd_isset($selected['searchKeyFl']['all']); ?>>통합검색</option>
                <option value="designCode" <?php echo gd_isset($selected['searchKeyFl']['designCode']); ?>>치환코드</option>
                <option value="designCodeInfo" <?php echo gd_isset($selected['searchKeyFl']['designCodeInfo']); ?>>설명</option>
            </select>
            <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control" style="width:100px; height:28px;">
            <input type="submit" value="검색" class="btn btn-sm btn-black form-control" style="height:28px;">
        </form>
    </div>

    <div id="design-code-inner-area">
        <div id="design-code-inner">
            <!-- 현제페이지 치환코드 시작 -->
            <?php echo $designCode; ?>
            <!-- 현제페이지 치환코드 끝 -->

            <!-- 공통변수 치환코드 시작 -->
            <?php echo $commonVarCode; ?>
            <!-- 공통변수 치환코드 끝 -->

            <!-- 공통함수 치환코드 시작 -->
            <?php echo $commonFuncCode; ?>
            <!-- 공통함수 치환코드 끝 -->
        </div>
    </div>
</div>
<div class="clear-both"></div>

<div class="pd15 center">
    <?php if ($designUrl['isPreview'] == true){ ?>
        <!--<button type="button" class="btn btn-black btn-lg js-preview">편집소스 화면보기</button>-->
    <?php } ?>
</div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {
         // 디자인 페이지 수정
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

        // 편집소스 화면보기
        $('.js-preview').click(function (e) {
            DCPV.design_preview = window.open('about:blank');

            try {
                if (DCTM.editor_type == "codemirror" && DCTM.textarea_view_id == DCTM.textarea_merge_body) {
                    DCTC.ed1.setValue(DCTC.merge_ed.editor().getValue());
                }
            }
            catch(e) {}

            $('#frmDesign input[name=designPagePreview]').val('y');
            $('#frmDesign').submit();

            $('#frmDesign input[name=designPagePreview]').val('');
        });

        // 새이름으로 저장
        $('.js-saveas').click(function (e) {
            var params = {
                dirPath: '<?php echo $designInfo['dir']['path']; ?>' ,
                dirText: '<?php echo $designInfo['dir']['text']; ?>' ,
                skinType: '<?php echo $skinType; ?>',
                saveMode: 'saveas'
            };
            $.get('layer_design_page_create.php', params, function (data) {
                BootstrapDialog.show({
                    title: '새이름으로 저장',
                    message: $(data),
                    closable: true
                });
            });
        });

        // 디자인 페이지 삭제
        $('.js-remove').click(function () {
            var designPage = $(this).data('page');

            BootstrapDialog.show({
                title: '디자인 페이지 삭제',
                type: BootstrapDialog.TYPE_DANGER,
                message: '[' + designPage + '] 디자인 페이지를 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
                buttons: [
                {
                    id: 'btn-cancel',
                    label: '삭제 취소',
                    action: function(dialogItself){
                        dialogItself.close();
                    }
                },
                {
                    id: 'btn-del',
                    label: '[' + designPage + '] 디자인 페이지 삭제',
                    cssClass: 'btn-danger',
                    action: function(dialog) {
                        var $delButton = this;
                        var $cancelButton = dialog.getButton('btn-cancel');
                        $delButton.disable();
                        $cancelButton.disable();
                        $delButton.spin();
                        dialog.setClosable(false);
                        dialog.setMessage('[' + designPage + '] 디자인 페이지를 삭제 중입니다.');

                        $.ajax({
                            type: 'POST'
                            , url: 'design_page_edit_ps.php'
                            , data: {'mode': 'deleteDesignPage', 'designPage': designPage, 'skinType': '<?php echo $skinType; ?>'}
                            , success: function (res) {
                                if (res == '') {
                                    dialog.getModalBody().html('[' + designPage + '] 디자인 페이지가 삭제 되었습니다. 잠시후 완료 됩니다.');
                                    setTimeout(function() {
                                        document.location.replace('./design_skin_list.php');
                                    }, 1000);
                                } else {
                                    dialog.getModalBody().html('[' + designPage + '] 디자인 페이지 삭제에 실패 하였습니다. <br />실패 이유 : ' + res);
                                    setTimeout(function() {
                                        dialog.close();
                                    }, 3000);
                                }
                            }
                        });
                    }
                }]
            });
            return;
        });

        $('#designCodeSearchForm').submit(function(){
            var param = $(this).serialize();

            $.ajax({
                type: "POST",
                url: "../design/design_code_ps.php",
                data: param,
                dataType: "json",
                success: function (data) {
                    $('#design-code-inner').html(data);

                    if ($('.js-clipboard').length) {
                        // https://clipboardjs.com
                        var clipboard = new Clipboard('.js-clipboard');
                        clipboard.on('success', function (e){
                            var title = $(e.trigger).attr('title') == undefined ? '' : $(e.trigger).attr('title');
                            //alert('[' + title + '] 정보를 클립보드에 복사했습니다.\n<code>Ctrl+V</code>를 이용해서 사용하세요.');
                            e.clearSelection();
                        });
                        clipboard.on('error', function (e) {
                            console.error('Action:', e.action);
                            console.error('Trigger:', e.trigger);
                        });
                    }
                }
            });
            return false;
        });

        $(document).on({
            mouseenter: function () {
                $(this).find('.design-code-info').css('color','#fa2828');
                $(this).find('.design-code-copy').show();
            },
            mouseleave: function () {
                $(this).find('.design-code-info').css('color','#444');
                $(this).find('.design-code-copy').hide();
            }
        }, '.design-code-tbl td');

        $(document).on('click', '.design-code-tbl th', function(){
            if ($(this).hasClass('on')) {
                $(this).parent().parent().find('tr:not(:eq(0))').show();
                $(this).removeClass('on');
            } else {
                $(this).parent().parent().find('tr:not(:eq(0))').hide();
                $(this).addClass('on');
            }
        });

        changeCodeAreaHeight();
        $(window).resize(function() {
            changeCodeAreaHeight();
        });
        $('.design-code-btn button, .design-code-close').click(function(){
            var designCodeWidth = $('#design-code').width() + 30;

            if ($('.design-code-btn button').hasClass('code-close')) {
                $('#design-code').hide();
                $('#design-area, .information').css('width', '100%');
                $('.design-code-btn button').removeClass('code-close').text('치환코드 열기');
            } else {
                $('#design-code').show();
                $('#design-area, .information').css('width', 'calc(100% - ' + designCodeWidth + 'px)');
                $('.design-code-btn button').addClass('code-close').text('치환코드 닫기');
            }
            $('.js-maxlength').trigger('maxlength.reposition');
        });

        <?php if ($search['mode'] == 'search') { ?>
        $('.design-code-btn button').click();
        <?php } ?>
    });

    $('.design-code-key-btn button').bind('click',function () {
        $('.layer-design-code-key-btn').toggle();
    })

    // 미리보기 팝업
    function preview_popup() {
        var url = "<?php echo $designUrl['realLinkurl'];?>";
        DCPV.preview_popup(url, "<?php echo $getPageID;?>");
    }

    function code_view( sno ) {
        var title = "치환코드 예제";
        $.get('../design/code_preview.php',{ sno : sno }, function(data){

            data = '<div id="viewInfoForm">'+data+'</div>';

            var layerForm = data;

            BootstrapDialog.show({
                title:title,
                size: get_layer_size('normal'),
                message: $(layerForm),
                closable: true
            });
        });
    }

    function changeCodeAreaHeight() {
        $('#design-code-inner-area').height($('html').innerHeight() - ($('#header').height() + $('div.page-header').height()));
    }

    /*var viewType = 'close';
    var designCodeWidth = $('#design-code').width() + 10;
    var designAreaWidth = $('#design-area').width();
    $('#design-code').height($('html').innerHeight() - $('div.page-header').height());
    function designCodeView() {
        if (viewType == 'close') {
            $('#design-code').show();
            $('#design-area, .information').css('width', designAreaWidth - designCodeWidth + 'px');
            viewType = 'open';
        } else {
            $('#design-code').hide();
            $('#design-area, .information').css('width', '100%');
            viewType = 'close';
        }
    }*/
    //-->
</script>
