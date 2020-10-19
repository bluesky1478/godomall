<?php
//펼침,닫힘 정보
$toggle = gd_policy('display.toggle');
$SessScmNo = Session::get('manager.scmNo');
?>
<script type="text/javascript">
    <!--
    $(document).ready(function () {

        viewThemeConfig('tbl_pcThemeInfo',$('select[name="goods[pcThemeCd]"]').val());
        viewThemeConfig('tbl_mobileThemeInfo',$('select[name="goods[mobileThemeCd]"]').val());

        <?php if(gd_isset($data['quick']['quickFl']) =='y') {?>display_switch('tr_quick_show');<?php } ?>
        <?php if(gd_isset($data['keyword']['keywordFl']) =='y') {?>display_switch('tr_keyword_show');<?php } ?>

        initDepthToggle(<?=$SessScmNo?>);//4depth 메뉴 보임안보임처리
    });

    /**
     * 테마보기
     */
    function viewThemeConfig(tbl,themeCd) {

        var parameters = {
            'mode': 'theme_ajax',
            'themeCd': themeCd
        };

        $.post("../goods/display_config_ps.php",parameters,
            function(data){
                $("#"+tbl+" .js_tbl_theme_themeCd").data('code',data.themeCd);
                $.each(data, function(i,item){
                    $("#"+tbl+" .tbl_theme_"+i).html(item);
                });
            }, "json");
    }

    /**
     * 테마 입력
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function add_theme_popup(mobileFl)
    {
        if(mobileFl =="n") var addTheme = "goods[pcThemeCd]";
        else var addTheme = "goods[mobileThemeCd]";
        window.open('../goods/display_config_theme_register.php?popupMode=yes&themeCate=A&addTheme='+addTheme+'&mobileFl='+mobileFl, 'member_crm', 'width=1210, height=700, scrollbars=yes');

    };

    /**
     * 테마 수정
     *
     * @param string themeCd 테마코드
     */
    function modify_theme_popup(val)
    {
        var themeCd = $(val).data('code');
        var addTheme = $(val).data('target');

        window.open('../goods/display_config_theme_register.php?popupMode=yes&addTheme='+addTheme+'&callFunc=update_theme_info&themeCd='+themeCd, 'theme_popup', 'width=1210, height=700, scrollbars=yes');
    };

    /**
     * 테마 수정 정보 업데이트
     *
     * @param string themeCd 테마코드
     * @param string themeNm 테마명
     */
    function update_theme_info(themeCd,themeNm,target)
    {
        viewThemeConfig("tbl_"+target+"Info",themeCd);
        $('select[name="goods['+target+'Cd]"] option:selected').text(themeNm);
    };


    function display_switch(prefix)
    {
        if($('.'+prefix).is(':hidden')) $('.'+prefix).show();
        else $('.'+prefix).hide();


    }

    function field_remove(fieldID,fieldNo) {
        $("#"+fieldID+fieldNo).remove();
        if($("tr[id*='"+fieldID+"']").length  == 0)  $("#"+fieldID+" thead").hide();
    }


    //-->
</script>
<form id="frmGoods" name="frmGoods"  target ="ifrmProcess" action="./display_ps.php" method="post"
      enctype="multipart/form-data">
    <input type="hidden" name="mode" value="search_register"/>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location);?> </h3>
        <div class="btn-group">
            <input type="submit"   value="저장" class="btn btn-red" />
        </div>
    </div>


    <div class="table-title gd-help-manual">
        기본정보
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="searchInfo"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-searchInfo" value="<?=$toggle['searchInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-searchInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-searchInfo">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class="width-3xl"/>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th >진열방법 선택</th>
                <td class="input_area" colspan="3" > <div class="form-inline">
                    <?=gd_select_box('goods[sort]', 'goods[sort]', $data['set']['sortList'], null, $data['goods']['sort'], null); ?></div>
                </td>
            </tr>
            <tr>
                <th >검색조건 선택</th>
                <td class="input_area" colspan="3">
                    <input type="hidden" name="goods[searchType][]" value="keyword">
                    <?php foreach($data['set']['searchType'] as $k => $v) { ?>
                        <label class="checkbox-inline"><input type="checkbox" name="goods[searchType][]" value="<?=$k?>"  <?=gd_isset($checked['goods']['searchType'][$k]); ?>   <?php if($k =='keyword')  { echo "disabled='true'"; } ?> > <?=$v?></label>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>PC쇼핑몰 테마선택</th>
                <td class="input_area" ><div class="form-inline">
                        <select name="goods[pcThemeCd]" onchange="viewThemeConfig('tbl_pcThemeInfo',this.value);" class="form-control">
                            <?php foreach($data['set']['pcThemeList'] as $k => $v) { ?>
                                <option value="<?=$v['themeCd']?>" <?=gd_isset($selected['goods']['pcThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
                            <?php } ?>
                        </select>
                        <input type="button" class="btn btn-sm btn-black" value="테마 등록" onclick="add_theme_popup('n')" /></div>
                </td>
                <th>모바일쇼핑몰 테마선택</th>
                <td class="input_area" ><div class="form-inline">
                        <select name="goods[mobileThemeCd]" onchange="viewThemeConfig('tbl_mobileThemeInfo',this.value);" class="form-control">
                            <?php foreach($data['set']['mobileThemeList'] as $k => $v) { ?>
                                <option value="<?=$v['themeCd']?>" <?=gd_isset($selected['goods']['mobileThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
                            <?php } ?>
                        </select>
                        <input type="button" class="btn btn-sm btn-black" value="테마 등록" onclick="add_theme_popup('y')" /></div>
                </td>
            </tr>
        </table>
    </div>


    <div class="table-title">
        <span class="gd-help-manual">선택된 PC쇼핑몰 테마 정보</span>
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="pcTemaInfo"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-pcTemaInfo" value="<?=$toggle['pcTemaInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-pcTemaInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-pcTemaInfo">
        <table class="table table-cols" id="tbl_pcThemeInfo">
            <colgroup>
                <col class="width-md"/>
                <col class="width-3xl"/>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th >테마명</th>
                <td  colspan="3"><span class="tbl_theme_themeNm"></span> <input type="button" value="수정" class="btn btn-sm btn-white js_tbl_theme_themeCd" data-code="" data-target="pcTheme" onclick="modify_theme_popup(this)"  /></td>
            </tr>
            <tr>
                <th >이미지 설정</th>
                <td  colspan="3" class="tbl_theme_imageCdNm">  </td>
            </tr>
            <tr>
                <th >상품 노출 개수</th>
                <td  colspan="3"  class="tbl_theme_cntNm">  </td>
            </tr>
            <tr>
                <th >품절상품 노출</th>
                <td  class="tbl_theme_soldOutFlNm">  </td>
                <th >품절상품 진열</th>
                <td  class="tbl_theme_soldOutDisplayFlNm">  </td>
            </tr>
            <tr>
                <th >품절 아이콘 노출</th>
                <td  class="tbl_theme_soldOutIconFlNm">  </td>
                <th >아이콘 노출</th>
                <td  class="tbl_theme_iconFlNm">  </td>
            </tr>
            <tr>
                <th >노출항목 설정</th>
                <td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
            </tr>
            <tr>
                <th >디스플레이 유형</th>
                <td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
            </tr>
        </table>
    </div>


    <div class="table-title">
        <span class="gd-help-manual">선택된 모바일쇼핑몰 테마 정보</span>
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="mobileThemeInfo"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-mobileThemeInfo" value="<?=$toggle['mobileThemeInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-mobileThemeInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-mobileThemeInfo">
        <table class="table table-cols" id="tbl_mobileThemeInfo">
            <colgroup>
                <col class="width-md"/>
                <col class="width-3xl"/>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th >테마명</th>
                <td  colspan="3"><span class="tbl_theme_themeNm"></span> <input type="button" value="수정" class="btn btn-sm btn-white js_tbl_theme_themeCd" data-code="" data-target="mobileTheme" onclick="modify_theme_popup(this)"  /></td>
            </tr>
            <tr>
                <th >이미지 설정</th>
                <td  colspan="3" class="tbl_theme_imageCdNm">  </td>
            </tr>
            <tr>
                <th >상품 노출 개수</th>
                <td  colspan="3"  class="tbl_theme_cntNm">  </td>
            </tr>
            <tr>
                <th >품절상품 노출</th>
                <td  class="tbl_theme_soldOutFlNm">  </td>
                <th >품절상품 진열</th>
                <td  class="tbl_theme_soldOutDisplayFlNm">  </td>
            </tr>
            <tr>
                <th >품절 아이콘 노출</th>
                <td  class="tbl_theme_soldOutIconFlNm">  </td>
                <th >아이콘 노출</th>
                <td  class="tbl_theme_iconFlNm">  </td>
            </tr>
            <tr>
                <th >노출항목 설정</th>
                <td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
            </tr>
            <tr>
                <th >디스플레이 유형</th>
                <td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
            </tr>
        </table>
    </div>

    <?php if($msgFl) { ?>
    <div>
        <div class="notice-danger">기존 검색페이지 진열관리에 존재하였던 검색창 관련 설정 항목들은 보다 효율적인 검색창 관리를 하실 수 있도록 별도의 메뉴로 이동되었습니다.</div>
        <div><span class="info_gray">메뉴 이동 위치</span> : <a href="search_settings.php" target="_blank" class="btn-link">상품 > 상품 노출형태 관리 >  검색창 관련 설정</a></div>
    </div>
    <?php } ?>
</form>
