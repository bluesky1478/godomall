<script type="text/javascript" xmlns="http://www.w3.org/1999/html">
    <!--
    var agent = navigator.userAgent.toLowerCase();
    var isOptionLoaded = false;

    $(document).ready(function () {
        $(document).on('click', '.btn-remove-layer', function(){
            var loc = $(this).data('loc');
            var locNo = $(this).data('loc-no');
            var fieldID = 'optionValueLayer' + loc;
            var optionCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;
            $('#option_optionCnt_' + loc).val(optionCnt);			// 옵션값 수 변경

            // 옵션값의 ID 변경 (순서데로)
            var targetID = '';
            var newID = '';
            var idArr = new Array('optVal_', 'option_optionValue_', 'optValCode_', 'optValImage_');
            var idArrFirst = new Array('optValDetail_', 'imageStorageModeOptionGoodsImage_', 'imageStorageModeOptionGoodsText_');
            for (var i = locNo; i < optionCnt; i++) {
                for (var j = 0; j < idArr.length; j++) {
                    targetID = $('#' + fieldID).find('input[id*=\'' + idArr[j] + loc + '\']').eq(i).attr('id');
                    newID = idArr[j] + loc + '_' + i;
                    $('#' + targetID).attr('id', newID);
                    if (j == 1) {
                        $('#' + newID).closest('div').find('.btn-remove').attr('data-loc-no', i);
                    }
                }
                if (loc == 0) {
                    for (var j = 0; j < idArrFirst.length; j++) {
                        targetID = $('#' + fieldID).find('input[id*=\'' + idArrFirst[j] + loc + '\']').eq(i).attr('id');
                        newID = idArrFirst[j] + loc + '_' + i;
                        $('#' + targetID).attr('id', newID);
                    }
                }
            }

            // 옵션값 삭제시
            optionValueChange = true;

            option_grid_layer();
        });

        $(window).scroll(function () {
            var height = $(document).scrollTop();

            if (height >= 1) {
                $('.scrollTop').css('display','block');
                $('.scrollDown').css('display','block');
            } else {
                $('.scrollTop').css('display','none');
            }

            if (Math.round($(window).scrollTop()) >= $(document).height() - $(window).height()) {
                $('.scrollDown').css('display','none');
            }
        });

        if (Math.round($(window).scrollTop()) >= $(document).height() - $(window).height()) {
            $('.scrollDown').css('display','none');
        }
        <?php
        if ($data['optionFl'] == 'y') {
            echo '	display_toggle_layer(\'optionExistLayer\',\'show\');' . chr(10);
            if ($data['optionCnt'] > 0) {
                echo '	fill_option_layer();' . chr(10);
            }
            echo '	disabled_switch_layer(\'stockCnt\',true);' . chr(10);
        }
        ?>
        setGridSettingLayer();

        <?php
        // 상품 재고 수정 권한 없는 경우 상품재고 수정 불가
        if (empty($data['goodsNo']) === false && empty($optionSession) && Session::get('manager.functionAuth.goodsStockModify') != 'y') {
        ?>
        $('#option_stockCntApply').prop('readonly', true);
        $('[name="optionY[stockCnt][]"]').prop('readonly', true);
        <?php
        }
        ?>

        option_setting_layer(<?=$data['optionCnt'];?>);

        // 탑버튼 클릭
        $(document).on("click", "a[href=#top]", function(e) {
            $('html body').animate({scrollTop: 0}, 'fast');
            $('.scrollDown').css('display','block');
            $('.scrollTop').css('display','none');
        });

        // 다운버튼 클릭
        $(document).on("click", "a[href=#down]", function(e) {
            $('html body').animate({scrollTop: $(document).scrollTop($(document).height())}, 'fast');
            $('.scrollDown').css('display','none');
            $('.scrollTop').css('display','block');
        });

    });

    $(document).on('click', '.btn-red', function(){
        if($('#optionGridTable').length == 0){
            if(confirm('옵션이 설정 되지 않았습니다.\n옵션 창을 닫으시겠습니까?')){
                self.close();
            }
        }else{
            $('[name="addOptions"]').submit();
        }
    });

    var optionGridChange = false;			// 옵션 변경 여부
    var optionValueChange = false;			// 옵션값 변경 여부
    var optionValueFill = true;				// 옵션값 채울지의 여부

    /**
     * 상품선택 Ajax layer
     */
    function select_goods_layer() {
        $('[name="optionFl"]:last').prop('checked', 'checked');
        display_toggle_layer('optionExistLayer','hide');
        display_toggle_layer('optionGrid','hide');
        disabled_switch_layer('callGoodsOption',true);

        var loadChk = $('#layerSelectGoodsFormLayer').length;

        var parameters = {
            'optionRegister' : 'y',
            'layerFormID' : 'layerSelectGoodsFormLayer',
        };
        $.get('../share/layer_goods.php', parameters, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerSelectGoodsFormLayer">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '상품 선택', 'wide');
        });
    }

    /**
     * 조회항목 설정
     */
    function setGridSettingLayer(cnt){
        if(cnt == 0 || cnt == '' || cnt == undefined){
            cnt = <?=count($data['option'])?>
        }
        //옵션 조회항목 설정에 따른 표기
        $('.colOptionCostPrice').hide();
        $('.colOptionPrice').hide();
        $('.colStockCnt').hide();
        //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
        //$('.colOptionStopFl').hide();
        //$('.colOptionRequestFl').hide();
        $('.colOptionViewFl').hide();
        $('.colOptionSellFl').hide();
        $('.colOptionDeliveryFl').hide();
        $('.colOptionCode').hide();
        $('.colOptionMemo').hide();
        //보임/안보임 설정
        <?php
        foreach($goodsOptionGridConfigList as $key => $value){
        ?>$('.col<?=ucfirst($key)?>').show();
        <?php
        }
        ?>
        //순서 설정
            <?php
            $firstValue = true;
            foreach($goodsOptionGridConfigList as $key => $value){
            if($firstValue){
                $left = 'optionValueLast';
                $firstValue = false;
            }
            ?>for(i=0; i<cnt+2; i++){
            $('.col<?=ucfirst($key)?>:eq('+i+')').insertAfter($('.col<?=ucfirst($left)?>:eq('+i+')'));
        }
        <?php
        $left = $key;
        }
        ?>
    }

    /**
     * 자주쓰는 옵션 Ajax layer
     */
    function manage_option_list_layer() {
        var loadChk = $('#layerOptionListForm').length;

        $.get('layer_goods_option_list.php', null, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerOptionListForm">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '자주쓰는 옵션 리스트');
        });
    }

    /**
     * 자주쓰는 옵션 등록 Ajax layer
     */
    function manage_option_register_layer() {

        var optionCnt = $('#optionY_optionCnt').val();

        // 옵션 개수가 있는 지는 체크
        if (optionCnt == '' || optionCnt == 0) {
            alert('옵션을 먼저 기재해주세요.');
            return false;
        }

        var loadChk = $('#layerOptionRegisterFormLayer').length;
        var scmNo = '<?=Session::get('manager.scmNo')?>';

        $.post('layer_goods_option_register.php', {'scmNo':scmNo}, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerOptionRegisterFormLayer">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '자주쓰는 옵션 등록');
        });
    }

    /**`
     * 출력 여부
     *
     * @param string arrayID 해당 ID
     * @param string modeStr 출력 여부 (show or hide)
     */
    function display_toggle_layer(thisID, modeStr) {
        var mode = $('#frmGoods [name="mode"]').val();
        if(mode == 'modify') {
            if (modeStr == 'show') {
                $('#' + thisID).attr('class', '');
                if(thisID == 'goodsNmExt') $('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: '1103px'});
            } else if (modeStr == 'hide') {
                $('#' + thisID).attr('class', 'display-none');
            }
        }else{
            if (modeStr == 'show') {
                $('#' + thisID).attr('class', '');
                if ($('input[name="applyGoodsCopy"]').val() != '' && thisID == 'goodsNmExt') {
                    //$('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: '1103px'});
                    var left = $('input[name="goodsNm"]').next('span.bootstrap-maxlength').css('left');
                    $('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: left});
                } else {
                    if (thisID == 'goodsNmExt') $('.js-maxlength').trigger('maxlength.reposition');
                }
            } else if (modeStr == 'hide') {
                $('#' + thisID).attr('class', 'display-none');
            }
        }
    }

    /**
     * 체크박스 출력여부에 따라서 노출
     *
     * @param string checkbox 해당 ID
     * @param string display 출력 여부 (show or hide)
     */
    function checkbox_toggle_layer_layer(checkboxName, displayInput) {
        if($("input[name='"+checkboxName+"']").prop("checked")) {
            $("input[name='"+displayInput+"']").show();
        } else {
            $("input[name='"+displayInput+"']").hide();
        }
    }

    function display_toggle_class_layer_layer(thisName, thisClass) {
        var modeStr = $('input[name="' + thisName + '"]:checked').val();
        if (modeStr == 'y') {
            $('.' + thisClass).removeClass('display-none');
            // !중요! 숨겨진 엘리먼트를 보여지게 할 경우 maxlength 표시 부분의 위치가 어긋난다. 이에 아래 트리거를 사용해 위치를 재 설정한다.
        } else if (modeStr == 'n') {
            $('.' + thisClass).addClass('display-none');
        }
    }

    /**
     * 출력 토글
     *
     * @param string thisID 해당 ID
     */
    function view_switch_layer(thisID) {
        $('#' + thisID).slideToggle('slow');
    }

    /**
     * disabled 여부
     *
     * @param string  inputName 해당 input Box의 name
     * @param boolean modeBool 출력 여부 (true or false)
     */
    function disabled_switch_layer(inputName, modeBool) {
        if ($('input[name=\'' + inputName + '\']').length) {
            $('input[name=\'' + inputName + '\']').prop('disabled', modeBool);
        } else if ($('select[name=\'' + inputName + '\']').length) {
            $('select[name=\'' + inputName + '\']').prop('disabled', modeBool);
        }
    }

    <?php if ($data['optionFl'] == 'y') {?>
    /**
     * 옵션 정보 채우기
     */
    function fill_option_layer() {
        option_setting_layer(<?=$data['optionCnt'];?>);
        if ( (navigator.appName == 'Netscape' && navigator.userAgent.search('Trident') != -1) || (agent.indexOf("msie") != -1) ) {
        }else{
            if(!isOptionLoaded && opener.$('#optionTmp *> #option').length > 0){
                isOptionLoaded = true;
                return;
            }
        }
        <?php
        $optionImageAddUrlFl = "n";
        for ($i = 0; $i < $data['optionCnt']; $i++) {
            $optionCnt = count(gd_isset($data['option']['optVal'][$i + 1]));
            echo "	$('#option_optionName_layer_" . $i . "').val('" . gd_htmlspecialchars_slashes($data['optionName'][$i], 'add') . "');" . chr(10);
            echo "	$('#option_optionCnt_" . $i . "').val('" . $optionCnt . "');" . chr(10);
            echo "	option_value_conf_layer(" . $i . ", " . $optionCnt . ", true);" . chr(10);
            if (is_array($data['option']['optVal'][$i + 1])) {
                $j = 0;
                $optIcon = [];
                foreach ($data['option']['optVal'][$i + 1] as $key => $val) {
                    echo "	$('#option_optionValue_" . $i . "_" . $j . "').val('" . gd_htmlspecialchars_slashes($val, 'add') . "');" . chr(10);
                    $optIcon[json_encode($val)] = $j;    // 옵션 아이콘의 키값
                    $j++;
                }

                // 옵션 추가노출 여부
                if (!gd_isset($data['optionIcon'])) {
                    continue;
                }

                // 옵션 아이콘 값
                foreach ($data['optionIcon'] as $key => $val) {
                    // 번호가 맞지 않으면 패스
                    if ($val['optionNo'] != $i) {
                        continue;
                    }
                    // 옵션 아이콘의 키값
                    $k = $optIcon[json_encode($val['optionValue'])];


                    // 기존 상품 복사 등록/수정이 아닌경우
                    echo "	$('#option_Icon_sno_" . $i . "_" . $k . "').val('" . $val['sno'] . "');" . chr(10);
                    echo "	$('#option_Icon_optionNo_" . $i . "_" . $k . "').val('" . $val['optionNo'] . "');" . chr(10);
                    echo "	$('#option_Icon_colorCode_" . $i . "_" . $k . "').val('" . $val['colorCode'] . "');" . chr(10);
                    if ($data['imageStorage'] == 'url') {
                        echo "	$('#option_Icon_iconImageText_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['iconImage'], 'add') . "');" . chr(10);
                        echo "	$('#option_Icon_goodsImageText_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['goodsImage'], 'add') . "');" . chr(10);
                    } else {
                        if ($val['iconImage'] || $val['goodsImage']) {
                            if (strtolower(substr($val['goodsImage'],0,4)) =='http' ) {
                                $optionImageAddUrlFl = "y";
                                $preViewImg = gd_html_preview_image($val['goodsImage'], $data['imagePath'],'url', 20, 'goods', null, null, true);
                                echo "	$('#option_Icon_goodsImageText_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['goodsImage'], 'add') . "');" . chr(10);

                                if($preViewImg) $preViewImg .= " <input type='checkbox' name='optionYIcon[optionImageDeleteFl][".$i."][".$k."]' value='y'>삭제";
                                echo "	$('#option_Icon_goodsImageUrl_" . $i . "_" . $k . "').html('" . gd_htmlspecialchars_slashes($preViewImg, 'add') . "');" . chr(10);

                            } else {
                                $preViewImg = gd_html_preview_image($val['goodsImage'], $data['imagePath'], $data['imageStorage'], 20, 'goods', null, null, true);
                                echo "	$('#option_Icon_goodsImageName_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['goodsImage'], 'add') . "');" . chr(10);

                                if($preViewImg) $preViewImg .= " <input type='checkbox' name='optionY[optionImageDeleteFl][".$i."][".$k."]' value='y'>삭제";
                                echo "	$('#option_Icon_goodsImage_" . $i . "_" . $k . "').html('" . gd_htmlspecialchars_slashes($preViewImg, 'add') . "');" . chr(10);
                            }
                        }
                    }
                }
            }
        }

        // 사용된 옵션 값 제거
        if (isset($data['option']['optVal']) === true) {
            unset($data['option']['optVal']);
        }

        if($optionImageAddUrlFl =='y') {
            echo "$('input[name=optionImageAddUrl]').click();". chr(10);
        }
        ?>

        if ( (navigator.appName == 'Netscape' && navigator.userAgent.search('Trident') != -1) || (agent.indexOf("msie") != -1) ) {
            //IE일 경우 URL정보 채우기
            for(i=0;i<opener.$('#optionTmp *> input[name="optionYIcon[goodsImageText][0][]"]').length;i++){
                $('input[name="optionYIcon[goodsImageText][0][]"]:eq('+i+')').val(opener.$('#optionTmp *> input[name="optionYIcon[goodsImageText][0][]"]:eq('+i+')').val());
            }
        }

        optionGridChange = true;
        syncMainChecked();
        IEmainFileAccess();
        option_image_add_url_layer();
    }

    /**
     * 옵션값 채우기
     */
    function fill_value_layer() {
        <?php
        if (gd_isset($data['option'])) {

        foreach($data['option'] as $k => $v) {
        $optionName = [];
        for ($i = 1; $i <= DEFAULT_LIMIT_OPTION; $i++) {
            if($v['optionValue'.$i]) $optionName[]= $v['optionValue'.$i];
        }
        ?>

        var optionItem = $('#optionGridTable input[value="<?=implode(STR_DIVISION,$optionName)?>"]').closest('tr').attr("id");
        $("#"+optionItem+ " input[name='optionY[sno][]']").val("<?=$v['sno']?>");
        if($("#"+optionItem+ " input[name='optionY[optionPrice][]']").val() == '' && $("#"+optionItem+ " input[name='optionY[optionPrice][]']").val() != '0'){
            $("#"+optionItem+ " input[name='optionY[optionPrice][]']").val("<?= gd_money_format(gd_isset($v['optionPrice']), false)?>");
        }
        if($("#"+optionItem+ " input[name='optionY[optionCostPrice][]']").val() == '' && $("#"+optionItem+ " input[name='optionY[optionCostPrice][]']").val() != '0'){
            $("#"+optionItem+ " input[name='optionY[optionCostPrice][]']").val("<?= gd_money_format(gd_isset($v['optionCostPrice']), false)?>");
        }
        $("#"+optionItem+ " input[name='optionY[stockCnt][]']").val("<?=$v['stockCnt']?>");
        $("#"+optionItem+ " select[name='optionY[optionViewFl][]']").val("<?=$v['optionViewFl']?>");
        <?php
        if($v['optionSellFl'] == 't'){
        ?>$("#"+optionItem+ " select[name='optionY[optionSellFl][]']").val("<?=$v['optionSellCode']?>");<?php
        }else{
        ?>$("#"+optionItem+ " select[name='optionY[optionSellFl][]']").val("<?=$v['optionSellFl']?>");<?php
        }
        ?>
        <?php
        if($v['optionDeliveryFl'] == 't'){
        ?>$("#"+optionItem+ " select[name='optionY[optionDeliveryFl][]']").val("<?=$v['optionDeliveryCode']?>");<?php
        }else{
        ?>$("#"+optionItem+ " select[name='optionY[optionDeliveryFl][]']").val("<?=$v['optionDeliveryFl']?>");<?php
        }
        ?>
        $("#"+optionItem+ " input[name='optionY[optionCode][]']").val("<?=$v['optionCode']?>");
        $("#"+optionItem+ " input[name='optionY[optionMemo][]']").val("<?=$v['optionMemo']?>");

        <?php }
        }
        ?>
    }
    <?php }?>

    /**
     * 옵션정보 리셋 - 전부 지우기
     */
    function option_reset_layer() {
        $('#optionY_optionCnt').val('1');
        $('#option').html('');
        $('#optionGrid').html('');
        optionGridChange = false;

        $('#optionApplyBtn').val('옵션 정보 적용');
        $('#info-bottom').addClass("display-none");
        option_setting_layer('<?=$data['optionCnt'];?>');
    }

    /**
     * 옵션 세팅 - 옵션명 설정 및 추가 정보
     *
     * @param string thisCnt 옵션 개수
     */
    function option_setting_layer(thisCnt) {
        if(thisCnt == undefined || thisCnt == "undefined" || thisCnt == ""){
            return;
        }
        if ( (navigator.appName == 'Netscape' && navigator.userAgent.search('Trident') != -1) || (agent.indexOf("msie") != -1) ) {
        }else{
            if (!isOptionLoaded && opener.$('#optionTmp *> #option').length > 0) {
                $('#optionRegisterCell > #option').remove();
                $('#optionRegisterCell').append(opener.$('#optionTmp *> #option').clone());
                getMainFileList();
                return;
            }
        }

        var fieldID = 'option';
        var fieldCnt = $('#' + fieldID).find('input[id*=\'option_optionName_layer_\']').length;
        var fieldChk = parseInt(thisCnt - fieldCnt);
        var addHtml = '';
        var templateHtml = '';

        var imageStorage = $('#imageStorage').val();

        if(imageStorage =='url') {
            var imageUploadView = "display-none";
            var imageUrlView = "display-inline";
        } else {
            var imageUploadView = "display-inline";
            var imageUrlView = "display-none";
        }

        if (fieldCnt == '0' && fieldChk > 0) {
            templateHtml += '<table class="table table-cols"  id="opation_add_tbody_layer">';
            templateHtml += '<colgroup><col class="width-2xs" /><col  class="width-lg"/><col/></colgroup>';
            templateHtml += '<tr>';
            templateHtml += '<th class="left width-md">옵션명</th>';
            templateHtml += '<th class="left" style="width:380px">옵션값</th>';
            templateHtml += '<th class="left width-md">옵션가</th>';
            templateHtml += '<th class="left" style="width:325px">옵션 이미지 <span class="js-option-image-url '+imageUploadView+'"  >( <input type="checkbox" name="optionImageAddUrl" value="y" onclick="option_image_add_url_layer();"/> URL 직접입력 추가사용 )</span></th>';
            templateHtml += '<th class="left width-xs">추가/삭제</th>';
            templateHtml += '<th class="left"></th>';
            templateHtml += '</tr>';
        }

        if (fieldChk > 0) {

            for (var i = fieldCnt; i < thisCnt; i++) {

                addHtml += '<tr class="option-items">';
                addHtml += '<td><input type="text" id="option_optionName_layer_' + i + '" name="optionY[optionName][]" value="" class="form-control width-md" placeholder="ex)사이즈" onblur="option_grid_layer();" maxlength="30" /></td>';
                addHtml += '<td colspan="5" style="padding:0px;margin:0px;">';
                addHtml += '<table id="optionValueLayer' + i + '" class="table table-cols table-cols-none" width="100%">';
                addHtml += '<colgroup><col style="width:325px"/><col/></colgroup>';
                addHtml += '<tr id="optVal_' + i + '_0">';
                addHtml += '<td style="width:380px"><div class="form-inline">';
                addHtml += '<input type="text" id="option_optionValue_' + i + '_0" data-option-sno="'+i+'" name="optionY[optionValue][' + i + '][]" value="" class="form-control"  style="width:330px;"  placeholder="Enter키를 이용 옵션값을 연속적으로 입력하세요. ex)XL" onblur=" if(option_value_check_layer(\'' + i + '\',\'0\') == true) { option_grid_layer(); } " maxlength="100" />';

                //addHtml += ' <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus" onclick="option_value_conf_add_layer(' + i + ')" />';
                addHtml += '<input type="hidden" id="option_optionCnt_' + i + '" name="optionY[optionCnt][]" value="1" class="input_int" onblur="option_value_conf_layer(' + i + ',this.value);" />';
                addHtml += '</div></td>';

                addHtml += '<td class="width-md"><div class="form-inline"><input type="text" id="option_all_price_' + i + '_0"data-option-sno="\'+i+\'" name="optionP[optionPrice][\' + i + \'][]" value="" class="form-control" onblur=" option_grid_layer()" style="width:100px;"></div></td>';

                if (i == 0) {
                    addHtml += '<td id="optValDetail_' + i + '_0" style="width:325px"><div class="form-inline">';
                    addHtml += '<span id="imageStorageModeOptionGoodsImage_' + i + '_0"  class="'+imageUploadView+'" >';
                    addHtml += '<input type="file" name="optionYIcon[goodsImage][' + i + '][]" value="" class="form-control" style="height:30px" />';
                    addHtml += '<input type="hidden" id="option_Icon_goodsImageName_' + i + '_0" name="optionYIcon[goodsImage][' + i + '][]" value="" />';
                    addHtml += ' <span id="option_Icon_goodsImage_' + i + '_0" ></span>';
                    addHtml += '</span>';
                    addHtml += '<span id="imageStorageModeOptionGoodsText_' + i + '_0" class="'+imageUrlView+'">';
                    addHtml += '<input type="text" id="option_Icon_goodsImageText_' + i + '_0" name="optionYIcon[goodsImageText][' + i + '][]" value="" class="form-control width90p" onChange="$(\'#option_Icon_goodsImageText_0_0\').next().val(\'y\')" />';
                    addHtml += '<input type="hidden" name="optionYIcon[goodsImageTextChanged][' + i + '][]" value="n" />';
                    addHtml += ' <span id="option_Icon_goodsImageUrl_' + i + '_0" ></span>';
                    addHtml += '</span>';
                    addHtml += '</div></td>';
                    addHtml += '<td class="width-xs"><input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus" onclick="option_value_conf_add_layer(' + i + ')" /></td>';
                    addHtml += '<td></td>';
                }else{
                    addHtml += '<td style="width:325px"></td>';
                    addHtml += '<td><input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus" onclick="option_value_conf_add_layer(' + i + ')" /></td>';
                    addHtml += '<td></td>';
                }


                addHtml += '</tr>';


                addHtml += '</table>';
                //addHtml += '<td><div class="form-inline">';
                //addHtml += '<input type="text" id="option_all_price_"' + i + 'data-option-sno="\'+i+\'" name="optionP[optionPrice][\' + i + \'][]" value="" class="form-control"  style="width:100px;">';
                //addHtml += '</div></td>';
                addHtml += '</td>';
                //addHtml += '<td>추가/삭제 버튼</td>';
                addHtml += '</tr>';


            }


        } else if (fieldChk < 0) {
            for (var j = thisCnt; j < fieldCnt; j++) {
                $('#opation_add_tbody_layer').find('tr.option-items:last').remove();
            }
        }

        if (fieldCnt == '0' && fieldChk > 0) $('#' + fieldID).append(templateHtml + addHtml + '</html>');
        else $('#opation_add_tbody_layer').append(addHtml);


        $('input[name*=\'optionCnt\']').number_only();
        $('.imageStorageText').html($('#imageStorage option:selected').text());
        option_grid_layer();
        init_file_style();

        if($(document).height() - $(window).height() > 0){
            $('.scrollDown').css('display','block');
        } else {
            $('.scrollDown').css('display','none');
        }

        $("input[id*='option_optionValue_']").off('keypress');
        $("input[id*='option_optionValue_']").on('keypress', function (e) {
            if (e.which == 13) {

                var selOption = $(this).attr('id').split("_");
                if(option_value_check_layer(selOption[2],selOption[3]) == true) {
                    option_value_conf_add_layer($(this).data('option-sno'));
                }
                $("input[id*='option_optionValue_" + $(this).data('option-sno') + "']:last").focus();
                e.preventDefault();
                return false
            }
        });
    }

    /**
     * 옵션값 추가
     *
     * @param string loc 옵션 순서 (1-5)
     */
    function option_value_conf_add_layer(loc) {
        //var optionCnt = $('#option_optionCnt_' + loc).val();
        var optionCnt = $("input[id*='option_optionValue_"+loc+"']").length;
        var addOptionCnt = 0;
        if (!optionCnt) {
            optionCnt = 0;
        }

        if(option_total_check_layer(loc)) {
            addOptionCnt = parseInt(optionCnt) + 1;

            $('#option_optionCnt_' + loc).val(addOptionCnt);

            option_value_conf_layer(loc, addOptionCnt, true);
        }

        if($(document).height() - $(window).height() > 0){
            $('.scrollDown').css('display','block');
        } else {
            $('.scrollDown').css('display','none');
        }

        arrangeFileField();
    }

    function option_total_check_layer(loc) {

        var optionTotalCnt = $("#optionY_optionCnt").val();
        var totalOption = 1;

        for(var i = 0; i < optionTotalCnt; i++ ) {

            var tmp = $("input[id*='option_optionValue_"+i+"']").length;

            if(loc == i) tmp += 1;

            totalOption = totalOption*tmp;
        }

        if(totalOption > 1000) {
            alert("옵션의 조합은 1000개 이하로 가능합니다.");
            return false;
        } else {
            return true;
        }

    }

    /**
     * 옵션값 삭제 후 수량 및 ID 변경
     *
     * @param string loc 옵션 순서 (1-5)
     * @param string locNo 순서 번호
     */
    function option_value_conf_remove_layer(loc, locNo) {
        var fieldID = 'optionValueLayer' + loc;
        var optionCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;
        $('#option_optionCnt_' + loc).val(optionCnt);			// 옵션값 수 변경

        // 옵션값의 ID 변경 (순서데로)
        var targetID = '';
        var newID = '';
        var idArr = new Array('optVal_', 'option_optionValue_', 'optValCode_', 'optValImage_');
        var idArrFirst = new Array('optValDetail_', 'imageStorageModeOptionGoodsImage_', 'imageStorageModeOptionGoodsText_');
        for (var i = locNo; i < optionCnt; i++) {
            for (var j = 0; j < idArr.length; j++) {
                targetID = $('#' + fieldID).find('input[id*=\'' + idArr[j] + loc + '\']').eq(i).attr('id');
                newID = idArr[j] + loc + '_' + i;
                $('#' + targetID).attr('id', newID);
            }
            if (loc == 0) {
                for (var j = 0; j < idArrFirst.length; j++) {
                    targetID = $('#' + fieldID).find('input[id*=\'' + idArrFirst[j] + loc + '\']').eq(i).attr('id');
                    newID = idArrFirst[j] + loc + '_' + i;
                    $('#' + targetID).attr('id', newID);
                }
            }
        }

        // 옵션값 삭제시
        optionValueChange = true;

        option_grid_layer();
    }

    /**
     * 옵션값 설정 - 옵션값 ,색상표, 아이콘 등
     *
     * @param string loc 옵션 순서 (1-5)
     * @param string thisCnt 옵션값 개수
     * @param string loadChk 옵션값 개수 제한 체크 여부 (기본 false)
     */
    function option_value_conf_layer(loc, thisCnt, loadChk) {
        if (!loadChk) {
            // 옵션값 개수 제한
            var optionCnt = $('#optionY_optionCnt').val();
            var optTotVal = 1;
            for (var i = 0; i < optionCnt; i++) {
                if ($('#option_optionCnt_' + i).val() > 0) {
                    optTotVal = parseInt(optTotVal) * parseInt($('#option_optionCnt_' + i).val());
                }
            }
            if (optTotVal > <?=DEFAULT_LIMIT_OPTION_VALUE;?>) {
                dialog_confirm('옵션값 개수가 ' + optTotVal + '개 입니다.<br/>옵션이 <?=DEFAULT_LIMIT_OPTION_VALUE;?>개 이상이 되면<br/>너무 많아 작성이 힘들어 지거나 느려질수 있습니다.<br/>계속 옵션 작성 하시겠습니까?<br/>(확인-그대로 진행, 취소-해당 옵션을 재설정함)', function (result) {
                    if (!result) {
                        $('#option_optionCnt_' + loc).val('');
                        thisCnt = 0;
                        //return false;
                    }
                });
            }
        }

        var fieldID = 'optionValueLayer' + loc;
        var fieldCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;
        var fieldChk = parseInt(thisCnt - fieldCnt);

        var imageStorage = $('#imageStorage').val();
        if(imageStorage =='url') {
            var imageUploadView = "display-none";
            var imageUrlView = "display-block";
        } else {
            var imageUploadView = "display-block";
            var imageUrlView = "display-none";
        }

        if($('input[name="optionImageAddUrl"]').is(":checked")) {
            imageUrlView  = "display-none display-block";
        }

        var addHtml = '';
        if (fieldChk > 0) {

            for (var i = fieldCnt; i < thisCnt; i++) {
                addHtml += '<tr id="optVal_' + loc + '_' + i + '">';
                if (loc == 0)  addHtml += '<td ><div class="form-inline">';
                else addHtml += '<td style="width:325px"><div class="form-inline">';
                addHtml += '<input type="text" id="option_optionValue_' + loc + '_' + i + '" data-option-sno="'+loc+'" name="optionY[optionValue][' + loc + '][]" value="" class="form-control" style="width:330px;" placeholder="Enter키를 이용 옵션값을 연속적으로 입력하세요. ex)XL" onblur=" if(option_value_check_layer(\'' + loc + '\',\'' + i + '\') == true) { option_grid_layer(); } " />';
                //addHtml += ' <input type="button" class="btn btn-sm btn-white btn-icon-minus btn-remove" id="remove_option_' + loc + '_' + i + '" data-loc="' + loc + '" data-loc-no="' + i + '" onclick="field_remove(\'optVal_' + loc + '_' + i + '\');" value="삭제" /> ';
                if (i == 0) {
                    addHtml += ' <span class="button black small"><input type="button" value="추가" onclick="option_value_conf_add_layer(' + loc + ')" /></span>';
                    addHtml += '<input type="hidden" id="option_optionCnt_' + i + '" name="optionY[optionCnt][]" value="" class="input_int" onblur="option_value_conf_layer(' + i + ',this.value);" />';
                }

                if (loc == 0) addHtml += '<td><div class="form-inline"><input type="text" id="option_all_price_' + loc + '_' + i + '"data-option-sno="'+i+'" name="optionP[optionPrice][' + i + '][]" value="" class="form-control"  style="width:100px;" onblur=" option_grid_layer()"></div></td>';
                else addHtml += '<td><div class="form-inline"><input type="text" id="option_all_price_' + loc + '_' + i + '"data-option-sno="'+i+'" name="optionP[optionPrice][' + i + '][]" value="" class="form-control"  style="width:100px;" onblur=" option_grid_layer()"></div></td>';

                addHtml += '</div></td>';
                if (loc == 0) {
                    addHtml += '<td id="optValDetail_' + loc + '_' + i + '" class="width-xl"><div class="form-inline">';
                    addHtml += '<span id="imageStorageModeOptionGoodsImage_' + loc + '_' + i + '"  class="'+imageUploadView+'">';
                    addHtml += '<input type="file" name="optionYIcon[goodsImage][' + loc + '][]" value="" class="form-control" style="height:30px" />';
                    addHtml += '<input type="hidden" id="option_Icon_goodsImageName_' + loc + '_' + i + '" name="optionYIcon[goodsImage][' + loc + '][]" value="" />';
                    addHtml += ' <span id="option_Icon_goodsImage_' + loc + '_' + i + '"></span>';
                    addHtml += '</span>';
                    addHtml += '<span id="imageStorageModeOptionGoodsText_' + loc + '_' + i + '" class="'+imageUrlView+'">';
                    addHtml += '<input type="text" id="option_Icon_goodsImageText_' + loc + '_' + i + '" name="optionYIcon[goodsImageText][' + loc + '][]" value="" class="form-control width90p" onChange="$(\'#option_Icon_goodsImageText_0_0\').next().val(\'y\')" />';
                    addHtml += '<input type="hidden" name="optionYIcon[goodsImageTextChanged][' + loc + '][]" value="n" />';
                    addHtml += ' <span id="option_Icon_goodsImageUrl_' + loc + '_' + i + '"></span>';
                    addHtml += '</span>';
                    addHtml += '</div></td>';
                    addHtml += '<td class="width-xs"><input type="button" class="btn btn-sm btn-white btn-icon-minus btn-remove" id="remove_option_' + loc + '_' + i + '" data-loc="' + loc + '" data-loc-no="' + i + '" onclick="deleteFileField($(this));field_remove(\'optVal_' + loc + '_' + i + '\');" value="삭제" /></td>';
                    addHtml += '<td></td>';
                }else{
                    addHtml += '<td style="width:325px"></td>';
                    addHtml += '<td><input type="button" class="btn btn-sm btn-white btn-icon-minus btn-remove" id="remove_option_' + loc + '_' + i + '" data-loc="' + loc + '" data-loc-no="' + i + '" onclick="deleteFileField($(this)); field_remove(\'optVal_' + loc + '_' + i + '\');" value="삭제" /></td>';
                    addHtml += '<td></td>';
                }
                addHtml += '</tr>';
            }
        } else if (fieldChk < 0) {
            for (var j = thisCnt; j < fieldCnt; j++) {
                $('#optVal_' + loc + '_' + j).remove();
            }
        }

        $('#' + fieldID).append(addHtml);
        init_file_style();

        $("input[id*='option_optionValue_']").off('keypress');
        $("input[id*='option_optionValue_']").on('keypress', function (e) {
            if (e.which == 13) {
                var selOption = $(this).attr('id').split("_");
                if(option_value_check_layer(selOption[2],selOption[3]) == true) {
                    option_value_conf_add_layer($(this).data('option-sno'));
                }
                $("input[id*='option_optionValue_" + $(this).data('option-sno') + "']:last").focus();
                e.preventDefault();
                return false
            }
        });

        if (thisCnt > 0) {
            /*
             for (var k = 0; k < thisCnt; k++) {
             if ($('#option_optionValue_' + loc + '_' + k).val() == '') {
             return false;
             }
             }
             */
            option_grid_layer();
        }
    }


    /**
     * 동일한 옵션값 여부를 체크
     *
     * @param string loc 옵션 순서 (1-5)
     * @param string locNo 순서 번호
     */
    function option_value_check_layer(loc, locNo) {
        var thisOptionValue = $('#option_optionValue_' + loc + '_' + locNo).val().trim();
        // 입력값이 없는경우
        if (thisOptionValue == '') {
            return true;
        }
        var chkOptionValue = '';
        var fieldID = 'optionValueLayer' + loc;
        var fieldCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;

        for (var i = 0; i < fieldCnt; i++) {
            if (locNo != i) {
                chkOptionValue = $('#option_optionValue_' + loc + '_' + i).val().trim();
                if (thisOptionValue == chkOptionValue) {
                    alert('현재 입력한 옵션값과 동일한 옵션값이 존재합니다.\n다시 입력해 주세요!');
                    $('#option_optionValue_' + loc + '_' + locNo).val('');
                    $('#option_optionValue_' + loc + '_' + locNo).focus();

                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 옵션값 테이블 설정
     *
     * @param string manualFl 수동 체크 여부
     */
    /**
     * 옵션값 테이블 설정
     *
     * @param string manualFl 수동 체크 여부
     */
    function option_grid_layer(manualFl) {
        // 수동 여부 체크
        if (typeof manualFl == 'undefined') {
            manualFl = 'n';
        }

        if (manualFl == 'n'){
            return;
        }

        for(i=0;i<$('[id*="option_optionName_layer"]').length;i++){
            if(
                $('[id*="option_optionName_layer"]:eq('+i+')').val().indexOf('`') != -1 ||
                $('[id*="option_optionName_layer"]:eq('+i+')').val().indexOf('‘') != -1 ||
                $('[id*="option_optionName_layer"]:eq('+i+')').val().indexOf('‘') != -1 ||
                $('[id*="option_optionName_layer"]:eq('+i+')').val().indexOf('“') != -1 ||
                $('[id*="option_optionName_layer"]:eq('+i+')').val().indexOf('“') != -1 ||
                $('[id*="option_optionName_layer"]:eq('+i+')').val().indexOf('\'') != -1 ||
                $('[id*="option_optionName_layer"]:eq('+i+')').val().indexOf('"') != -1
            ){
                alert('옵션명에 사용 할 수 없는 문자가 있습니다.');
                return;
            }
        }
        for(i=0;i<$('[id*="option_optionValue_"]').length;i++){
            if(
                $('[id*="option_optionValue_"]:eq('+i+')').val().indexOf('`') != -1 ||
                $('[id*="option_optionValue_"]:eq('+i+')').val().indexOf('‘') != -1 ||
                $('[id*="option_optionValue_"]:eq('+i+')').val().indexOf('‘') != -1 ||
                $('[id*="option_optionValue_"]:eq('+i+')').val().indexOf('“') != -1 ||
                $('[id*="option_optionValue_"]:eq('+i+')').val().indexOf('“') != -1 ||
                $('[id*="option_optionValue_"]:eq('+i+')').val().indexOf('\'') != -1 ||
                $('[id*="option_optionValue_"]:eq('+i+')').val().indexOf('"') != -1
            ){
                alert('옵션명에 사용 할 수 없는 문자가 있습니다.');
                return;
            }
        }

        if (optionGridChange){
            if(!confirm('옵션 정보 재적용 시 기존 옵션 정보는 삭제됩니다.\n현재 등록된 옵션 정보로 조합하여 재적용 하시겠습니까?')){
                return;
            }
        }

        var fieldID = 'optionGrid';
        var fieldTable = fieldID + 'Table';
        var optionCnt = $('#optionY_optionCnt').val();
        var optTotCnt = 1;

        // 옵션 개수가 있는 지는 체크
        if (optionCnt == '' || optionCnt == 0) {
            if (manualFl == 'y') {
                alert('[옵션설정 오류]\n\n옵션 개수를 선택해 주세요!');
            }
            return false;
        }

        // 옵션값이 있는지를 체크하며, 전체 옵션값 개수를 계산을 함
        for (var i = 0; i < optionCnt; i++) {
            if ($('#option_optionName_layer_' + i).val() == '') {
                if (manualFl == 'y') {
                    alert('옵션명을 입력해 주세요!');
                }
                return false;
            }
            if ($('#option_optionCnt_' + i).val() <= 0) {
                if (manualFl == 'y') {
                    alert('옵션값이 설정되있지 않습니다.\n\n추가를 눌러 수량에 맞게 옵션값을 넣어 주세요.!');
                }
                return false;
            } else {
                for (var j = 0; j < $('#option_optionCnt_' + i).val(); j++) {
                    if ($('#option_optionValue_' + i + '_' + j).val() == '') {
                        if (manualFl == 'y') {
                            alert('옵션값을 입력해 주세요!');
                        }
                        return false;
                    }
                }
            }
            //var optTotCnt = optTotCnt * $('#option_optionCnt_' + i).val();
            var optTotCnt = optTotCnt * $('[id*="option_optionValue_'+i+'"]').length;
        }

        // 옵션값을 수정시 옵션GroptionY[optionCnt][]id 를 다시 갱신 할지를 선택함
        if (optionGridChange == true) {
            if ($('#' + fieldTable).length) {
                $('#' + fieldTable).remove();
            }
        }

        // 옵션 값 개수 설정
        var valGab = new Array();
        var valCnt = new Array();
        for (var i = 0; i < optionCnt; i++) {
            if (i == 0) {
                valGab[i] = optTotCnt / $('[id*="option_optionValue_'+i+'"]').length;
            } else {
                valGab[i] = valGab[i - 1] / $('[id*="option_optionValue_'+i+'"]').length;
            }
            valCnt[i] = $('[id*="option_optionValue_'+i+'"]').length;
        }

        // 옵션 값 체크 설정
        var valChk = new Array();
        var valIdNo = new Array();
        <?php
        for ($i = 0; $i < DEFAULT_LIMIT_OPTION; $i++) {
        echo '	valChk[' . $i . ']		= 1;' . chr(10);
        echo '	valIdNo[' . $i . ']		= 0;' . chr(10);

        ?>
        optionLength = $('[id^="option_optionValue_<?php echo $i; ?>_"]').length;
        for(idChange=0;idChange<optionLength;idChange++){
            $('[id^="option_optionValue_<?php echo $i; ?>_"]:eq('+idChange+')').prop('id', 'option_optionValue_<?php echo $i; ?>_'+idChange);
            $('[id^="option_all_price_<?php echo $i; ?>_"]:eq('+idChange+')').prop('id', 'option_all_price_<?php echo $i; ?>_'+idChange);
            $('[id^="option_Icon_goodsImageName_<?php echo $i; ?>_"]:eq('+idChange+')').prop('id', 'option_Icon_goodsImageName_<?php echo $i; ?>_'+idChange);
            $('[id^="optValDetail_<?php echo $i; ?>_"]:eq('+idChange+')').prop('id', 'optValDetail_<?php echo $i; ?>_'+idChange);
            $('[id^="imageStorageModeOptionGoodsImage_<?php echo $i; ?>_"]:eq('+idChange+')').prop('id', 'imageStorageModeOptionGoodsImage_<?php echo $i; ?>_'+idChange);
            $('[id^="imageStorageModeOptionGoodsText_<?php echo $i; ?>_"]:eq('+idChange+')').prop('id', 'imageStorageModeOptionGoodsText_<?php echo $i; ?>_'+idChange);
            $('[id^="option_Icon_goodsImageText_<?php echo $i; ?>_"]:eq('+idChange+')').prop('id', 'option_Icon_goodsImageText_<?php echo $i; ?>_'+idChange);
        }
        <?php
        }
        ?>

        // 옵션 그리기
        var addHtml = '';

        addHtml += '<div class="table-title gd-help-manual">■ 옵션 정보</div>';
        addHtml += '<table cellpadding="0" cellspacing="0" width="100%" height="30" id="optionOptionTable">';
        addHtml += '<tr>';
        addHtml += '<td>';
        addHtml += '<table cellpadding="0" cellspacing="0" width="100%">';
        addHtml += '<tr>';
        addHtml += '<td width="150">';
        addHtml += '<div class="btn-group">';
        addHtml += '<button type="button" class="btn btn-white btn-icon-bottom js-moverow goodsChoice_downArrowMore" data-direction="bottom">맨아래</button>';
        addHtml += '<button type="button" class="btn btn-white btn-icon-down js-moverow goodsChoice_downArrow" data-direction="down">아래</button>';
        addHtml += '<button type="button" class="btn btn-white btn-icon-up js-moverow goodsChoice_upArrow" data-direction="up">위</button>';
        addHtml += '<button type="button" class="btn btn-white btn-icon-top js-moverow goodsChoice_upArrowMore" data-direction="top">맨위</button>';
        addHtml += '</div>';
        addHtml += '</td>';
        addHtml += '<td><input type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_option_layer();" value="옵션 추가" />';
        addHtml += '<input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="delete_option(\'optionY[optionNo][]\',\'tbl_option_info_\');" value="선택 삭제" /></td>';
        addHtml += '<td class="right"><button type="button" class="btn btn-sm btn-black" style="height: 27px !important;" onclick="confirmGrid()">조회항목설정</button><button type="button" class="js-layer-register btn btn-sm btn-black display-none" data-type="goods_option_grid_config" data-goods-option-grid-mode="<?=$goodsOptionAdminGridMode?>"></button></td>';
        addHtml += '</tr>';
        addHtml += '</table>';
        addHtml += '</td>';
        addHtml += '</tr>';
        addHtml += '</table>';

        addHtml += '<table id="' + fieldTable + '" class="table table-cols">';
        for (var j = 0; j <= optTotCnt; j++) {
            if (j == 0) {
                addHtml += '<thead>';
                addHtml += '<tr>';
                addHtml += '<th class="width2p"><input type="checkbox" id="allOptionCheck" value="y" onclick="check_toggle(this.id,\'optionY[optionNo][]\');"/></th>';
                addHtml += '<th class="width2p">번호</th>';
                for (var k = 0; k < optionCnt; k++) {
                    optClass = '';
                    if(k == optionCnt - 1){
                        optClass = 'colOptionValueLast';
                    }
                    addHtml += '<th class="width10p '+optClass+'">' + $('#option_optionName_layer_' + k).val() + '</th>';
                }
                addHtml += '<th class="width10p colOptionCostPrice">옵션 매입가</th>';
                addHtml += '<th class="width10p colOptionPrice">옵션가</th>';
                addHtml += '<th class="width10p colStockCnt">재고량</th>';
                //addHtml += '<th class="width10p colOptionStopFl">판매중지수량</th>';
                //addHtml += '<th class="width10p colOptionRequestFl">확인요청수량</th>';
                addHtml += '<th class="width10p colOptionViewFl">옵션노출상태</th>';
                addHtml += '<th class="width10p colOptionSellFl">옵션품절상태</th>';
                addHtml += '<th class="width10p colOptionDeliveryFl">옵션배송상태</th>';
                addHtml += '<th class="width10p colOptionCode">자체 옵션코드</th>';
                addHtml += '<th class="width10p colOptionMemo">메모</th>';
                addHtml += '</tr>';
                addHtml += '</thead>';
                addHtml += '<tr>';
                addHtml += '<th class="center colOptionValueLast" colspan="' + (parseInt(optionCnt) + 2) + '"><input type="button" onclick="option_value_apply_layer();" value="옵션 정보 일괄 적용" class="btn btn-xs btn-gray" /></th>';
                addHtml += '<th class="center colOptionCostPrice"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_optionCostPriceApply" class="form-control width-2xs" /><?=gd_currency_string();?></div></th>';
                addHtml += '<th class="center colOptionPrice"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_opotionPriceApply" class="form-control width-2xs" /><?=gd_currency_string();?></div></td>';
                addHtml += '<th class="center colStockCnt"><div class="form-inline"><input type="text" id="option_stockCntApply" class="form-control width-2xs" />개</div></td>';
                //addHtml += '<th class="center colOptionStopFl"><div class="form-inline"><nobr><select class="form-control" id="option_optionStopFlApply" ><option value="y">사용함</optiton><option value="n" selected>사용안함</optiton></select> <input type="text" id="option_optionStopApply" class="form-control width-2xs" />개</nobr></div></td>';
                //addHtml += '<th class="center colOptionRequestFl"><div class="form-inline"><nobr><select class="form-control" id="option_optionRequestFlApply" ><option value="y">사용함</optiton><option value="n" selected>사용안함</optiton></select> <input type="text" id="option_optionRequestApply" class="form-control width-2xs" />개</nobr></div></td>';
                addHtml += '<th class="center colOptionViewFl"><select class="form-control" id="option_optionViewFlApply" ><option value="y">노출함</optiton><option value="n">노출안함</optiton></select></td>';
                addHtml += '<th class="center colOptionSellFl"><select class="form-control" id="option_optionSellFlApply" ><?php foreach($stockReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?></select></td>';
                addHtml += '<th class="center colOptionDeliveryFl"><select class="form-control" id="option_optionDeliveryFlApply" ><?php foreach($deliveryReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?></select></td>';
                addHtml += '<th class="center colOptionCode"><input type="text" id="option_optionCodeApply" class="form-control width-sm js-maxlength" maxlength="30" /></td>';
                addHtml += '<th class="center colOptionMemo"><div class="form-inline"><input type="text" id="option_optionMemoApply" class="form-control width-xm" /></div></th>';
                addHtml += '</tr>';
            } else {
                addHtml += '<tr id="tbl_option_info_' + j + '">';
                addHtml += '<td class="center"><input type="checkbox" name="optionY[optionNo][]" value="' + j + '"></td>';
                addHtml += '<td class="center">' + j + '</td>';
                var optKey = 0;
                var optKey2 = '';
                var optChkValue = '';
                var arrOption = [];
                optionDefaultPrice = 0;
                for (var k = 0; k < optionCnt; k++) {
                    optKey = k + 1;
                    if ((valChk[k] - 1) == valGab[k]) {
                        if (valCnt[k] > (valIdNo[k] + 1)) {
                            valIdNo[k]++;
                        } else {
                            valIdNo[k] = 0;
                        }
                        valChk[k] = 1;
                    }
                    if($('#option_optionValue_' + k + '_' + valIdNo[k]).length) {
                        var optVal = $('#option_optionValue_' + k + '_' + valIdNo[k]).val().replace(/"/g, '&quot;');
                        var optPrice = parseInt($('#option_all_price_' + k + '_' + valIdNo[k]).val().replace(/"/g, '&quot;'));
                        arrOption.push(optVal);
                        optClass = '';
                        if(k == optionCnt - 1){
                            optClass = 'colOptionValueLast';
                        }
                        addHtml += '<td class="center '+optClass+'">' + optVal + '</td>';
                        if(isNaN(optPrice) || optPrice == null) optPrice = 0;
                        if(optionDefaultPrice == '') optionDefaultPrice = 0;
                        optionDefaultPrice = parseInt(optionDefaultPrice) + parseInt(optPrice);
                        if(optionDefaultPrice == 0) optionDefaultPrice = '';

                        optChkValue = optChkValue + optVal.trim();
                        optKey2 = optKey2 + valIdNo[k];
                        valChk[k]++;
                    }
                }

                addHtml += '<input type="hidden" id="option_sno_' + optKey2 + '" name="optionY[sno][]" value="" />';
                addHtml += '<input type="hidden" name="optionY[optionValueText][]" value="'+arrOption.join("<?=STR_DIVISION?>")+'" />';
                addHtml += '<td class="center colOptionCostPrice"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_optionCostPrice_' + optKey2 + '" name="optionY[optionCostPrice][]" value="" class="form-control width-2xs" /><?=gd_currency_string();?></div></td>';
                addHtml += '<td class="center colOptionPrice"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_optionPrice_' + optKey2 + '" name="optionY[optionPrice][]" value="'+optionDefaultPrice+'" class="form-control width-2xs" /><?=gd_currency_string();?></div></td>';
                addHtml += '<td class="center colStockCnt"><div class="form-inline"><input type="text" id="option_stockCnt_' + optKey2 + '" name="optionY[stockCnt][]" value="" class="form-control width-2xs" />개</div></td>';
                //addHtml += '<td class="center colOptionStopFl"><div class="form-inline"><select class="form-control" id="option_optionStopFl_'+optKey2+'" name="optionY[optionStopFl][]"><option value="y">사용함</optiton><option value="n" selected>사용안함</optiton></select> <input type="text" id="option_optionStopApply" class="form-control width-2xs" name="optionY[optionStopCnt][]" />개</div></td>';
                //addHtml += '<td class="center colOptionRequestFl"><div class="form-inline"><select class="form-control" id="option_optionRequestFl_'+optKey2+'" name="optionY[optionRequestFl][]"><option value="y">사용함</optiton><option value="n" selected>사용안함</optiton></select> <input type="text" id="option_optionRequestApply" class="form-control width-2xs" name="optionY[optionRequestCnt][]" />개</div></td>';
                addHtml += '<td class="center colOptionViewFl"><select class="form-control" id="option_optionViewFl_' + optKey2 + '" name="optionY[optionViewFl][]"><option value="y">노출함</optiton><option value="n">노출안함</optiton></select></td>';
                addHtml += '<td class="center colOptionSellFl"><select  class="form-control" id="option_optionSellFl_' + optKey2 + '" name="optionY[optionSellFl][]"><?php foreach($stockReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?></select></td>';
                addHtml += '<td class="center colOptionDeliveryFl"><select class="form-control" id="option_optionDeliveryFlApply" name="optionY[optionDeliveryFl][]" ><?php foreach($deliveryReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?></select></td>';
                addHtml += '<td class="center colOptionCode"><input type="text" id="option_optionCode_' + optKey2 + '" name="optionY[optionCode][]" value="" class="form-control width-sm js-maxlength" maxlength="30" /></td>';
                addHtml += '<td class="center colOptionMemo"><div class="form-inline"><input type="text" id="option_optionMemo_' + optKey2 + '" name="optionY[optionMemo][]" value="" class="form-control width-xm" /></div></td>';
                addHtml += '</tr>';
            }
        }

        addHtml += '</table>';
        $('#optionOptionTable').prev().remove();
        $('#optionOptionTable').remove();
        $('#optionGridTable').remove();
        $('#' + fieldID).append(addHtml);

        $('input[name*=\'optionPrice\']').number_only();
        $('input[name*=\'stockCnt\']').number_only();

        $("input.js-type-normal").bind('keyup', function () {
            $(this).val($(this).val().replace(/[^a-z0-9_]*/gi, ''));
        });

        <?php if ($data['optionFl'] == 'y') {?>    if (optionValueFill == true) {
            fill_value_layer();
        }<?php }?>

        $('#info-bottom').removeClass("display-none");
        optionGridChange = true;
        setGridSettingLayer(optTotCnt);
        setMoveRowLayer();
        $('#optionApplyBtn').val('옵션 정보 재적용');
        if($(document).height() - $(window).height() > 0){
            $('.scrollDown').css('display','block');
        } else {
            $('.scrollDown').css('display','none');
        }
        <?php
        // 상품 재고 수정 권한 없는 경우 상품재고 수정 불가
        if (empty($data['goodsNo']) === false && empty($optionSession) && Session::get('manager.functionAuth.goodsStockModify') != 'y') {
        ?>
        $('#option_stockCntApply').prop('readonly', true);
        $('[name="optionY[stockCnt][]"]').prop('readonly', true);
        <?php
        }
        ?>
    }

    var move_row = {
        up: function () {
            var $checkbox = $('#optionGridTable').find(':checkbox[name="optionY[optionNo][]"]:checked');
            $checkbox.each(function (idx, item) {
                var $row = $(item).closest('tr');
                $row.insertBefore($row.prev());
            });
        }, down: function () {
            var $checkbox = $('#optionGridTable').find(':checkbox[name="optionY[optionNo][]"]:checked');
            $($checkbox.get().reverse()).each(function (idx, item) {
                var $row = $(item).closest('tr');
                var $next = $row.next();
                var enableCheckboxLength = $next.find(':checkbox[name="optionY[optionNo][]"]').length;
                if (enableCheckboxLength > 0) {
                    $row.insertAfter($next);
                }
            });
        }, top: function () {
            var $checkbox = $('#optionGridTable').find(':checkbox[name="optionY[optionNo][]"]:checked');
            $checkbox.each(function (idx, item) {
                var $row = $(item).closest('tr');
                var $targetRow = $(':checkbox[name="optionY[optionNo][]"]').first().closest('tr');
                $row.insertBefore($targetRow);
            });
        }, bottom: function () {
            var $checkbox = $('#optionGridTable').find(':checkbox[name="optionY[optionNo][]"]:checked');
            $($checkbox.get().reverse()).each(function (idx, item) {
                var $row = $(item).closest('tr');
                var $targetRow = $(':checkbox[name="optionY[optionNo][]"]').last().closest('tr');
                $row.insertAfter($targetRow);
            });

        }
    };

    function setMoveRowLayer(){
        // 위/아래이동 버튼 이벤트
        $('.js-moverow').on('click', function (e) {
            var $target = $(e.target);
            $moveChecked = $(':checked[name="optionY[optionNo][]"]', 'tbody');
            $moveUnChecked = $(':checkbox[name="optionY[optionNo][]"]:not(:checked)', 'tbody');
            if ($moveChecked.length > 0) {
                var direction = $target.data('direction');
                if (_.isUndefined(direction)) {
                    direction = $target.closest('button').data('direction');
                }
                switch (direction) {
                    case 'up':
                        move_row.up();
                        break;
                    case 'down':
                        move_row.down();
                        break;
                    case 'top':
                        move_row.top();
                        break;
                    case 'bottom':
                        move_row.bottom();
                        break;
                }
            }
            else {
                alert("선택된 옵션이 없습니다.");
            }
        });
    }


    function delete_option(inputName, trName) {

        $('input[name="' + inputName + '"]:checked').each(function () {
            field_remove(trName + $(this).val());
        });
    }

    function add_option_layer(inputName, trName) {
        var loadChk = $('#layerOptionAddForm').length;
        var optionRegister = 'y';

        $.get('../share/layer_goods_option_add.php', {'optionRegister': optionRegister}, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerOptionAddForm">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '옵션 추가', 'wide');
        });
    }


    /**
     * 옵션값 일괄 적용
     */
    function option_value_apply_layer() {


        if($('input[name="optionY[optionNo][]"]:checked').length) {

            var optionPrice = $('#option_opotionPriceApply').val();
            var stockCnt = $('#option_stockCntApply').val();
            var optionStopFl = $('#option_optionStopFlApply').val();
            var optionStopCnt = $('#option_optionStopApply').val();
            var optionRequestFl = $('#option_optionRequestFlApply').val();
            var optionRequestCnt = $('#option_optionRequestApply').val();
            var optionCode = $('#option_optionCodeApply').val();
            var optionViewFl = $('#option_optionViewFlApply').val();
            var optionSellFl = $('#option_optionSellFlApply').val();
            var optionCostPrice = $('#option_optionCostPriceApply').val();
            var optionDelivery = $('#option_optionDeliveryFlApply').val();
            var optionMemo = $('#option_optionMemoApply').val();

            $('input[name="optionY[optionNo][]"]').each(function (i) {
                if (this.checked) {

                    if (optionCostPrice !='') {
                        $('input[name*=\'optionY\[optionCostPrice\]\']').eq(i).val(optionCostPrice);
                    }
                    if (optionPrice !='') {
                        $('input[name*=\'optionY\[optionPrice\]\']').eq(i).val(optionPrice);
                    }
                    if (stockCnt >= 0 && stockCnt != '') {
                        $('input[name*=\'optionY\[stockCnt\]\']').eq(i).val(stockCnt);
                    }
                    if (optionStopCnt >= 0 && optionStopCnt != '') {
                        $('input[name*=\'optionY\[optionStopCnt\]\']').eq(i).val(optionStopCnt);
                    }
                    if (optionRequestCnt >= 0 && optionRequestCnt != '') {
                        $('input[name*=\'optionY\[optionRequestCnt\]\']').eq(i).val(optionRequestCnt);
                    }
                    if (optionCode) {
                        $('input[name*=\'optionY\[optionCode\]\']').eq(i).val(optionCode);
                    }
                    if (optionMemo) {
                        $('input[name*=\'optionY\[optionMemo\]\']').eq(i).val(optionMemo);
                    }

                    $('select[name*=\'optionY\[optionViewFl\]\']').eq(i).val(optionViewFl);
                    $('select[name*=\'optionY\[optionSellFl\]\']').eq(i).val(optionSellFl);
                    $('select[name*=\'optionY\[optionStopFl\]\']').eq(i).val(optionStopFl);
                    $('select[name*=\'optionY\[optionRequestFl\]\']').eq(i).val(optionRequestFl);
                    $('select[name*=\'optionY\[optionDeliveryFl\]\']').eq(i).val(optionDelivery);

                }
            });


        } else {
            alert("선택된 옵션이 없습니다.");
            return false;
        }
    }

    /**
     * 이미지 저장소에 따른 상품 이미지 input 종류 (text or file)
     *
     * @param string fieldID 해당 ID
     * @param string addBtnYN 추가버튼 여부
     * @param string urlType URL 직접 입력 여부
     */
    function goods_image_layer(fieldID, addBtnYN, urlType) {
        if ($('#' + fieldID).find('div:last').html()) {

            var fieldNoChk = $('#' + fieldID + ' > div').last().attr('id').replace(fieldID, '');
            if (fieldNoChk == '') {
                var fieldNoChk = 0;
            }
        } else {
            var fieldNoChk = 0
        }

        var fieldNo = parseInt(fieldNoChk) + 1;
        var addBtnFl = "n";
        if((addBtnYN =='r' || addBtnYN =='y') && urlType == 'y') {
            if($("#"+fieldID).find("input[id*='"+fieldID+"URL']").length == 0) {
                addBtnFl = "y";
            }
        }

        if(fieldID == 'imageFbGoods' || fieldID == 'imageFbGoodsURL') {
            var canAdd = $('#'+fieldID).find(".form-inline").length;
            if (canAdd > 9) {
                alert('페이스북 피드 이미지 업로드는 10개까지 가능합니다.');
                return;
            }
        }
        if (fieldNo == 1 || addBtnFl == 'y') {
            var addBtn = '<input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus" onclick="goods_image_layer(\'' + fieldID + '\',\'y\',\'' + urlType + '\');" /> ';
        } else {
            if (addBtnYN == 'r') var addBtn = '';
            else var addBtn = '<input type="button" value="삭제" class="btn btn-sm btn-white btn-icon-minus"  onclick="deleteFileField($(this)); field_remove(\'' + fieldID + fieldNo + '\');" /> ';
        }


        if(addBtnYN =='r')  addBtnYN ='y';

        var addHtml = '';
        addHtml += '<div id="' + fieldID + fieldNo + '" class="form-inline">';

        var imageInfo = '';
        if (urlType == 'y') {
            imageInfo = '<span id="' + fieldID + 'PreView' + fieldNoChk + '"></span>';
            if(fieldID == 'imageFbGoods' || fieldID == 'imageFbGoodsURL') { // 페이스북 피드 이미지 입력하는 경우
                addHtml += '<input type="text" id="' + fieldID + 'URL' + fieldNoChk + '" name="imageFb[' + fieldID + '][]" value="" class="form-control width60p" />' + imageInfo;
            } else {
                addHtml += '<input type="text" id="' + fieldID + 'URL' + fieldNoChk + '" name="image[' + fieldID + '][]" value="" class="form-control width60p" />' + imageInfo;
            }
        } else {
            var clickCheck = '';
            if (fieldID == 'imageOriginal') {
                clickCheck = 'onclick="image_resize_check_all_layer(\'imageResize[original]\',\'y\');"';
            } else {
                imageInfo = '<span id="' + fieldID + 'PreView' + fieldNoChk + '"></span>';
            }
            if(fieldID == 'imageFbGoods'){ // 페이스북 피드 이미지 입력하는 경우
                addHtml += '<input type="file" name="imageFb[' + fieldID + '][]" class="form-control" ' + clickCheck + ' />' + imageInfo;
            }else {
                addHtml += '<input type="file" name="image[' + fieldID + '][]" class="form-control" ' + clickCheck + ' />' + imageInfo;
            }
        }
        if (addBtnYN == 'y') {
            addHtml += addBtn;
        }

        addHtml += '</div>';

        if (urlType == 'y') {
            $('#' + fieldID).append(addHtml);
        } else {
            if($("#"+fieldID).find("input[type='file']:last").closest('div').length == 0) {
                $('#' + fieldID).append(addHtml);
            } else {
                $("#"+fieldID).find("input[type='file']:last").closest('div').after(addHtml);
            }
        }

        init_file_style();
    }

    /**
     * 원본이미지의 리사이즈 체크
     *
     * @param string checkName 원본 이미지 체크박스 name
     * @param string addBtnYN 원본 이미지의 input file 를 클릭 여부
     */
    function image_resize_check_all_layer(checkName, fileTypeChk) {

        if ($('input[name="imageAddUrl"]').is(":checked")) {
            $('input[name="imageAddUrl"]').prop("checked",false);
        }

        if (fileTypeChk == 'y') {
            $('input[name=\'' + checkName + '\']').prop('checked', true);
        }
        var checkboxCnt = $('input[name*=\'imageResize\']').length;
        var checkboxNm = '';
        for (var i = 1; i < checkboxCnt; i++) {
            checkboxNm = $('input[name*=\'imageResize\']:checkbox').eq(i).get(0).name;
            if ($('input[name=\'' + checkName + '\']:checked').length == 1) {
                $('input[name=\'' + checkboxNm + '\']').prop('checked', true);
            } else {
                $('input[name=\'' + checkboxNm + '\']').prop('checked', false);
            }
            image_resize_check_layer(checkboxNm, 'y');
        }
    }

    /**
     * 상품 옵션 이미지 이미지 URL직접입력 추가 사용
     *
     */
    function option_image_add_url_layer() {

        $('span[id*=\'imageStorageModeOptionGoodsImage_\']').addClass('display-block');

        if($('input[name="optionImageAddUrl"]').is(":checked")) {
            $('span[id*=\'imageStorageModeOptionGoodsText_\']').addClass('display-block');
        } else {
            $('span[id*=\'imageStorageModeOptionGoodsText_\']').removeClass('display-block');
        }
    }

    /**
     * 각 이미지의 리사이즈 체크
     *
     * @param string checkName 해당 이미지 체크박스 name
     * @param string allCheck 전부 체크 되었는지의 여부
     */
    function image_resize_check_layer(checkName, allCheck) {
        var tempID = checkName.replace(/Resize\[|\]/g, '');
        var checkID = tempID.substring(0, 5) + tempID.substr(5, 1).toUpperCase() + tempID.substring(6);

        if ($('input[name=\'' + checkName + '\']:checked').length == 1) {
            $('#' + checkID).hide('fast');
        } else {
            $('#' + checkID).show('fast', function () {
                $('#' + checkID + 'Text').remove();
            });
        }

        if (typeof allCheck == 'undefined') {
            if ($('input[name=\'imageResize\[original\]\']:checked').length == 1) {
                $('input[name=\'imageResize\[original\]\']').prop('checked', false);
            } else {
                var checkboxCnt = $('input[name*=\'imageResize\']').length;
                var checkedCnt = $('input[name*=\'imageResize\']:checked').length;
                if (checkboxCnt == parseInt(checkedCnt + 1)) {
                    $('input[name=\'imageResize\[original\]\']').prop('checked', true);
                }
            }
        }
    }

    function confirmGrid(){
        if(confirm('조회항목 설정시 화면이 새로고침되어 입력된 내용은 저장되지 않습니다.\n조회항목 설정을 계속 진행하시겠습니까?')){
            $('.js-layer-register').click();
        }
    }

    function IEmainFileAccess(frm){
        for(i=0;i<$('[id*="imageStorageModeOptionGoodsImage_0"]').length;i++){
            $('[id*="imageStorageModeOptionGoodsImage_0"] *> input[type="text"]:eq('+i+')').val(opener.$('[id*="imageStorageModeOptionGoodsImage_0"] *> input[type="text"]:eq('+i+')').val())
        }
    }

    function getFileName(i){
        fileNm = opener.$('#optionTmpFile > input[type="file"]:eq('+i+')').val();
        fileNmArr = fileNm.split('\\');
        return fileNmArr[fileNmArr.length - 1]
    }

    function arrangeFileField(){
        mainFileField = opener.$('#optionTmpFile > input[type="file"]').length;
        for(i=mainFileField;i>=0;i--){
            if(i >= $('[id*="optValDetail_0_"]').length){
                opener.$('#optionTmpFile > input[type="file"]:eq('+i+')').remove();
            }
        }
    }

    function deleteFileField(frm){
        opener.$('#optionTmpFile > input[type="file"]:eq('+($('[id*="remove_option_0"]').index(frm)+1)+')').remove();
    }

    function getMainFileList(){
        //파일 스타일이 적용 되었는지 확인
        timerId = setInterval(function(){
            if($('.bootstrap-filestyle').length > 0 ){
                for(i=0;i<$('[id*=filestyle-]').length;i++){
                    fileNm = opener.$('#optionTmp *> input[type="file"]:eq('+i+')').val();
                    fileNmArr = fileNm.split('\\');
                    $('.bootstrap-filestyle > input:text:eq('+i+')').val(fileNmArr[fileNmArr.length - 1]);
                }
                clearInterval(timerId);
            }
        }, 500);
    }

    function syncMainChecked(){

        for(i=0;i<opener.$('#optionTmp *> input[type="checkbox"]').length;i++){

            $('#depth-toggle-layer-stockOption-popup *> input[type="checkbox"]:eq('+i+')').prop('checked', opener.$('#optionTmp *> input[type="checkbox"]:eq('+i+')').prop('checked'));
        }
        for(i=0;i<opener.$('#optionTmp *> input[type="radio"]').length;i++){
            $('#depth-toggle-layer-stockOption-popup *> input[type="radio"]:eq('+i+')').prop('checked', opener.$('#optionTmp *> input[type="radio"]:eq('+i+')').prop('checked'));
        }
        $('#depth-toggle-layer-stockOption-popup *> input[type="radio"]:eq('+0+')').prop('checked', true);
        $('#depth-toggle-layer-stockOption-popup *> input[type="radio"]:eq('+1+')').prop('checked', false);
    }
    //-->
</script>

<form name="addOptions" target="ifrmProcess" method="post" enctype="multipart/form-data" action="goods_ps.php">
    <input type="hidden" name="mode" value="goods_option_temp_reigster" />
    <input type="hidden" name="sess" value="<?=$optionSession?>" />
    <div class="page-header js-affix affix-top" id="stockOption"><strong><h3>
                <?php
                if(empty($optionSession) && empty($data['optionName'])){
                    ?>
                    옵션 등록
                    <?php
                } else {
                    ?>옵션 수정<?php
                }
                ?>
            </h3></strong>
        <div class="btn-group"><input type="button" value="저장" class="btn btn-red"/></div></span>
    </div>
    <div id="depth-toggle-line-stockOption" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-stockOption-popup">
        <div class="table-title gd-help-manual">■ 기본 정보</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>옵션 입력 방식</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="optionFl" value="y" onclick="display_toggle_layer('optionExistLayer','show');display_toggle_layer('optionGrid','show');disabled_switch_layer('callGoodsOption',false);" checked>직접입력
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="optionFl" value="n" onclick="display_toggle_layer('optionExistLayer','hide');display_toggle_layer('optionGrid','hide');disabled_switch_layer('callGoodsOption',true);">기존상품 옵션 불러오기 <span><input id="callGoodsOption" type="button" class="btn btn-white btn-sm" onclick="select_goods_layer();" value="상품 선택"/></span>
                    </label>
                </td>
            </tr>
            </tbody>
            <tbody id="optionExistLayer" class="display-none">
            <tr>
                <th>자주쓰는 옵션</th>
                <td>
                    <button type="button" class="btn btn-sm btn-gray" onclick="manage_option_list_layer()">자주쓰는 옵션</button>
                    <button type="button" class="btn btn-sm btn-gray" onclick="manage_option_register_layer();">자주쓰는 옵션 등록</button>
                </td>
            </tr>
            <?php if ($data['goodsPriceString']) { ?>
                <tr>
                    <th>안내</th>
                    <td><span class="notice-danger">가격대체문구를 사용중이므로 해당 상품은 주문이 되지 않습니다.</span></td>
                </tr>
            <?php } ?>
            <tr>
                <th>옵션 노출 방식</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="optionY[optionDisplayFl]" value="s" <?=gd_isset($checked['optionDisplayFl']['s']); ?> />일체형(조합)
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="optionY[optionDisplayFl]" value="d" <?=gd_isset($checked['optionDisplayFl']['d']); ?> />분리형(조합)
                    </label>
                </td>
            </tr>
            <tr>
                <th>옵션 이미지 노출 설정</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="optionImagePreviewFl" value="y" <?=gd_isset($checked['optionImagePreviewFl']['y']); ?> />미리보기 사용
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="optionImageDisplayFl" value="y" <?=gd_isset($checked['optionImageDisplayFl']['y']); ?> />상세이미지에 추가
                    </label>
                </td>
            </tr>
            <tr>
                <th>옵션 개수</th>
                <td>
                    <div class="form-inline">
                        <?=gd_select_box('optionY_optionCnt', 'optionY[optionCnt]', gd_array_change_key_value(range(1, DEFAULT_LIMIT_OPTION)), '개', $data['optionCnt'], '=옵션 개수=', 'onchange="option_setting_layer(this.value);"'); ?>
                        <span><input type="button" class="btn btn-white btn-sm" onclick="option_reset_layer();" value="초기화"/></span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>옵션 등록</th>
                <td id="optionRegisterCell">
                    <div id="option"></div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        if(empty($optionSession) && empty($data['goodsNo'])){
            $btnTitle = "옵션 정보 적용";
        } else {
            $btnTitle = "옵션 정보 재적용";
        }
        ?>
        <div class="center"><input type="button" class="btn btn-sm btn-gray" id="optionApplyBtn" onclick="option_grid_layer('y');" value="<?php echo $btnTitle;?>"></div>
        <div class="notice-info" style="margin-bottom:20px;">
            미리보기 사용 : 쇼핑몰 상세페이지에서 옵션 선택 시 옵션 이미지가 상세이미지 영역에 노출됩니다.<br/>
            상세이미지에 추가 : 옵션 이미지가 상세이미지 영역 하단에 추가 노출됩니다.<br/>
            옵션명은 30자, 옵션값은 100자까지 등록 가능합니다. (한글, 영문 대/소문자, 숫자, 특수문자 등록 가능합니다.)<br/>
            <span class="text-danger">- ` ‘ ‘ “ “ 특수문자는 입력이 불가합니다.</span><br/>
            옵션이미지는 첫번째 옵션명의 "옵션값" 별로 이미지 등록이 가능합니다.<br/>
            “직접 업로드와 URL 직접입력” 방식 모두 사용하여 이미지를 등록한 경우 “직접 업로드”된 이미지만 적용됩니다.<br/>
            옵션명/옵션값 입력 후에 [옵션 정보 적용] 버튼 클릭 시 옵션의 가격/재고/상태 설정부분이 출력 됩니다.<br/>
            옵션 정보 적용 후 옵션을 수정한 경우 [옵션 정보 적용] 버튼을 다시 클릭해야 수정된 정보가 옵션 정보 항목에 적용됩니다.
        </div>
        <div id="optionGrid" style="overflow-x: scroll; width:100%;">
            <?php
            if (!empty($data['option']) && $data['optionCnt'] > 0 && $data['optionFl'] == 'y') {
                ?>
                <table cellpadding="0" cellspacing="0" width="100%" height="30" id="optionOptionTable">
                    <tr>
                        <td>
                            <div class="table-title gd-help-manual">■ 옵션 정보</div>
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="150">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-white btn-icon-bottom js-moverow goodsChoice_downArrowMore" data-direction="bottom">맨아래</button>
                                            <button type="button" class="btn btn-white btn-icon-down js-moverow goodsChoice_downArrow" data-direction="down">아래</button>
                                            <button type="button" class="btn btn-white btn-icon-up js-moverow goodsChoice_upArrow" data-direction="up">위</button>
                                            <button type="button" class="btn btn-white btn-icon-top js-moverow goodsChoice_upArrowMore" data-direction="top">맨위</button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_option_layer();" value="옵션 추가" />
                                        <input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="delete_option('optionY[optionNo][]','tbl_option_info_');" value="선택 삭제" />
                                    </td>
                                    <td class="right"><button type="button" class="btn btn-sm btn-black" style="height: 27px !important;" onclick="confirmGrid()">조회항목설정</button><button type="button" class="js-layer-register btn btn-sm btn-black display-none" data-type="goods_option_grid_config" data-goods-option-grid-mode="<?=$goodsOptionAdminGridMode?>"></button></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table id="optionGridTable" class="table table-cols">
                    <thead>
                    <tr>
                        <th class="width2p"><input type="checkbox" id="allOptionCheck" value="y" onclick="check_toggle(this.id,'optionY[optionNo][]');"/></th>
                        <th class="width2p">번호</th>
                        <?php
                        for ($i = 0; $i < $data['optionCnt']; $i++) {
                            $optClass = '';
                            if($i == $data['optionCnt'] - 1){
                                $optClass = 'colOptionValueLast';
                            }
                            echo '<th class="width10p '.$optClass.'">' . $data['optionName'][$i];
                        }
                        ?>
                        </th>
                        <th class="width10p colOptionCostPrice">옵션 매입가</th>
                        <th class="width10p colOptionPrice">옵션가</th>
                        <th class="width10p colStockCnt">재고량</th>
                        <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                        <!--<th class="width10p colOptionStopFl">판매중지수량</th>-->
                        <!--<th class="width10p colOptionRequestFl">확인요청수량</th>-->
                        <th class="width10p colOptionViewFl">옵션노출상태</th>
                        <th class="width10p colOptionSellFl">옵션품절상태</th>
                        <th class="width10p colOptionDeliveryFl">옵션배송상태</th>
                        <th class="width10p colOptionCode">자체 옵션코드</th>
                        <th class="width10p colOptionMemo">메모</th>
                    </tr>
                    </thead>
                    <tr>
                        <th class="center colOptionValueLast" colspan="<?=$data['optionCnt']+2; ?>">
                            <input type="button" onclick="option_value_apply_layer();" value="옵션 정보 일괄 적용" class="btn btn-xs btn-gray"/>
                        </th>
                        <th class="center colOptionCostPrice">
                            <div class="form-inline"><?=gd_currency_symbol(); ?>
                                <input type="text" id="option_optionCostPriceApply" class="form-control width-2xs"/><?=gd_currency_string(); ?>
                            </div>
                        </th>
                        <th class="center colOptionPrice">
                            <div class="form-inline"><?=gd_currency_symbol(); ?>
                                <input type="text" id="option_opotionPriceApply" class="form-control width-2xs"/><?=gd_currency_string(); ?>
                            </div>
                        </th>
                        <th class="center colStockCnt">
                            <div class="form-inline">
                                <input type="text" id="option_stockCntApply" class="form-control width-2xs"/>개
                            </div>
                        </th>
                        <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                        <!--<th class="center colOptionStopFl">
                            <div class="form-inline">
                                <nobr>
                                    <select class="form-control" id="option_optionStopFlApply" >
                                        <option value="y">사용함</optiton>
                                        <option value="n" selected>사용안함</optiton>
                                    </select>
                                    <input type="text" id="option_optionStopApply" class="form-control width-2xs" />개
                                </nobr>
                            </div>
                        </th>
                        <th class="center colOptionRequestFl">
                            <div class="form-inline">
                                <nobr>
                                <select class="form-control" id="option_optionRequestFlApply" >
                                    <option value="y">사용함</optiton>
                                    <option value="n" selected>사용안함</optiton>
                                </select>
                                <input type="text" id="option_optionRequestApply" class="form-control width-2xs" />개
                                </nobr>
                            </div>
                        </th>-->
                        <th class="center colOptionViewFl">
                            <select class="form-control" id="option_optionViewFlApply" >
                                <option value="y">노출함</optiton>
                                <option value="n">노출안함</optiton>
                            </select>
                        </th>
                        <th class="center colOptionSellFl">
                            <select class="form-control" id="option_optionSellFlApply" >
                                <?php foreach($stockReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?>
                            </select>
                        </th>
                        <th class="center colOptionDeliveryFl">
                            <select class="form-control" id="option_optionDeliveryFlApply" >
                                <?php foreach($deliveryReason as $k => $v) { ?><option value="<?=$k?>"><?=$v?></option><?php } ?>
                            </select>
                        </th>
                        <th class="center colOptionCode">
                            <input type="text" id="option_optionCodeApply" class="form-control width-sm" maxlength="30" />
                        </th>
                        <th class="center colOptionMemo">
                            <div class="form-inline"><input type="text" id="option_optionMemoApply" class="form-control width-xm" /></div>
                        </th>
                    </tr>
                    <?php
                    $nextNo = 0;
                    foreach ($data['option'] as $key => $val) {
                        $nextNo++;
                        ?>
                        <tr id="tbl_option_info_<?=$key+1?>">
                            <td class="center"><input type="checkbox" name="optionY[optionNo][]" value="<?=$key+1?>"></td>
                            <td class="center"><?=$key+1?></td>
                            <?php
                            $tmpOptionText = [];
                            for ($i = 0; $i < $data['optionCnt']; $i++) {
                                $tmpOptionText[] = $data['option'][$key]['optionValue' . ($i + 1)];
                                $optClass = '';
                                if($i == $data['optionCnt'] - 1){
                                    $optClass = 'colOptionValueLast';
                                }
                                ?>
                                <td class="center <?=$optClass?>">
                                    <?=gd_isset($data['option'][$key]['optionValue' . ($i + 1)]); ?>
                                </td>
                                <?php
                            }
                            ?>
                            <input type="hidden" name="optionY[sno][]" value="<?php if ($applyGoodsCopy === false) {
                                echo gd_isset($data['option'][$key]['sno']);
                            } ?>"/>
                            <input type="hidden" name="optionY[optionValueText][]" value="<?=implode(STR_DIVISION,$tmpOptionText)?>"/>
                            <td class="center colOptionCostPrice">
                                <div class="form-inline">
                                    <input type="text" name="optionY[optionCostPrice][]" value="<?=gd_money_format(gd_isset($data['option'][$key]['optionCostPrice']), false);?>" class="form-control width-2xs"/><?=gd_currency_string();?>
                                </div>
                            </td>
                            <td class="center colOptionPrice">
                                <div class="form-inline"><?=gd_currency_symbol(); ?>
                                    <input type="text" name="optionY[optionPrice][]" value="<?=gd_money_format(gd_isset($data['option'][$key]['optionPrice']), false); ?>" class="form-control width-2xs"/><?=gd_currency_string(); ?>
                                </div>
                            </td>
                            <td class="center colStockCnt">
                                <div class="form-inline">
                                    <input type="text" name="optionY[stockCnt][]" value="<?=gd_isset($data['option'][$key]['stockCnt']); ?>" class="form-control width-2xs"/>개
                                </div>
                            </td>
                            <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                            <!--<td class="center colOptionStopFl">
                            <div class="form-inline">
                                <nobr>
                                <select class="form-control" name="optionY[optionStopFl][]">
                                    <option value="y" <?php if ($data['option'][$key]['sellStopFl'] == 'y') {
                                echo "selected";
                            } ?>>사용함
                                    </option>
                                    <option value="n" <?php if ($data['option'][$key]['sellStopFl'] == 'n') {
                                echo "selected";
                            } ?>>사용안함
                                    </option>
                                </select>
                                    <input type="text" name="optionY[optionStopCnt][]" value="<?=gd_isset($data['option'][$key]['sellStopStock']); ?>" class="form-control width-2xs"/>개
                                </nobr>
                            </div>
                        </td>
                        <td class="center colOptionRequestFl">
                            <div class="form-inline">
                                <nobr>
                                <select class="form-control" name="optionY[optionRequestFl][]">
                                    <option value="y" <?php if ($data['option'][$key]['confirmRequestFl'] == 'y') {
                                echo "selected";
                            } ?>>사용함
                                    </option>
                                    <option value="n" <?php if ($data['option'][$key]['confirmRequestFl'] == 'n') {
                                echo "selected";
                            } ?>>사용안함
                                    </option>
                                </select>
                                <input type="text" name="optionY[optionRequestCnt][]" value="<?=gd_isset($data['option'][$key]['confirmRequestStock']); ?>" class="form-control width-2xs"/>개
                                </nobr>
                            </div>
                        </td>-->
                            <td class="center colOptionViewFl"><select class="form-control" name="optionY[optionViewFl][]">
                                    <option value="y" <?php if ($data['option'][$key]['optionViewFl'] == 'y') {
                                        echo "selected";
                                    } ?>>노출함
                                    </option>
                                    <option value="n" <?php if ($data['option'][$key]['optionViewFl'] == 'n') {
                                        echo "selected";
                                    } ?>>노출안함
                                    </option>
                                </select>
                            </td>
                            <td class="center colOptionSellFl">
                                <select class="form-control" name="optionY[optionSellFl][]">
                                    <?php
                                    foreach($stockReason as $k => $v) {
                                        ?>
                                        <option value="<?=$k?>" <?php if ($data['option'][$key]['optionSellFl'] == $k || $data['option'][$key]['optionSellFl'] == $k || ($data['option'][$key]['optionSellFl'] == 't' && $data['option'][$key]['optionSellCode'] == $k)) {
                                            echo "selected";
                                        } ?>><?=$v?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td class="center colOptionDeliveryFl"><select class="form-control" name="optionY[optionDeliveryFl][]">
                                    <?php
                                    foreach($deliveryReason as $k => $v) {
                                        ?>
                                        <option value="<?=$k?>" <?php if ($data['option'][$key]['optionSellFl'] != 'normal' && $data['option'][$key]['optionDeliveryCode'] == $k) {
                                            echo "selected";
                                        } ?>><?=$v?>
                                        </option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            <td class="center colOptionCode">
                                <input type="text" name="optionY[optionCode][]" class="form-control" value="<?=gd_isset($data['option'][$key]['optionCode']); ?>"/>
                            </td>

                            <td class="center colOptionMemo">
                                <div class="form-inline">
                                    <input type="text" name="optionY[optionMemo][]" value="<?=gd_isset($data['option'][$key]['optionMemo']); ?>" class="form-control width-xm"/>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tfoot>
                    <!--<tr>
                        <td colspan="<?=$data['optionCnt']+12?>"><input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="delete_option('optionY[optionNo][]','tbl_option_info_');" value="선택 삭제" /></td>
                    </tr>-->
                    </tfoot>
                </table>
                <?php
                $displayBottomInfo = true;
            }else{
                $displayBottomInfo = false;
            }
            ?>
        </div>
        <div id="info-bottom" class="notice-info<?php if (!$displayBottomInfo) { ?> display-none<?php } ?>">
            옵션 매입가는 상품 매입가 기준, 옵션가는 상품의 판매가 기준 추가 또는 차감될 옵션별 금액이 있는 경우에만 입력합니다.<br />
            <span class="text-danger">상품 매입가 및 판매가에 추가될 금액은 양수, 차감될 금액는 음수(마이너스)로 입력 합니다.</span><br />
            순서조정 버튼을 이용하여 옵션의 순서를 변경할 수 있으며, 설정된 순서대로 쇼핑몰에 노출됩니다.<br />
            옵션추가 버튼을 이용하여 옵션 정보를 초기화하지 않고 추가 생성할 수 있습니다.<br />
            옵션 정보에 출력되는 항목을 [조회항목설정] 버튼을 이용하여 설정할 수 있습니다.<br />
            옵션품절상태의 "정상/품절" 제외 상태와 옵션배송상태는 쇼핑몰에만 적용되며, 네이버 ep/네이버 쇼핑/다음 쇼핑하우 ep 등의 외부연동 시 적용되지 않습니다.<br />
            <!--[상품 > 상품 관리 > 재고 알림 설정 관리]에서 "판매중지/확인요청" 알림 발송을 설정할 수 있습니다.-->
        </div>
    </div>
    <div id="gnbAnchor" style="position: fixed; bottom: 25px; right: 25px;">
        <div class="scrollTop" style="display:none;">
            <a href="#top"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/scroll_top_btn.png"></a>
        </div>
        <div class="scrollDown" style="display:block;">
            <a href="#down"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/scroll_down_btn.png"></a>
        </div>
    </div>
</form>
<script type="text/javascript">
    display_toggle_layer('optionExistLayer','show');disabled_switch_layer('stockCnt',true);
    setMoveRowLayer();
</script>