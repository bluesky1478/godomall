<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 등록
        $("#frmGoods").validate({
            submitHandler: function (form) {

                var tbodyCnt = $("#tbl_add_goods_set tbody").length;
                var fixGoods = [];


                for(var i = 0; i < tbodyCnt; i ++ ) {
                    if($("#tabGoodsCnt"+i).length > 0  || i == 0) {
                        if($("#add_goods"+i+" input[name='sortFix[]']").length) {

                            var tmpFixGoods = [];
                            $("#add_goods"+i+" input[name='sortFix[]']:checked").each(function () {
                                tmpFixGoods.push($(this).val());
                            });

                            fixGoods.push(tmpFixGoods.join('<?=INT_DIVISION?>'));
                        }
                    } else {
                        $("#add_goods"+i).remove();
                    }
                }
                var fixGoodsNo = fixGoods.join('<?=STR_DIVISION?>');


                if(fixGoodsNo !='null') {
                    $("#frmGoods").append("<input type='hidden' name='fixGoodsNo' value='"+fixGoodsNo+"'>");
                }

                var submitFl = true;
                $('input[name="presentExceptFl[]"]:checked').each(function () {
                    var exceptKey =  $(this).val().substr(0,1).toUpperCase() + $(this).val().substr(1);

                    if($("input[name='except"+exceptKey+"[]']").length == 0)  {
                        alert('예외 '+$(this).data('name')+' 지정해주세요.');
                        submitFl = false;
                        return false;
                    }
                });

                if(submitFl) {
                    form.target='ifrmProcess';
                    form.submit();
                } else {
                    return false;
                }

            },
            rules: {
                themeNm:{
                    required: true,
                    regex: /[:'"<>`]/
                },
                themeCd: 'required',
            },
            messages: {
                themeNm: {
                    required: '분류명을 입력하세요.',
                    regex: '분류명에 사용할 수 없는 문자가 있습니다.'
                },
                themeCd: {
                    required: '테마를 선택해주세요.'
                }
            }
        });
        set_theme_list();

        $.validator.addMethod('regex', function (value, element, regexpr){
            return regexpr.test(value) !== true;
        });

        <?php if($data['sortAutoFl'] =='n') { ?>
        $("select[name='sort']").attr("disabled",true);
        <?php } else { ?>

        <?php  if (is_array($data['exceptGoodsNo'])) { ?> $('input[name="presentExceptFl[]"][value=goods]').click();<?php  } ?>
        <?php  if (is_array($data['exceptCateCd'])) { ?> $('input[name="presentExceptFl[]"][value=category]').click();<?php  } ?>
        <?php  if (is_array($data['exceptBrandCd'])) { ?> $('input[name="presentExceptFl[]"][value=brand]').click();<?php  } ?>
        <?php  if (is_array($data['exceptScmNo'])) { ?> $('input[name="presentExceptFl[]"][value=scm]').click();<?php  } ?>

        <?php } ?>

        $("input[name='sortAutoFl']").click(function () {
            if($(this).val() =='y')  {
                $("select[name='sort']").attr("disabled",false);
                $(".js-tbl_add_goods_set").hide();
                $(".js-tbl_add_except").show();
            }
            else {
                $("select[name='sort']").attr("disabled",true);
                $(".js-tbl_add_goods_set").show();
                $(".js-tbl_add_except").hide();
            }
        });


    });

    /**
     * 테마보기
     */
    function viewThemeConfig(themeCd) {
        var parameters = {
            'mode': 'theme_ajax',
            'themeCd': themeCd
        };


        $("#tabTitle").html('');
        set_goods_body(0);

        $.post("../goods/display_config_ps.php",parameters, function(data){

            if (typeof data.tabConfig != 'undefined') {
                $("input[name='sortAutoFl']").eq(0).prop('checked',true).trigger('click');
                $("input[name='sortAutoFl']").eq(1).attr('disabled',true);
                $("select[name='sort']").attr("disabled",true);
            } else {
                $("input[name='sortAutoFl']").eq(1).attr('disabled',false);
            }

            $('.js_tbl_theme_themeCd').data('code',data.themeCd);

            $.each(data, function(i,item){
                if(i =='tabConfig') {
                    set_tab_info(item);
                } else {
                    $("#tbl_theme_"+i).html(item);
                }
            });

        }, "json");
    }

    /**
     * 상품 선택
     *
     * @param string orderNo 주문 번호
     */
    function goods_search_popup()
    {
        var mobileFl =  $("input[name='mobileFl']:checked").val();
        window.open('../share/popup_goods.php?mobileFl='+mobileFl, 'popup_goods_search', 'width=1255, height=790, scrollbars=no');
    }

    /**
     * 테마 입력
     *
     * @param string orderNo 주문 번호
     */
    function add_theme_popup()
    {
        var mobileFl = $("input[name='mobileFl']:checked").val();
        window.open('../goods/display_config_theme_register.php?popupMode=yes&themeCate=B&addTheme=themeCd&mobileFl='+mobileFl, 'member_crm', 'width=1210, height=700, scrollbars=yes');
    }

    /**
     * 테마 수정
     *
     * @param string themeCd 테마코드
     */
    function modify_theme_popup()
    {
        var themeCd = $('select[name="themeCd"]').val()
        var addTheme = '';
        if ($('input[name="mobileFl"]:checked').val() === 'y') {
            addTheme = 'mobileTheme';
        } else {
            addTheme = 'pcTheme';
        }

        window.open('../goods/display_config_theme_register.php?popupMode=yes&addTheme='+addTheme+'&callFunc=update_theme_info&themeCd='+themeCd, 'theme_popup', 'width=1210, height=700, scrollbars=yes');
    }

    /**
     * 테마 수정 정보 업데이트
     *
     * @param string themeCd 테마코드
     * @param string themeNm 테마명
     */
    function update_theme_info(themeCd,themeNm)
    {
        viewThemeConfig(themeCd);
        $('select[name="themeCd"] option:selected').text(themeNm);
    };

    var activeGoodsBody = "0";

    function delete_option() {

        var chkCnt = $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 상품이 없습니다.');
            return;
        }

        dialog_confirm('선택한 ' + chkCnt + '개 상품을 삭제하시겠습니까?', function (result) {
            if (result) {
                $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]:checked').each(function () {
                    //field_remove('tbl_add_goods_' + $(this).val());
                    $(this).closest("tr").remove();

                });

                var cnt = $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').length;
                if($("#tabGoodsCnt"+activeGoodsBody).length) {
                    $("#tabGoodsCnt"+activeGoodsBody).html(cnt);
                    $("input[name='tabGoodsCnt["+activeGoodsBody+"]']").val(cnt);
                }

                $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').each(function (idx) {
                    $('#add_goods'+activeGoodsBody+' .addGoodsNumber_'+$(this).val()).html(idx+1);
                });
            }
        });
    }

    function set_theme_list() {
        var mobileFl = $('input[name="mobileFl"]:checked').val();
        if(mobileFl=='n') {
            $('.mobile-display-show').addClass('display-none');
        } else {
            $('.mobile-display-show').removeClass('display-none');
        }

        $.post('./display_ps.php', {'mode': 'search_theme', 'mobileFl': mobileFl, 'themeCd':'B' }, function (data) {
            var themeinfo = $.parseJSON(data);
            if ($.type(themeinfo) != 'array') var themeinfo = {};
            var addHtml = "";

            if(themeinfo) {
                $.each(themeinfo, function (key, val) {
                    addHtml += "<option value='" + val.themeCd + "'>" + val.themeNm + "</option>";
                });
            }

            $('select[name="themeCd"]').html(addHtml);

            <?php if(gd_isset($data['themeCd'])) { ?>
            $('select[name="themeCd"]').val("<?=$data['themeCd']?>");
            <?php } ?>

            if($('select[name="themeCd"]').val()) viewThemeConfig($('select[name="themeCd"]').val());
            else  $('select[name="themeCd"]').val('');

        });
    }


    function setAddGoods(frmData) {
        var addHtml = "";

        $.each(frmData.info, function (key, val) {

            var stockText = "";
            // 상품 재고
            if (val.stockFl == 'n') {
                totalStock    = '∞';
            } else {
                totalStock    = val.totalStock ;
            }

            if(val.soldOutFl =='y' || totalStock== 0) stockText = "품절";
            else stockText = "정상";

            if(val.sortFix == true) {
                sortFix = "checked = 'checked'";
                tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
            } else {
                sortFix = '';
                tableCss = "class='add_goods_free'";
            }

            if(val.goodsDisplayFl =='y') goodsDisplayFl = "노출함";
            else goodsDisplayFl = "노출안함";

            if(val.goodsDisplayMobileFl =='y') goodsDisplayMobileFl = "노출함";
            else goodsDisplayMobileFl = "노출안함";

            addHtml += '<tr id="tbl_add_goods_'+val.goodsNo+'" '+tableCss+'>';
            addHtml += '<td class="center">';
            addHtml += '<input type="hidden" name="itemGoodsNm[]" value="'+val.goodsNm+'" />';
            addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="'+val.goodsPrice+'" />';
            addHtml += '<input type="hidden" name="itemScmNm[]" value="'+val.scmNm+'" />';
            addHtml += '<input type="hidden" name="itemTotalStock[]" value="'+val.totalStock+'" />';
            addHtml += '<input type="hidden" name="itemBrandNm[]" value="'+val.brandNm+'" />';
            addHtml += '<input type="hidden" name="itemMakerNm[]" value="'+val.makerNm+'" />';
            addHtml += '<input type="hidden" name="itemImage[]" value="'+val.image+'" />';
            addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="'+val.soldOutFl+'" />';
            addHtml += '<input type="hidden" name="itemStockFl[]" value="'+val.stockFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsDisplayFl[]" value="'+val.goodsDisplayFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsDisplayMobileFl[]" value="'+val.goodsDisplayMobileFl+'" />';
            addHtml += '<input type="hidden" name="itemIcon[]" value="'+val.goodsIcon+'" />';
            addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_'+val.goodsNo+'"  value="'+val.goodsNo+'"/></td>';
            addHtml += '<td class="center number addGoodsNumber_'+val.goodsNo+'">'+(key+1)+'</td>';
            addHtml += '<td class="center">'+decodeURIComponent(val.image)+'</td>';
            addHtml += '<td><a class="text-blue hand" onclick="goods_register_popup(\''+ val.goodsNo +'\')">'+val.goodsNm+'</a><input type="hidden" name="goodsNoData[]" value="'+val.goodsNo+'" />';
            addHtml += '<input type="checkbox" name="sortFix[]" class="layer_sort_fix_'+val.goodsNo+'"  value="'+val.goodsNo+'" '+sortFix+' style="display:none" >';
            addHtml += '<div>'+decodeURIComponent(val.goodsIcon) +'</div></td>';
            addHtml += '<td class="center">'+val.goodsPrice+'</td>';
            addHtml += '<td class="center">'+val.scmNm+'</td>';
            addHtml += '<td class="center">'+totalStock+'</td>';
            addHtml += '<td class="center">'+stockText+'</td>';
            addHtml += '<td class="center displayFl">'+goodsDisplayFl+'</td>';
            addHtml += '<td class="center displayFl">'+goodsDisplayMobileFl+'</td>';
            addHtml += '</tr>';

        });

        $("#add_goods"+activeGoodsBody).html(addHtml);


        var cnt = $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').length;

        if($("#tabGoodsCnt"+activeGoodsBody).length) {
            $("#tabGoodsCnt"+activeGoodsBody).html(cnt);
            $("input[name='tabGoodsCnt["+activeGoodsBody+"]']").val(cnt);
        }


        $('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').each(function (idx) {
            $('#add_goods'+activeGoodsBody+' .addGoodsNumber_'+$(this).val()).html(idx+1);
        });


    }

    function set_goods_body(goods_tbody) {

        $("#tbl_add_goods_set tbody").removeClass('active');
        $("#tbl_add_goods_set tbody").hide();
        //$("#tabTitle span").removeClass('btn-gray');

        activeGoodsBody = goods_tbody;
        $("#add_goods"+activeGoodsBody).addClass('active');
        $("#add_goods"+activeGoodsBody).show();
       // $("#btnTabTitle"+activeGoodsBody).addClass('btn-gray');

    }

    function set_tab_info(item) {
        for(var i = 0; i < item[0]; i++) {
            if(i > 0 && $("#add_goods"+i).length ==0 ) $("#tbl_add_goods_set").append("<tbody id='add_goods"+i+"' style='display:none'><tr id='tbl_add_goods_tr_none'><td colspan='11' class='no-data'>선택된 상품이 없습니다.</td></tr></tbody>");
            $("#tabTitle").append("<span class='btn btn-white width-sm tab-title' id='btnTabTitle"+i+"' onclick='set_goods_body("+i+")' >"+item[i+2]+"(<span id='tabGoodsCnt"+i+"'>0</span>개)<input type='hidden' name='tabGoodsCnt["+i+"]' value=''></span>");

            var cnt = $('#add_goods'+i+' input[name="itemGoodsNo[]"]').length;
            if(cnt) {
                $("#tabGoodsCnt"+i).html(cnt);
                $("input[name='tabGoodsCnt["+i+"]']").val(cnt);
            }

        }

       // $("#btnTabTitle0").addClass('btn-gray');
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
        var layerTitle		= '';

        typeStrId =  typeStr.substr(0,1).toUpperCase() + typeStr.substr(1);

        if (modeStr == 'except') {
            var parentFormID	= 'except'+typeStrId;
            var dataFormID		= 'idExcept'+typeStrId;
            var dataInputNm		= 'except'+typeStrId;
            var layerTitle		= '예외  ';
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
            var layerTitle = layerTitle+'공급사';
            var mode =  'check';
            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }

        if (typeStr == 'display_main') {
            var layerTitle		= '기존 진열상품';
            var mode =  'search';
        }

        layerTitle  = layerTitle + " 선택";

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
        };

        if(typeStr == 'display_main'){
            addParam['callFunc'] = 'copy_display_main';
        }


        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }



    /**
     * 기존상품 선택
     */
    function copy_display_main(displayMain) {

        var sno = displayMain.displayMainSno;

        $.post('./display_ps.php', {'mode': 'search_goods', 'sno': sno}, function (data) {
            var getData = $.parseJSON(data);

            if(getData.info){
                setAddGoods(getData);
            } else {
                if(typeof(getData.alertMessage) !== 'undefined' && getData.alertMessage != '') {
                    alert(getData.alertMessage);
                } else {
                    alert("해당 분류에 선택된 저장된 상품이 없습니다.");
                }
            }
            return false;
        });

    }

    function presentExcept_conf(thisValue) {
        if($('#presentFlExcept_'+thisValue +"_tbl").is(':hidden')) $('#presentFlExcept_'+thisValue +"_tbl").show();
        else  $('#presentFlExcept_'+thisValue +"_tbl").hide();
    }

    //-->
</script>
<form id="frmGoods" name="frmGoods" action="./display_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="main_<?=$data['mode']; ?>"/>
    <?php if ($data['mode'] == 'modify') { ?><input type="hidden" name="sno" value="<?=gd_isset($data['sno']); ?>" /><?php } ?>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?> <?=$data['mode'] == "register" ?  "등록" : "수정"; ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./display_main_list.php');" />
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title gd-help-manual">
        기본정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col  style="width:170px;"/>
            <col/>
            <col  style="width:170px;"/>
            <col/>
        </colgroup>
        <tr>
            <th>쇼핑몰 유형</th>
            <td>
                <label class="radio-inline"><input type="radio" name="mobileFl" value="n" onclick="set_theme_list()" <?=gd_isset($checked['mobileFl']['n']);?>/>PC쇼핑몰</label>
                <label class="radio-inline"><input type="radio" name="mobileFl" value="y" onclick="set_theme_list()" <?=gd_isset($checked['mobileFl']['y']);?>/>모바일쇼핑몰</label>
            </td>
            <th >노출상태</th>
            <td>
                <label class="radio-inline"><input type="radio" name="displayFl" value="y" <?=gd_isset($checked['displayFl']['y']);?>/>노출함</label>
                <label class="radio-inline"><input type="radio" name="displayFl" value="n" <?=gd_isset($checked['displayFl']['n']);?>/>노출안함</label>
            </td>
        </tr>
        <tr>
            <th class="require">분류명</th>
            <td>
                <label title=""><input type="text" name="themeNm" value="<?=gd_isset($data['themeNm']); ?>"class="form-control width-lg js-maxlength"  maxlength="30"/></label>
            </td>
            <th >분류 설명</th>
            <td>
                <label title=""><input type="text" name="themeDescription" value="<?=gd_isset($data['themeDescription']); ?>"class="form-control width-2xl js-maxlength"  maxlength="100"/></label>
            </td>
        </tr>
        <tr>
            <th>분류 이미지 등록</th>
            <td colspan="3">
                <div class="pull-left width40p ">
                <input type="file" name="imageNm" value="" class="form-control "  />
                <?php if($data['imageNm']) { ?><br/><label><img src="/data/display/<?=$data['imageNm']?>?<?=time()?>" style="max-width:200px;"><input type="hidden" name="imageNm" value="<?=$data['imageNm']?>"> <input type="checkbox" name="imageDel[imageNm]" value="y">삭제</label><?php } ?>
                    </div>
            </td>
        </tr>
        <tr>
            <th class="require">테마 선택</th>
            <td> <div class="form-inline">
                    <input type="hidden" name="themeCdChk"  value="<?=$data['themeCd']?>" >
                    <select name="themeCd" onchange="viewThemeConfig(this.value);" class="form-control input-sm">
                    </select>
                    <input type="button" class="btn btn-sm btn-black" value="테마 등록" onclick="add_theme_popup()" /></div>
            </td>
            <th>진열방법 선택</th>
            <td><div class="form-inline">
                <label class="radio-inline"><input type="radio" name="sortAutoFl" value="n" <?=gd_isset($checked['sortAutoFl']['n']);?>/>수동진열</label>
                <label class="radio-inline"><input type="radio" name="sortAutoFl" value="y"  <?=gd_isset($checked['sortAutoFl']['y']);?>/>자동진열
                <?=gd_select_box('sort', 'sort', $data['sortList'], null, $data['sort'], null); ?>
                </label>
                    </div>
            </td>
        </tr>
        <tr>
            <th>상단 더보기 노출 상태</th>
            <td> <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="moreTopFl" value="y" <?=gd_isset($checked['moreTopFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="moreTopFl" value="n"  <?=gd_isset($checked['moreTopFl']['n']);?>/>노출안함</label>
                </div>
            </td>
            <th>하단 더보기 노출 상태</th>
            <td><div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="moreBottomFl" value="y" <?=gd_isset($checked['moreBottomFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="moreBottomFl" value="n"  <?=gd_isset($checked['moreBottomFl']['n']);?>/>노출안함</label>
                </div>
            </td>
        </tr>
    </table>
    <div class="notice-info">
        상단 더보기 : 쇼핑몰 메인페이지에서 해당 분류의 상품 리스트 페이지로 이동되어 등록된 상품 전체를 확인 할 수 있습니다.<br/>
        하단 더보기 : 쇼핑몰 메인페이지에서 해당 분류에 등록된 상품 전체를 확인 할 수 있습니다..<br/>
        (디스플레이 유형이 “상품이동형/세로이동형/스크롤형/탭진열형” 테마는 하단 더보기 노출상태를 노출함으로 설정하여도 쇼핑몰에 노출되지 않습니다.)<br/>
        <span class="text-danger">진열방법 선택 : 선택된 테마의 디스플레이 유형이 ‘탭 진열형‘인 경우 수동진열만 사용할 수 있습니다..<br/>
        <span style="padding-left:80px;display:block">자동진열 선택 시 상품은 최대 500개 까지만 진열됩니다.</span></span>
    </div>

    <div class="table-title gd-help-manual" style="padding-top:10px;">
        선택된 테마 정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col  style="width:170px;"/>
            <col/>
            <col  style="width:170px;"/>
            <col/>
        </colgroup>
        <tr>
            <th>테마명</th>
            <td colspan="3"><span id="tbl_theme_themeNm"></span> <input type="button" value="수정" class="btn btn-sm btn-white js_tbl_theme_themeCd" data-code="" data-target="" onclick="modify_theme_popup()" /></td>
        </tr>
        <tr>
            <th >이미지 설정</th>
            <td  colspan="3" id="tbl_theme_imageCdNm">  </td>
        </tr>
        <tr>
            <th >상품 노출 개수</th>
            <td  colspan="3"  id="tbl_theme_cntNm">  </td>
        </tr>
        <tr>
            <th >품절상품 노출</th>
            <td  id="tbl_theme_soldOutFlNm">  </td>
            <th >품절상품 진열</th>
            <td  id="tbl_theme_soldOutDisplayFlNm">  </td>
        </tr>
        <tr>
            <th >품절 아이콘 노출</th>
            <td  id="tbl_theme_soldOutIconFlNm">  </td>
            <th >아이콘 노출</th>
            <td  id="tbl_theme_iconFlNm">  </td>
        </tr>
        <tr>
            <th >노출항목 설정</th>
            <td  colspan="3"  id="tbl_theme_displayFieldNm">  </td>
        </tr>
        <tr>
            <th >디스플레이 유형</th>
            <td  colspan="3"  id="tbl_theme_displayTypeNm">  </td>
        </tr>
    </table>
    <div class="js-tbl_add_goods_set" <?php if($data['sortAutoFl'] =='y') { ?>style="display:none"<?php } ?>>
    <div class="table-title gd-help-manual">
        진열상품 설정
    </div>
    <div id="tabTitle">


    </div>
    <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows">
        <thead>
        <tr id="goodsRegisteredTrArea">
            <th class="width2p"><input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="itemGoodsNo[]"/></th>
            <th class="width2p center">진열순서</th>
            <th class="width5p center">이미지</th>
            <th >상품명</th>
            <th class="width10p center">판매가</th>
            <th class="width10p center">공급사</th>
            <th class="width5p center">재고</th>
            <th class="width5p center">품절상태</th>
            <th class="width10p center">PC쇼핑몰 노출상태</th>
            <th class="width10p center">모바일쇼핑몰 노출상태</th>
        </tr>
        </thead>


        <?php
        if (count(gd_isset($data['goodsNo']))) {
            foreach ($data['goodsNo'] as $k => $goodsNoData) { ?>
            <tbody id="add_goods<?=$k?>"  <?php if($k =='0') { echo 'class="active"'; } else { echo "style=display:none"; } ?>>
                <?php if(count($goodsNoData)) {
                    ?>
                    <?php
                    $cnt = 1;
                    foreach ($goodsNoData as $key => $val) {
                        list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);
                        ?>

                        <tr id="tbl_add_goods_<?=$val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'][$k] && in_array($val['goodsNo'], array_values($data['fixGoodsNo'][$k]))) { ?>style='background:#d3d3d3' class="add_goods_fix" <?php } else { ?>class="add_goods_free" <?php } ?>>
                            <td class="center">
                                <input type="hidden" name="itemGoodsNm[]" value="<?= strip_tags($val['goodsNm']) ?>"/>
                                <input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice']) ?>"/>
                                <input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
                                <input type="hidden" name="itemTotalStock[]" value="<?= $val['totalStock'] ?>"/>
                                <input type="hidden" name="itemImage[]" value="<?= rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>"/>
                                <input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
                                <input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
                                <input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
                                <input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockFl']) ?>"/>
                                <input type="hidden" name="itemGoodsDisplayFl[]" value="<?= gd_isset($val['goodsDisplayFl']) ?>"/>
                                <input type="hidden" name="itemGoodsDisplayMobileFl[]" value="<?= gd_isset($val['goodsDisplayMobileFl']) ?>"/>
                                <input type="hidden" name="itemIcon[]" value="<?=rawurlencode(gd_isset($val['goodsIcon'])); ?>" />
                                <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?=$val['goodsNo']; ?>" value="<?=$val['goodsNo']; ?>"/>
                            </td>
                            <td class="center number addGoodsNumber_<?=$val['goodsNo']; ?>" style="width:64px;"><?= $cnt++ ?></td>
                            <td class="center"><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                            <td>
                                <a class="text-blue hand js-goods-popup" data-goodsno="<?=$val['goodsNo']; ?>"><?php echo $val['goodsNm']; ?></a>
                                <input type="hidden" name="goodsNoData[]" value="<?= $val['goodsNo'] ?>"/>
                                <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?=$val['goodsNo']; ?>" value="<?=$val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'][$k] && in_array($val['goodsNo'], $data['fixGoodsNo'][$k])) {
                                    echo "checked='true'";
                                } ?> style="display:none">
                                <div>
                                    <?= $val['goodsIcon'] ?>
                                </div>
                            </td>
                            <td class="center"><?=gd_currency_display($val['goodsPrice']); ?></td>
                            <td class="center"><?=$val['scmNm']; ?></td>
                            <td class="center"><?= $totalStock ?></td>
                            <td class="center"><?= $stockText ?></td>
                            <td class="center displayFl"><?=$val['goodsDisplayFl'] == "y" ?  "노출함" : "노출안함"; ?></td>
                            <td class="center displayFl"><?=$val['goodsDisplayMobileFl'] == "y" ?  "노출함" : "노출안함"; ?></td>
                        </tr>
                        <?php
                    } ?>

                <?php } else {  ?>
                    <tr id="tbl_add_goods_tr_none"><td colspan="11" class="no-data">선택된 상품이 없습니다.</td></tr>
                <?php } ?>
            </tbody>
            <?php }
        } else {
            ?>
            <tbody id="add_goods0" class="active">
            <tr id="tbl_add_goods_tr_none"><td colspan="11" class="no-data">선택된 상품이 없습니다.</td></tr>
            </tbody>

            </tbody>
        <?php } ?>
    </table>


    <div class="table-action">
        <div class="pull-left">
            <button class="btn btn-white checkDelete" type="button" onclick="delete_option()">선택 삭제</button>
        </div>

        <div class="pull-right">
            <button class="btn btn-white checkRegister" type="button"  onclick="layer_register('display_main')">기존 진열상품 불러오기</button>
            <button class="btn btn-white checkRegister" type="button"  onclick="goods_search_popup()">상품 선택</button>
        </div>

    </div>
    </div>

    <div class="js-tbl_add_except" <?php if($data['sortAutoFl'] =='n') { ?>style="display:none"<?php } ?>>
    <div class="table-title " >
        예외 상품 조건 설정
    </div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th >진열 예외 상품 선택</th>
                <td>
                    <span id="presentFlExcept_goods"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="goods" data-name="상품" onclick="presentExcept_conf(this.value)">예외 상품</label></span>
                    <span id="presentFlExcept_category"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="category" data-name="카테고리" onclick="presentExcept_conf(this.value)">예외 카테고리</label></span>
                    <span id="presentFlExcept_brand"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="brand" data-name="브랜드" onclick="presentExcept_conf(this.value)">예외 브랜드</label></span>
                    <span id="presentFlExcept_brand"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="scm" data-name="공급사" onclick="presentExcept_conf(this.value)">예외 공급사</label></span>
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
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:691px;">
                                <table  class="table table-cols" style="padding:0px;margin:0px;">
                                    <colgroup><col class="width5p" /><col class="width10p" /><col /><col class="width8p" /></colgroup>
                                    <tbody id="exceptGoods">
                                    <?php
                                    if (is_array($data['exceptGoodsNo'])) {
                                        foreach ($data['exceptGoodsNo'] as $key => $val) {
                                            echo '<tr id="idExceptGoods_'.$val['goodsNo'].'">'.chr(10);
                                            echo '<td class="width5p center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                            echo '<td  class="width10p center">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                            echo '<td><a href="../goods/goods_register.php?goodsNo='.$val['goodsNo'].'" target="_blank">'.$val['goodsNm'].'</a></td>'.chr(10);
                                            echo '<td  class="width8p center"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\'idExceptGoods_'.$val['goodsNo'].'\');" value="삭제" /></td>'.chr(10);
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                </div>
                            </td>
                        </tr>
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
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:450px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col /><col class="width8p" /></colgroup>
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
                                        </table>
                                    </div>
                             </td>
                            </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptCateCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptCategory').html('');"></td></tr>
                        </tfoot>
                    </table>

                </td>

            </tr>

            <tr id="presentFlExcept_brand_tbl" style="display:none">
                <th>예외 브랜드
                    <div><input type="button"  class="btn btn-sm btn-gray" value="브랜드 선택" onclick="layer_register('brand','except');" /></div>
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
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:450px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col /><col class="width8p" /></colgroup>
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
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptBrandCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptBrand').html('');"></td></tr>
                        </tfoot>
                    </table>

                </td>

            </tr>

            <tr id="presentFlExcept_scm_tbl" style="display:none">
                <th>예외 공급사
                    <div><input type="button"  class="btn btn-sm btn-gray" value="공급사 선택" onclick="layer_register('scm','except');" /></div>
                </th>
                <td>
                    <table id="exceptScmTable"class="table table-cols" style="width:80%">
                        <thead <?php if (is_array($data['exceptScmNo']) == false)  { echo "style='display:none'"; } ?>>
                        <tr>
                            <th class="width5p">번호</th>
                            <th>공급사</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:450px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col /><col class="width8p" /></colgroup>
                                            <tbody id="exceptScm" >
                                            <?php
                                            if (is_array($data['exceptScmNo'])) {
                                                foreach ($data['exceptScmNo']as $key => $val) {
                                                    echo '<tr id="idExceptScm_'.$val['scmNo'].'">'.chr(10);
                                                    echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptScm[]" value="'.$val['scmNo'].'" /></td>'.chr(10);
                                                    echo '<td>'.$val['companyNm'].'</td>'.chr(10);
                                                    echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptScm_'.$val['scmNo'].'\');" value="삭제" /></td>'.chr(10);
                                                    echo '</tr>'.chr(10);
                                                }
                                            }
                                            ?>
                                            </tbody>
                                         </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptScmNo']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptScm').html('');"></td></tr>
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
    </div>

</form>
<script>
    $( ".js-goods-popup" ).click(function() {
        goods_register_popup($(this).data('goodsno'));
    });
</script>

