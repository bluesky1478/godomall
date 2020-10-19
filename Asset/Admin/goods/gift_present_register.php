<script type="text/javascript">
    <!--
    $(document).ready(function(){

        $.validator.addMethod(
            'optionCheck', function (value, element) {

                // 구매 상품 범위 체크
                var valueCode	= new Array('g', 'c', 'b', 'e');
                var idCode		= new Array('presentGoods', 'presentCategory', 'presentBrand', 'presentEvent');
                var textCode	= new Array('상품', '카테고리', '브랜드', '이벤트');

                for (var i = 0; i < valueCode.length; i++) {
                    if ($('input[name=\'presentFl\']:checked').val() == valueCode[i] && $('#'+idCode[i]).html() == '') {
                        $.validator.messages.optionCheck = textCode[i]+'이(가) 등록되어 있지 않습니다. '+textCode[i]+'을(를) 선택해 주세요.';
                        return false;
                    }
                }

                // 사은품 설정 체크
                var valueGift	= new Array('a', 'p', 'c', 'l');
                var objectGift	= new Object({'a' : '','l' : '', 'p' : '금액', 'c' : '수량'});
                var idGift		= new Array('multi');
                var conditionFl	= $('input[name=\'conditionFl\']:checked').val();
                var fieldCnt	= $('#'+fieldID).find('tr.giftAddTr:last').attr('id').replace(fieldID+'No_','');

                for (var i = 1; i <= fieldCnt; i++) {
                    if ($('input[name=\'gift[conditionStart]['+i+']\']').val() == '' || $('input[name=\'gift[conditionEnd]['+i+']\']').val() == '' ) {
                        $.validator.messages.optionCheck = '구매 '+objectGift[conditionFl]+'을 0이상의 숫자(양의 정수)로 작성해 주세요!';
                        //$.warnUI(objectGift[conditionFl]+' 체크', '구매 '+objectGift[conditionFl]+'을 0이상의 숫자(양의 정수)로 작성해 주세요!');
                        return false;
                    }
                    if (parseInt($('input[name=\'gift[conditionStart]['+i+']\']').val()) > parseInt($('input[name=\'gift[conditionEnd]['+i+']\']').val())) {
                        $.validator.messages.optionCheck = objectGift[conditionFl]+'범위가 잘못 되었습니다.';
                        //$.warnUI(objectGift[conditionFl]+' 체크', objectGift[conditionFl]+'범위가 잘못 되었습니다.');
                        return false;
                    }
                    var standardStart = parseInt($('input[name=\'gift[conditionStart]['+i+']\']').val());
                    var standardEnd =  parseInt($('input[name=\'gift[conditionEnd]['+i+']\']').val());
                    for (var j = 1; j <= fieldCnt; j++) {
                        var compareStart = parseInt($('input[name=\'gift[conditionStart]['+j+']\']').val());
                        var compareEnd = parseInt($('input[name=\'gift[conditionEnd]['+j+']\']').val());
                        if ( i == j ) { continue; }
                        else {
                            if ((standardStart <= compareEnd && standardStart >= compareStart) ||
                                (standardStart <= compareStart && standardEnd >= compareEnd) ||
                                (standardEnd >= compareStart && standardEnd <= compareEnd))
                            {
                                $.validator.messages.optionCheck = objectGift[conditionFl]+'범위가 중복 되었습니다.';
                                //$.warnUI(objectGift[conditionFl]+' 체크', objectGift[conditionFl]+'범위가 잘못 되었습니다.');
                                return false;
                            }
                        }
                    }

                    if (conditionFl != 'a' && conditionFl != 'l') {
                        if (parseInt($('input[name=\'gift[conditionStart]['+i+']\']').val()) == '0' || parseInt($('input[name=\'gift[conditionEnd]['+i+']\']').val()) == '0') {
                            $.validator.messages.optionCheck ='구매 '+objectGift[conditionFl]+'은 0을 입력하실 수 없습니다.';
                            //$.warnUI(objectGift[conditionFl]+' 체크', '구매 '+objectGift[conditionFl]+'은 0을 입력하실 수 없습니다.');
                            return false;
                        }
                    }
                    var multiHtml	= $('#div_multi_'+i).html();

                    if (multiHtml == '' ) {
                        $.validator.messages.optionCheck =  '사은품을 등록해 주세요.';
                        return false;
                    }

                }
                return true;
            },'');

        $("#frmGiftPresent").validate({
            submitHandler: function (form) {
                form.target='ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                presentTitle: {
                    required: true
                },
                mode: {
                    optionCheck: true
                }
            },
            messages: {
                presentTitle: {
                    required: "사은품 증정 제목을 입력해주세요."
                }
            }
        });

        present_conf('<?=$data['presentFl'];?>');
        condition_table('<?=$data['conditionFl'];?>');
        fill_condition_table('<?=$data['conditionFl'];?>');

        <?php  if (is_array($data['exceptGoodsNo'])) { ?> $('input[name="presentExceptFl[]"][value=goods]').click();<?php  } ?>
        <?php  if (is_array($data['exceptCateCd'])) { ?> $('input[name="presentExceptFl[]"][value=category]').click();<?php  } ?>
        <?php  if (is_array($data['exceptBrandCd'])) { ?> $('input[name="presentExceptFl[]"][value=brand]').click();<?php  } ?>

        <?php if( $data['mode'] =='present_modify') { ?>
        $('input:radio[name=scmFl]').prop("disabled", true);
        $('button.scmBtn').attr("disabled", true);
        <?php }?>
    });

    var fieldID		= 'conditionTable';

    /**
     * 사은품 정보 채우기
     *
     * @param string thisValue 사은품 증정 조건 값
     */
    function fill_condition_table(thisValue)
    {
        <?php
            if ($data['mode'] == 'present_modify' && gd_isset($data['gift']) && is_array($data['gift'])) {
                echo "	if (thisValue == '".$data['conditionFl']."') {".chr(10);
                for ($i = 1; $i < count(gd_isset($data['gift'])); $i++) {
                    echo "		condition_table_add('".$data['conditionFl']."');".chr(10);
                }
                echo chr(10);
                $arrGiftKey    = array('multi');
                foreach ($data['gift'] as $key => $val) {
                    $giftKey    = $key + 1;

                    echo "  $('#div_multi_".$giftKey."_tbl thead').show();".chr(10);
                    echo "  $('#div_multi_".$giftKey."_tbl tfoot').show();".chr(10);

                    echo "		$('input[name=\'gift[giftSno][".$giftKey."]\']').val('".$val['sno']."');".chr(10);

                    if($data['conditionFl'] == 'c') {
                        echo "		$('input[name=\'gift[conditionStart][".$giftKey."]\']').val('".(int)$val['conditionStart']."');".chr(10);
                        echo "		$('input[name=\'gift[conditionEnd][".$giftKey."]\']').val('".(int)$val['conditionEnd']."');".chr(10).chr(10);
                    } else {
                        echo "		$('input[name=\'gift[conditionStart][".$giftKey."]\']').val('".gd_money_format($val['conditionStart'],false)."');".chr(10);
                        echo "		$('input[name=\'gift[conditionEnd][".$giftKey."]\']').val('".gd_money_format($val['conditionEnd'],false)."');".chr(10).chr(10);
                    }

                    if (!empty($val['multiGiftNo'])) {
                        foreach ($val['multiGiftNo'] as $mKey => $mVal) {
                            echo "		var addHtml	= '';".chr(10);
                            echo "		addHtml		+= '<tr id=\"multi_".$giftKey."_".$mVal['giftNo']."\" >';".chr(10);
                            echo "		addHtml		+= '<td class=\"center\">".($mKey+1)."<input type=\"hidden\" name=\"gift[multiGiftNo][".$giftKey."][]\" value=\"".$mVal['giftNo']."\" /></td>';".chr(10);
                            echo "		addHtml		+= '<td class=\"outline\">".gd_htmlspecialchars_slashes(gd_html_gift_image($mVal['imageNm'], $mVal['imagePath'], $mVal['imageStorage'], 50, $mVal['giftNm']),'add')."</td>';".chr(10);
                            echo "		addHtml		+= '<td class=\"outline\">".$mVal['giftNm']."</td>';".chr(10);
                            echo "		addHtml		+= '<td><input type=\"button\" class=\"btn btn-sm btn-gray\" onclick=\"field_remove(\'multi_".$giftKey."_".$mVal['giftNo']."\');mode_selectbox_reset(\'multiGiftNo_".$giftKey."\', \'multi\');\" value=\"삭제\" /></td>';".chr(10);
                            echo "		addHtml		+= '</tr>';".chr(10);
                            echo "		$('#div_multi_".$giftKey."').append(addHtml);".chr(10).chr(10);
                        }

                            echo "		mode_selectbox_reset('multiGiftNo_".$giftKey."', 'multi');".chr(10);
                            echo "		$('#multi".$giftKey." option[value=\'".$val['selectCnt']."\']').prop('selected',true);".chr(10).chr(10);
                            echo "		$('input[name=\'gift[giveCnt][".$giftKey."]\']').val('".$val['giveCnt']."');".chr(10).chr(10);

                    }
                }
                echo "	}".chr(10);
            }
        ?>
    }

    /**
     * 사은품 선택
     *
     * @param string modeStr 사은품 모드
     */
    function layer_gift_select(modeStr)
    {
        var loadChk	= $('#addPresentForm').length;

        $("#"+modeStr+"_tbl thead").show();
        $("#"+modeStr+"_tbl tfoot").show();

        var scmNo = $('input[name=\'scmNo\']').val();
        var scmNoNm = $('button[name=\'scmNoNm\']').html();
        var scmFl = $('input:radio[name=scmFl]:checked').val();

        $.ajax({
            url: 'layer_gift_select.php',
            type: 'get',
            data: { condition : modeStr,scmNo : scmNo,scmNoNm : scmNoNm,scmFl : scmFl },
            async: false,
            success: function (data) {
                if (loadChk == 0) {
                    data = '<div id="addPresentForm">'+data+'</div>';
                }
                var layerForm = data;

                BootstrapDialog.show({
                    title:'사은품 선택',
                    message: $(layerForm),
                    closable: true
                });
            }
        });
    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string typeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(typeStr, modeStr,isDisabled)
    {
        var layerFormID		= 'addPresentForm';

        typeStrId =  typeStr.substr(0,1).toUpperCase() + typeStr.substr(1);

        if (typeof modeStr == 'undefined') {
            var parentFormID	= 'present'+typeStrId;
            var dataFormID		= 'id'+typeStrId;
            var dataInputNm		= 'present'+typeStrId;
            var layerTitle		= '사은품 증정을 위한 조건 - ';
        } else {
            var parentFormID	= 'except'+typeStrId;
            var dataFormID		= 'idExcept'+typeStrId;
            var dataInputNm		= 'except'+typeStrId;
            var layerTitle		= '사은품 증정을 위한 예외 조건 - ';
        }

        // 레이어 창
        if (typeStr == 'goods') {
            var layerTitle		= layerTitle+'상품';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'category') {
            var layerTitle		= layerTitle+'카테고리';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'brand') {
            var layerTitle		= layerTitle+'브랜드';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'scm') {
            var layerTitle = '공급사';
            var dataInputNm = typeStr + "No";
            var parentFormID = typeStr + 'Layer';
            var dataFormID = 'info_scm_';

            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);

            var mode =  'radio';
        }

        if (typeStr == 'member_group') {
            var mode =  'search';
            var layerTitle = '회원등급 선택';
            var dataInputNm = "memberGroupNo";
            var parentFormID = "member_groupLayer";
            var dataFormID ="info_member_group";
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
        };
        console.log(addParam);

        if(typeStr == 'goods'){
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
        }

        if(typeStr == 'scm'){
            addParam['callFunc'] = 'set_scm_select';
        }


        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }

    function set_scm_select(data) {
        displayTemplate(data);
        condition_table('a');
    }

    /**
     * 구매 상품 범위에 따른 상품 선택
     *
     * @param string thisValue 레이어창 종류 (구매상품 범위 값)
     */
    function present_conf(thisValue)
    {
        $('div[id*=\'presentFl_\']').removeClass();
        $('div[id*=\'presentFl_\']').addClass('display-none');
        $('div[id*=\'presentFl_'+thisValue+'\']').removeClass();
        $('div[id*=\'presentFl_'+thisValue+'\']').addClass('display-block');

        $('span[id*=\'presentFlExcept_\']').removeClass();
        $('span[id*=\'presentFlExcept_\']').addClass('display-inline');
        $('span[id*=\'presentFlExcept_'+thisValue+'\']').removeClass();
        $('span[id*=\'presentFlExcept_'+thisValue+'\']').addClass('display-none');
        $('span[id*=\'presentFlExcept_'+thisValue+'\']').find("input:checkbox").prop("checked",false);

        $('tr[id*=\'presentFlExcept_\']').hide();
        if($("input[name='presentExceptFl[]']:checked").length > 0 ) {
            $("input[name='presentExceptFl[]']:checked").each(function () {
                $('#presentFlExcept_'+$(this).val()+"_tbl").show();
            })
        }
    }

    function presentExcept_conf(thisValue) {
        if($('#presentFlExcept_'+thisValue +"_tbl").is(':hidden')) $('#presentFlExcept_'+thisValue +"_tbl").show();
        else  $('#presentFlExcept_'+thisValue +"_tbl").hide();
    }

    /**
     * 사은품 증정 선택 기본 테이블
     *
     * @param string thisValue 사은품 증정 조건 값
     */
    function condition_table(thisValue)
    {
        var addHtml		= '';

        addHtml	+= '<table id="'+fieldID+'"class="table table-rows">';
        addHtml	+= '<colgroup><col class="width-2xl" /><col /><col class="width-sm" /><col class="width-xs" /></colgroup>';
        addHtml	+= '<thead><tr class="giftAddTr">';
        addHtml	+= '<th>';
        if (thisValue == 'p') {
            addHtml	+= '구매 상품 금액 조건 ';
        }
        if (thisValue == 'c') {
            addHtml	+= '구매 상품 수량 조건 ';
        }
        if (thisValue == 'a' || thisValue == 'l' ) {
            addHtml	+= '비고';
        } else {
            addHtml	+= '<input type="button" class="btn btn-sm btn-white" value="추가" onclick="condition_table_add(\''+thisValue+'\');" />';
        }
        addHtml	+= '</th>';
        addHtml	+= '<th><img src="<?=PATH_ADMIN_GD_SHARE;?>img/bl_required.png" style="padding-right: 5px">사은품</th>';
        addHtml	+= '<th>선택 수량</th>';
        addHtml	+= '<th>지급 수량</th>';
        addHtml	+= '</tr></thead>';
        addHtml	+= '</table>';

        $('#conditionDiv').html(addHtml);

        condition_table_add(thisValue);

        // 구매상품 수량만큼 지급(l) || 수량별 지급(c) 만 추가상품 수량포함 체크박스 노출
        if(thisValue == 'l' || thisValue == 'c') {
            $('.addGoodsDiv').show();
        } else {
            $('.addGoodsDiv').hide();
        }
    }

    /**
     * 사은품 증정 선택 값 테이블
     *
     * @param string thisValue 사은품 증정 조건 값
     */
    function condition_table_add(thisValue)
    {
        var addHtml		= '';
        var fieldNoChk	= '';
        var fieldNoCnt =  $('#'+fieldID+" tr.giftAddTr").length;
        if(fieldNoCnt > 1 ) fieldNoChk	= $('#'+fieldID).find('tr.giftAddTr:last').attr('id').replace(fieldID+'No_','');
        if (fieldNoChk == '') {
            var fieldNoChk	= 0;
        }

        var fieldNo		= parseInt(fieldNoChk) + 1;
        var fieldAddID	= fieldID+'No_'+fieldNo;

        addHtml	+= '<tr id="'+fieldAddID+'" class="giftAddTr">';
        addHtml	+= '<td class="center"><div class="form-inline">';
        addHtml	+= '<input type="hidden" name="gift[giftSno]['+fieldNo+']" value="" />';
        if (thisValue == 'a' || thisValue == 'l') {
            addHtml	+= '<input type="hidden" name="gift[conditionStart]['+fieldNo+']" value="0" /><input type="hidden" name="gift[conditionEnd]['+fieldNo+']" value="0" />';
            addHtml	+= '금액 수량 조건 없음';
        } else {
            if (fieldNo > 1) {
                addHtml	+= '<input type="button" class="btn btn-sm btn-white btn-icon-minus" value="삭제" onclick="field_remove(\''+fieldAddID+'\');" /> ';
            }
        }
        if (thisValue == 'p') {
            addHtml	+= '<?=gd_currency_symbol();?><input type="text" name="gift[conditionStart]['+fieldNo+']" value="" class="form-control width-xs" />  <?=gd_currency_string();?> ~';
            addHtml	+= '<?=gd_currency_symbol();?><input type="text" name="gift[conditionEnd]['+fieldNo+']" value="" class="form-control width-xs" />  <?=gd_currency_string();?>';
        }
        if (thisValue == 'c') {
            addHtml	+= '<input type="text" name="gift[conditionStart]['+fieldNo+']" value="" class="form-control width-2xs" /> 개 ~';
            addHtml	+= '<input type="text" name="gift[conditionEnd]['+fieldNo+']" value="" class="form-control width-2xs" /> 개';
        }
        if (thisValue != 'a' && thisValue != 'l') {
            addHtml	+= ' <input type="button" class="btn btn-sm btn-gray" value="복사" onclick="condition_table_add(\''+thisValue+'\');condition_table_copy(\''+fieldNo+'\');" />';
        }
        addHtml	+= '</div></td>';
        addHtml	+= '<td class="left">';
        addHtml	+= '<div>';
        addHtml	+= '<input type="button" class="btn btn-sm btn-gray" value="사은품 선택" onclick="layer_gift_select(\'div_multi_'+fieldNo+'\');" />';
        addHtml	+= '</div>';
        addHtml	+= '<table id="div_multi_'+fieldNo+'_tbl" class="mgt10 mgb0 table table-rows" style="width:80%"><thead style="display:none"><tr><th class="width7p">번호</th><th class="width10p">이미지</th><th>사은품명</th><th class="width8p">삭제</th></tr></thead>';
        addHtml	+= '<tbody id="div_multi_'+fieldNo+'" >';
        addHtml	+= '</tbody>';
        addHtml	+= '<tfoot style="display:none"><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$(\'#div_multi_'+fieldNo+'\').html(\''+''+'\');"></td></tfoot></table>';
        addHtml	+= '</td>';
        addHtml	+= '<td class="center">';
        addHtml	+= '<div>';
        addHtml	+= '<?=str_replace(chr(10),'',gd_select_box('multiCodeNo', 'gift[selectCnt][CodeNo]', array('전체지급'),null,null,null));?>'.replace(/CodeNo/g,fieldNo);
        addHtml	+= '</div>';
        addHtml	+= '</td>';
        addHtml	+= '<td class="center">';
        addHtml	+= '<div>';
        addHtml	+= '<input type="text" name="gift[giveCnt]['+fieldNo+']" value="1" class="form-control" />';
        addHtml	+= '</div>';
        addHtml	+= '</td>';
        addHtml	+= '</tr>';

        $('#'+fieldID).append(addHtml);

        $('input[name*=\'conditionStart\']').number_only();
        $('input[name*=\'conditionEnd\']').number_only();

        // inputbox에 focus효과
    }

    /**
     * 사은품 복사
     *
     * @param string fieldNo 현재의 번호
     */
    function condition_table_copy(fieldNo)
    {
        var commonIdPre1	= eval('/\\['+fieldNo+'\\]/g');
        var commonIdPre2	= eval('/GiftNo_'+fieldNo+'/g');
        var multiIdPre		= eval('/multi_'+fieldNo+'_/g');

        var multiHtml		= $('#div_multi_'+fieldNo).html();
        var multiValue		= $('#multi'+fieldNo).val();
        var countValue		= $('input[name=\'gift[giveCnt]['+fieldNo+']\']').val();

        var fieldNewNo		= $('#'+fieldID).find('tr.giftAddTr:last').attr('id').replace(fieldID+'No_','');

        var commonIdNew1	= '['+fieldNewNo+']';
        var commonIdNew2	= 'GiftNo_'+fieldNewNo;
        var multiIdNew		= 'multi_'+fieldNewNo+'_';

        var changMultiHtml	= multiHtml.replace(commonIdPre1, commonIdNew1).replace(commonIdPre2, commonIdNew2).replace(multiIdPre, multiIdNew);

        $('#div_multi_'+fieldNewNo).html(changMultiHtml);

        if (changMultiHtml != '') {
            mode_selectbox_reset('multiGiftNo_'+fieldNewNo,'multi');
            $('#multi'+fieldNewNo+' option[value='+multiValue+']').prop('selected',true);
        }

        $('input[name=\'gift[giveCnt]['+fieldNewNo+']\']').val(countValue);
    }

    /**
     * 멀티 선택형 상품의 선택 조건 select Box
     *
     * @param string modeID 멀티 선택형 사은품 ID
     * @param string modeNm select Box ID 이름 키값
     */
    function mode_selectbox_reset(modeID, modeNm)
    {
        var thisID		= modeID.split('_');
        var modeCnt		= $('input[name*=\'gift['+thisID[0]+']['+thisID[1]+']\']').length;
        var modeID		= modeNm+thisID[1];
        var modeVal		= $('#'+modeID).val();
        var addOpt		= '';
        var seletedStr	= '';

        if (modeCnt > 0) {
            for (var i = 0; i < modeCnt; i++) {
                if (i == 0) {
                    var textStr	= '전체지급';
                } else {
                    var textStr	= i+'개 선택';
                }
                if (i == modeVal) {
                    seletedStr	= ' selected=\'selected\'';
                } else {
                    seletedStr	= '';
                }
                addOpt	+= '<option value="'+i+'" '+seletedStr+'>'+textStr+'</option>';
            }
        }
        $('#'+modeID).html(addOpt);
    }

    function set_cate_permission(val) {
        if(val =='group') {
            $("#btn_member_group").attr("disabled",false);
            layer_register('member_group');

        } else {
            $("#btn_member_group").attr("disabled",true);
            $("#member_groupLayer").html('');
        }
    }

    //-->
</script>

<form id="frmGiftPresent" name="frmGiftPresent" action="gift_ps.php" method="post">
    <input type="hidden" name="mode" value="<?=$data['mode'];?>" />
    <input type="hidden" name="sno" value="<?=$data['sno'];?>" />

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./gift_present_list.php');" />
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title ">
        기본정보
    </div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <?php if(gd_use_provider() === true) { ?>
            <?php if(gd_is_provider()) { ?>
            <input type="hidden" name="scmNo" value="<?=$data['scmNo']?>">
            <?php } else { ?>
            <tr>
                <th class="input_title r_space ">공급사 구분</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="scmFl"value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('');condition_table('a')" ;/>본사</label>
                    <label class="radio-inline"><input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?>onclick="layer_register('scm','radio',true)"/>공급사</label>
                    <label><button type="button" class="btn btn-sm btn-gray scmBtn" onclick="layer_register('scm','radio',true)">공급사 선택</button></label>
                    <div id="scmLayer" class="selected-btn-group <?=$data['scmNoNm'] && $data['scmNo'] != DEFAULT_CODE_SCMNO ? 'active' : ''?>">
                        <?php if ($data['scmNo']) { ?>
                            <h5>선택된 공급사 : </h5>
                            <span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
							<input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
                                <?php if($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>
                                    <span class="btn"> <?= $data['scmNoNm'] ?></span>
  <?php if($data['mode'] =='register' ) { ?>
                                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button> <?php } ?>
                                <?php }?>
					        </span>
                        <?php } ?>
                    </div>

                </td>
            </tr>
            <?php } ?>
            <?php } ?>
            <tr>
                <th class="require">사은품 지급조건명</th>
                <td > <div class="form-inline">
                    <input type="text" name="presentTitle" value="<?=$data['presentTitle'];?>" class="form-control width-xl" />
                    <?php
                    // 진행현황 처리
                    if ($data['mode'] == 'present_modify') {
                        if ($data['presentPeriodFl'] == 'y') {
                            if ($data['periodStartYmd'] > date('Y-m-d H:i:s')) {
                                echo '<span class="button small"><input type="button" value="대기중" /></span> ';
                            } else if ($data['periodEndYmd'] >= date('Y-m-d H:i:s')) {
                                echo '<span class="button black small"><input type="button" value="진행중" /></span> ';
                            } else {
                                echo '<span class="button red small"><input type="button" value="종료" /></span> ';
                            }
                        } else {
                            echo '<span class="button black small"><input type="button" value="진행중" /></span> ';
                        }
                        //echo '<span class="button black small"><a href="" target="_blank">사은품 이벤트 페이지 바로가기</a></span>';
                    }
                    ?></div>
                </td>
            </tr>
            <tr>
                <th >지급기간</th>
                <td >
                    <div class="form-inline">
                        <label class="radio-inline"><input type="radio" name="presentPeriodFl" value="n" <?=gd_isset($checked['presentPeriodFl']['n']);?> /> 제한 없음</label>
                        <label class="radio-inline">
                            <input type="radio" name="presentPeriodFl" value="y" <?=gd_isset($checked['presentPeriodFl']['y']);?> />
                            <div class="input-group js-datetimepicker">
                                <input type="text" class="form-control width-sm" name="periodStartYmd" value="<?=$data['periodStartYmd'];?>" onclick="$('input[name=\'presentPeriodFl\']').eq(1).prop('checked',true);"  >
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                            ~
                            <div class="input-group js-datetimepicker endDatetime">
                                <input type="text" class="form-control width-sm" name="periodEndYmd"  value="<?=$data['periodEndYmd'];?>" onclick="$('input[name=\'presentPeriodFl\']').eq(1).prop('checked',true);">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                            </div>
                        </label>
                    </div>
                </td>
            </tr>
            <tr class="hideDivisionFl">
                <th>지급 회원등급</th>
                <td colspan="3">
                    <?php foreach($group as $k =>$v) { ?>
                        <label class="radio-inline"><input type="radio" name="presentPermission" value="<?=$k?>" <?=gd_isset($checked['presentPermission'][$k]);?> onclick="set_cate_permission(this.value)" /> <?=$v?></label>
                    <?php } ?>
                    <input type="button" value="회원등급 선택"  class="btn btn-sm btn-gray" id="btn_member_group" onclick="layer_register('member_group')" <?php if($data['presentPermission'] !='group') echo "disabled"; ?>>
                    <div id="member_groupLayer" class="selected-btn-group <?=is_array($data['presentPermissionGroup']) ? 'active' : ''?>">
                        <?php if ($data['presentPermissionGroup']) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['presentPermissionGroup'] as $k => $v) { ?>

                                <div id="info_member_group_<?= $k ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="memberGroupNo[]" value="<?= $k ?>">
                                    <input type="hidden" name="memberGroupNoNm[]" value="<?= $v ?>">
                                    <span class="btn"><?= $v ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_member_group_<?= $k ?>">삭제</button>
                                </div>
                            <?php }
                        } ?>

                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title ">
        상품조건 설정
    </div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th >지급상품 선택</th>
                <td >
                    <label class="radio-inline"><input type="radio" name="presentFl" value="a" onclick="present_conf(this.value);" <?=gd_isset($checked['presentFl']['a']);?> />전체 상품</label>
                    <label class="radio-inline"><input type="radio" name="presentFl" value="g" onclick="present_conf(this.value);" <?=gd_isset($checked['presentFl']['g']);?> />특정 상품</label>
                    <label class="radio-inline"><input type="radio" name="presentFl" value="c" onclick="present_conf(this.value);" <?=gd_isset($checked['presentFl']['c']);?> />특정 카테고리</label>
                    <label class="radio-inline"><input type="radio" name="presentFl" value="b" onclick="present_conf(this.value);" <?=gd_isset($checked['presentFl']['b']);?> />특정 브랜드</label>
                    <!--<label><input type="radio" name="presentFl" value="e" onclick="present_conf(this.value);" <?=gd_isset($checked['presentFl']['e']);?> />특정 이벤트</label>-->
                </td>
            </tr>
        </table>

        <div id="presentFl_all" class="display-none">
            <table class="table table-cols">
                <colgroup><col class="width-md" /><col /></colgroup>
                <tr>
                    <th >전체 상품</th>
                    <td >
                        <div class="notice-info">전체 상품에 대해서 주문시 사은품을 증정하게 됩니다.<br>단, 예외조건에 해당되는 상품은 사은품 증정이 안내되지 않습니다</div>
                    </td>
                </tr>
            </table>
        </div>

        <div id="presentFl_goods" class="display-none">
            <table class="table table-cols">
                <colgroup><col class="width-md" /><col /></colgroup>
                <tr>
                    <th >특정 상품
                        <div><input type="button" value="상품 선택" onclick="layer_register('goods');"  class="btn btn-sm btn-gray"/></div>
                    </th>
                    <td >
                        <div class="notice-info">선택된 상품에 대해서 주문시 사은품을 증정하게 됩니다.<br/>
                            단, 예외조건에 해당되는 상품은 사은품 증정이 안내되지 않습니다</div>
                        <table id="presentGoodsTable"class="table table-cols" style="width:80%">
                            <thead <?php if (is_array($data['presentKindCd']) == false)  { echo "style='display:none'"; } ?>>
                            <tr>
                                <th class="width5p">번호</th>
                                <th class="width10p">이미지</th>
                                <th>상품명</th>
                                <th class="width8p">삭제</th>
                            </tr>
                            </thead>
                            <tbody id="presentGoods" >
                            <?php
                            if ($data['presentFl'] == 'g' && is_array($data['presentKindCd'])) {
                                foreach ($data['presentKindCd'] as $key => $val) {
                                    echo '<tr id="idGoods_'.$val['goodsNo'].'">'.chr(10);
                                    echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="presentGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                    echo '<td  class="center">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                    echo '<td>'.$val['goodsNm'].'</td>'.chr(10);
                                    echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idGoods_'.$val['goodsNo'].'\');" value="삭제" /></td>'.chr(10);
                                    echo '</tr>'.chr(10);
                                }
                            }
                            ?>
                            </tbody>
                            <tfoot <?php if (is_array($data['presentKindCd']) == false)  { echo "style='display:none'"; } ?>>
                            <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#presentGoods').html('');"></td></tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div id="presentFl_category" class="display-none">
            <table class="table table-cols">
                <colgroup><col class="width-md" /><col /></colgroup>
                <tr>
                    <th >특정 카테고리
                        <div><input type="button" value="카테고리 선택"  class="btn btn-sm btn-gray" onclick="layer_register('category');" /></div>
                    </th>
                    <td >
                        <div class="notice-info">선택된 카테고리내 상품에 대해서 주문시 사은품을 증정하게 됩니다.<br/>
                            단, 예외조건에 해당되는 상품은 사은품 증정이 안내되지 않습니다</div>
                        <table id="presentCategoryTable"class="table table-cols" style="width:80%">
                            <thead <?php if (is_array($data['presentKindCd']) == false || $data['presentFl'] != 'c')  { echo "style='display:none'"; } ?>>
                            <tr>
                                <th class="width5p">번호</th>
                                <th>카테고리</th>
                                <th class="width8p">삭제</th>
                            </tr>
                            </thead>
                            <tbody id="presentCategory" >
                            <?php
                            if ($data['presentFl'] == 'c' && is_array($data['presentKindCd'])) {
                                foreach ($data['presentKindCd']['code'] as $key => $val) {
                                    echo '<tr id="idCategory_'.$val.'">'.chr(10);
                                    echo '<td class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="presentCategory[]" value="'.$val.'" /></td>'.chr(10);
                                    echo '<td>'.$data['presentKindCd']['name'][$key].'</td>'.chr(10);
                                    echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idCategory_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                    echo '</tr>'.chr(10);
                                }
                            }
                            ?>
                            </tbody>
                            <tfoot <?php if (is_array($data['presentKindCd']) == false || $data['presentFl'] != 'c')  { echo "style='display:none'"; } ?>>
                            <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#presentCategory').html('');"></td></tr>
                            </tfoot>
                        </table>

                    </td>
                </tr>
            </table>
        </div>

        <div id="presentFl_brand" class="display-none">
            <table class="table table-cols">
                <colgroup><col class="width-md" /><col /></colgroup>
                <tr>
                    <th >특정 브랜드
                        <div><input type="button" value="브랜드 선택" onclick="layer_register('brand');"   class="btn btn-sm btn-gray" /></div>
                    </th>
                    <td >

                        <div class="notice-info">※ 선택된 브랜드내 상품에 대해서 주문시 사은품을 증정하게 됩니다.<br/>
                            ※ 단, 예외조건에 해당되는 상품은 사은품 증정이 안내되지 않습니다</div>
                        <table id="presentBrandTable"class="table table-cols" style="width:80%">
                            <thead <?php if (is_array($data['presentKindCd']) == false || $data['presentFl'] != 'b')  { echo "style='display:none'"; } ?>>
                            <tr>
                                <th class="width5p">번호</th>
                                <th>브랜드</th>
                                <th class="width8p">삭제</th>
                            </tr>
                            </thead>
                            <tbody id="presentBrand" >
                            <?php
                            if ($data['presentFl'] == 'b' && is_array($data['presentKindCd'])) {
                                foreach ($data['presentKindCd']['code'] as $key => $val) {
                                    echo '<tr id="idBrand_'.$val.'">'.chr(10);
                                    echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="presentBrand[]" value="'.$val.'" /></td>'.chr(10);
                                    echo '<td>'.$data['presentKindCd']['name'][$key].'</td>'.chr(10);
                                    echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idBrand_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                    echo '</tr>'.chr(10);
                                }
                            }
                            ?>
                            </tbody>
                            <tfoot <?php if (is_array($data['presentKindCd']) == false || $data['presentFl'] != 'b')  { echo "style='display:none'"; } ?>>
                            <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#presentBrand').html('');"></td></tr>
                            </tfoot>
                        </table>



                    </td>
                </tr>
            </table>
        </div>


    </div>

    <div class="table-title ">
        사은품 지급 예외조건
    </div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th >예외 조건</th>
                <td>
                    <span id="presentFlExcept_goods"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="goods" onclick="presentExcept_conf(this.value)">예외 상품</label></span>
                    <span id="presentFlExcept_category"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="category" onclick="presentExcept_conf(this.value)">예외 카테고리</label></span>
                    <span id="presentFlExcept_brand"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="brand" onclick="presentExcept_conf(this.value)">예외 브랜드</label></span>
                </td>
            </tr>
            <tr id="presentFlExcept_goods_tbl" style="display:none">
                <th>예외 상품
                    <div><input type="button"  class="btn btn-sm btn-gray" value="상품 선택" onclick="layer_register('goods','except');" /></div>
                </th>
                <td>

                    <table id="exceptGoodsTable" class="table table-cols" style="width:80%">
                        <thead <?php if (is_array($data['exceptGoodsNo']) == false)  { echo "style='display:none'"; } ?>>
                        <tr>
                            <th class="width5p">번호</th>
                            <th class="width10p">이미지</th>
                            <th>상품명</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="exceptGoods" >
                        <?php
                        if (is_array($data['exceptGoodsNo'])) {
                            foreach ($data['exceptGoodsNo'] as $key => $val) {
                                echo '<tr id="idExceptGoods_'.$val['goodsNo'].'">'.chr(10);
                                echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                echo '<td  class="center">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                echo '<td>'.$val['goodsNm'].'</td>'.chr(10);
                                echo '<td  class="center"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\'idExceptGoods_'.$val['goodsNo'].'\');" value="삭제" /></td>'.chr(10);
                                echo '</tr>'.chr(10);
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptGoodsNo']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptGoods').html('');"></td></tr>
                        </tfoot>
                    </table>

                </td>
            </tr>

            <tr id="presentFlExcept_category_tbl" style="display:none">
                <th>예외 카테고리
                    <div><input type="button" class="btn btn-sm btn-gray"  value="카테고리 선택" onclick="layer_register('category','except');" /></div>
                </th>
                <td>

                    <table id="exceptCategoryTable"class="table table-cols" style="width:80%">
                        <thead <?php if (is_array($data['exceptCateCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr>
                            <th class="width5p">번호</th>
                            <th>카테고리</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="exceptCategory" >
                        <?php
                        if (is_array($data['exceptCateCd'])) {
                            foreach ($data['exceptCateCd']['code'] as $key => $val) {
                                echo '<tr id="idExceptCategory_'.$val.'">'.chr(10);
                                echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptCategory[]" value="'.$val.'" /></td>'.chr(10);
                                echo '<td>'.$data['exceptCateCd']['name'][$key].'</td>'.chr(10);
                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptCategory_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                echo '</tr>'.chr(10);
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptCateCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptCategory').html('');"></td></tr>
                        </tfoot>
                    </table>

                </td>

            </tr>

            <tr id="presentFlExcept_brand_tbl" style="display:none">
                <th>예외 브랜드
                    <div><input type="button"  class="btn btn-sm btn-gray" value="예외 브랜드 선택" onclick="layer_register('brand','except');" /></div>
                </th>
                <td>
                    <table id="exceptBrandTable"class="table table-cols" style="width:80%">
                        <thead <?php if (is_array($data['exceptBrandCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr>
                            <th class="width5p">번호</th>
                            <th>브랜드</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="exceptBrand" >
                        <?php
                        if (is_array($data['exceptBrandCd'])) {
                            foreach ($data['exceptBrandCd']['code'] as $key => $val) {
                                echo '<tr id="idExceptBrand_'.$val.'">'.chr(10);
                                echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptBrand[]" value="'.$val.'" /></td>'.chr(10);
                                echo '<td>'.$data['exceptBrandCd']['name'][$key].'</td>'.chr(10);
                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptBrand_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                echo '</tr>'.chr(10);
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptBrandCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptBrand').html('');"></td></tr>
                        </tfoot>
                    </table>

                </td>

            </tr>

            <!--<div id="presentFlExcept_event" class="display-none">
                <table class="list_table">
                <colgroup><col class="width-md" /><col /></colgroup>
                <tr>
                    <th class="left">예외 이벤트</th>
                    <td>
                        <div><span class="button blue small"><input type="button" value="예외 이벤트 선택하기" onclick="layer_register('Event','except');" /></span></div>
                        <div id="exceptEvent" class="width100p"></div>
                    </td>
                </tr>
                </table>
            </div>-->

        </table>
    </div>

    <div class="table-title ">
        사은품 선택
    </div>

        <p class="notice-info">
            멀티 선택형은 설정한 개수의 조건에 맞게 주문 고객이 선택을 할수 있는 방식임<br/>
            상품 갯수형은 등록된 사은품 상품을 무조건 설정된 증정갯수 만큼 전부 증정을 하는 방식임<br/>
            <span class="text-danger">사은품 상품의 재고가 없거나 부족한 경우 주문시 자동으로 제외 됩니다.</span><br/>
        </p>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th >증정 조건</th>
                <td >
                    <label  class="radio-inline" ><input type="radio" name="conditionFl" value="a" onclick="condition_table(this.value);fill_condition_table(this.value);" <?=gd_isset($checked['conditionFl']['a']);?> />무조건 지급</label>
                    <label  class="radio-inline" ><input type="radio" name="conditionFl" value="l" onclick="condition_table(this.value);fill_condition_table(this.value);" <?=gd_isset($checked['conditionFl']['l']);?> />구매상품 수량만큼 지급</label>
                    <label  class="radio-inline" ><input type="radio" name="conditionFl" value="p" onclick="condition_table(this.value);fill_condition_table(this.value);" <?=gd_isset($checked['conditionFl']['p']);?> />금액별 지급</label>
                    <label  class="radio-inline" ><input type="radio" name="conditionFl" value="c" onclick="condition_table(this.value);fill_condition_table(this.value);" <?=gd_isset($checked['conditionFl']['c']);?> />수량별 지급</label>
                    <div class="addGoodsDiv mgt10">
                        <label class="checkbox-inline"><input type="checkbox" name="addGoodsFl" value="y" <?=gd_isset($checked['addGoodsFl']['y']);?>>추가상품 수량 포함</label>
                        <div class="notice-info">추가상품 수량 포함 체크 시 상품조건 설정에서 선택한 상품에 연결된 추가상품 개수도 포함되어 주문 시 사은품을 증정하게 됩니다.
                            <br>단, 예외조건에 해당되는 상품에 연결된 추가상품 수량은 증정 조건에 포함되지 않습니다.</div>
                    </div>
                </td>
            </tr>

        </table>
        <div id="conditionDiv"></div>
    </div>

</form>
