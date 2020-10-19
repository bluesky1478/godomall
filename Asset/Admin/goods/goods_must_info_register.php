<script type="text/javascript">
    <!--

    $(document).ready(function() {

        $.validator.addMethod(
            'optionCheck', function (value, element) {


                if($('input[name*="addMustInfo["]').length > 1) {

                    var infoValue = true,
                        infoTitleFl = true,
                        infoValueFl = true;

                    $('input[name*="addMustInfo[infoTitle]["]').each(function () {
                        if($(this).val() =='' && $(this).parent().next().find('input').val() =='') infoValue = false;
                        if ($(this).val() && $(this).val().length > 60) infoTitleFl = false;
                        if ($(this).parent().next().find('input').val() && $(this).parent().next().find('input').val().length > 500) infoValueFl = false;
                    });

                    if(infoValue == false) {
                        $.validator.messages.optionCheck =  '상세정보 항목을 입력해주세요.';
                        return false;
                    } else {
                        if (infoTitleFl == false) {
                            $.validator.messages.optionCheck =  '상세정보 항목은 60자를 넘을 수 없습니다.';
                            return false;
                        }
                        if (infoValueFl == false) {
                            $.validator.messages.optionCheck =  '상세정보 내용은 500자를 넘을 수 없습니다.';
                            return false;
                        }
                        return true;
                    }


                } else {
                    $.validator.messages.optionCheck =  '상세정보 항목을 추가해주세요.';
                    return false;
                }


            },'');

        $("#frmGoods").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                mustInfoNm: {
                    required: true
                },
                mode: {
                    optionCheck: true
                }
            },
            messages: {
                mustInfoNm: {
                    required: '필수정보명 입력하세요.'
                }
            }
        });

    });


    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }

    /**
     * 상품 필수 정보 추가
     */
    function add_must_info(infoCnt)
    {
        $(".add-must-notice").remove();
        var fieldID		= 'addMustInfo';
        $('#'+fieldID + " thead").removeClass('display-none');
        var fieldNoChk	= $('#'+fieldID).find('tr:last').get(0).id.replace(fieldID,'');
        if (fieldNoChk == '') {
            var fieldNoChk	= 0;
        }
        var fieldNo		= parseInt(fieldNoChk) + 1;

        var colspanStr	= '';
        if (infoCnt	== 2) {
            colspanStr	= ' colspan="3"';
        } else {
        }

        var addHtml		= '';
        addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
        addHtml	+= '<td class="center"><input type="text" name="addMustInfo[infoTitle]['+fieldNo+'][]" value="" class="form-control width-lg" maxlength="60" /></td>';
        addHtml	+= '<td class="center"'+colspanStr+'><input type="text" name="addMustInfo[infoValue]['+fieldNo+'][]" value="" class="form-control" maxlength="500" /></td>';
        if (infoCnt	== 4) {
            addHtml	+= '<td class="center"><input type="text" name="addMustInfo[infoTitle]['+fieldNo+'][]" value="" class="form-control width-lg" maxlength="60" /></td>';
            addHtml	+= '<td class="center"><input type="text" name="addMustInfo[infoValue]['+fieldNo+'][]" value="" class="form-control" maxlength="500"  /></td>';
        }
        addHtml	+= '<td class="center"><button type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="remove_must_info(\''+fieldID+fieldNo+'\');"  >삭제</button></td>';
        addHtml	+= '</tr>';
        $('#'+fieldID).append(addHtml);
    }

    function remove_must_info(fieldID) {

        field_remove(fieldID);

        var fieldNoChk	= $('#addMustInfo tbody').find('tr').length;

        if(fieldNoChk == 0) {
            $('#addMustInfo thead').addClass('display-none');
        }

    }



    //-->
</script>
<form id="frmGoods" name="frmGoods" action="./goods_must_info_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?=$data['mode']?>"/>
    <input type="hidden" name="sno" value="<?=$data['sno'];?>" />

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./goods_must_info_list.php');" />
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>


    <div class="desc_box">
        <div class="notice-danger">
            공정거래위원회에서 공고한 전자상거래법 상품정보제공 고시에 관한 내용을 필독해 주세요!
            <a href="http://www.ftc.go.kr/www/FtcRelLawUList.do?key=290&law_div_cd=07" target="_blank" class="btn-link-underline">내용 확인 ></a>
        </div>
        <div class="notice-info">
            전자상거래법에 의거하여 판매 상품의 필수 (상세) 정보 등록이 필요합니다.<br />
            <a class="btn-link-underline" onclick="goods_must_info_popup();">품목별 상품정보고시 내용보기</a>를 참고하여 상품필수 정보를 등록하여 주세요.<br/>
            등록된 정보는 쇼핑몰 상품상세페이지에 상품기본정보 아래에 표형태로 출력되어 보여집니다.
        </div>
        <div class="notice-danger">
            전기용품 및 생활용품 판매 시 "전기용품 및 생활용품 안전관리법"에 관한 내용을 필독해 주세요!
            <a href="http://www.law.go.kr/lsInfoP.do?lsiSeq=180398#0000" target="_blank" class="btn-link-underline">내용 확인 ></a>
        </div>
        <div class="notice-info">
            안전관리대상 제품은 안전인증 등의 표시(KC 인증마크 및 인증번호)를 소비자가 확인할 수 있도록 상품 상세페이지 내 표시해야 합니다.<br/>
            <a class="btn-link-underline"  href="http://safetykorea.kr/policy/targetsSafetyCert" target="_blank" >국가기술표준원(KATS) 제품안전정보센터</a>에서 인증대상 품목여부를 확인하여 등록하여 주세요.
        </div>
    </div>


    <div class="table-title gd-help-manual">
        기본정보
    </div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <?php if(gd_use_provider() === true) { ?>
            <?php if(gd_is_provider()) { ?>
                <input type="hidden" name="scmNo" value="<?=$data['scmNo']?>">
            <?php } else { ?>
            <tr>
                <th class="input_title r_space ">공급사 구분</th>
                <td>
                    <label  class="radio-inline"><input type="radio" name="scmFl"
                                  value="n" <?=gd_isset($checked['scmFl']['n']); ?>    onclick="$('#scmLayer').html('')";/>본사</label>
                    <label  class="radio-inline"><input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?>
                                  onclick="layer_register('scm', 'radio',true)"/>공급사</label>
                    <label> <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm', 'radio',true)">공급사 선택</button></label>
                    <label  class="radio-inline"><input type="radio" name="scmFl"
                                                        value="a" <?=gd_isset($checked['scmFl']['a']); ?>    onclick="$('#scmLayer').html('')";/>구분없음</label>
                    <div id="scmLayer" class="selected-btn-group <?=$data['scmNoNm'] && $data['scmNo'] != DEFAULT_CODE_SCMNO ? 'active' : ''?>">
                        <?php if ($data['scmNo']) { ?>
                            <h5>선택된 공급사 : </h5>
                            <span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
							<input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
                                <?php if($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>
                                    <span class="btn"><?= $data['scmNoNm'] ?></span>
                                    <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button>
                                <?php }?>
					        </span>
                        <?php } ?>
                    </div>

                </td>
            </tr>
<?php } ?>
            <?php } ?>
            <tr>
                <th class="require">필수정보명</th>
                <td class="input_area" >
                    <label title=""><input type="text" name="mustInfoNm" value="<?=gd_isset($data['mustInfoNm']); ?>"  class="form-control js-maxlength width-2xl" maxlength="100"/></label>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
       상세정보
    </div>
    <div>

        <div>
            <input type="button" class="btn btn-sm btn-white btn-icon-goods-must-info-02" onclick="add_must_info(4);"  value="4칸 항목 추가" />
            <input type="button" class="btn btn-sm btn-white btn-icon-goods-must-info-01" onclick="add_must_info(2);"  value="2칸 항목 추가" />
            <span class="notice-danger">
                항목과 내용 란에 아무 내용도 입력하지 않으면 저장되지 않습니다.
            </span>

        </div>
        <table class="table table-cols" id="addMustInfo">
            <colgroup><col class="width15p" /><col class="width30p"/><col class="width15p" /><col class="width30p"/><col class="width10p"/></colgroup>
            <thead  >
            <tr>
                <th><img src="<?=PATH_ADMIN_GD_SHARE;?>img/bl_required.png" style="padding-right: 5px">항목</th>
                <th>내용</th>
                <th><img src="<?=PATH_ADMIN_GD_SHARE;?>img/bl_required.png" style="padding-right: 5px">항목</th>
                <th>내용</th>
                <th>-</th>
            </tr>
            </thead>
            <tdody>
            <?php if(gd_isset($data['addMustInfo'])) { ?>
                <?php foreach($data['addMustInfo']['infoTitle'] as $infoKey => $infoValue) { ?>
                    <tr id="addMustInfo<?=$infoKey?>">
                    <?php foreach($infoValue as $k => $v) { ?>
                        <td class="center"><input value="<?=$v?>" name="addMustInfo[infoTitle][<?=$infoKey?>][]"  class="form-control" ></td>
                        <td class="center" <?=count($infoValue) == 1 ?  "colspan='3'" : ""; ?>><input value="<?=$data['addMustInfo']['infoValue'][$infoKey][$k]?>" name="addMustInfo[infoValue][<?=$infoKey?>][]"  class="form-control" ></td>
                    <?php } ?>
                        <td class="center">
                            <button type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="remove_must_info('addMustInfo<?=$infoKey?>');"  >삭제</button>
                            </td>
                    </tr>
                <?php } ?>
            <?php } else {  ?>
                <tr><td colspan="5" class="center add-must-notice">상세정보를 추가해주세요.</td></tr>

            <?php } ?>
            </tdody>

        </table>
    </div>


</form>
